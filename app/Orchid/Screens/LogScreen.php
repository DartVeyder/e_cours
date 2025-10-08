<?php
namespace App\Orchid\Screens;

use Illuminate\Pagination\LengthAwarePaginator;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\SimpleMDE;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Screen\Layouts\Table;
use Orchid\Support\Facades\Layout;
use Spatie\Activitylog\Models\Activity;

class LogScreen extends Screen
{

    public function name(): ?string
    {
        return 'Логи';
    }

    public function  description(): ?string
    {
        return 'Перегляд логів з файлу storage/logs/laravel.log';
    }
    public function query(): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return ['logs' => collect([])];
        }

        $content = file_get_contents($logPath);

        // Розбиваємо на окремі записи по датах
        preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}].*(?=(?:\[\d{4}-\d{2}-\d{2}|\Z))/sU', $content, $matches);
        $entries = collect($matches[0] ?? [])->reverse()->values();

        // Пагінація
        $perPage = 20;
        $currentPage = request('page', 1);

        $items = $entries->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $entries->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'logs' => $paginator,
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('logs', [
                TD::make('date', 'Дата')
                    ->render(function ($entry) {
                        preg_match('/\[(.*?)\]/', $entry, $m);
                        return $m[1] ?? '-';
                    })
                    ->width('200px'),

                TD::make('level', 'Рівень')
                    ->render(function ($entry) {
                        preg_match('/\]\s([a-zA-Z]+)\./', $entry, $m);
                        return strtoupper($m[1] ?? '-');
                    })
                    ->width('100px'),

                TD::make('message', 'Повідомлення')
                    ->render(function ($entry) {
                        // Короткий текст
                        $lines = explode("\n", trim($entry));
                        $short = implode("\n", array_slice($lines, 0, 5)); // перші 5 рядків
                        $isLong = count($lines) > 5;

                        $html = '<pre class="text-xs whitespace-pre-wrap">'.e($short).'</pre>';

                        if ($isLong) {
                            $rest = implode("\n", array_slice($lines, 5));
                            $html .= '<details class="mt-1"><summary class="text-blue-600 cursor-pointer">Показати повністю</summary><pre class="text-xs whitespace-pre-wrap">'.e($rest).'</pre></details>';
                        }

                        return $html;
                    })
                    ->width('100%')
                    ->align('start') ,
            ]),
        ];
    }
}
