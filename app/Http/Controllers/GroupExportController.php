<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Services\GroupExcelExport;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupExportController extends Controller
{
    public function exportExcel(string $group): StreamedResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['department', 'roles']);
        }

        $isStaff = $user && (
            $user->hasAccess('platform.systems.groups') ||
            $user->hasAccess('platform.systems.students') ||
            $user->roles->contains('slug', 'administrator') ||
            $user->roles->contains('slug', 'admin') ||
            $user->roles->contains('slug', 'dekanat')
        );

        if (!$isStaff) {
            abort(403, 'Доступ до експорту груп заборонено.');
        }

        // Ізоляція для деканату: перевіряємо, чи належить група факультету користувача
        if ($user->roles->contains('slug', 'dekanat') && $user->department_id) {
            $groupModel = Group::where('name', $group)->first();
            if ($groupModel && $groupModel->department_id && $groupModel->department_id !== $user->department_id) {
                abort(403, 'Доступ заборонено: група належить іншому факультету/підрозділу.');
            }
        }

        activity()
            ->causedBy($user)
            ->withProperties(['group' => $group])
            ->log("Вивантаження Excel-звіту по групі «{$group}»");

        return (new GroupExcelExport())->export($group);
    }
}
