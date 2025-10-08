<?php
namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Screen\Layouts\Table;
use Orchid\Support\Facades\Layout;
use Spatie\Activitylog\Models\Activity;

class ActivityLogScreen extends Screen
{

    public function name(): ?string
    {
        return 'Журнал подій';
    }

    public function  description(): ?string
    {
        return 'Журнал подій через Spatie Activitylog';
    }
    public function query(): array
    {
        return [
            'logs' => Activity::with('causer')->latest()->paginate(),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('logs', [
                TD::make('id', 'ID')->width('70px'),
                TD::make('causer_id', 'Користувач')->render(fn($log) => "(ID: $log->causer_id) " . $log->causer?->name  ?? 'Гість'),
                TD::make('description', 'Опис'),
                TD::make('created_at', 'Дата'),
            ]),
        ];
    }
}
