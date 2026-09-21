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
                                    📜 Журнал змін (Changelog)
                                </span>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill fw-semibold">
                                    ✨ Поточна версія: v{{ $currentVersion }}
                                </span>
                            </div>

                            <h1 class="h2 fw-bold text-white mb-2">
                                Історія оновлень платформи E-Cours
                            </h1>
                            <p class="text-white-50 mb-0" style="max-width: 680px; font-size: 1.05rem; line-height: 1.6;">
                                Детальний перелік нових можливостей, покращень безпеки та інтерфейсу, оптимізації алгоритмів вибору та виправлень помилок за всіма випущеними версіями.
                            </p>
                        </div>

                        <div class="col-lg-4 d-none d-lg-flex justify-content-end">
                            <div class="p-3 px-4 rounded-4 text-center" style="background: rgba(255,255,255,0.06); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.12);">
                                <div class="text-white-50 small mb-1">Зареєстровано релізів</div>
                                <div class="h3 fw-bold text-white mb-1">{{ count($releases) }}</div>
                                <div class="small text-white-50">
                                    v1.0.0 → v{{ $currentVersion }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Releases Timeline --}}
    <div class="row">
        <div class="col-12 col-xl-10 mx-auto">
            @forelse($releases as $release)
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden {{ $release['is_current'] ? 'border-start border-4 border-success' : '' }}" style="background: var(--bs-card-bg, #fff);">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="badge rounded-pill px-3 py-2 fw-bold {{ $release['is_current'] ? 'bg-success text-white' : 'bg-dark bg-opacity-10 text-dark border' }}" style="font-size: 1rem;">
                                v{{ $release['version'] }}
                            </span>

                            @if($release['is_current'])
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill fw-semibold small">
                                    ● Поточна версія
                                </span>
                            @endif
                        </div>

                        <div class="text-muted small d-inline-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-calendar-event me-1" viewBox="0 0 16 16">
                                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                            <span>{{ $release['date'] }}</span>
                        </div>
                    </div>

                    <div class="card-body px-4 pb-4 pt-2 changelog-body">
                        {!! $release['html'] !!}
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center" style="background: var(--bs-card-bg, #fff);">
                    <div class="text-muted mb-2" style="font-size: 2.5rem;">📜</div>
                    <h5 class="fw-bold">Файл журналу змін порожній або відсутній</h5>
                    <p class="text-muted mb-0">Файл CHANGELOG.md не знайдено в корені проєкту.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.changelog-body {
    font-size: 0.96rem;
    line-height: 1.65;
    color: var(--bs-body-color, #334155);
}
.changelog-body h3 {
    font-size: 1.12rem;
    font-weight: 700;
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    color: var(--bs-heading-color, #1e293b);
}
.changelog-body h3:first-child {
    margin-top: 0.25rem;
}
.changelog-body ul {
    padding-left: 1.4rem;
    margin-bottom: 1rem;
}
.changelog-body li {
    margin-bottom: 0.45rem;
}
.changelog-body li > ul {
    margin-top: 0.35rem;
    margin-bottom: 0.35rem;
}
.changelog-body code {
    background: rgba(15, 23, 42, 0.06);
    color: #d63384;
    padding: 0.15rem 0.4rem;
    border-radius: 0.35rem;
    font-size: 0.88em;
}
.changelog-body a {
    color: #0d6efd;
    text-decoration: underline;
}
.changelog-body a:hover {
    color: #0a58ca;
}
</style>
