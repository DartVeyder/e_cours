<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Доступ обмежено — {{ config('app.name', 'E-Cours') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: radial-gradient(circle at 10% 20%, rgba(225, 29, 72, 0.15), transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.12), transparent 40%),
                        #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .card-custom {
            background: #ffffff;
            color: #1e293b;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .shield-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4);
        }
    </style>
</head>
<body class="d-flex flex-column justify-content-between py-4">
    <div class="container text-center pt-3 pb-2">
        <div class="d-inline-flex flex-column align-items-center text-decoration-none">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 shadow shield-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="white" viewBox="0 0 16 16">
                    <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                </svg>
            </div>
            <div class="h3 fw-bold text-white mb-0">E-COURS SECURITY</div>
            <div class="text-white-50 small mt-1">Система автоматичного захисту інфраструктури</div>
        </div>
    </div>

    <div class="container my-auto py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card card-custom border-0 p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold mb-2">
                            403 Forbidden • Чорний список
                        </span>
                        <h2 class="h4 fw-bold text-slate-900 mt-2 mb-1">Доступ тимчасово обмежено</h2>
                        <p class="text-muted small">
                            Вашу IP-адресу додано до чорного списку системи через підозрілу активність або порушення безпеки.
                        </p>
                    </div>

                    <div class="bg-light rounded-3 p-3 mb-4 border">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">IP-адреса:</span>
                            <span class="font-monospace fw-bold text-danger">{{ $ip }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">Причина:</span>
                            <span class="text-dark small text-end fw-medium" style="max-width: 65%;">{{ $reason }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted small">Діє до:</span>
                            <span class="text-dark small fw-medium">{{ $blocked_until }}</span>
                        </div>
                    </div>

                    <p class="text-muted small text-center mb-4">
                        Якщо ви вважаєте, що це блокування відбулося помилково, зверніться до технічної підтримки університету з деталями вище.
                    </p>

                    @if(!empty($telegram_url))
                        <div class="d-grid">
                            <a href="{{ $telegram_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 py-2 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.017 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3 0-.749.16-3.056 1.12z"/>
                                </svg>
                                <span>Звернутися до підтримки в Telegram</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-2 pt-3 text-center text-white-50 small">
        © {{ date('Y') }} {{ config('app.name', 'E-Cours') }} • Дрогобицький державний педагогічний університет імені Івана Франка
    </div>
</body>
</html>
