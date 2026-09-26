<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal de Pagos') · {{ $setting->school_name ?? config('app.name', 'TuKardex') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #16a34a;
            --brand-dark: #0f172a;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .checkout-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .checkout-footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem 0;
            color: #64748b;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <header class="checkout-header">
        <div class="container d-flex align-items-center justify-content-between" style="max-width: 1050px;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">{{ $setting->school_name ?? config('app.name', 'TuKardex') }}</h5>
                    <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i> Portal Seguro de Pagos</span>
                </div>
            </div>
            <div>
                <span class="badge bg-light text-secondary border px-3 py-2">
                    <i class="bi bi-lock-fill text-success me-1"></i> Encriptación SSL 256-bit
                </span>
            </div>
        </div>
    </header>

    <main class="container py-4 flex-grow-1" style="max-width: 1050px;">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="checkout-footer text-center">
        <div class="container" style="max-width: 1050px;">
            <p class="mb-1"><strong>{{ $setting->school_name ?? 'Colegio' }}</strong> · Todos los pagos están protegidos y verificados con validez fiscal.</p>
            <p class="mb-0 small text-muted">Impulsado por TuKardex · Pagos escolares en línea y facturación SAT automatizada</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
