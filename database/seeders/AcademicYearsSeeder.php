<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Si estamos después de septiembre, el año académico actual es el que empezó en septiembre
        // Si estamos antes de septiembre, el año académico actual es el que empezó el septiembre anterior
        $academicYearStart = $currentMonth >= 9 ? $currentYear : $currentYear - 1;
        $academicYearEnd = $academicYearStart + 1;

        $academicYears = [
            [
                'year' => ($academicYearStart - 2).'-'.($academicYearStart - 1),
                'start_date' => ($academicYearStart - 2).'-09-01',
                'end_date' => ($academicYearStart - 1).'-06-30',
                'is_current' => false,
            ],
            [
                'year' => ($academicYearStart - 1).'-'.$academicYearStart,
                'start_date' => ($academicYearStart - 1).'-09-01',
                'end_date' => $academicYearStart.'-06-30',
                'is_current' => false,
            ],
            [
                'year' => $academicYearStart.'-'.$academicYearEnd,
                'start_date' => $academicYearStart.'-09-01',
                'end_date' => $academicYearEnd.'-06-30',
                'is_current' => true,
            ],
            [
                'year' => $academicYearEnd.'-'.($academicYearEnd + 1),
                'start_date' => $academicYearEnd.'-09-01',
                'end_date' => ($academicYearEnd + 1).'-06-30',
                'is_current' => false,
            ],
        ];

        foreach ($academicYears as $yearData) {
            AcademicYear::firstOrCreate(
                ['year' => $yearData['year']],
                $yearData
            );
        }
    }
}
