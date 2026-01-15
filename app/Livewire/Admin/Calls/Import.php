<?php

namespace App\Livewire\Admin\Calls;

use App\Exports\CallsTemplateExport;
use App\Imports\CallsImport;
use App\Models\Call;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Import extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    /**
     * Excel/CSV file to import.
     */
    public ?UploadedFile $file = null;

    /**
     * Dry-run mode (validate only, don't save).
     */
    public bool $dryRun = false;

    /**
     * Import results.
     */
    public ?array $results = null;

    /**
     * Whether import is processing.
     */
    public bool $isProcessing = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', Call::class);
    }

    /**
     * Download template Excel file.
     */
    public function downloadTemplate()
    {
        $this->authorize('create', Call::class);

        $filename = 'plantilla-convocatorias-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new CallsTemplateExport, $filename);
    }

    /**
     * Validate uploaded file (called by Filepond).
     * The $response parameter is the temporary path returned by Livewire's upload() method.
     */
    public function validateUploadedFile(string $response): bool
    {
        // For single file uploads, validate the file directly
        if (! $this->file instanceof UploadedFile) {
            return false;
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['file' => $this->file],
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls,csv',
                    'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,application/csv,text/plain',
                    'max:10240', // 10MB max in KB
                ],
            ],
            [
                'file.required' => __('El archivo es obligatorio.'),
                'file.file' => __('Debe ser un archivo válido.'),
                'file.mimes' => __('El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv).'),
                'file.mimetypes' => __('El tipo de archivo no es válido. Debe ser Excel (.xlsx, .xls) o CSV (.csv).'),
                'file.max' => __('El archivo no puede ser mayor de :max KB.', ['max' => 10240]),
            ]
        );

        if ($validator->fails()) {
            // Log validation errors for debugging
            \Log::debug('File validation failed', [
                'errors' => $validator->errors()->all(),
                'file_name' => $this->file->getClientOriginalName(),
                'file_size' => $this->file->getSize(),
                'mime_type' => $this->file->getMimeType(),
            ]);

            return false;
        }

        // Reset results when new file is selected
        $this->results = null;

        return true;
    }

    /**
     * Import the file.
     */
    public function import(): void
    {
        $this->authorize('create', Call::class);

        // Validate file
        $this->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ], [
            'file.required' => __('El archivo es obligatorio.'),
            'file.file' => __('Debe ser un archivo válido.'),
            'file.mimes' => __('El archivo debe ser Excel (.xlsx, .xls) o CSV (.csv).'),
            'file.max' => __('El archivo no puede ser mayor de :max KB.', ['max' => 10240]),
        ]);

        $this->isProcessing = true;

        try {
            // Create import instance
            $import = new CallsImport($this->dryRun);

            // Import the file
            Excel::import($import, $this->file);

            // Get results
            $this->results = [
                'imported' => $this->dryRun ? $import->getValidatedCount() : $import->getImportedCount(),
                'failed' => $import->getFailedCount(),
                'errors' => $import->getRowErrors()->map(function ($error) {
                    return [
                        'row' => $error['row'],
                        'errors' => collect($error['errors'])->flatten()->toArray(),
                        'data' => $error['data'] ?? [],
                    ];
                })->toArray(),
                'dry_run' => $this->dryRun,
            ];

            // Show success message
            if ($this->dryRun) {
                $message = __('Se validaron :count registros correctamente.', [
                    'count' => $this->results['imported'],
                ]);
            } else {
                $message = __('Se importaron :count convocatorias correctamente.', [
                    'count' => $this->results['imported'],
                ]);
            }

            if ($this->results['failed'] > 0) {
                $message .= ' '.__('Se encontraron :count errores.', [
                    'count' => $this->results['failed'],
                ]);
            }

            $this->dispatch('import-completed', [
                'message' => $message,
                'title' => $this->dryRun ? __('Validación completada') : __('Importación completada'),
            ]);
        } catch (\Exception $e) {
            $this->results = [
                'imported' => 0,
                'failed' => 0,
                'errors' => [
                    [
                        'row' => 0,
                        'errors' => [__('Error al procesar el archivo: :error', ['error' => $e->getMessage()])],
                        'data' => [],
                    ],
                ],
                'dry_run' => $this->dryRun,
            ];

            $this->dispatch('import-error', [
                'message' => __('Error al importar el archivo: :error', ['error' => $e->getMessage()]),
                'title' => __('Error de importación'),
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Reset the form.
     */
    public function resetForm(): void
    {
        $this->reset(['file', 'dryRun', 'results', 'isProcessing']);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.import')
            ->layout('components.layouts.app', [
                'title' => __('Importar Convocatorias'),
            ]);
    }
}
