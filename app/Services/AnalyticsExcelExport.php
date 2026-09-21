<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExcelExport
{
    /**
     * Export analytics data to Excel (.xlsx).
     *
     * @param array $analyticsData
     * @param string|null $filename
     * @return StreamedResponse
     */
    public function export(array $analyticsData, ?string $filename = null): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        $summary = $analyticsData['summary'];
        $faculties = $analyticsData['faculties'];
        $specialties = $analyticsData['specialties'];
        $filters = $analyticsData['filters'];

        $defaultFilename = 'Аналітика_вибору_магістри_' . ($filters['entry_year'] ?? '2026') . '_' . date('Y-m-d') . '.xlsx';
        $filename = $filename ?? $defaultFilename;

        // -------------------------------------------------------------
        // Sheet 1: Загальне зведення
        // -------------------------------------------------------------
        $sheetSummary = $spreadsheet->getActiveSheet();
        $sheetSummary->setTitle('Загальне зведення');

        // Title and filters header
        $sheetSummary->setCellValue('A1', 'ЗВІТ: АНАЛІТИКА ВИБОРУ ВИБІРКОВИХ ДИСЦИПЛІН');
        $sheetSummary->mergeCells('A1:D1');
        $sheetSummary->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheetSummary->setCellValue('A2', 'Сформовано: ' . date('d.m.Y H:i:s'));
        $sheetSummary->setCellValue('A3', 'Рік вступу: ' . ($filters['entry_year'] ?: 'Всі'));
        $sheetSummary->setCellValue('B3', 'Освітній рівень: ' . ($filters['degree'] ?: 'Всі'));
        $sheetSummary->setCellValue('C3', 'Факультет: ' . ($filters['department'] ?: 'Всі'));
        $sheetSummary->setCellValue('D3', 'Форма: ' . ($filters['study_form'] ?: 'Всі'));

        // KPI Table Headers
        $sheetSummary->setCellValue('A5', 'Показник');
        $sheetSummary->setCellValue('B5', 'Кількість здобувачів');
        $sheetSummary->setCellValue('C5', 'Частка (%)');
        $sheetSummary->setCellValue('D5', 'Статус');

        $kpis = [
            ['Всього здобувачів (когорта)', $summary['total'], '100%', 'Загалом'],
            ['Обрали всі дисципліни повністю', $summary['all'], $summary['all_percent'] . '%', 'Повністю обрано'],
            ['Обрали дисципліни частково', $summary['partial'], $summary['partial_percent'] . '%', 'В процесі вибору'],
            ['Не обрали жодної дисципліни', $summary['none'], $summary['none_percent'] . '%', 'Вибір відсутній'],
        ];

        $rowIdx = 6;
        foreach ($kpis as $kpi) {
            $sheetSummary->setCellValue('A' . $rowIdx, $kpi[0]);
            $sheetSummary->setCellValue('B' . $rowIdx, $kpi[1]);
            $sheetSummary->setCellValue('C' . $rowIdx, $kpi[2]);
            $sheetSummary->setCellValue('D' . $rowIdx, $kpi[3]);
            $rowIdx++;
        }

        $this->styleHeaderRange($sheetSummary, 'A5:D5', 'FFD9E1F2');
        $this->styleDataRange($sheetSummary, 'A6:D9');

        // -------------------------------------------------------------
        // Sheet 2: По факультетах
        // -------------------------------------------------------------
        $sheetFaculties = $spreadsheet->createSheet();
        $sheetFaculties->setTitle('По факультетах');

        $headersFaculties = [
            '№',
            'Факультет / Підрозділ',
            'Всього',
            'Повністю',
            '% Повністю',
            'Частково',
            '% Частково',
            'Не обрали',
            '% Не обрали',
            '% Завершення',
        ];

        foreach ($headersFaculties as $idx => $h) {
            $sheetFaculties->setCellValue(Coordinate::stringFromColumnIndex($idx + 1) . '1', $h);
        }

        $rowIdx = 2;
        foreach ($faculties as $i => $fac) {
            $sheetFaculties->setCellValue('A' . $rowIdx, $i + 1);
            $sheetFaculties->setCellValue('B' . $rowIdx, $fac['name']);
            $sheetFaculties->setCellValue('C' . $rowIdx, $fac['total']);
            $sheetFaculties->setCellValue('D' . $rowIdx, $fac['all']);
            $sheetFaculties->setCellValue('E' . $rowIdx, $fac['all_percent'] . '%');
            $sheetFaculties->setCellValue('F' . $rowIdx, $fac['partial']);
            $sheetFaculties->setCellValue('G' . $rowIdx, $fac['partial_percent'] . '%');
            $sheetFaculties->setCellValue('H' . $rowIdx, $fac['none']);
            $sheetFaculties->setCellValue('I' . $rowIdx, $fac['none_percent'] . '%');
            $sheetFaculties->setCellValue('J' . $rowIdx, $fac['completion_rate'] . '%');
            $rowIdx++;
        }

        $lastRow = max(2, $rowIdx - 1);
        $this->styleHeaderRange($sheetFaculties, 'A1:J1', 'FFD9E1F2');
        if ($rowIdx > 2) {
            $this->styleDataRange($sheetFaculties, "A2:J{$lastRow}");
        }

        // -------------------------------------------------------------
        // Sheet 3: По спеціальностях та ОП
        // -------------------------------------------------------------
        $sheetSpecialties = $spreadsheet->createSheet();
        $sheetSpecialties->setTitle('По спеціальностях та ОП');

        $headersSpecialties = [
            '№',
            'Спеціальність',
            'Освітня програма / Спеціалізація',
            'Факультет',
            'Всього',
            'Повністю',
            '% Повністю',
            'Частково',
            '% Частково',
            'Не обрали',
            '% Не обрали',
            '% Завершення',
        ];

        foreach ($headersSpecialties as $idx => $h) {
            $sheetSpecialties->setCellValue(Coordinate::stringFromColumnIndex($idx + 1) . '1', $h);
        }

        $rowIdx = 2;
        $counter = 1;
        foreach ($specialties as $spec) {
            if ($spec['is_broad'] && !empty($spec['programs'])) {
                // Specialty header row
                $sheetSpecialties->setCellValue('A' . $rowIdx, $counter++);
                $sheetSpecialties->setCellValue('B' . $rowIdx, $spec['name']);
                $sheetSpecialties->setCellValue('C' . $rowIdx, '[Всі програми спеціальності разом]');
                $sheetSpecialties->setCellValue('D' . $rowIdx, $spec['department']);
                $sheetSpecialties->setCellValue('E' . $rowIdx, $spec['total']);
                $sheetSpecialties->setCellValue('F' . $rowIdx, $spec['all']);
                $sheetSpecialties->setCellValue('G' . $rowIdx, $spec['all_percent'] . '%');
                $sheetSpecialties->setCellValue('H' . $rowIdx, $spec['partial']);
                $sheetSpecialties->setCellValue('I' . $rowIdx, $spec['partial_percent'] . '%');
                $sheetSpecialties->setCellValue('J' . $rowIdx, $spec['none']);
                $sheetSpecialties->setCellValue('K' . $rowIdx, $spec['none_percent'] . '%');
                $sheetSpecialties->setCellValue('L' . $rowIdx, $spec['completion_rate'] . '%');
                $sheetSpecialties->getStyle("A{$rowIdx}:L{$rowIdx}")->getFont()->setBold(true);
                $rowIdx++;

                // Individual program sub-rows
                foreach ($spec['programs'] as $prog) {
                    $sheetSpecialties->setCellValue('A' . $rowIdx, '   ↳');
                    $sheetSpecialties->setCellValue('B' . $rowIdx, $spec['name']);
                    $sheetSpecialties->setCellValue('C' . $rowIdx, $prog['name']);
                    $sheetSpecialties->setCellValue('D' . $rowIdx, $prog['department']);
                    $sheetSpecialties->setCellValue('E' . $rowIdx, $prog['total']);
                    $sheetSpecialties->setCellValue('F' . $rowIdx, $prog['all']);
                    $sheetSpecialties->setCellValue('G' . $rowIdx, $prog['all_percent'] . '%');
                    $sheetSpecialties->setCellValue('H' . $rowIdx, $prog['partial']);
                    $sheetSpecialties->setCellValue('I' . $rowIdx, $prog['partial_percent'] . '%');
                    $sheetSpecialties->setCellValue('J' . $rowIdx, $prog['none']);
                    $sheetSpecialties->setCellValue('K' . $rowIdx, $prog['none_percent'] . '%');
                    $sheetSpecialties->setCellValue('L' . $rowIdx, $prog['completion_rate'] . '%');
                    $rowIdx++;
                }
            } else {
                $progName = !empty($spec['programs']) ? $spec['programs']->first()['name'] : $spec['name'];
                $sheetSpecialties->setCellValue('A' . $rowIdx, $counter++);
                $sheetSpecialties->setCellValue('B' . $rowIdx, $spec['name']);
                $sheetSpecialties->setCellValue('C' . $rowIdx, $progName);
                $sheetSpecialties->setCellValue('D' . $rowIdx, $spec['department']);
                $sheetSpecialties->setCellValue('E' . $rowIdx, $spec['total']);
                $sheetSpecialties->setCellValue('F' . $rowIdx, $spec['all']);
                $sheetSpecialties->setCellValue('G' . $rowIdx, $spec['all_percent'] . '%');
                $sheetSpecialties->setCellValue('H' . $rowIdx, $spec['partial']);
                $sheetSpecialties->setCellValue('I' . $rowIdx, $spec['partial_percent'] . '%');
                $sheetSpecialties->setCellValue('J' . $rowIdx, $spec['none']);
                $sheetSpecialties->setCellValue('K' . $rowIdx, $spec['none_percent'] . '%');
                $sheetSpecialties->setCellValue('L' . $rowIdx, $spec['completion_rate'] . '%');
                $rowIdx++;
            }
        }

        $lastRow = max(2, $rowIdx - 1);
        $this->styleHeaderRange($sheetSpecialties, 'A1:L1', 'FFD9E1F2');
        if ($rowIdx > 2) {
            $this->styleDataRange($sheetSpecialties, "A2:L{$lastRow}");
        }

        // Auto-fit column widths for all sheets
        foreach ([$sheetSummary, $sheetFaculties, $sheetSpecialties] as $sheet) {
            $highestCol = $sheet->getHighestColumn();
            $highestColIdx = Coordinate::columnIndexFromString($highestCol);
            for ($col = 1; $col <= $highestColIdx; $col++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
            }
        }

        // Return StreamedResponse
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    protected function styleHeaderRange($sheet, string $range, string $bgColor = 'FFD9E1F2'): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($bgColor);
        $sheet->getStyle($range)->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    protected function styleDataRange($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
}
