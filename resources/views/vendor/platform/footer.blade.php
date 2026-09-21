<div class="user-select-none py-3 text-muted small border-top mt-4" style="border-color: rgba(0,0,0,0.07) !important;">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 px-2">
        {{-- Left: Copyright & University --}}
        <div class="text-center text-md-start">
            <div>
                <span>© {{ date('Y') }} <strong>{{ config('app.name', 'E-Cours') }}</strong> — ДДПУ ім. Івана Франка</span>
            </div>
            <div class="d-inline-flex align-items-center flex-wrap gap-2 mt-1">
                <a href="{{ route('platform.changelog') }}" class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill text-decoration-none" title="Переглянути журнал змін (Changelog)" style="transition: all 0.2s ease;" onmouseover="this.classList.add('bg-primary', 'text-white')" onmouseout="this.classList.remove('bg-primary', 'text-white')">
                    v{{ config('app.version', '1.5.0') }}
                </a>
                <span class="text-muted">•</span>
                <span class="text-muted">Електронний вибір дисциплін</span>
            </div>
        </div>

        {{-- Right: Developers & Telegram Link --}}
        <div class="d-flex align-items-center flex-wrap justify-content-center gap-3">
            @if(config('app.developer'))
                <div class="d-inline-flex align-items-center gap-1 text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                        <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                    </svg>
                    <span>{{ config('app.developer') }}</span>
                </div>
            @endif

            @if(config('app.telegram_url'))
                <a href="{{ config('app.telegram_url') }}" target="_blank" rel="noopener noreferrer" 
                   class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 text-decoration-none shadow-sm"
                   style="border-color: #229ED9; color: #229ED9; font-size: 0.82rem;"
                   onmouseover="this.style.backgroundColor='#229ED9'; this.style.color='#fff';"
                   onmouseout="this.style.backgroundColor='transparent'; this.style.color='#229ED9';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.017 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3 0-.749.16-3.056 1.12z"/>
                    </svg>
                    <span>Telegram</span>
                </a>
            @endif
        </div>
    </div>
</div>
