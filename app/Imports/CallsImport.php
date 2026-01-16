<?php

namespace App\Imports;

use App\Http\Requests\StoreCallRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class CallsImport implements SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    /**
     * Whether this is a dry-run (validation only, no saving).
     */
    protected bool $dryRun;

    /**
     * User ID performing the import.
     */
    protected ?int $userId;

    /**
     * Collection of processed calls.
     */
    protected Collection $processedCalls;

    /**
     * Collection of errors by row.
     */
    protected Collection $rowErrors;

    /**
     * Create a new import instance.
     */
    public function __construct(bool $dryRun = false, ?int $userId = null)
    {
        $this->dryRun = $dryRun;
        $this->userId = $userId ?? Auth::id();
        $this->processedCalls = collect();
        $this->rowErrors = collect();
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
                    $this->processedCalls->push([
                        'row' => $rowNumber,
                        'data' => $validated,
                        'status' => 'valid',
                    ]);
                } else {
                    // Ensure created_by and updated_by are included (they may be filtered out by validation)
                    $validated['created_by'] = $this->userId;
                    $validated['updated_by'] = $this->userId;

                    // Create the call
                    $call = Call::create($validated);
                    $this->processedCalls->push([
                        'row' => $rowNumber,
                        'call' => $call,
                        'status' => 'created',
                    ]);
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

        // WithHeadingRow converts headers to snake_case, removing special characters
        // "Año Académico" becomes "ano_academico", "Número de Plazas" becomes "numero_de_plazas"
        $programa = $row['programa'] ?? $row['program'] ?? null;
        $anioAcademico = $row['ano_academico'] ?? $row['año_academico'] ?? $row['anio_academico'] ?? $row['año_académico'] ?? $row['academic_year'] ?? null;

        // Map programa to program_id
        if ($programa) {
            $program = $this->findProgram($programa);
            if ($program) {
                $data['program_id'] = $program->id;
            } else {
                throw new \Exception(__('El programa ":programa" no existe.', ['programa' => $programa]));
            }
        }

        // Map año_academico to academic_year_id
        if ($anioAcademico) {
            $academicYear = $this->findAcademicYear($anioAcademico);
            if ($academicYear) {
                $data['academic_year_id'] = $academicYear->id;
            } else {
                throw new \Exception(__('El año académico ":year" no existe.', ['year' => $anioAcademico]));
            }
        }

        // Map titulo to title (handle both Spanish and English headers)
        $titulo = $row['titulo'] ?? $row['title'] ?? null;
        if ($titulo) {
            $data['title'] = trim($titulo);
        }

        // Map slug (optional, will be generated if empty)
        $slug = $row['slug'] ?? null;
        if ($slug && ! empty(trim($slug))) {
            $data['slug'] = Str::slug(trim($slug));
        }

        // Map tipo to type
        $tipo = $row['tipo'] ?? $row['type'] ?? null;
        if ($tipo) {
            $data['type'] = strtolower(trim($tipo));
        }

        // Map modalidad to modality
        $modalidad = $row['modalidad'] ?? $row['modality'] ?? null;
        if ($modalidad) {
            $data['modality'] = strtolower(trim($modalidad));
        }

        // Map numero_plazas to number_of_places
        // "Número de Plazas" becomes "numero_de_plazas" (with underscore)
        $numeroPlazas = $row['numero_de_plazas'] ?? $row['numero_plazas'] ?? $row['number_of_places'] ?? null;
        if ($numeroPlazas !== null) {
            $data['number_of_places'] = (int) $numeroPlazas;
        }

        // Map destinos to destinations (array)
        $destinos = $row['destinos'] ?? $row['destinations'] ?? null;
        if ($destinos) {
            $destinations = $this->parseDestinations($destinos);
            $data['destinations'] = $destinations;
        }

        // Map fecha_inicio_estimada to estimated_start_date
        // "Fecha Inicio Estimada" becomes "fecha_inicio_estimada"
        $fechaInicio = $row['fecha_inicio_estimada'] ?? $row['estimated_start_date'] ?? null;
        if ($fechaInicio && ! empty(trim($fechaInicio))) {
            $data['estimated_start_date'] = $this->parseDate($fechaInicio);
        }

        // Map fecha_fin_estimada to estimated_end_date
        // "Fecha Fin Estimada" becomes "fecha_fin_estimada"
        $fechaFin = $row['fecha_fin_estimada'] ?? $row['estimated_end_date'] ?? null;
        if ($fechaFin && ! empty(trim($fechaFin))) {
            $data['estimated_end_date'] = $this->parseDate($fechaFin);
        }

        // Map requisitos to requirements
        $requisitos = $row['requisitos'] ?? $row['requirements'] ?? null;
        if ($requisitos && ! empty(trim($requisitos))) {
            $data['requirements'] = trim($requisitos);
        }

        // Map documentacion to documentation
        $documentacion = $row['documentacion'] ?? $row['documentation'] ?? null;
        if ($documentacion && ! empty(trim($documentacion))) {
            $data['documentation'] = trim($documentacion);
        }

        // Map criterios_seleccion to selection_criteria
        // "Criterios de Selección" becomes "criterios_de_seleccion"
        $criterios = $row['criterios_de_seleccion'] ?? $row['criterios_seleccion'] ?? $row['selection_criteria'] ?? null;
        if ($criterios && ! empty(trim($criterios))) {
            $data['selection_criteria'] = trim($criterios);
        }

        // Map estado to status
        $estado = $row['estado'] ?? $row['status'] ?? null;
        if ($estado && ! empty(trim($estado))) {
            $data['status'] = strtolower(trim($estado));
        }

        // Map fecha_publicacion to published_at
        // "Fecha Publicación" becomes "fecha_publicacion"
        $fechaPublicacion = $row['fecha_publicacion'] ?? $row['published_at'] ?? null;
        if ($fechaPublicacion && ! empty(trim($fechaPublicacion))) {
            $data['published_at'] = $this->parseDate($fechaPublicacion);
        }

        // Map fecha_cierre to closed_at
        $fechaCierre = $row['fecha_cierre'] ?? $row['closed_at'] ?? null;
        if ($fechaCierre && ! empty(trim($fechaCierre))) {
            $data['closed_at'] = $this->parseDate($fechaCierre);
        }

        // Set created_by and updated_by
        $data['created_by'] = $this->userId;
        $data['updated_by'] = $this->userId;

        // Generate slug if not provided
        if (empty($data['slug']) && ! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }

    /**
     * Validate row data using StoreCallRequest rules.
     */
    protected function validateRow(array $data, int $rowNumber): array
    {
        $rules = (new StoreCallRequest)->rules();
        $messages = (new StoreCallRequest)->messages();

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    /**
     * Find program by code or name.
     */
    protected function findProgram(string $value): ?Program
    {
        $value = trim($value);

        // Try to find by code first
        $program = Program::where('code', $value)->first();

        // If not found, try by name
        if (! $program) {
            $program = Program::where('name', 'like', "%{$value}%")->first();
        }

        return $program;
    }

    /**
     * Find academic year by year value.
     */
    protected function findAcademicYear(string|int $value): ?AcademicYear
    {
        $value = trim((string) $value);

        // Try to find by year (integer)
        if (is_numeric($value)) {
            $academicYear = AcademicYear::where('year', (int) $value)->first();
            if ($academicYear) {
                return $academicYear;
            }
        }

        // Try to find by year as string
        $academicYear = AcademicYear::where('year', $value)->first();

        return $academicYear;
    }

    /**
     * Parse destinations string to array.
     */
    protected function parseDestinations(string $destinations): array
    {
        // Split by comma or semicolon
        $destinations = preg_split('/[,;]/', $destinations);
        $destinations = array_map('trim', $destinations);
        $destinations = array_filter($destinations); // Remove empty values

        return array_values($destinations);
    }

    /**
     * Parse date from various formats.
     */
    protected function parseDate(string|int|float $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // If it's a numeric value (Excel date serial number), convert it
        if (is_numeric($date)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);

                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                // If conversion fails, try as string
            }
        }

        // Try to parse as date string
        $dateString = trim((string) $date);

        // Try different date formats
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'd.m.Y',
        ];

        foreach ($formats as $format) {
            try {
                $parsed = \DateTime::createFromFormat($format, $dateString);
                if ($parsed && $parsed->format($format) === $dateString) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon as last resort
        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception(__('Formato de fecha inválido: :date', ['date' => $dateString]));
        }
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
     * Get processed calls.
     */
    public function getProcessedCalls(): Collection
    {
        return $this->processedCalls;
    }

    /**
     * Get row errors.
     */
    public function getRowErrors(): Collection
    {
        return $this->rowErrors;
    }

    /**
     * Get total imported count.
     */
    public function getImportedCount(): int
    {
        return $this->processedCalls->where('status', 'created')->count();
    }

    /**
     * Get total validated count (for dry-run).
     */
    public function getValidatedCount(): int
    {
        return $this->processedCalls->where('status', 'valid')->count();
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
