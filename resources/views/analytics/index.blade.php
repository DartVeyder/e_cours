@php
    $summary = $analytics['summary'];
    $faculties = $analytics['faculties'];
    $specialties = $analytics['specialties'];
    $educationPrograms = $analytics['educationPrograms'] ?? collect();
    $filters = $analytics['filters'];
    $options = $analytics['filterOptions'];

    // Helper to generate drill-down URLs to the student list
    $getStudentListUrl = function (?string $dept = null, ?string $spec = null, ?string $status = null, ?string $prog = null) use ($filters) {
        $params = [];
        if (!empty($filters['entry_year']) && $filters['entry_year'] !== 'all') {
            $params['entry_year'] = $filters['entry_year'];
        }
        if (!empty($filters['degree']) && $filters['degree'] !== 'all') {
            $params['filter']['degree'] = $filters['degree'];
        }
        if (!empty($filters['study_form']) && $filters['study_form'] !== 'all') {
            $params['filter']['study_form'] = $filters['study_form'];
        }
        if ($dept && $dept !== 'Не вказано') {
            $params['filter']['department'] = $dept;
        }
        if ($spec && $spec !== 'Не вказано') {
            $params['filter']['specialty'] = $spec;
        }
        if ($prog && $prog !== 'Не вказано' && $prog !== $spec) {
            $params['filter']['education_program'] = $prog;
        }
        if ($status) {
            $params['subject_selection'] = $status;
        }
        return route('platform.students', $params);
    };
@endphp

