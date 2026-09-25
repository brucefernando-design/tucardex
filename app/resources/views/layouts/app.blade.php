<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">

    <!-- PWA Settings & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B1A14">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TuCardex">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
</head>
<body>
@if(session()->has('impersonator_id'))
<div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; padding: 10px 24px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.18);">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-shield-lock-fill fs-5"></i>
        <span>Navegando como Administrador de <strong>{{ auth()->user()->school?->name ?? 'Colegio' }}</strong> (Modo Soporte SaaS)</span>
    </div>
    <form action="{{ route('schools.leave_impersonation') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn btn-sm btn-dark fw-bold px-3 py-1 shadow-sm" style="border-radius: 8px;">
            <i class="bi bi-box-arrow-left me-1"></i> Salir al Panel SuperAdmin
        </button>
    </form>
</div>
@endif
<div class="app">
    @include('layouts.sidebar')
    <div class="sidebar-backdrop" id="backdrop"></div>

    <div class="main">
        <header class="topbar">
            <button class="toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <div class="page-title"><i class="bi bi-mortarboard-fill"></i> @yield('title', 'Inicio')</div>
            <div class="spacer"></div>
            <div class="top-actions">
                @unless(auth()->user()->isSuperAdmin())
                    @php
                        $topUnread = \App\Models\Message::unreadCountFor(auth()->id());
                        $notifUnread = \App\Models\Notification::unreadFor(auth()->id());
                    @endphp
                    <a href="{{ route('notifications.index') }}" title="Notificaciones"><i class="bi bi-bell fs-5"></i>@if($notifUnread)<span class="count">{{ $notifUnread }}</span>@endif</a>
                    <a href="{{ route('messages.index') }}" title="Mensajes"><i class="bi bi-chat-dots fs-5"></i>@if($topUnread)<span class="count">{{ $topUnread }}</span>@endif</a>
                @endunless
                @if(auth()->user()->hasAnyRole(['admin','secretaria','docente']))
                    <a href="{{ route('announcements.index') }}" title="Comunicados"><i class="bi bi-megaphone fs-5"></i></a>
                @endif
                @if(auth()->user()->hasAnyRole(['admin','secretaria']))
                    <a href="{{ route('payments.index') }}" title="Colegiaturas"><i class="bi bi-cash-coin fs-5"></i></a>
                @endif
                <!-- Botón de Asistente en Barra Superior -->
                <button onclick="window.openCardexAssistant()" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 rounded-pill px-3 py-1 me-2" title="Abrir Asistente TuCardex">
                    <i class="bi bi-robot"></i>
                    <span class="d-none d-lg-inline fw-semibold" style="font-size:12.5px">Asistente</span>
                </button>

                <div class="dropdown">
                    <div class="user-chip" data-bs-toggle="dropdown">
                        @if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" class="avatar" style="object-fit:cover">@else<div class="avatar">{{ auth()->user()->initials() }}</div>@endif
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Mi perfil</a></li>
                        @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

                <main class="content">
            @php
                $currentSchool = auth()->user()?->school;
            @endphp
            @if($currentSchool && !auth()->user()->isSuperAdmin())
                @php
                    $planName = \App\Models\School::PLANS[$currentSchool->plan] ?? ucfirst($currentSchool->plan);
                    $onTrial = $currentSchool->isOnTrial();
                    $trialDays = $currentSchool->trialDaysRemaining();
                @endphp
                <div class="plan-banner-line">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 11px;">
                            <i class="bi bi-shield-check me-1"></i>Plan {{ $planName }}
                        </span>
                        @if($onTrial)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 11px;">
                                <i class="bi bi-clock-history me-1"></i>Prueba: {{ $trialDays }} {{ $trialDays === 1 ? 'día restante' : 'días restantes' }}
                            </span>
                            <span class="badge {{ $currentSchool->students()->count() >= $currentSchool->maxStudents() ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary border' }} px-2 py-1" style="font-size: 11px;">
                                <i class="bi bi-people me-1"></i>Cupo: {{ $currentSchool->students()->count() }}/{{ $currentSchool->maxStudents() }}
                            </span>
                        @endif
                        <span class="text-muted small d-none d-md-inline" style="font-size: 12px;">
                            {{ $currentSchool->name }}
                        </span>
                    </div>
                    @if($currentSchool->effectivePlan() === 'basico' || $onTrial)
                        <a href="mailto:ventas@tucardex.com?subject={{ urlencode('Mejorar Plan - ' . $currentSchool->name) }}&body={{ urlencode('Hola, deseo solicitar información para mejorar el plan de mi colegio (' . $currentSchool->name . ').') }}" 
                           class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" style="font-size: 11.5px; font-weight: 600;">
                            <i class="bi bi-stars me-1"></i>Mejorar plan
                        </a>
                    @endif
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const tg=document.getElementById('sidebarToggle'),sb=document.getElementById('sidebar'),bd=document.getElementById('backdrop');
    tg&&tg.addEventListener('click',()=>{sb.classList.toggle('open');bd.classList.toggle('show')});
    bd&&bd.addEventListener('click',()=>{sb.classList.remove('open');bd.classList.remove('show')});
</script>
@stack('scripts')
    @include('layouts.assistant')

    <!-- Banner PWA discreto para instalar app en celular -->
    <div id="pwaInstallBanner" style="display:none; position:fixed; bottom:20px; left:16px; right:16px; max-width:420px; margin:0 auto; z-index:9999; background:#0B1A14; color:#fff; border-radius:16px; padding:12px 16px; box-shadow:0 8px 30px rgba(0,0,0,0.4); align-items:center; justify-content:space-between; gap:12px; border:1px solid rgba(255,255,255,0.12);">
        <div class="d-flex align-items-center gap-3" style="min-width:0;">
            <img src="/icons/icon-192.png" alt="TuCardex" style="width:40px; height:40px; border-radius:10px; flex-shrink:0;">
            <div style="min-width:0; line-height:1.2;">
                <div style="font-weight:700; font-size:13.5px; color:#fff;">Instalar TuCardex App</div>
                <small style="color:#94a3b8; font-size:11.5px;">Acceso rápido con ícono en tu teléfono</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="installPWA()" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold" style="font-size:12px;">Instalar</button>
            <button onclick="dismissPwaInstall()" class="btn btn-sm text-secondary p-1" style="line-height:1;" title="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

    <script>
        // Registro del Service Worker PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('SW registration error:', err);
                });
            });
        }

        let pwaDeferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            pwaDeferredPrompt = e;
            const banner = document.getElementById('pwaInstallBanner');
            if (banner && !localStorage.getItem('pwa_dismissed')) {
                banner.style.display = 'flex';
            }
        });

        function installPWA() {
            if (pwaDeferredPrompt) {
                pwaDeferredPrompt.prompt();
                pwaDeferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        const banner = document.getElementById('pwaInstallBanner');
                        if (banner) banner.style.display = 'none';
                    }
                    pwaDeferredPrompt = null;
                });
            }
        }

        function dismissPwaInstall() {
            const banner = document.getElementById('pwaInstallBanner');
            if (banner) banner.style.display = 'none';
            localStorage.setItem('pwa_dismissed', 'true');
        }
    </script>
</body>
</html>
