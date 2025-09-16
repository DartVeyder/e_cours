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
            Menu::make('Студенти')
                ->permission('platform.systems.students')
                ->route('platform.students'),
            Menu::make('Групи')
                ->route('platform.groups'),
            Menu::make('Журнал подій')
                ->permission('platform.systems.logs')
                ->route('platform.activity.logs'),


            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
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
                ->addPermission('platform.systems.users', __('Users'))

                //->addPermission('platform.selectedStudents', 'Вибір за студентів')
        ];
    }
}
