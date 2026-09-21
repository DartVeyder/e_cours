@extends('platform::app')

@section('body')
<div class="auth-page-wrapper d-flex flex-column justify-content-between min-vh-100 py-4" style="background: radial-gradient(circle at 10% 20%, rgba(13, 110, 253, 0.12), transparent 40%), radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.12), transparent 40%), #0f172a; min-height: 100vh;">
    {{-- Top Brand Header --}}
    <div class="container text-center pt-3 pb-2">
        <a href="{{ \Orchid\Support\Facades\Dashboard::prefix() }}" class="text-decoration-none d-inline-flex flex-column align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 shadow" style="width: 58px; height: 58px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: 2px solid rgba(255,255,255,0.2); box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.5) !important;">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" viewBox="0 0 16 16">
                    <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5ZM8 8.46 1.758 5.965 8 3.052l6.242 2.913L8 8.46Z"/>
                    <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Z"/>
                </svg>
            </div>
            <div class="h3 fw-bold text-white mb-0 tracking-wide" style="letter-spacing: 0.5px;">
                E-COURS
            </div>
            <div class="text-white-50 small mt-1" style="max-width: 480px; font-size: 0.88rem; line-height: 1.4;">
                Дрогобицький державний педагогічний університет імені Івана Франка
            </div>
        </a>
    </div>

    {{-- Main Content Card --}}
    <div class="container my-auto py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: #ffffff; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;">
                    <div class="card-body p-4 p-md-5">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auth Footer --}}
    <div class="container pb-2 pt-3">
        <div class="text-center text-white-50 small">
            <div class="mb-2">
                <span>© {{ date('Y') }} <strong>{{ config('app.name', 'E-Cours') }}</strong> — ДДПУ ім. Івана Франка</span>
            </div>
            <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
                <a href="{{ route('platform.changelog') }}" class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-20 px-2 py-1 rounded-pill text-decoration-none" title="Журнал змін">
                    v{{ config('app.version', '1.5.0') }}
                </a>
                <span>•</span>
                <span>Електронний вибір дисциплін</span>
                @if(config('app.telegram_url'))
                    <span>•</span>
                    <a href="{{ config('app.telegram_url') }}" target="_blank" rel="noopener noreferrer" class="text-info text-decoration-none d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.017 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3 0-.749.16-3.056 1.12z"/>
                        </svg>
                        <span>Підтримка Telegram</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.container,
.container-fluid,
.container-lg,
.container-md,
.container-sm,
.container-xl,
.container-xxl {
    --bs-gutter-x: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}
body {
    background: #0f172a !important;
}
</style>
@endsection
