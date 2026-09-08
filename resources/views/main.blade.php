<div class="container-fluid p-0">
    @if($isStaff)
        {{-- ========================================================================= --}}
        {{-- ПАНЕЛЬ АДМІНІСТРАТОРА / ДЕКАНАТУ (ADMINISTRATOR & DEKANAT DASHBOARD)      --}}
        {{-- ========================================================================= --}}

        {{-- Admin Hero Banner --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                        🛡️ {{ $isAdmin ? 'Панель Адміністратора' : 'Панель Деканату' }}
                                    </span>

                                    @if($isSelectionEnabled)
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true" style="width: 0.6rem; height: 0.6rem;"></span>
                                            Вибір дисциплін ВІДКРИТО
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <i class="bi bi-lock-fill me-2"></i>
                                            Вибір дисциплін ЗАКРИТО
                                        </span>
                                    @endif

                                    @if($isTestMode)
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                            🧪 Тестовий режим Google Таблиць
                                        </span>
                                    @endif
                                </div>

                                <h1 class="h2 fw-bold text-white mb-2">
                                    Вітаємо, {{ $user->name ?? 'Користувач' }}!
                                </h1>
                                <p class="text-white-50 mb-4" style="max-width: 650px; font-size: 1.05rem; line-height: 1.6;">
                                    Управління навчальним процесом, вибірковими дисциплінами, студентами та синхронізацією даних з Google Таблицями.
                                </p>

                                <div class="d-flex flex-wrap gap-3">
                                    <a href="{{ route('platform.students') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-semibold d-inline-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                        </svg>
                                        Список студентів
                                    </a>

                                    <a href="{{ route('platform.subjects') }}" class="btn btn-outline-light px-4 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                        </svg>
                                        Дисципліни
                                    </a>

                                    @if($isAdmin)
                                        <a href="{{ route('platform.settings') }}" class="btn btn-outline-warning px-4 py-2 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319z"/>
                                            </svg>
                                            Налаштування
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                                <div class="p-4 rounded-4 text-center" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 320px;">
                                    <div class="mb-3 text-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/>
                                            <path d="M4.179 13.819A4.989 4.989 0 0 1 2.5 10.5V8.154l5.5 2.2v5.618a4.996 4.996 0 0 1-3.821-2.153Z"/>
                                        </svg>
                                    </div>
                                    <h6 class="text-white fw-bold mb-1">ДДПУ ім. І. Франка</h6>
                                    <p class="text-white-50 small mb-0">Система керування вибірковими дисциплінами</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Metrics Grid --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(13, 110, 253, 0.12); color: #0d6efd; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Студенти</span>
                            <div class="h3 fw-bold mb-0 text-dark">{{ number_format($totalStudents) }}</div>
                            <small class="text-muted">{{ number_format($studentsWithSelectionsCount) }} зробили вибір</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(25, 135, 84, 0.12); color: #198754; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Дисципліни</span>
                            <div class="h3 fw-bold mb-0 text-dark">{{ number_format($totalSubjects) }}</div>
                            <small class="text-muted">активних предметів</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(111, 66, 193, 0.12); color: #6f42c1; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .263.075zM10 8h3v1h-3V8zm0 2h3v1h-3v-1zm0 2h3v1h-3v-1zM7 2v1h2V2H7zm0 2v1h2V4H7zm0 2v1h2V6H7zM2 11v1h3v-1H2zm0 2v1h3v-1H2z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Групи</span>
                            <div class="h3 fw-bold mb-0 text-dark">{{ number_format($totalGroups) }}</div>
                            <small class="text-muted">академічних груп</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(253, 126, 20, 0.12); color: #fd7e14; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5c0 .538-.012 1.05-.034 1.536a3 3 0 1 1-1.133 5.89c-.79 1.865-1.878 2.774-3.333 2.774-1.455 0-2.543-.909-3.333-2.774a3 3 0 1 1-1.133-5.89A33.437 33.437 0 0 1 2.5.5zm.97 2.088c.046.39.112.784.198 1.178.291 1.34 1.008 2.45 2.148 2.924.288.12.593.208.914.262.247-1.378.816-2.536 1.77-3.415a7.71 7.71 0 0 1-5.03-.949z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Вибори</span>
                            <div class="h3 fw-bold mb-0 text-dark">{{ number_format($totalSelections) }}</div>
                            <small class="text-muted">обраних дисциплін</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Navigation & Recent Activity --}}
        <div class="row g-4 mb-4">
            {{-- Management Modules --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="background: var(--bs-card-bg, #fff);">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                            <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                        </svg>
                        Модулі керування
                    </h5>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <a href="{{ route('platform.students') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none border bg-light h-100">
                                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Студенти</h6>
                                    <small class="text-muted">Імпорт, вибір, списки</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('platform.subjects') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none border bg-light h-100">
                                <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Дисципліни</h6>
                                    <small class="text-muted">Каталог та семестри</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('platform.groups') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none border bg-light h-100">
                                <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .263.075z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Групи</h6>
                                    <small class="text-muted">Ліміти та спеціальності</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a href="{{ route('platform.selsubjects') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none border bg-light h-100">
                                <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">Вибір компонентів</h6>
                                    <small class="text-muted">Перегляд вибору</small>
                                </div>
                            </a>
                        </div>

                        @if($isAdmin)
                            <div class="col-sm-6">
                                <a href="{{ route('platform.settings.google-sheets') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none border bg-light h-100">
                                    <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                            <path d="M3 12.5h10v1H3v-1zm0-2h10v1H3v-1zm0-2h10v1H3v-1zm0-2h10v1H3v-1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Google Таблиці</h6>
                                        <small class="text-muted">Таблиці та аркуші</small>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6">
                                <a href="{{ route('platform.settings') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none border bg-light h-100">
                                    <div class="rounded-3 p-2 bg-secondary bg-opacity-10 text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Налаштування</h6>
                                        <small class="text-muted">Кампанія вибору</small>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Activity Log --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                            </svg>
                            Останні дії в системі
                        </h5>
                        <a href="{{ route('platform.activity.logs') }}" class="small text-decoration-none">Всі логи →</a>
                    </div>

                    @if($recentActivities->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($recentActivities as $act)
                                <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold text-dark small">{{ $act->description }}</div>
                                        <small class="text-muted">{{ $act->causer?->name ?? 'Користувач (ID: '.$act->causer_id.')' }}</small>
                                    </div>
                                    <span class="badge bg-light text-muted border small">{{ $act->created_at?->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small">
                            Жодних останніх подій не зафіксовано
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Administrator & Dekanat Detailed Instructions --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--bs-card-bg, #fff);">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                            <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-4.354-3.854a.5.5 0 0 1 .708 0L4.5 6.793l1.146-1.147a.5.5 0 1 1 .708.708l-1.5 1.5a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 0-.708z"/>
                        </svg>
                        Інструкція та регламент роботи адміністратора / деканату
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100 border" style="background: rgba(13, 110, 253, 0.03);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle">1</span>
                                    <h6 class="fw-bold mb-0 text-dark">Імпорт та синхронізація</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Завантажуйте списки студентів та дисциплін через розділ «Студенти» та «Предмети» кнопками імпорту з Google Таблиць. Усі налаштування таблиць та вкладок доступні у розділі «Google Таблиці».
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100 border" style="background: rgba(25, 135, 84, 0.03);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success rounded-circle">2</span>
                                    <h6 class="fw-bold mb-0 text-dark">Ліміти та відкриття вибору</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Перевірте семестрові ліміти дисциплін у розділі «Групи». Відкрийте кампанію вибору для студентів у «Налаштування системи». Після завершення терміну закрийте кампанію для фіксації результатів.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100 border" style="background: rgba(111, 66, 193, 0.03);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle" style="background: #6f42c1 !important;">3</span>
                                    <h6 class="fw-bold mb-0 text-dark">Коригування та експорт</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Працівники деканату можуть вибирати предмети від імені студента (кнопка «Вибрати» біля студента). Завантажуйте готові відомості вибору у форматах Excel та Google Sheets через експорт груп.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- ========================================================================= --}}
        {{-- ПАНЕЛЬ СТУДЕНТА (STUDENT DASHBOARD)                                       --}}
        {{-- ========================================================================= --}}

        {{-- Student Hero Banner --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                    <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                        🎓 Особистий кабінет студента
                                    </span>

                                    @if($isSelectionEnabled)
                                        <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true" style="width: 0.6rem; height: 0.6rem;"></span>
                                            Вибір дисциплін ВІДКРИТО
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold d-inline-flex align-items-center">
                                            <i class="bi bi-lock-fill me-2"></i>
                                            Вибір дисциплін ЗАКРИТО
                                        </span>
                                    @endif
                                </div>

                                <h1 class="h2 fw-bold text-white mb-2">
                                    Вітаємо, {{ $studentSpecialty->full_name ?? $user->name }}!
                                </h1>
                                <p class="text-white-50 mb-4" style="max-width: 650px; font-size: 1.05rem; line-height: 1.6;">
                                    Формуйте свою індивідуальну освітню траєкторію. Обирайте вибіркові дисципліни згідно з навчальним планом вашої освітньої програми.
                                </p>

                                <div class="d-flex flex-wrap gap-3">
                                    <a href="{{ route('platform.selsubjects') }}" class="btn btn-light text-primary px-4 py-3 rounded-pill shadow fw-bold d-inline-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                        </svg>
                                        👉 Перейти до вибору дисциплін
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                                <div class="p-4 rounded-4 text-center" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); width: 100%; max-width: 320px;">
                                    <div class="mb-3 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/>
                                            <path d="M4.179 13.819A4.989 4.989 0 0 1 2.5 10.5V8.154l5.5 2.2v5.618a4.996 4.996 0 0 1-3.821-2.153Z"/>
                                        </svg>
                                    </div>
                                    <h6 class="text-white fw-bold mb-1">ДДПУ ім. І. Франка</h6>
                                    <p class="text-white-50 small mb-0">Електронний вибір дисциплін</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Student Profile & Choice Progress Cards --}}
        <div class="row g-4 mb-4">
            {{-- Student Info Card --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                            </svg>
                            Дані студента
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-semibold">
                            {{ $studentSpecialty->group_name ?? 'Без групи' }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">ПІБ</small>
                            <span class="fw-semibold text-dark">{{ $studentSpecialty->full_name ?? $user->name }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Номер картки / ЄДЕБО</small>
                            <span class="fw-semibold text-dark">{{ $studentSpecialty->card_id ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Спеціальність</small>
                            <span class="fw-semibold text-dark">{{ $studentSpecialty->specialty ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Факультет / Інститут</small>
                            <span class="fw-semibold text-dark">{{ $studentSpecialty->department ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Освітня програма</small>
                            <span class="fw-semibold text-dark">{{ $studentSpecialty->education_program ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Ступінь та форма</small>
                            <span class="fw-semibold text-dark">{{ $studentSpecialty->degree ?? 'Бакалавр' }} / {{ $studentSpecialty->study_form ?? 'Денна' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Student Progress Card --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="background: var(--bs-card-bg, #fff);">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 16 16">
                                <path d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V.5z"/>
                                <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z"/>
                            </svg>
                            Стан вибору дисциплін
                        </h5>
                        @if($maxSubjectsLimit > 0 && $selectedSubjects->count() >= $maxSubjectsLimit)
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-semibold">
                                ✓ Вибір виконано
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill fw-semibold">
                                В процесі
                            </span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-semibold">Обрано дисциплін:</span>
                            <span class="fw-bold text-dark fs-5">
                                {{ $selectedSubjects->count() }} @if($maxSubjectsLimit > 0) <span class="text-muted fs-6">/ {{ $maxSubjectsLimit }}</span> @endif
                            </span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px; background: rgba(0,0,0,0.06);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated {{ $selectionProgressPercent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                 role="progressbar" 
                                 style="width: {{ $selectionProgressPercent }}%;" 
                                 aria-valuenow="{{ $selectionProgressPercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                    </div>

                    @if($selectedSubjects->isNotEmpty())
                        <div class="mb-0">
                            <small class="text-muted fw-semibold d-block mb-2">Ваші обрані дисципліни:</small>
                            <div class="d-flex flex-wrap gap-2" style="max-height: 120px; overflow-y: auto;">
                                @foreach($selectedSubjects as $subj)
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small" title="{{ $subj->name }}">
                                        @if($subj->pivot->semester)
                                            <span class="text-primary fw-bold">{{ $subj->pivot->semester }} сем:</span>
                                        @endif
                                        {{ Str::limit($subj->name, 35) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border rounded-3 p-3 text-center my-auto">
                            <p class="text-muted mb-2 small">Ви ще не обрали жодної дисципліни</p>
                            <a href="{{ route('platform.selsubjects') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                Розпочати вибір
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Student Step-by-Step Instructions & FAQ --}}
        <div class="row g-4">
            {{-- Steps Guide --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="background: var(--bs-card-bg, #fff);">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                        </svg>
                        Покрокова інструкція вибору дисциплін
                    </h5>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(13, 110, 253, 0.04); border-left: 4px solid #0d6efd;">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; min-width: 28px;">1</span>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Перейдіть до каталогу вибіркових дисциплін</h6>
                                <p class="text-muted small mb-0">
                                    Натисніть кнопку «Перейти до вибору дисциплін». Перегляньте перелік доступних дисциплін для вашого ступеня та курсу, ознайомтесь з анотаціями та робочими програмами.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(25, 135, 84, 0.04); border-left: 4px solid #198754;">
                            <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; min-width: 28px;">2</span>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Оберіть предмети на кожен семестр</h6>
                                <p class="text-muted small mb-0">
                                    Для кожної бажаної дисципліни натисніть відповідну кнопку семестру. Кількість обраних предметів у кожному семестрі повинна відповідати ліміту вашої академічної групи.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background: rgba(111, 66, 193, 0.04); border-left: 4px solid #6f42c1;">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="background: #6f42c1 !important; width: 28px; height: 28px; min-width: 28px;">3</span>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Перевірте збереження результатів</h6>
                                <p class="text-muted small mb-0">
                                    Обрані дисципліни миттєво зберігаються у вашому особистому кабінеті. Ви можете змінювати свій вибір, доки кампанія вибору відкрита деканатом.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FAQ & Rules --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4" style="background: var(--bs-card-bg, #fff);">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-warning" viewBox="0 0 16 16">
                            <path d="M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641l2.5-8.5z"/>
                        </svg>
                        Важливі правила та запитання
                    </h5>

                    <div class="accordion accordion-flush" id="studentFaqAccordion">
                        <div class="accordion-item border-0 mb-2 rounded-3" style="background: rgba(0,0,0,0.02);">
                            <h2 class="accordion-header" id="faqHeadingOne">
                                <button class="accordion-button collapsed fw-semibold text-dark bg-transparent py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne">
                                    Чи можна змінити обрані дисципліни?
                                </button>
                            </h2>
                            <div id="faqCollapseOne" class="accordion-collapse collapse" data-bs-parent="#studentFaqAccordion">
                                <div class="accordion-body text-muted small pt-0">
                                    Так, поки кампанія вибору відкрита, ви можете скасувати попередній вибір або замінити дисципліну на іншу у межах встановлених лімітів.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-2 rounded-3" style="background: rgba(0,0,0,0.02);">
                            <h2 class="accordion-header" id="faqHeadingTwo">
                                <button class="accordion-button collapsed fw-semibold text-dark bg-transparent py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo">
                                    Що робити, якщо дисципліна не обирається?
                                </button>
                            </h2>
                            <div id="faqCollapseTwo" class="accordion-collapse collapse" data-bs-parent="#studentFaqAccordion">
                                <div class="accordion-body text-muted small pt-0">
                                    Перевірте, чи не вичерпано ліміт вибору на цей семестр, та переконайтесь, що статус кампанії вибору відображається зеленим індикатором «ВІДКРИТО».
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 rounded-3" style="background: rgba(0,0,0,0.02);">
                            <h2 class="accordion-header" id="faqHeadingThree">
                                <button class="accordion-button collapsed fw-semibold text-dark bg-transparent py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree">
                                    Як переглянути робочу програму (силабус)?
                                </button>
                            </h2>
                            <div id="faqCollapseThree" class="accordion-collapse collapse" data-bs-parent="#studentFaqAccordion">
                                <div class="accordion-body text-muted small pt-0">
                                    У розділі вибору дисциплін натисніть на посилання робочої програми біля назви предмета для перегляду PDF-файлу з описом курсу.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
