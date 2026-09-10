<?php

namespace App\Orchid\Screens\Subject;

use App\Models\Subject;
use App\Orchid\Layouts\Subject\SubjecSpecialtytListLayout;
use Orchid\Screen\Screen;

class SubjectSpecialtyListScreen extends Screen
{
    private $subjectName;
    private $countSpecialties;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Subject $subject): iterable
    {
        $subjectUserSpecialties = Subject::with(['userSpecialties' => function($query) {
            $query->filters()
                ->filtersApply([\App\Orchid\Filters\EntryYearFilter::class])
                ->select('user_specialties.id', 'user_specialties.full_name', 'user_specialties.study_start', 'user_specialties.specialty', 'user_specialties.group_name','user_specialties.study_form');
        }])->find($subject->id);
        $this->subjectName = $subjectUserSpecialties->name;
        $this->countSpecialties = $subjectUserSpecialties->userSpecialties->count();
        return [
            'userSpecialties' => $subjectUserSpecialties->userSpecialties
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->subjectName;
    }
    public function description(): ?string
    {
        return "Всього вибрало: ". $this->countSpecialties;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $exportParams = [];
        if (request()->has('filter')) {
            foreach (request()->get('filter') as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $exportParams["filter[$key][$k]"] = $v;
                    }
                } else {
                    $exportParams["filter[$key]"] = $value;
                }
            }
        }
        if (request()->has('sort')) {
            $exportParams['sort'] = request()->get('sort');
        }
        if (request()->has('entry_year')) {
            $exportParams['entry_year'] = request()->get('entry_year');
        }

        return [
            \Orchid\Screen\Actions\Button::make('Експорт в Excel')
                ->icon('cloud-download')
                ->method('exportExcel', $exportParams)
                ->rawClick(),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            \App\Orchid\Layouts\Subject\SubjectSpecialtyFiltersLayout::class,
            SubjecSpecialtytListLayout::class,
        ];
    }

    public function exportExcel(Subject $subject)
    {
        $subjectUserSpecialties = Subject::with(['userSpecialties' => function($query) {
            $query->filters()
                ->filtersApply([\App\Orchid\Filters\EntryYearFilter::class])
                ->select('user_specialties.id', 'user_specialties.full_name', 'user_specialties.study_start', 'user_specialties.specialty', 'user_specialties.group_name','user_specialties.study_form');
        }])->findOrFail($subject->id);

        $students = $subjectUserSpecialties->userSpecialties;

        activity()
            ->causedBy(\Illuminate\Support\Facades\Auth::user())
            ->withProperties(['subject_id' => $subject->id, 'subject_name' => $subject->name])
            ->log("Експорт студентів, які обрали дисципліну «{$subject->name}» у Excel");

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($subject->name, 0, 31));

        $headers = [
            '№',
            'Рік вступу',
            'ПІБ',
            'Спеціальність',
            'Група',
            'Форма навчання',
            'Семестр',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        $rowIdx = 2;
        foreach ($students as $i => $student) {
            $year = $student->entry_year ?? ($student->study_start ? substr((string)$student->study_start, 0, 4) : '—');
            $sheet->setCellValue('A' . $rowIdx, $i + 1);
            $sheet->setCellValue('B' . $rowIdx, $year);
            $sheet->setCellValue('C' . $rowIdx, $student->full_name);
            $sheet->setCellValue('D' . $rowIdx, $student->specialty);
            $sheet->setCellValue('E' . $rowIdx, $student->group_name);
            $sheet->setCellValue('F' . $rowIdx, $student->study_form);
            $sheet->setCellValue('G' . $rowIdx, $student->pivot->semester ?? '—');
            $rowIdx++;
        }

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = max(1, $rowIdx - 1);

        $sheet->freezePane('A2');
        foreach (range(1, count($headers)) as $colIndex) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex))->setAutoSize(true);
        }

        $sheet->getStyle("A1:{$lastColLetter}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColLetter}1")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A1:{$lastColLetter}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        if ($lastRow >= 2) {
            $sheet->getStyle("A1:{$lastColLetter}{$lastRow}")->getBorders()
                ->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
            for ($r = 2; $r <= $lastRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF2F2F2');
                }
            }
        }

        $cleanSubjectName = preg_replace('/[\\\\\\/:*?"<>|]/', '_', $subject->name);
        $filename = "Дисципліна_" . mb_substr($cleanSubjectName, 0, 40) . "_" . date('Y-m-d') . '.xlsx';

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . rawurlencode($filename) . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
