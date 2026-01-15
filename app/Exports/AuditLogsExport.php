<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Activitylog\Models\Activity;

class AuditLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Filters to apply to the export.
     *
     * @var array<string, mixed>
     */
    protected array $filters;

    /**
     * Create a new export instance.
     *
     * @param  array<string, mixed>  $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Get the collection to export.
     */
    public function collection(): Collection
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->filters['search'] ?? null, function ($query) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['filterModel'] ?? null, function ($query) {
                $query->where('subject_type', $this->filters['filterModel']);
            })
            ->when($this->filters['filterCauserId'] ?? null, function ($query) {
                $query->where('causer_id', $this->filters['filterCauserId'])
                    ->where('causer_type', User::class);
            })
            ->when($this->filters['filterDescription'] ?? null, function ($query) {
                $query->where('description', $this->filters['filterDescription']);
            })
            ->when($this->filters['filterLogName'] ?? null, function ($query) {
                $query->where('log_name', $this->filters['filterLogName']);
            })
            ->when($this->filters['filterDateFrom'] ?? null, function ($query) {
                $query->whereDate('created_at', '>=', $this->filters['filterDateFrom']);
            })
            ->when($this->filters['filterDateTo'] ?? null, function ($query) {
                $query->whereDate('created_at', '<=', $this->filters['filterDateTo']);
            })
            ->orderBy($this->filters['sortField'] ?? 'created_at', $this->filters['sortDirection'] ?? 'desc')
            ->orderBy('id', 'desc');

        return $query->get();
    }

    /**
     * Define the headings for the export.
     *
     * @return array<string>
     */
    public function headings(): array
    {
        return [
            __('ID'),
            __('Fecha/Hora'),
            __('Usuario'),
            __('Email Usuario'),
            __('Acción'),
            __('Modelo'),
            __('ID Registro'),
            __('Registro'),
            __('Log Name'),
            __('Cambios'),
        ];
    }

    /**
     * Map each activity to a row.
     *
     * @return array<mixed>
     */
    public function map($activity): array
    {
        $changes = $this->formatChangesSummary($activity->properties);

        return [
            $activity->id,
            $activity->created_at->format('d/m/Y H:i:s'),
            $activity->causer?->name ?? __('common.messages.system'),
            $activity->causer?->email ?? '-',
            $this->getDescriptionDisplayName($activity->description),
            $this->getModelDisplayName($activity->subject_type),
            $activity->subject_id ?? '-',
            $this->getSubjectTitle($activity->subject),
            $activity->log_name ?? __('default'),
            $changes,
        ];
    }

    /**
     * Get the title for the sheet.
     */
    public function title(): string
    {
        return __('Logs de Auditoría');
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Get model display name.
     */
    protected function getModelDisplayName(?string $subjectType): string
    {
        if (! $subjectType) {
            return '-';
        }

        return match ($subjectType) {
            'App\Models\Program' => __('Programa'),
            'App\Models\Call' => __('Convocatoria'),
            'App\Models\NewsPost' => __('Noticia'),
            'App\Models\Document' => __('Documento'),
            'App\Models\ErasmusEvent' => __('Evento'),
            'App\Models\AcademicYear' => __('Año Académico'),
            'App\Models\DocumentCategory' => __('Categoría de Documento'),
            'App\Models\NewsTag' => __('Etiqueta de Noticia'),
            'App\Models\CallPhase' => __('Fase de Convocatoria'),
            'App\Models\Resolution' => __('Resolución'),
            default => class_basename($subjectType),
        };
    }

    /**
     * Get description display name.
     */
    protected function getDescriptionDisplayName(string $description): string
    {
        return match (strtolower($description)) {
            'created' => __('Creado'),
            'updated' => __('Actualizado'),
            'deleted' => __('Eliminado'),
            'publish' => __('Publicado'),
            'published' => __('Publicado'),
            'archive' => __('Archivado'),
            'archived' => __('Archivado'),
            'restore' => __('Restaurado'),
            'restored' => __('Restaurado'),
            default => ucfirst($description),
        };
    }

    /**
     * Get subject title for display.
     */
    protected function getSubjectTitle($subject): string
    {
        if (! $subject) {
            return '-';
        }

        if (isset($subject->title)) {
            return $subject->title;
        }

        if (isset($subject->name)) {
            return $subject->name;
        }

        return __('Registro #:id', ['id' => $subject->id ?? '-']);
    }

    /**
     * Format changes summary from properties.
     */
    protected function formatChangesSummary(array|\Illuminate\Support\Collection|null $properties): string
    {
        if (! $properties) {
            return '-';
        }

        // Convert Collection to array if needed
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        $changes = [];

        if (isset($properties['old']) && isset($properties['attributes'])) {
            foreach ($properties['old'] as $key => $oldValue) {
                $newValue = $properties['attributes'][$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[] = $key;
                }
            }
        }

        if (empty($changes)) {
            return __('Sin cambios');
        }

        return implode(', ', array_slice($changes, 0, 10)).(count($changes) > 10 ? '...' : '');
    }
}
