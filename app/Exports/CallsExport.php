<?php

namespace App\Exports;

use App\Models\Call;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CallsExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, WithTitle
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
     * Get the query to export (uses chunking for memory efficiency).
     */
    public function query(): Builder
    {
        return Call::query()
            ->when(($this->filters['showDeleted'] ?? '0') === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when(($this->filters['showDeleted'] ?? '0') === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->filters['filterProgram'] ?? null, fn ($query) => $query->where('program_id', $this->filters['filterProgram']))
            ->when($this->filters['filterAcademicYear'] ?? null, fn ($query) => $query->where('academic_year_id', $this->filters['filterAcademicYear']))
            ->when($this->filters['filterType'] ?? null, fn ($query) => $query->where('type', $this->filters['filterType']))
            ->when($this->filters['filterModality'] ?? null, fn ($query) => $query->where('modality', $this->filters['filterModality']))
            ->when($this->filters['filterStatus'] ?? null, fn ($query) => $query->where('status', $this->filters['filterStatus']))
            ->when($this->filters['search'] ?? null, function ($query) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->with(['program', 'academicYear', 'creator', 'updater'])
            ->orderBy($this->filters['sortField'] ?? 'created_at', $this->filters['sortDirection'] ?? 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Chunk size for reading (memory optimization).
     */
    public function chunkSize(): int
    {
        return 500;
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
            __('Título'),
            __('Programa'),
            __('Año Académico'),
            __('Tipo'),
            __('Modalidad'),
            __('Número de Plazas'),
            __('Destinos'),
            __('Fecha Inicio Estimada'),
            __('Fecha Fin Estimada'),
            __('Estado'),
            __('Fecha Publicación'),
            __('Fecha Cierre'),
            __('Creador'),
            __('Fecha Creación'),
            __('Fecha Actualización'),
        ];
    }

    /**
     * Map each call to a row.
     *
     * @param  Call  $call
     * @return array<mixed>
     */
    public function map($call): array
    {
        return [
            $call->id,
            $call->title,
            $call->program?->name ?? '-',
            $call->academicYear?->year ?? '-',
            $this->getTypeLabel($call->type),
            $this->getModalityLabel($call->modality),
            $call->number_of_places ?? '-',
            $this->formatDestinations($call->destinations),
            $call->estimated_start_date ? $call->estimated_start_date->format('d/m/Y') : '-',
            $call->estimated_end_date ? $call->estimated_end_date->format('d/m/Y') : '-',
            $this->getStatusLabel($call->status),
            $call->published_at ? $call->published_at->format('d/m/Y H:i') : '-',
            $call->closed_at ? $call->closed_at->format('d/m/Y H:i') : '-',
            $call->creator?->name ?? __('common.messages.system'),
            $call->created_at->format('d/m/Y H:i'),
            $call->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Get the title for the sheet.
     */
    public function title(): string
    {
        return __('Convocatorias');
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
     * Get type label.
     */
    protected function getTypeLabel(?string $type): string
    {
        if (! $type) {
            return '-';
        }

        return match ($type) {
            'alumnado' => __('common.call_types.students'),
            'personal' => __('common.call_types.staff'),
            default => $type,
        };
    }

    /**
     * Get modality label.
     */
    protected function getModalityLabel(?string $modality): string
    {
        if (! $modality) {
            return '-';
        }

        return match ($modality) {
            'corta' => __('common.call_modalities.short'),
            'larga' => __('common.call_modalities.long'),
            default => $modality,
        };
    }

    /**
     * Get status label.
     */
    protected function getStatusLabel(?string $status): string
    {
        if (! $status) {
            return '-';
        }

        return match ($status) {
            'borrador' => __('common.call_status.draft'),
            'abierta' => __('common.call_status.open'),
            'cerrada' => __('common.call_status.closed'),
            'en_baremacion' => __('common.call_status.evaluating'),
            'resuelta' => __('common.call_status.resolved'),
            'archivada' => __('common.call_status.archived'),
            default => $status,
        };
    }

    /**
     * Format destinations array to a comma-separated string.
     */
    protected function formatDestinations(?array $destinations): string
    {
        if (! $destinations || ! is_array($destinations) || empty($destinations)) {
            return '-';
        }

        return implode(', ', $destinations);
    }
}
