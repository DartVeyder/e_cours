<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Student;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class StudentArchivedListLayout extends Table
{
    protected $target = 'students';

    protected function columns(): iterable
    {
        return [
            TD::make('full_name', 'ПІБ')
                ->sort()
                ->style('white-space: nowrap;'),

            TD::make('group_name', 'Група')
                ->sort()
                ->style('white-space: nowrap;')
                ->render(fn ($s) => $s->group_name ?: '—'),

            TD::make('card_id', 'ЄДЕБО')
                ->sort()
                ->style('white-space: nowrap;')
                ->render(fn ($s) => $s->card_id ?: '—'),

            TD::make('email', 'Email')
                ->sort()
                ->style('white-space: nowrap;')
                ->render(fn ($s) => $s->email ?: '—'),

            TD::make('specialty', 'Спеціальність')
                ->sort(),

            TD::make('study_status', 'Статус')
                ->sort()
                ->style('white-space: nowrap;')
                ->render(function ($s) {
                    if (empty($s->study_status)) {
                        return '<span class="text-muted">—</span>';
                    }
                    $color = match ($s->study_status) {
                        'Відраховано' => 'danger',
                        'Зараховано'  => 'success',
                        default       => 'secondary',
                    };
                    return "<span class=\"badge bg-{$color} bg-opacity-75\">{$s->study_status}</span>";
                }),

            TD::make('deleted_at', 'Дата архівації')
                ->sort()
                ->style('white-space: nowrap;')
                ->render(fn ($s) => $s->deleted_at?->format('d.m.Y H:i') ?? '—'),

            TD::make('actions', '')
                ->align(TD::ALIGN_CENTER)
                ->render(fn ($s) => Button::make('Відновити')
                    ->icon('bs.arrow-counterclockwise')
                    ->confirm('Відновити студента ' . e($s->full_name) . ' з архіву?')
                    ->method('restore', ['id' => $s->id])
                ),
        ];
    }
}