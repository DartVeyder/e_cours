<?php

namespace App\Orchid\Screens\Selsubject;

use App\Models\Subject;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use App\Orchid\Layouts\SelSubject\SelSubjectListLayout;
use App\Services\GoogleSheet\GroupsSheet;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SelsubjectListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $userId = Auth::id();

        $specialtyId = request()->cookie('user_specialty_id');

        if($specialtyId){
            $userSpecialty =  UserSpecialty::find($specialtyId);


            return [
                'subjects' =>  Subject::filters()
                    ->defaultSort('is_selected', 'DESC')
                    ->withCount(['users as is_selected' => function ($query) use ($specialtyId) {
                        $query->where('user_specialty_subjects.user_specialty_id', $specialtyId);
                    }])
                    ->addSelect([
                        'is_student_choice' => function ($query) use ($specialtyId) {
                            $query->select('user_specialty_subjects.is_student_choice')
                                ->from('user_specialty_subjects')
                                ->whereColumn('user_specialty_subjects.subject_id', 'subjects.id')
                                ->where('user_specialty_subjects.user_specialty_id', $specialtyId)
                                ->limit(1);
                        }
                    ])
                    ->where('education_level', $userSpecialty->degree)
                    ->where('active',  1)
                    ->paginate()

            ];
        }else{
            return [
                'subjects' =>  Subject::filters()
                    ->defaultSort('is_selected', 'DESC')

                    ->withCount(['users as is_selected' => function ($query) use ($specialtyId) {
                        $query->where('user_specialty_subjects.user_specialty_id', $specialtyId);
                    }])
                    ->addSelect([
                        'is_student_choice' => function ($query) use ($specialtyId) {
                            $query->select('user_specialty_subjects.is_student_choice')
                                ->from('user_specialty_subjects')
                                ->whereColumn('user_specialty_subjects.subject_id', 'subjects.id')
                                ->where('user_specialty_subjects.user_specialty_id', $specialtyId)
                                ->limit(1);
                        }
                    ])
                    ->where('active',  1)
                    ->paginate()
            ];
        }


    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Вибіркові освітні компоненти університету';
    }

    public function description(): ?string
    {
        return "Всього вибрано: ". UserSpecialtySubject::where('user_specialty_id', request()->cookie('user_specialty_id')) ->count();
    }

    private function specialtiesButtons()
    {
        $array = [];
        $specialties = Auth::user()->load('specialties')->specialties;

        $userSpecialtyId = request()->cookie('user_specialty_id');

        if(!$userSpecialtyId){
            $titleButtons = 'Виберіть спеціальність';
        }else{
            $userSpecialty = UserSpecialty::find($userSpecialtyId);
            $titleButtons = " $userSpecialty->specialty ($userSpecialty->group, $userSpecialty->degree, $userSpecialty->full_name)";
        }

        if( count($specialties) > 0 ){
            foreach ($specialties as $specialty) {
                $array[]  = Button::make($specialty->specialty . "( $specialty->group  )")
                    ->method('chooseSpecialty',
                        [
                            'id' => $specialty->id,
                            'text' => $specialty->specialty . "( $specialty->group  )",
                        ]);
            }
            return DropDown::make( $titleButtons)
                ->list(
                    $array,
                );
        }else{
               return Button::make(   $titleButtons )->disabled();

        }





    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            $this->specialtiesButtons(),

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
            SelSubjectListLayout::class,
        ];
    }

    public function chooseSubject($subjectId, $subjectName)
    {
        $userSpecialtyId = request()->cookie('user_specialty_id');
        $userSpecialty = UserSpecialty::find($userSpecialtyId);

        if(!$userSpecialtyId){
            Toast::warning('Виберіть свою спеціальність');
            return;
        }

        $userId = Auth::id();

        $userSpecialtySubject = UserSpecialtySubject::where([
            'user_specialty_id' => $userSpecialtyId,
            'subject_id' => $subjectId,
        ])->first();

        if($userSpecialtySubject){
            $userSpecialtySubject->delete();
            Toast::error("Дисципліна скасована «{$subjectName}»");

            // Логування скасування дисципліни
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'subject_name' => $subjectName,
                    'specialty_name' => $userSpecialty->specialty,
                    'is_student_choice' => $userSpecialtySubject->is_student_choice
                ])
                ->log(
                    $userSpecialtySubject->is_student_choice
                        ? "Студент: {$userSpecialty->full_name} скасував дисципліну: {$subjectName} ({$userSpecialty->specialty})"
                        : "Дисципліна: {$subjectName} ({$userSpecialty->specialty}) скасована адміністратором за студента: {$userSpecialty->full_name}"
                );


            return;
        } else {
            $selectedSubjectsCount = UserSpecialtySubject::where('user_specialty_id', $userSpecialtyId)->count();
            $groupSheet = new GroupsSheet();
            $groups = array_column($groupSheet->readAssoc(), 'electiveCount','group');
            $electiveCount = $groups[$userSpecialty->group] ?? 6;

            if($selectedSubjectsCount >= $electiveCount){
                Toast::warning('Можна обрати не більше '.$electiveCount.' дисциплін.');
                return;
            }

            $userSpecialtySubjectData = [
                'user_id' => $userId,
                'user_specialty_id' => $userSpecialtyId,
                'subject_id' => $subjectId,
            ];

            if ($userSpecialty->user_id != $userId) {
                $userSpecialtySubjectData['is_student_choice'] = false;
            } else {
                $userSpecialtySubjectData['is_student_choice'] = true;
            }
            $userSpecialtySubject = UserSpecialtySubject::create($userSpecialtySubjectData);
            Toast::success("Дисципліна обрана «{$subjectName}»");

            // Логування вибору дисципліни
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'subject_name' => $subjectName,
                    'specialty_name' => $userSpecialty->specialty,
                    'is_student_choice' => $userSpecialtySubject->is_student_choice
                ])
                ->log(
                    $userSpecialtySubject->is_student_choice
                        ? "Дисципліна обрана студентом {$userSpecialty->full_name}: {$subjectName} ({$userSpecialty->specialty})"
                        : "Дисципліна:  {$subjectName} ({$userSpecialty->specialty})  призначена адміністратором за студента: {$userSpecialty->full_name} "
                );

            return;
        }
    }

    public function chooseSpecialty($id, $text){
        Cookie::queue('user_specialty_id', $id, 1440);
        Toast::info("Вибрано: ".$text);

        // Логування вибору спеціальності
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'specialty_name' => $text   // використовуємо ім'я спеціальності
            ])
            ->log("Вибір спеціальності: {$text}");
    }
}
