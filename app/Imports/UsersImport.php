<?php

namespace App\Imports;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Spatie\Permission\Models\Role;

class UsersImport implements SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    /**
     * Whether this is a dry-run (validation only, no saving).
     */
    protected bool $dryRun;

    /**
     * Whether to send emails with generated passwords.
     */
    protected bool $sendEmails;

    /**
     * Collection of processed users.
     */
    protected Collection $processedUsers;

    /**
     * Collection of errors by row.
     */
    protected Collection $rowErrors;

    /**
     * Collection of users with generated passwords (for email notification).
     */
    protected Collection $usersWithPasswords;

    /**
     * Create a new import instance.
     */
    public function __construct(bool $dryRun = false, bool $sendEmails = false)
    {
        $this->dryRun = $dryRun;
        $this->sendEmails = $sendEmails;
        $this->processedUsers = collect();
        $this->rowErrors = collect();
        $this->usersWithPasswords = collect();
    }

    /**
     * Process the collection of rows.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index is 0-based and we skip header row

            try {
                // Convert row array to associative array with snake_case keys
                $data = $this->mapRowToData($row);

                // Validate the row data
                $validated = $this->validateRow($data, $rowNumber);

                // If dry-run, just collect validated data without saving
                if ($this->dryRun) {
                    $this->processedUsers->push([
                        'row' => $rowNumber,
                        'data' => $validated,
                        'status' => 'valid',
                    ]);
                } else {
                    // Create the user
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                    ]);

                    // Assign roles if provided
                    if (! empty($validated['roles'])) {
                        $roles = $this->parseRoles($validated['roles']);
                        $user->syncRoles($roles);
                    }

                    $userData = [
                        'row' => $rowNumber,
                        'user' => $user,
                        'status' => 'created',
                    ];

                    // Store generated password if it was auto-generated
                    if (isset($validated['generated_password'])) {
                        $userData['generated_password'] = $validated['generated_password'];
                        $this->usersWithPasswords->push([
                            'user' => $user,
                            'password' => $validated['generated_password'],
                        ]);
                    }

                    $this->processedUsers->push($userData);
                }
            } catch (ValidationException $e) {
                // Collect validation errors
                $this->rowErrors->push([
                    'row' => $rowNumber,
                    'errors' => $e->errors(),
                    'data' => $row->toArray(),
                ]);
            } catch (\Exception $e) {
                // Collect other errors
                $this->rowErrors->push([
                    'row' => $rowNumber,
                    'errors' => ['general' => [$e->getMessage()]],
                    'data' => $row->toArray(),
                ]);
            }
        }
    }

    /**
     * Map row data from Excel format to database format.
     */
    protected function mapRowToData(Collection $row): array
    {
        $data = [];

        // Map nombre to name
        $nombre = $row['nombre'] ?? $row['name'] ?? null;
        if ($nombre) {
            $data['name'] = trim($nombre);
        }

        // Map email
        $email = $row['email'] ?? null;
        if ($email) {
            $data['email'] = strtolower(trim($email));
        }

        // Map contraseña to password (optional - will be generated if empty)
        $password = $row['contraseña'] ?? $row['password'] ?? null;
        if ($password && ! empty(trim($password))) {
            $data['password'] = trim($password);
            $data['password_confirmation'] = $data['password']; // For validation
        } else {
            // Generate random password
            $generatedPassword = Str::random(12);
            $data['password'] = $generatedPassword;
            $data['password_confirmation'] = $generatedPassword;
            $data['generated_password'] = $generatedPassword; // Flag to know it was generated
        }

        // Map roles (comma-separated string)
        $roles = $row['roles'] ?? null;
        if ($roles && ! empty(trim($roles))) {
            $data['roles'] = $this->parseRolesString($roles);
        }

        return $data;
    }

    /**
     * Validate row data using StoreUserRequest rules.
     */
    protected function validateRow(array $data, int $rowNumber): array
    {
        // Custom validation rules for import (password_confirmation is handled internally)
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $messages = (new StoreUserRequest)->messages();

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    /**
     * Parse roles string (comma-separated) to array.
     */
    protected function parseRolesString(string $rolesString): array
    {
        // Split by comma or semicolon
        $roles = preg_split('/[,;]/', $rolesString);
        $roles = array_map('trim', $roles);
        $roles = array_map('strtolower', $roles);
        $roles = array_filter($roles); // Remove empty values

        // Validate roles exist
        $validRoles = [];
        foreach ($roles as $role) {
            if (in_array($role, Roles::all(), true)) {
                $validRoles[] = $role;
            }
        }

        return array_values($validRoles);
    }

    /**
     * Parse roles array and ensure they exist in database.
     */
    protected function parseRoles(array $roles): array
    {
        $validRoles = [];

        foreach ($roles as $role) {
            // Check if role exists in database
            $roleModel = Role::where('name', $role)->first();
            if ($roleModel) {
                $validRoles[] = $role;
            }
        }

        return $validRoles;
    }

    /**
     * Define validation rules for the import.
     */
    public function rules(): array
    {
        // Return empty array - we'll validate manually in validateRow()
        // This is required by WithValidation interface but we handle validation ourselves
        return [];
    }

    /**
     * Get processed users.
     */
    public function getProcessedUsers(): Collection
    {
        return $this->processedUsers;
    }

    /**
     * Get row errors.
     */
    public function getRowErrors(): Collection
    {
        return $this->rowErrors;
    }

    /**
     * Get users with generated passwords.
     */
    public function getUsersWithPasswords(): Collection
    {
        return $this->usersWithPasswords;
    }

    /**
     * Get total imported count.
     */
    public function getImportedCount(): int
    {
        return $this->processedUsers->where('status', 'created')->count();
    }

    /**
     * Get total validated count (for dry-run).
     */
    public function getValidatedCount(): int
    {
        return $this->processedUsers->where('status', 'valid')->count();
    }

    /**
     * Get total failed count.
     */
    public function getFailedCount(): int
    {
        return $this->rowErrors->count();
    }

    /**
     * Handle validation failure.
     */
    public function onFailure(Failure ...$failures): void
    {
        // This method is called for each row that fails validation
        // We're already handling errors in collection() method, so this is a fallback
        foreach ($failures as $failure) {
            $this->rowErrors->push([
                'row' => $failure->row(),
                'errors' => $failure->errors(),
                'data' => $failure->values(),
            ]);
        }
    }
}
