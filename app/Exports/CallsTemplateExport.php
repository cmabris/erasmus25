<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CallsTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    /**
     * Get the array data for the export.
     */
    public function array(): array
    {
        // Return example row with sample data
        return [
            [
                'KA131', // programa
                '2024-2025', // año_academico
                'Convocatoria Movilidad Alumnado 2024-2025', // titulo
                '', // slug (opcional, se genera automáticamente)
                'alumnado', // tipo
                'corta', // modalidad
                20, // numero_plazas
                'Francia, Alemania, Italia', // destinos (separados por comas)
                '2024-09-01', // fecha_inicio_estimada
                '2025-06-30', // fecha_fin_estimada
                'Requisitos básicos para la movilidad', // requisitos (opcional)
                'Documentación necesaria para la solicitud', // documentacion (opcional)
                'Criterios de selección aplicables', // criterios_seleccion (opcional)
                'borrador', // estado (opcional)
                '', // fecha_publicacion (opcional)
                '', // fecha_cierre (opcional)
            ],
        ];
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            __('Programa'), // programa - Código o nombre del programa
            __('Año Académico'), // año_academico - Año académico
            __('Título'), // titulo - Título de la convocatoria (requerido)
            __('Slug'), // slug - Slug (opcional, se genera automáticamente)
            __('Tipo'), // tipo - Tipo: "alumnado" o "personal" (requerido)
            __('Modalidad'), // modalidad - Modalidad: "corta" o "larga" (requerido)
            __('Número de Plazas'), // numero_plazas - Número de plazas (requerido)
            __('Destinos'), // destinos - Destinos separados por comas (requerido)
            __('Fecha Inicio Estimada'), // fecha_inicio_estimada - Fecha de inicio (formato: yyyy-mm-dd o dd/mm/yyyy)
            __('Fecha Fin Estimada'), // fecha_fin_estimada - Fecha de fin (formato: yyyy-mm-dd o dd/mm/yyyy)
            __('Requisitos'), // requisitos - Requisitos (opcional)
            __('Documentación'), // documentacion - Documentación (opcional)
            __('Criterios de Selección'), // criterios_seleccion - Criterios de selección (opcional)
            __('Estado'), // estado - Estado: "borrador", "abierta", "cerrada", etc. (opcional)
            __('Fecha Publicación'), // fecha_publicacion - Fecha de publicación (opcional)
            __('Fecha Cierre'), // fecha_cierre - Fecha de cierre (opcional)
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
        // Style header row
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Blue color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15); // programa
        $sheet->getColumnDimension('B')->setWidth(15); // año_academico
        $sheet->getColumnDimension('C')->setWidth(40); // titulo
        $sheet->getColumnDimension('D')->setWidth(30); // slug
        $sheet->getColumnDimension('E')->setWidth(12); // tipo
        $sheet->getColumnDimension('F')->setWidth(12); // modalidad
        $sheet->getColumnDimension('G')->setWidth(15); // numero_plazas
        $sheet->getColumnDimension('H')->setWidth(30); // destinos
        $sheet->getColumnDimension('I')->setWidth(18); // fecha_inicio_estimada
        $sheet->getColumnDimension('J')->setWidth(18); // fecha_fin_estimada
        $sheet->getColumnDimension('K')->setWidth(30); // requisitos
        $sheet->getColumnDimension('L')->setWidth(30); // documentacion
        $sheet->getColumnDimension('M')->setWidth(30); // criterios_seleccion
        $sheet->getColumnDimension('N')->setWidth(15); // estado
        $sheet->getColumnDimension('O')->setWidth(18); // fecha_publicacion
        $sheet->getColumnDimension('P')->setWidth(18); // fecha_cierre

        // Style example row
        $sheet->getStyle('A2:P2')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'], // Light gray
            ],
        ]);

        // Add instructions as comments in first cell
        $sheet->getComment('A1')->getText()->createTextRun(__('INSTRUCCIONES:'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('1. La primera fila contiene los encabezados (NO MODIFICAR)'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('2. La segunda fila contiene un ejemplo (BORRAR antes de importar)'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('3. Programa: Código o nombre del programa existente'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('4. Año Académico: Año del año académico existente'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('5. Tipo: "alumnado" o "personal"'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('6. Modalidad: "corta" o "larga"'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('7. Destinos: Separar por comas o punto y coma'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('8. Fechas: Formato yyyy-mm-dd o dd/mm/yyyy'));

        return [];
    }
}