<div class="container-fluid p-0">
    {{-- Header Banner --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                    📊 Аналітика вибору дисциплін
                                </span>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                    🎓 {{ $filters['degree'] ?: 'Всі освітні рівні' }}
                                </span>
                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                    📅 Вступ: {{ $filters['entry_year'] ?: 'Всі роки' }}
                                </span>
                            </div>

                            <h1 class="h2 fw-bold text-white mb-2">
                                Статус вибору дисциплін: {{ $filters['degree'] ?: 'Всі' }} ({{ $filters['entry_year'] ?: 'Всі роки' }})
                            </h1>
                            <p class="text-white-50 mb-0" style="max-width: 700px; font-size: 1.05rem; line-height: 1.6;">
                                Зведений моніторинг результатів кампанії вибору: кількість студентів, які обрали всі дисципліни, зробили частковий вибір або ще не визначилися, з деталізацією за факультетами, спеціальностями та освітніми програмами.
                            </p>
                        </div>

                        <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                            <div class="p-4 rounded-4 text-center" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 320px;">
                                <div class="text-white-50 small mb-1">Загальний прогрес кампанії</div>
                                <div class="h2 fw-bold text-success mb-2">{{ $summary['all_percent'] }}%</div>
                                <div class="progress rounded-pill bg-dark bg-opacity-50 mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $summary['all_percent'] }}%;" aria-valuenow="{{ $summary['all_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="small text-white-50">
                                    {{ number_format($summary['all']) }} з {{ number_format($summary['total']) }} студентів обрали всі дисципліни
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden dummy form to detach filter controls from Orchid's #post-form --}}
    <form id="analyticsFilterDummyForm" class="d-none" onsubmit="return false;"></form>

    {{-- Filter Panel --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bs-card-bg, #fff);">
        <div class="card-body p-4">
            <div id="analyticsFilterPanel" class="row g-3 align-items-end" onchange="event.stopPropagation()" oninput="event.stopPropagation()">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">
                        <i class="bi bi-calendar-event me-1"></i> Рік вступу
                    </label>
                    <select id="filter_entry_year" name="entry_year" form="analyticsFilterDummyForm" class="form-select rounded-3" onchange="event.stopPropagation()">
                        <option value="all" {{ ($filters['entry_year'] === 'all') ? 'selected' : '' }}>Всі роки вступу</option>
                        @foreach($options['entry_years'] as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['entry_year'] == $val) ? 'selected' : '' }}>
                                {{ $label }} {{ ($val == '2026') ? '(поточний набір)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">
                        <i class="bi bi-mortarboard me-1"></i> Освітній ступінь (рівень)
                    </label>
                    <select id="filter_degree" name="degree" form="analyticsFilterDummyForm" class="form-select rounded-3" onchange="event.stopPropagation()">
                        <option value="all" {{ ($filters['degree'] === 'all') ? 'selected' : '' }}>Всі рівні</option>
                        @foreach($options['degrees'] as $deg)
                            <option value="{{ $deg }}" {{ ($filters['degree'] == $deg) ? 'selected' : '' }}>
                                {{ $deg }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">
                        <i class="bi bi-building me-1"></i> Факультет / Підрозділ
                    </label>
                    <select id="filter_department" name="department" form="analyticsFilterDummyForm" class="form-select rounded-3" onchange="event.stopPropagation()">
                        <option value="all" {{ empty($filters['department']) || $filters['department'] === 'all' ? 'selected' : '' }}>Всі факультети</option>
                        @foreach($options['departments'] as $dept)
                            <option value="{{ $dept }}" {{ ($filters['department'] == $dept) ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold text-secondary small mb-1">
                        <i class="bi bi-book me-1"></i> Форма навчання
                    </label>
                    <select id="filter_study_form" name="study_form" form="analyticsFilterDummyForm" class="form-select rounded-3" onchange="event.stopPropagation()">
                        <option value="all" {{ empty($filters['study_form']) || $filters['study_form'] === 'all' ? 'selected' : '' }}>Всі форми навчання</option>
                        @foreach($options['study_forms'] as $sf)
                            <option value="{{ $sf }}" {{ ($filters['study_form'] == $sf) ? 'selected' : '' }}>
                                {{ $sf }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 pt-2 border-top">
                    <button type="button" onclick="submitAnalyticsFilters()" class="btn btn-primary px-4 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/>
                        </svg>
                        Застосувати фільтри
                    </button>

                    <a href="{{ route('platform.analytics', ['entry_year' => '2026', 'degree' => 'Магістр']) }}" class="btn btn-outline-secondary px-3 rounded-pill fw-semibold">
                        Скинути до «Магістри 2026»
                    </a>

                    <a href="{{ route('export.analytics.excel', array_filter(['entry_year' => $filters['entry_year'] !== 'all' ? $filters['entry_year'] : null, 'degree' => $filters['degree'] !== 'all' ? $filters['degree'] : null, 'department' => $filters['department'] !== 'all' ? $filters['department'] : null, 'study_form' => $filters['study_form'] !== 'all' ? $filters['study_form'] : null])) }}" class="btn btn-outline-success px-3 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        Експорт в Excel (.xlsx)
                    </a>

                    <div class="ms-auto">
                        <a href="{{ route('platform.students', array_filter(['entry_year' => $filters['entry_year'] !== 'all' ? $filters['entry_year'] : null, 'filter' => array_filter(['degree' => $filters['degree'] !== 'all' ? $filters['degree'] : null, 'department' => $filters['department'] !== 'all' ? $filters['department'] : null])])) }}" class="btn btn-outline-primary px-3 rounded-pill fw-semibold d-inline-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                            </svg>
                            Переглянути список студентів
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: var(--bs-card-bg, #fff);">
                <div class="d-flex align-items-center">
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(13, 110, 253, 0.12); color: #0d6efd; width: 56px; height: 56px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Всього здобувачів</span>
                        <div class="h3 fw-bold mb-0 text-dark">{{ number_format($summary['total']) }}</div>
                        <small class="text-muted">когорта вибору</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fully Chosen --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ $getStudentListUrl(null, null, 'all') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-card" style="background: var(--bs-card-bg, #fff); transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(25, 135, 84, 0.12); color: #198754; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Повністю обрали</span>
                            <div class="h3 fw-bold mb-0 text-success">{{ number_format($summary['all']) }}</div>
                            <small class="text-success fw-semibold">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">{{ $summary['all_percent'] }}%</span> від загальної к-сті
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Partially Chosen --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ $getStudentListUrl(null, null, 'partial') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-card" style="background: var(--bs-card-bg, #fff); transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(255, 193, 7, 0.15); color: #b48500; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 0 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3.5v-1h-1a.5.5 0 0 1-.5-.5zm2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256.911.751.911 1.342v.7c0 .591-.378 1.086-.911 1.342A3.5 3.5 0 0 0 4.5 12.5v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.086 8.6 8.591 8.6 8v-.7c0-.591.378-1.086.911-1.342A3.5 3.5 0 0 0 11.5 3.5v-1h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Частково обрали</span>
                            <div class="h3 fw-bold mb-0 text-warning" style="color: #c48300 !important;">{{ number_format($summary['partial']) }}</div>
                            <small class="text-warning fw-semibold" style="color: #c48300 !important;">
                                <span class="badge bg-warning bg-opacity-25 text-dark rounded-pill px-2">{{ $summary['partial_percent'] }}%</span> у процесі вибору
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Not Chosen --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ $getStudentListUrl(null, null, 'none') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-card" style="background: var(--bs-card-bg, #fff); transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <div class="d-flex align-items-center">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center me-3" style="background: rgba(220, 53, 69, 0.12); color: #dc3545; width: 56px; height: 56px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Не обрали жодної</span>
                            <div class="h3 fw-bold mb-0 text-danger">{{ number_format($summary['none']) }}</div>
                            <small class="text-danger fw-semibold">
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">{{ $summary['none_percent'] }}%</span> ще не визначилися
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Visual Distribution Progress Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--bs-card-bg, #fff);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <span class="fw-bold text-dark">Структура вибору дисциплін</span>
                <div class="d-flex align-items-center gap-3 small flex-wrap">
                    <span class="d-inline-flex align-items-center gap-1 text-success fw-semibold">
                        <span class="rounded-circle d-inline-block bg-success" style="width: 10px; height: 10px;"></span>
                        Повністю обрали: {{ $summary['all'] }} ({{ $summary['all_percent'] }}%)
                    </span>
                    <span class="d-inline-flex align-items-center gap-1 text-warning fw-semibold" style="color: #c48300 !important;">
                        <span class="rounded-circle d-inline-block bg-warning" style="width: 10px; height: 10px;"></span>
                        Частково: {{ $summary['partial'] }} ({{ $summary['partial_percent'] }}%)
                    </span>
                    <span class="d-inline-flex align-items-center gap-1 text-danger fw-semibold">
                        <span class="rounded-circle d-inline-block bg-danger" style="width: 10px; height: 10px;"></span>
                        Не обрали: {{ $summary['none'] }} ({{ $summary['none_percent'] }}%)
                    </span>
                </div>
            </div>

            <div class="progress rounded-pill overflow-hidden" style="height: 18px; background-color: #e9ecef;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $summary['all_percent'] }}%;" title="Повністю: {{ $summary['all_percent'] }}%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $summary['partial_percent'] }}%;" title="Частково: {{ $summary['partial_percent'] }}%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $summary['none_percent'] }}%;" title="Не обрали: {{ $summary['none_percent'] }}%"></div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-3 gap-2" id="analyticsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-semibold shadow-sm" id="faculties-tab" data-bs-toggle="pill" data-bs-target="#faculties-pane" type="button" role="tab" aria-controls="faculties-pane" aria-selected="true">
                🏛️ По факультетах ({{ $faculties->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-semibold shadow-sm" id="specialties-tab" data-bs-toggle="pill" data-bs-target="#specialties-pane" type="button" role="tab" aria-controls="specialties-pane" aria-selected="false">
                🎓 По спеціальностях та ОП ({{ $specialties->count() }})
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="analyticsTabsContent">
        {{-- TAB 1: Faculties Breakdown --}}
        <div class="tab-pane fade show active" id="faculties-pane" role="tabpanel" aria-labelledby="faculties-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bs-card-bg, #fff);">
                <div class="card-header bg-transparent border-0 p-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Розподіл за структурними підрозділами (факультетами)</h5>
                        <p class="text-muted small mb-0">Клікніть на показник для переходу до списку відповідних студентів</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">№</th>
                                <th style="min-width: 250px;">Факультет / Підрозділ</th>
                                <th class="text-center text-nowrap" style="width: 90px;">Всього</th>
                                <th class="text-center text-nowrap" style="width: 140px;">Повністю обрали</th>
                                <th class="text-center text-nowrap" style="width: 130px;">Частково</th>
                                <th class="text-center text-nowrap" style="width: 130px;">Не обрали</th>
                                <th style="min-width: 160px;">Прогрес вибору</th>
                                <th class="text-end pe-4 text-nowrap" style="width: 120px;">Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faculties as $index => $fac)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $fac['name'] }}</div>
                                        <small class="text-muted">{{ $fac['specialties_count'] }} спеціальностей</small>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($fac['name']) }}" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold">
                                            {{ $fac['total'] }}
                                        </a>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($fac['name'], null, 'all') }}" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold" title="Переглянути студентів, які повністю обрали дисципліни">
                                            {{ $fac['all'] }}
                                            <span class="small text-muted fw-normal ms-1">({{ $fac['all_percent'] }}%)</span>
                                        </a>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($fac['name'], null, 'partial') }}" class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold" title="Переглянути студентів, які частково обрали дисципліни">
                                            {{ $fac['partial'] }}
                                            <span class="small text-muted fw-normal ms-1">({{ $fac['partial_percent'] }}%)</span>
                                        </a>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($fac['name'], null, 'none') }}" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold" title="Переглянути студентів, які не обрали жодної дисципліни">
                                            {{ $fac['none'] }}
                                            <span class="small text-muted fw-normal ms-1">({{ $fac['none_percent'] }}%)</span>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 rounded-pill" style="height: 10px; background-color: #e9ecef;">
                                                <div class="progress-bar bg-success" style="width: {{ $fac['all_percent'] }}%;"></div>
                                                <div class="progress-bar bg-warning" style="width: {{ $fac['partial_percent'] }}%;"></div>
                                                <div class="progress-bar bg-danger" style="width: {{ $fac['none_percent'] }}%;"></div>
                                            </div>
                                            <span class="small fw-bold text-dark text-nowrap" style="min-width: 45px;">{{ $fac['completion_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <a href="{{ $getStudentListUrl($fac['name']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                            Студенти →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                                        За вибраними критеріями фільтрації здобувачів не знайдено.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: Specialties & Educational Programs Breakdown --}}
        <div class="tab-pane fade" id="specialties-pane" role="tabpanel" aria-labelledby="specialties-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bs-card-bg, #fff);">
                <div class="card-header bg-transparent border-0 p-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Розподіл за спеціальностями та освітніми програмами</h5>
                        <p class="text-muted small mb-0">
                            Для широких спеціальностей (напр., «Середня освіта») відображено кожну предметну освітню програму окремо
                        </p>
                    </div>

                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" id="toggleAllProgramsBtn" onclick="toggleAllPrograms()">
                            <i class="bi bi-arrows-expand me-1"></i> Розгорнути всі ОП
                        </button>
                        <input type="text" id="specialtySearchInput" form="analyticsFilterDummyForm" class="form-control form-control-sm rounded-pill px-3" placeholder="Швидкий пошук..." style="width: 230px;" oninput="event.stopPropagation(); filterSpecialtiesTable();" onkeyup="filterSpecialtiesTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="specialtiesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">№</th>
                                <th style="min-width: 280px; max-width: 400px;">Спеціальність / Освітня програма</th>
                                <th style="min-width: 170px; max-width: 250px;">Факультет</th>
                                <th class="text-center text-nowrap" style="width: 85px;">Всього</th>
                                <th class="text-center text-nowrap" style="width: 140px;">Повністю обрали</th>
                                <th class="text-center text-nowrap" style="width: 130px;">Частково</th>
                                <th class="text-center text-nowrap" style="width: 130px;">Не обрали</th>
                                <th style="min-width: 140px;">Прогрес</th>
                                <th class="text-end pe-4 text-nowrap" style="width: 110px;">Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($specialties as $index => $spec)
                                {{-- Main Specialty Row --}}
                                <tr class="specialty-row {{ $spec['is_broad'] ? 'table-row-broad' : '' }}" id="spec-row-{{ $index }}">
                                    <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td style="white-space: normal; word-break: break-word;">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span class="fw-bold text-dark fs-6 specialty-name">{{ $spec['name'] }}</span>
                                            @if($spec['is_broad'])
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 small">
                                                    Широка спеціальність: {{ $spec['programs']->count() }} ОП
                                                </span>
                                            @endif
                                        </div>

                                        @if($spec['is_broad'])
                                            <div class="mt-1">
                                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small text-primary fw-semibold toggle-spec-btn" onclick="toggleSpecPrograms({{ $index }})">
                                                    <span id="toggle-icon-{{ $index }}">▶</span> Показати освітні програми ({{ $spec['programs']->count() }})
                                                </button>
                                            </div>
                                        @elseif(!empty($spec['programs']) && $spec['programs']->first()['name'] !== $spec['name'])
                                            <small class="text-muted d-block mt-1">{{ $spec['programs']->first()['name'] }}</small>
                                        @endif
                                    </td>
                                    <td style="white-space: normal; word-break: break-word;">
                                        <span class="badge bg-light text-secondary border rounded-pill py-1 px-2">{{ $spec['department'] }}</span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($spec['department'], $spec['name']) }}" class="badge bg-light text-dark border px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold">
                                            {{ $spec['total'] }}
                                        </a>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($spec['department'], $spec['name'], 'all') }}" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold" title="Повністю обрали">
                                            {{ $spec['all'] }}
                                            <span class="small text-muted fw-normal ms-1">({{ $spec['all_percent'] }}%)</span>
                                        </a>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($spec['department'], $spec['name'], 'partial') }}" class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold" title="Частково обрали">
                                            {{ $spec['partial'] }}
                                            <span class="small text-muted fw-normal ms-1">({{ $spec['partial_percent'] }}%)</span>
                                        </a>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ $getStudentListUrl($spec['department'], $spec['name'], 'none') }}" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill text-decoration-none fs-6 fw-bold" title="Не обрали жодної">
                                            {{ $spec['none'] }}
                                            <span class="small text-muted fw-normal ms-1">({{ $spec['none_percent'] }}%)</span>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 rounded-pill" style="height: 10px; background-color: #e9ecef;">
                                                <div class="progress-bar bg-success" style="width: {{ $spec['all_percent'] }}%;"></div>
                                                <div class="progress-bar bg-warning" style="width: {{ $spec['partial_percent'] }}%;"></div>
                                                <div class="progress-bar bg-danger" style="width: {{ $spec['none_percent'] }}%;"></div>
                                            </div>
                                            <span class="small fw-bold text-dark text-nowrap" style="min-width: 45px;">{{ $spec['completion_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <a href="{{ $getStudentListUrl($spec['department'], $spec['name']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                            Студенти →
                                        </a>
                                    </td>
                                </tr>

                                {{-- Educational Program Sub-Rows for Broad Specialties --}}
                                @if($spec['is_broad'] && !empty($spec['programs']))
                                    @foreach($spec['programs'] as $pIdx => $prog)
                                        <tr class="program-sub-row spec-prog-group-{{ $index }}" style="background-color: rgba(13, 110, 253, 0.03); display: none;">
                                            <td class="text-center text-muted small pe-0">
                                                <span class="text-primary fw-bold">↳</span>
                                            </td>
                                            <td style="white-space: normal; word-break: break-word;" class="ps-3">
                                                <div class="fw-semibold text-dark program-name">
                                                    {{ $prog['name'] }}
                                                </div>
                                                <small class="text-muted">{{ $spec['name'] }}</small>
                                            </td>
                                            <td style="white-space: normal; word-break: break-word;">
                                                <span class="small text-muted">{{ $prog['department'] }}</span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="{{ $getStudentListUrl($prog['department'], $prog['specialty'], null, $prog['name']) }}" class="badge bg-white text-dark border px-2 py-1 rounded-pill text-decoration-none fw-semibold">
                                                    {{ $prog['total'] }}
                                                </a>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="{{ $getStudentListUrl($prog['department'], $prog['specialty'], 'all', $prog['name']) }}" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill text-decoration-none fw-semibold" title="Повністю обрали">
                                                    {{ $prog['all'] }}
                                                    <span class="small text-muted fw-normal ms-1">({{ $prog['all_percent'] }}%)</span>
                                                </a>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="{{ $getStudentListUrl($prog['department'], $prog['specialty'], 'partial', $prog['name']) }}" class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 px-2 py-1 rounded-pill text-decoration-none fw-semibold" title="Частково обрали">
                                                    {{ $prog['partial'] }}
                                                    <span class="small text-muted fw-normal ms-1">({{ $prog['partial_percent'] }}%)</span>
                                                </a>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="{{ $getStudentListUrl($prog['department'], $prog['specialty'], 'none', $prog['name']) }}" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill text-decoration-none fw-semibold" title="Не обрали жодної">
                                                    {{ $prog['none'] }}
                                                    <span class="small text-muted fw-normal ms-1">({{ $prog['none_percent'] }}%)</span>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1 rounded-pill" style="height: 7px; background-color: #e9ecef;">
                                                        <div class="progress-bar bg-success" style="width: {{ $prog['all_percent'] }}%;"></div>
                                                        <div class="progress-bar bg-warning" style="width: {{ $prog['partial_percent'] }}%;"></div>
                                                        <div class="progress-bar bg-danger" style="width: {{ $prog['none_percent'] }}%;"></div>
                                                    </div>
                                                    <span class="small text-muted text-nowrap" style="min-width: 40px;">{{ $prog['completion_rate'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4 text-nowrap">
                                                <a href="{{ $getStudentListUrl($prog['department'], $prog['specialty'], null, $prog['name']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 small" style="font-size: 0.8rem;">
                                                    Студенти
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                                        За вибраними критеріями фільтрації спеціальностей не знайдено.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let allExpanded = false;

function toggleSpecPrograms(index) {
    const rows = document.querySelectorAll('.spec-prog-group-' + index);
    const icon = document.getElementById('toggle-icon-' + index);
    if (!rows.length) return;

    const isHidden = rows[0].style.display === 'none';
    rows.forEach(r => r.style.display = isHidden ? '' : 'none');
    if (icon) {
        icon.innerText = isHidden ? '▼' : '▶';
    }
}

function toggleAllPrograms() {
    allExpanded = !allExpanded;
    const allProgramRows = document.querySelectorAll('.program-sub-row');
    const allIcons = document.querySelectorAll('[id^="toggle-icon-"]');
    const btn = document.getElementById('toggleAllProgramsBtn');

    allProgramRows.forEach(r => {
        r.style.display = allExpanded ? '' : 'none';
    });

    allIcons.forEach(ic => {
        ic.innerText = allExpanded ? '▼' : '▶';
    });

    if (btn) {
        btn.innerHTML = allExpanded 
            ? '<i class="bi bi-arrows-collapse me-1"></i> Згорнути всі ОП' 
            : '<i class="bi bi-arrows-expand me-1"></i> Розгорнути всі ОП';
    }
}

function filterSpecialtiesTable() {
    const input = document.getElementById('specialtySearchInput');
    const filter = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.specialty-row');

    rows.forEach(row => {
        const idMatch = row.id.match(/\d+/);
        const index = idMatch ? idMatch[0] : null;
        const subRows = index ? document.querySelectorAll('.spec-prog-group-' + index) : [];

        let matchesMain = row.innerText.toLowerCase().includes(filter);
        let matchesSub = false;

        subRows.forEach(sr => {
            if (filter !== '' && sr.innerText.toLowerCase().includes(filter)) {
                matchesSub = true;
                sr.style.display = '';
            } else if (filter === '') {
                sr.style.display = allExpanded ? '' : 'none';
            }
        });

        if (filter === '') {
            row.style.display = '';
        } else if (matchesMain || matchesSub) {
            row.style.display = '';
            if (matchesSub) {
                const icon = document.getElementById('toggle-icon-' + index);
                if (icon) icon.innerText = '▼';
            }
        }
    });
}

// Neutralize Orchid form abandonment listeners and flags on analytics dashboard
function neutralizeFormAbandonment() {
    try {
        window.onbeforeunload = null;
        const postForm = document.getElementById('post-form');
        if (postForm) {
            postForm.setAttribute('data-form-need-prevents-form-abandonment-value', 'false');
            postForm.setAttribute('data-form-has-been-changed-value', 'false');
            if (window.application) {
                const controller = window.application.getControllerForElementAndIdentifier(postForm, 'form');
                if (controller) {
                    controller.needPreventsFormAbandonmentValue = false;
                    controller.hasBeenChangedValue = false;
                }
            }
        }
    } catch (e) {}
}

// Intercept beforeunload in capture phase so that Orchid's beforeunload listener NEVER receives the event
window.addEventListener('beforeunload', function(e) {
    neutralizeFormAbandonment();
    e.stopImmediatePropagation();
}, true);

// Execute immediately and on Turbo lifecycle events
neutralizeFormAbandonment();
document.addEventListener('DOMContentLoaded', neutralizeFormAbandonment);
document.addEventListener('turbo:load', neutralizeFormAbandonment);
document.addEventListener('turbo:before-fetch-request', neutralizeFormAbandonment);

function submitAnalyticsFilters() {
    neutralizeFormAbandonment();

    const year = document.getElementById('filter_entry_year') ? document.getElementById('filter_entry_year').value : '';
    const degree = document.getElementById('filter_degree') ? document.getElementById('filter_degree').value : '';
    const dept = document.getElementById('filter_department') ? document.getElementById('filter_department').value : '';
    const sf = document.getElementById('filter_study_form') ? document.getElementById('filter_study_form').value : '';

    const url = new URL('{{ route("platform.analytics") }}', window.location.origin);
    if (year) url.searchParams.set('entry_year', year);
    if (degree) url.searchParams.set('degree', degree);
    if (dept && dept !== 'all') url.searchParams.set('department', dept);
    if (sf && sf !== 'all') url.searchParams.set('study_form', sf);

    if (window.Turbo && typeof window.Turbo.visit === 'function') {
        window.Turbo.visit(url.toString());
    } else {
        window.location.href = url.toString();
    }
}
</script>
