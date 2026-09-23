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
                ->icon('bs.journal-bookmark-fill')
                ->route('platform.selsubjects'),
            Menu::make('Одногрупники')
                ->icon('bs.people-fill')
                ->route('platform.classmates')
                ->canSee(
                    !auth()->user()?->hasAccess('platform.systems.roles') &&
                    !auth()->user()?->hasAccess('platform.systems.users') &&
                    !auth()->user()?->hasAccess('platform.systems.students') &&
                    !auth()->user()?->hasAccess('platform.systems.subjects') &&
                    !auth()->user()?->hasAccess('dekanat') &&
                    !auth()->user()?->roles->contains('slug', 'administrator') &&
                    !auth()->user()?->roles->contains('slug', 'admin') &&
                    !auth()->user()?->roles->contains('slug', 'dekanat')
                ),
            Menu::make('Предмети')
                ->icon('bs.book-half')
                ->permission('platform.systems.subjects')
                ->route('platform.subjects'),
            Menu::make('Аналітика вибору')
                ->icon('bs.graph-up-arrow')
                ->permission('platform.systems.students')
                ->route('platform.analytics'),
            Menu::make('Студенти')
                ->icon('bs.mortarboard-fill')
                ->permission('platform.systems.students')
                ->route('platform.students'),
            Menu::make('Архів студентів')
                ->icon('bs.archive-fill')
                ->permission('platform.systems.students')
                ->route('platform.students.archived'),
            Menu::make('Групи')
                ->icon('bs.diagram-3-fill')
                ->permission('platform.systems.groups')
                ->route('platform.groups'),
            Menu::make('Журнал подій')
                ->icon('bs.clock-history')
                ->permission('platform.systems.logs')
                ->route('platform.activity.logs'),
            Menu::make('Логи')
                ->icon('bs.terminal-fill')
                ->permission('platform.systems.logs')
                ->route('platform.logs'),

            Menu::make('Налаштування системи')
                ->icon('bs.gear-fill')
                ->permission('platform.systems.roles')
                ->route('platform.settings'),

            Menu::make('Google Таблиці')
                ->icon('bs.file-earmark-spreadsheet-fill')
                ->permission('platform.systems.roles')
                ->route('platform.settings.google-sheets'),



            Menu::make(__('Users'))
                ->icon('bs.person-fill-gear')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield-fill-check')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),

            Menu::make('Чорний список IP')
                ->icon('bs.shield-fill-x')
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
