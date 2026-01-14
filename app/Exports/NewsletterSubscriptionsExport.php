<?php

namespace App\Exports;

use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NewsletterSubscriptionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        $query = NewsletterSubscription::query()
            ->when($this->filters['filterProgram'] ?? null, function ($query) {
                $query->whereJsonContains('programs', $this->filters['filterProgram']);
            })
            ->when(($this->filters['filterStatus'] ?? null) === 'activo', fn ($query) => $query->where('is_active', true))
            ->when(($this->filters['filterStatus'] ?? null) === 'inactivo', fn ($query) => $query->where('is_active', false))
            ->when(($this->filters['filterVerification'] ?? null) === 'verificado', fn ($query) => $query->whereNotNull('verified_at'))
            ->when(($this->filters['filterVerification'] ?? null) === 'no-verificado', fn ($query) => $query->whereNull('verified_at'))
            ->when($this->filters['search'] ?? null, function ($query) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy($this->filters['sortField'] ?? 'subscribed_at', $this->filters['sortDirection'] ?? 'desc')
            ->orderBy('email', 'asc');

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
            __('Email'),
            __('Nombre'),
            __('Programas'),
            __('Estado'),
            __('Verificado'),
            __('Fecha Suscripción'),
            __('Fecha Verificación'),
            __('Fecha Baja'),
        ];
    }

    /**
     * Map each subscription to a row.
     *
     * @return array<mixed>
     */
    public function map($subscription): array
    {
        return [
            $subscription->email,
            $subscription->name ?: '-',
            $this->formatPrograms($subscription),
            $subscription->is_active ? __('Activo') : __('Inactivo'),
            $subscription->isVerified() ? __('Sí') : __('No'),
            $subscription->subscribed_at->format('d/m/Y H:i'),
            $subscription->verified_at ? $subscription->verified_at->format('d/m/Y H:i') : '-',
            $subscription->unsubscribed_at ? $subscription->unsubscribed_at->format('d/m/Y H:i') : '-',
        ];
    }

    /**
     * Get the title for the sheet.
     */
    public function title(): string
    {
        return __('Suscripciones Newsletter');
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
     * Format programs array to a comma-separated string with names.
     */
    protected function formatPrograms(NewsletterSubscription $subscription): string
    {
        if (! $subscription->programs || ! is_array($subscription->programs) || empty($subscription->programs)) {
            return '-';
        }

        $programCodes = $subscription->programs;
        $programs = Program::whereIn('code', $programCodes)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $programNames = [];
        $foundCodes = [];

        foreach ($programs as $program) {
            $programNames[] = $program->name;
            $foundCodes[] = $program->code;
        }

        // Add codes that weren't found in database
        $notFoundCodes = array_diff($programCodes, $foundCodes);
        foreach ($notFoundCodes as $code) {
            $programNames[] = $code;
        }

        return implode(', ', $programNames);
    }
}
