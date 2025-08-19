<?php

namespace App\Orchid\Screens\Selsubject;

use App\Models\Subject;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use App\Orchid\Layouts\SelSubject\SelSubjectListLayout;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
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
        return [
            'subjects' =>  Subject::filters()->defaultSort('id', 'asc')
                ->paginate(),

        ];
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

    private function specialtiesButtons()
    {
        $array = [];
        $specialties = Auth::user()->load('specialties')->specialties;
        foreach ($specialties as $specialty) {
            $array[]  = Button::make($specialty->specialty . "( $specialty->group  )")
                ->method('chooseSpecialty',
                    [
                        'id' => $specialty->id,
                        'text' => $specialty->specialty . "( $specialty->group  )",
                    ]);
        }
        $userSpecialtyId = request()->cookie('user_specialty_id');
        if(!$userSpecialtyId){
            $titleButtons = 'Виберіть спеціальність';
        }else{
           $userSpecialty = UserSpecialty::find($userSpecialtyId);
           $titleButtons =  $userSpecialty->specialty . " (" . $userSpecialty->group . ")";
        }

        return DropDown::make( $titleButtons)
            ->list(
                $array,
            );

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
            Button::make('Загрузити дисципліни')
                ->icon('reload')
                ->method('importFromGoogleSheet'),

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

    public function importFromGoogleSheet()
    {
        $selsubjectSheet = new SelsubjectSheet();
        foreach ($selsubjectSheet->readAssoc() as $row)
        {
            Subject::updateOrCreate(
                ['name' => $row['name']], // перевірка унікальності по name
                [
                    'department' => $row['department'] ?? null,
                    'annotation' => $row['annotation'] ?? null,
                    'control_type' => $row['control_type'] ?? null,
                    'credits' => $row['credits'] ?? null,
                    'status' => $row['status'] ?? null,
                    'semester' => $row['semester'] ?? null,
                    'max_min_students' => $row['max_min_students'] ?? null,
                    'not_for_op' => $row['not_for_op'] ?? null,
                ]
            );
        }
    }

    public function chooseSubject($subjectId)
    {
        $userSpecialtyId = request()->cookie('user_specialty_id');
        $userId = Auth::id();

        if($userSpecialtyId ){
            $record = UserSpecialtySubject::where([
                'user_id' => $userId,
                'user_specialty_id' => $userSpecialtyId,
                'subject_id' => $subjectId,
            ])->first();

            $record ? $record->delete() : UserSpecialtySubject::create([
                'user_id' => $userId,
                'user_specialty_id' => $userSpecialtyId,
                'subject_id' => $subjectId,
            ]);
        }

    }

    public function chooseSpecialty($id, $text){
        Cookie::queue('user_specialty_id', $id, 1440);
        Toast::info("Вибрано: ".$text);
    }
}
