<?php

namespace App\Orchid\Screens\Security;

use App\Models\IpBlacklist;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class IpBlacklistScreen extends Screen
{
    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Чорний список IP (Anti-DDoS / Brute Force)';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Керування заблокованими IP-адресами та моніторинг підозрілої активності';
    }

    /**
     * The screen's permissions.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.roles',
        ];
    }

    /**
     * Query data.
     */
    public function query(): array
    {
        return [
            'blacklists' => IpBlacklist::latest()->paginate(20),
        ];
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Заблокувати IP вручну')
                ->modal('manualBlockModal')
                ->method('manualBlock')
                ->icon('bs.shield-slash')
                ->type(Color::DANGER),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): array
    {
        return [
            Layout::table('blacklists', [
                TD::make('id', 'ID')->width('70px'),
                TD::make('ip_address', 'IP-адреса')
                    ->render(fn (IpBlacklist $item) => "<span class='font-monospace fw-bold text-danger'>{$item->ip_address}</span>"),
                TD::make('reason', 'Причина блокування'),
                TD::make('request_count', 'Спроб/Запитів')
                    ->render(fn (IpBlacklist $item) => $item->request_count > 0 ? "<span class='badge bg-warning text-dark'>{$item->request_count}</span>" : '—'),
                TD::make('blocked_until', 'Заблоковано до')
                    ->render(function (IpBlacklist $item) {
                        if (! $item->blocked_until) {
                            return "<span class='badge bg-danger'>Безстроково</span>";
                        }
                        $isExpired = $item->blocked_until->isPast();
                        $badgeClass = $isExpired ? 'bg-secondary' : 'bg-primary';
                        $statusText = $isExpired ? ' (Закінчився)' : '';
                        return "<span class='badge {$badgeClass}'>{$item->blocked_until->format('d.m.Y H:i')}{$statusText}</span>";
                    }),
                TD::make('created_at', 'Дата додавання')
                    ->render(fn (IpBlacklist $item) => $item->created_at?->format('d.m.Y H:i') ?? '—'),
                TD::make('actions', 'Дії')
                    ->render(fn (IpBlacklist $item) => Button::make('Розблокувати')
                        ->icon('bs.unlock')
                        ->type(Color::SUCCESS)
                        ->confirm("Ви дійсно бажаєте розблокувати IP {$item->ip_address}?")
                        ->method('unblock', ['ip' => $item->ip_address])
                    ),
            ]),

            Layout::modal('manualBlockModal', [
                Layout::rows([
                    Input::make('ip')
                        ->title('IP-адреса')
                        ->placeholder('192.168.1.100')
                        ->required()
                        ->help('Вкажіть IPv4 або IPv6 адресу для блокування'),
                    Input::make('reason')
                        ->title('Причина блокування')
                        ->placeholder('Підозріла активність / ручне блокування')
                        ->required(),
                    Input::make('hours')
                        ->title('Тривалість блокування (у годинах)')
                        ->type('number')
                        ->value(24)
                        ->help('Вкажіть 0 для безстрокового блокування'),
                ]),
            ])->title('Ручне блокування IP-адреси')->applyButton('Заблокувати'),
        ];
    }

    /**
     * Manually block an IP.
     */
    public function manualBlock(Request $request): void
    {
        $validated = $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|max:255',
            'hours' => 'nullable|integer|min:0',
        ]);

        $hours = isset($validated['hours']) && (int) $validated['hours'] > 0 ? (int) $validated['hours'] : null;

        IpBlacklist::blockIp($validated['ip'], $validated['reason'], $hours);

        Toast::success("IP-адресу {$validated['ip']} успішно заблоковано.");
    }

    /**
     * Unblock an IP.
     */
    public function unblock(Request $request): void
    {
        $ip = (string) $request->input('ip');

        if ($ip && IpBlacklist::unblockIp($ip)) {
            Toast::info("IP-адресу {$ip} успішно розблоковано.");
        } else {
            Toast::warning("Не вдалося розблокувати IP {$ip}.");
        }
    }
}
