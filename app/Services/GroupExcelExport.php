<?php

namespace App\Services;

use App\Models\UserSpecialty;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupExcelExport
{
    /**
     * Layout:
     *   Row 1 — header: №, ПІБ, then for each semester: N repeated "Семестр X" headers
     *            where N = max number of subjects any student has in that semester
     *   Row 2+ — one row per student; each semester slot gets one discipline name (or empty)
     */
    public function export(string $groupName): StreamedResponse
    {
        // 1. Load students with subjects
        $students = UserSpecialty::where('group_name', $groupName)
            ->with(['subjects' => function ($q) {
                $q->select('subjects.id', 'subjects.name')->orderBy('subjects.name');
            }])
            ->orderBy('full_name')
            ->get();

        $filename = "Звіт_{$groupName}_" . date('Y-m-d') . '.xlsx';
        return $this->exportCollection($students, mb_substr($groupName, 0, 31), $filename, false);
    }

    public function exportCollection($students, string $sheetTitle, string $filename, bool $showGroup = false): StreamedResponse
    {
        // 2. Collect sorted semester list
        $semesters = collect();
        foreach ($students as $student) {
            foreach ($student->subjects as $subject) {
                $sem = (int) $subject->pivot->semester;
                if (!$semesters->contains($sem)) {
                    $semesters->push($sem);
                }
            }
        }
        $semesters = $semesters->sort()->values(); // [1, 2, 3, ...]

        // 3. For each semester find the max number of subjects any student has
        //    maxSlots[semester] = N
        $maxSlots = [];
        foreach ($semesters as $sem) {
            $max = 0;
            foreach ($students as $student) {
                $count = $student->subjects->filter(
                    fn($s) => (int) $s->pivot->semester === $sem
                )->count();
                if ($count > $max) {
                    $max = $count;
                }
            }
            $maxSlots[$sem] = max($max, 1); // at least 1 column per semester
        }

        // 4. Build column map: ordered list of [semester, slot_index]
        //    colMap[i] = ['semester' => X, 'slot' => 0..N-1]
        $colMap = [];
        foreach ($semesters as $sem) {
            for ($slot = 0; $slot < $maxSlots[$sem]; $slot++) {
                $colMap[] = ['semester' => $sem, 'slot' => $slot];
            }
        }

        $colOffset = $showGroup ? 3 : 2;
        $totalCols = count($colMap) + $colOffset; 

        // 5. Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        // --- Header row ---
        $sheet->setCellValue([1, 1], '№');
        $sheet->setCellValue([2, 1], 'ПІБ');
        
        if ($showGroup) {
            $sheet->setCellValue([3, 1], 'Група');
        }

        foreach ($colMap as $idx => $col) {
            $sheet->setCellValue([$idx + $colOffset + 1, 1], "Семестр {$col['semester']}");
        }

        // --- Data rows ---
        $rowIdx = 2;
        foreach ($students as $i => $student) {
            $sheet->setCellValue([1, $rowIdx], $i + 1);
            $sheet->setCellValue([2, $rowIdx], $student->full_name);
            
            if ($showGroup) {
                $sheet->setCellValue([3, $rowIdx], $student->group_name);
            }

            // Group disciplines by semester, sorted alphabetically
            $bySemester = [];
            foreach ($student->subjects as $subject) {
                $bySemester[(int) $subject->pivot->semester][] = $subject->name;
            }

            foreach ($colMap as $idx => $col) {
                $sem  = $col['semester'];
                $slot = $col['slot'];
                $name = $bySemester[$sem][$slot] ?? '';
                if ($name !== '') {
                    $cellRef = Coordinate::stringFromColumnIndex($idx + $colOffset + 1) . $rowIdx;
                    $sheet->setCellValue($cellRef, $name);
                }
            }

            $rowIdx++;
        }

        $lastRow = $rowIdx - 1;

        // 6. Styling
        $this->applyStyles($sheet, $totalCols, $lastRow, $semesters->toArray(), $maxSlots, $showGroup);

        // 7. Stream response
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . rawurlencode($filename) . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function applyStyles($sheet, int $totalCols, int $lastRow, array $semesters, array $maxSlots, bool $showGroup): void
    {
        $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

        // Freeze columns and header row
        if ($showGroup) {
            $sheet->freezePane('D2');
            $colStart = 4;
        } else {
            $sheet->freezePane('C2');
            $colStart = 3;
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(32);
        
        if ($showGroup) {
            $sheet->getColumnDimension('C')->setWidth(15);
        }

        for ($c = $colStart; $c <= $totalCols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(28);
        }

        // Header row: bold, centered, background
        $sheet->getStyle("A1:{$lastColLetter}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColLetter}1")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A1:{$lastColLetter}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        // Merge same-semester header cells (optional visual grouping)
        // Each run of identical semester labels gets a different shade
        $semesterColors = ['FFD9E1F2', 'FFE2EFDA', 'FFFFF2CC', 'FFFCE4D6', 'FFDDEBF7'];
        $currentColStart = $colStart;
        foreach ($semesters as $semIdx => $sem) {
            $slots     = $maxSlots[$sem];
            $colEnd    = $currentColStart + $slots - 1;
            $color     = $semesterColors[$semIdx % count($semesterColors)];
            $rangeFrom = Coordinate::stringFromColumnIndex($currentColStart) . '1';
            $rangeTo   = Coordinate::stringFromColumnIndex($colEnd) . '1';

            // Color this semester block
            $sheet->getStyle("{$rangeFrom}:{$rangeTo}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($color);

            $currentColStart = $colEnd + 1;
        }

        // Data rows: ПІБ left-aligned, all top-aligned
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("B2:{$lastColLetter}{$lastRow}")->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP);
        }

        // Borders on whole table
        $sheet->getStyle("A1:{$lastColLetter}{$lastRow}")->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Alternating row colors
        for ($r = 2; $r <= $lastRow; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F2F2');
            }
        }
    }
}
