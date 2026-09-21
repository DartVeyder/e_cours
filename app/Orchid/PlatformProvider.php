<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Вибіркові освітні компоненти університету')
                ->route('platform.selsubjects'),
            Menu::make('Предмети')
                ->permission('platform.systems.subjects')
                ->route('platform.subjects'),
            Menu::make('Аналітика вибору')
                ->icon('bs.graph-up-arrow')
                ->permission('platform.systems.students')
                ->route('platform.analytics'),
            Menu::make('Студенти')
                ->permission('platform.systems.students')
                ->route('platform.students'),
            Menu::make('Групи')
                ->permission('platform.systems.groups')
                ->route('platform.groups'),
            Menu::make('Журнал подій')
                ->permission('platform.systems.logs')
                ->route('platform.activity.logs'),
            Menu::make('Логи')
                ->permission('platform.systems.logs')
                ->route('platform.logs'),

            Menu::make('Налаштування системи')
                ->icon('bs.gear')
                ->permission('platform.systems.roles')
                ->route('platform.settings'),

            Menu::make('Google Таблиці')
                ->icon('bs.file-earmark-spreadsheet')
                ->permission('platform.systems.roles')
                ->route('platform.settings.google-sheets'),



            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),

            Menu::make('Чорний список IP')
                ->icon('bs.shield-slash')
                ->permission('platform.systems.roles')
                ->route('platform.systems.ip-blacklist')
                ->divider(),
//
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.logs', __('Logs'))
                ->addPermission('platform.systems.students', __('Students'))
                ->addPermission('platform.systems.subjects', __('Subjects'))
                ->addPermission('platform.systems.groups', __('Groups'))
                ->addPermission('platform.systems.users', __('Users'))

                //->addPermission('platform.selectedStudents', 'Вибір за студентів')
        ];
    }
}
