@extends('platform::auth')
@section('title', 'Вхід до системи — ' . config('app.name', 'E-Cours'))

@section('content')
    <div class="login-content">
        {{-- Card Heading --}}
        <div class="text-center mb-4">
            <h1 class="h4 fw-bold text-dark mb-1">
                Вхід до системи
            </h1>
            <p class="text-muted small mb-0">
                Каталог вибіркових освітніх компонент
            </p>
        </div>

        {{-- Error Alerts --}}
        @if(!empty($errors) && count($errors) > 0)
            <div class="alert alert-danger rounded-3 p-3 mb-4 border-0 shadow-sm" role="alert" style="background: rgba(220, 53, 69, 0.08); border-left: 4px solid #dc3545 !important;">
                <div class="d-flex align-items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#dc3545" class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-0.5" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <div class="small text-danger">
                        @php
                            $allErrors = method_exists($errors, 'all') ? $errors->all() : (is_iterable($errors) ? $errors : []);
                        @endphp
                        @foreach ($allErrors as $error)
                            @if(is_iterable($error))
                                @foreach($error as $subErr)
                                    <div class="fw-semibold mb-1">{{ $subErr }}</div>
                                @endforeach
                            @else
                                <div class="fw-semibold mb-1">{{ $error }}</div>
                            @endif
                        @endforeach
                        <div class="text-muted mt-1" style="font-size: 0.82rem;">
                            Будь ласка, переконайтеся, що ви обрали корпоративний акаунт <strong>@dspu.edu.ua</strong> або зверніться до служби підтримки.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Primary Action: Google SSO Button --}}
        <div class="mb-3">
            <a href="/auth/google/redirect" 
               class="btn btn-outline-dark w-100 py-2 px-3 rounded-pill d-flex align-items-center justify-content-center gap-2 text-decoration-none shadow-sm google-login-btn"
               style="border-color: #cbd5e1; background: #ffffff; color: #1e293b; font-size: 0.98rem; transition: all 0.2s ease;">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                <span class="fw-semibold">Увійти через Google</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 ms-auto d-none d-sm-inline" style="font-size: 0.75rem;">
                    @dspu.edu.ua
                </span>
            </a>
        </div>

        {{-- Student Tip Callout --}}
        <div class="p-3 rounded-3 mb-4" style="background: rgba(13, 110, 253, 0.05); border: 1px solid rgba(13, 110, 253, 0.12);">
            <div class="d-flex align-items-start gap-2">
                <span class="text-primary mt-0.5">🎓</span>
                <div class="small text-secondary" style="font-size: 0.85rem; line-height: 1.45;">
                    <strong>Для студентів та викладачів:</strong> вхід здійснюється без створення пароля. Використовуйте вашу університетську пошту <strong>@dspu.edu.ua</strong>.
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="d-flex align-items-center my-3">
            <hr class="flex-grow-1 my-0 text-muted opacity-25">
            <span class="px-3 text-muted small" style="font-size: 0.82rem;">або</span>
            <hr class="flex-grow-1 my-0 text-muted opacity-25">
        </div>

        {{-- Admin Local Login Section --}}
        @if($isLockUser ?? false)
            <form class="mt-3" role="form" method="POST" data-controller="form" data-form-need-prevents-form-abandonment-value="false" data-action="form#submit" action="{{ route('platform.login.auth') }}">
                @csrf
                @include('platform::auth.lockme')
            </form>
        @else
            <div>
                <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none w-100 text-center py-2 d-flex align-items-center justify-content-center gap-1" id="adminToggleBtn" onclick="toggleAdminLoginForm()" style="font-size: 0.84rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.535.536A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1H7.163a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg>
                    <span>Вхід для адміністраторів (за паролем)</span>
                    <span id="adminToggleArrow" class="ms-1">▾</span>
                </button>

                <div id="adminLoginFormContainer" style="display: {{ old('email') ? 'block' : 'none' }};" class="mt-3 pt-3 border-top">
                    <form role="form" method="POST" data-controller="form" data-form-need-prevents-form-abandonment-value="false" data-action="form#submit" action="{{ route('platform.login.auth') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                Електронна пошта (Email)
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="form-control rounded-3" placeholder="admin@dspu.edu.ua" style="font-size: 0.92rem;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">
                                Пароль
                            </label>
                            <input type="password" name="password" required autocomplete="current-password" class="form-control rounded-3" placeholder="••••••••" style="font-size: 0.92rem;">
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1">
                            <label class="form-check small text-muted mb-0">
                                <input type="checkbox" name="remember" value="true" class="form-check-input" {{ !old('remember') || old('remember') === 'true' ? 'checked' : '' }}>
                                <span class="form-check-label">Запам'ятати мене</span>
                            </label>

                            <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0v-2z"/>
                                    <path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3z"/>
                                </svg>
                                <span>Увійти</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Help & Support Link --}}
        <div class="text-center mt-4 pt-3 border-top">
            <small class="text-muted d-inline-flex align-items-center gap-1 flex-wrap justify-content-center">
                <span>Виникли труднощі з доступом?</span>
                @if(config('app.telegram_url'))
                    <a href="{{ config('app.telegram_url') }}" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold text-decoration-none">
                        Підтримка в Telegram
                    </a>
                @else
                    <span class="text-secondary fw-semibold">Зверніться до деканату</span>
                @endif
            </small>
        </div>
    </div>

    <script>
    function toggleAdminLoginForm() {
        const container = document.getElementById('adminLoginFormContainer');
        const arrow = document.getElementById('adminToggleArrow');
        if (!container) return;

        const isHidden = container.style.display === 'none';
        container.style.display = isHidden ? 'block' : 'none';
        if (arrow) {
            arrow.innerText = isHidden ? '▴' : '▾';
        }
    }
    </script>

    <style>
    .google-login-btn:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }
    </style>
@endsection
