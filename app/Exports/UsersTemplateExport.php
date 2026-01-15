<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    /**
     * Get the array data for the export.
     */
    public function array(): array
    {
        // Return example row with sample data
        return [
            [
                'Juan Pérez García', // nombre
                'juan.perez@example.com', // email
                '', // contraseña (opcional, se genera automáticamente si está vacío)
                'admin,editor', // roles (separados por comas)
            ],
            [
                'María López Sánchez', // nombre
                'maria.lopez@example.com', // email
                'MiPassword123!', // contraseña (opcional)
                'editor', // roles
            ],
        ];
    }

    /**
     * Define the headings for the export.
     */
    public function headings(): array
    {
        return [
            __('Nombre'), // nombre - Nombre completo del usuario (requerido)
            __('Email'), // email - Email del usuario (requerido, único)
            __('Contraseña'), // contraseña - Contraseña (opcional, se genera automáticamente si está vacío)
            __('Roles'), // roles - Roles separados por comas (opcional, ej: "admin,editor")
        ];
    }

    /**
     * Get the title for the sheet.
     */
    public function title(): string
    {
        return __('Usuarios');
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet): array
    {
        // Style header row
        $sheet->getStyle('A1:D1')->applyFromArray([
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
        $sheet->getColumnDimension('A')->setWidth(30); // nombre
        $sheet->getColumnDimension('B')->setWidth(35); // email
        $sheet->getColumnDimension('C')->setWidth(20); // contraseña
        $sheet->getColumnDimension('D')->setWidth(25); // roles

        // Style example rows
        $sheet->getStyle('A2:D3')->applyFromArray([
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
        $sheet->getComment('A1')->getText()->createTextRun(__('2. Las filas siguientes contienen ejemplos (BORRAR antes de importar)'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('3. Nombre: Nombre completo del usuario (requerido)'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('4. Email: Email único del usuario (requerido, debe ser único)'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('5. Contraseña: Opcional, se genera automáticamente si está vacío'));
        $sheet->getComment('A1')->getText()->createTextRun("\n");
        $sheet->getComment('A1')->getText()->createTextRun(__('6. Roles: Separar por comas (opcional): super-admin, admin, editor, viewer'));

        return [];
    }
}
