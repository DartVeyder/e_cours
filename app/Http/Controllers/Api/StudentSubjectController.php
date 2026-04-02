<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSubjectController extends Controller
{
    /**
     * Get a list of students and their chosen subjects.
     */
    public function index(Request $request)
    {
        $query = DB::table('user_specialties')
            ->leftJoin('user_specialty_subjects', 'user_specialties.id', '=', 'user_specialty_subjects.user_specialty_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'user_specialty_subjects.subject_id')
            ->select([
                'user_specialties.card_id as edebo',
                'user_specialties.full_name as student_name',
                'subjects.name as component',
                'user_specialty_subjects.semester as semester',
                'subjects.credits as component_volume',
            ]);

        // Filter if only elective (chosen) subjects are needed
        if ($request->boolean('only_chosen', false)) {
            $query->where('user_specialty_subjects.is_student_choice', true);
        }

        if ($request->get('export') === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM so Excel opens to correct encoding automatically
                fputs($file, "\xEF\xBB\xBF");
                
                // Add Headers
                fputcsv($file, [
                    'ЄДЕБО',
                    'ПІБ студента',
                    'Дисципліна',
                    'Семестр',
                    'Кількість кредитів'
                ], ';');

                // Process records in chunks to prevent memory exhaustion
                foreach ($query->cursor() as $row) {
                    fputcsv($file, [
                        $row->edebo,
                        $row->student_name,
                        $row->component,
                        $row->semester,
                        $row->component_volume,
                    ], ';');
                }

                fclose($file);
            }, 'students_subjects_selection.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->get('per_page', 100)),
        ]);
    }
}
