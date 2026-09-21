<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ChangelogScreen extends Screen
{
    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Журнал змін (Changelog)';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Історія оновлень, нові можливості та виправлення в системі E-Cours';
    }

    /**
     * Query data.
     */
    public function query(): iterable
    {
        $changelogPath = base_path('CHANGELOG.md');
        $rawContent = file_exists($changelogPath) ? file_get_contents($changelogPath) : '';
        $currentVersion = (string) config('app.version', '1.5.0');

        $releases = [];
        if (!empty($rawContent)) {
            // Regex to split CHANGELOG by version headers: ## [X.Y.Z] - YYYY-MM-DD
            preg_match_all(
                '/##\s*\[(.*?)\]\s*-\s*(\d{4}-\d{2}-\d{2})(.*?)(?=(?:##\s*\[|\Z))/s',
                $rawContent,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $version = trim($match[1]);
                $date = trim($match[2]);
                $bodyMarkdown = trim($match[3]);

                // Convert markdown body to HTML
                $html = Str::markdown($bodyMarkdown);

                $releases[] = [
                    'version'    => $version,
                    'date'       => $date,
                    'is_current' => ($version === $currentVersion),
                    'html'       => $html,
                ];
            }
        }

        return [
            'releases'       => $releases,
            'currentVersion' => $currentVersion,
        ];
    }

    /**
     * Action buttons for screen.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('На головну')
                ->icon('bs.house')
                ->route('platform.main'),

            Link::make('Аналітика вибору')
                ->icon('bs.bar-chart-line')
                ->route('platform.analytics'),
        ];
    }

    /**
     * Views for the screen.
     */
    public function layout(): iterable
    {
        return [
            Layout::view('system.changelog'),
        ];
    }
}
