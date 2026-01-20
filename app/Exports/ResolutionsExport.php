<?php

namespace App\Exports;

use App\Models\Resolution;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResolutionsExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping, WithStyles, WithTitle
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
        return Resolution::query()
            ->when($this->filters['call_id'] ?? null, fn ($query) => $query->where('call_id', $this->filters['call_id']))
            ->when(($this->filters['showDeleted'] ?? '0') === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when(($this->filters['showDeleted'] ?? '0') === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->filters['filterType'] ?? null, fn ($query) => $query->where('type', $this->filters['filterType']))
            ->when(($this->filters['filterPublished'] ?? null) === '1', fn ($query) => $query->whereNotNull('published_at'))
            ->when(($this->filters['filterPublished'] ?? null) === '0', fn ($query) => $query->whereNull('published_at'))
            ->when($this->filters['filterPhase'] ?? null, fn ($query) => $query->where('call_phase_id', $this->filters['filterPhase']))
            ->when($this->filters['search'] ?? null, function ($query) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->with([
                'call' => fn ($query) => $query->select('id', 'title', 'program_id', 'academic_year_id'),
                'callPhase' => fn ($query) => $query->select('id', 'call_id', 'name', 'phase_type'),
                'creator' => fn ($query) => $query->select('id', 'name', 'email'),
            ])
            ->orderBy($this->filters['sortField'] ?? 'official_date', $this->filters['sortDirection'] ?? 'desc')
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
            __('Convocatoria'),
            __('Fase'),
            __('Tipo'),
            __('Descripción'),
            __('Procedimiento de Evaluación'),
            __('Fecha Oficial'),
            __('Publicada'),
            __('Fecha Publicación'),
            __('Creador'),
            __('Fecha Creación'),
            __('Fecha Actualización'),
        ];
    }

    /**
     * Map each resolution to a row.
     *
     * @param  Resolution  $resolution
     * @return array<mixed>
     */
    public function map($resolution): array
    {
        return [
            $resolution->id,
            $resolution->title,
            $resolution->call?->title ?? '-',
            $resolution->callPhase?->name ?? '-',
            $this->getTypeLabel($resolution->type),
            $this->truncateText($resolution->description, 100),
            $this->truncateText($resolution->evaluation_procedure, 100),
            $resolution->official_date ? $resolution->official_date->format('d/m/Y') : '-',
            $resolution->published_at ? __('common.messages.yes') : __('common.messages.no'),
            $resolution->published_at ? $resolution->published_at->format('d/m/Y H:i') : '-',
            $resolution->creator?->name ?? __('common.messages.system'),
            $resolution->created_at->format('d/m/Y H:i'),
            $resolution->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Get the title for the sheet.
     */
    public function title(): string
    {
        return __('Resoluciones');
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
            'provisional' => __('common.resolutions.types.provisional'),
            'definitivo' => __('common.resolutions.types.definitivo'),
            'alegaciones' => __('common.resolutions.types.alegaciones'),
            default => $type,
        };
    }

    /**
     * Truncate text to a maximum length.
     */
    protected function truncateText(?string $text, int $maxLength = 100): string
    {
        if (! $text) {
            return '-';
        }

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength).'...';
    }
}
