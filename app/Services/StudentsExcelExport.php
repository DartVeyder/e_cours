<?php

namespace App\Services;

use App\Models\UserSpecialty;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentsExcelExport
{
    public function export(Collection $students): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Студенти');

        $headers = [
            '№',
            'Кількість вибрано',
            'ПІБ',
            'Група',
            'ЄДЕБО',
            'Email',
            'Форма навчання',
            'Рівень освіти',
            'Факультет',
            'Спеціальність',
            'Освітня програма',
            'Стать',
            'Статус навчання',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $rowIdx = 2;
        foreach ($students as $i => $student) {
            $totalRequired = $student->group ? $student->group->semesterLimits->sum('max_subjects') : 0;
            $selectedStr = (string)$student->subjects_count;
            if ($totalRequired > 0) {
                $selectedStr .= " / {$totalRequired}";
            }

            $sheet->setCellValue('A' . $rowIdx, $i + 1);
            $sheet->setCellValue('B' . $rowIdx, $selectedStr);
            $sheet->setCellValue('C' . $rowIdx, $student->full_name);
            $sheet->setCellValue('D' . $rowIdx, $student->group_name);
            $sheet->setCellValue('E' . $rowIdx, $student->card_id);
            $sheet->setCellValue('F' . $rowIdx, $student->email);
            $sheet->setCellValue('G' . $rowIdx, $student->study_form);
            $sheet->setCellValue('H' . $rowIdx, $student->degree);
            $sheet->setCellValue('I' . $rowIdx, $student->department);
            $sheet->setCellValue('J' . $rowIdx, $student->specialty);
            $sheet->setCellValue('K' . $rowIdx, $student->education_program);
            $sheet->setCellValue('L' . $rowIdx, $student->gender);
            $sheet->setCellValue('M' . $rowIdx, $student->study_status);

            $rowIdx++;
        }

        $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = max(1, $rowIdx - 1);

        // Styling
        $sheet->freezePane('A2');
        foreach (range(1, count($headers)) as $colIndex) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex))->setAutoSize(true);
        }

        $sheet->getStyle("A1:{$lastColLetter}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColLetter}1")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A1:{$lastColLetter}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        if ($lastRow >= 2) {
            $sheet->getStyle("A1:{$lastColLetter}{$lastRow}")->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
                
            for ($r = 2; $r <= $lastRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF2F2F2');
                }
            }
        }

        $filename = "Студенти_" . date('Y-m-d_H-i-s') . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . rawurlencode($filename) . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
