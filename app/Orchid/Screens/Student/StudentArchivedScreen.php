<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Student;

use App\Models\UserSpecialty;
use App\Orchid\Layouts\Student\StudentArchivedListLayout;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class StudentArchivedScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'students' => UserSpecialty::onlyTrashed()
                ->orderByDesc('deleted_at')
                ->paginate(50),
        ];
    }

    public function name(): ?string
    {
        return 'Архів студентів';
    }

    public function description(): ?string
    {
        return 'Студенти, яких було видалено з Google Таблиці або відраховано';
    }

    public function permission(): ?iterable
    {
        return ['platform.systems.students'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Студенти')
                ->icon('bs.arrow-left')
                ->route('platform.students'),
        ];
    }

    public function layout(): iterable
    {
        return [
            StudentArchivedListLayout::class,
        ];
    }

    /**
     * Відновлення студента з архіву.
     */
    public function restore(int $id): void
    {
        $student = UserSpecialty::withTrashed()->findOrFail($id);
        $student->restore();

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['student_id' => $id, 'full_name' => $student->full_name])
            ->log("Студента \"{$student->full_name}\" відновлено з архіву");

        Toast::success("Студента «{$student->full_name}» відновлено з архіву.");
    }
}