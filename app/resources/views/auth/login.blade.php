<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · {{ $appSettings->school_name ?? 'TuKardex' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- PWA Settings & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B1A14">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TuKardex">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    <style>
        :root{--brand:#16a34a;--brand-2:#22c55e;--brand-3:#15803d;--teal:#0d9488;
            --ink:#1f2a37;--muted:#64748b;--line:#e8edeb}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}
        .split{display:flex;min-height:100vh}

        /* ---- Panel de marca (izquierda) ---- */
        .brand-panel{flex:1;position:relative;overflow:hidden;color:#fff;
            background:linear-gradient(160deg,#0f1f18,#15803d 130%);
            display:flex;flex-direction:column;justify-content:center;padding:54px 56px}
        .brand-panel .orb{position:absolute;border-radius:50%;filter:blur(70px);opacity:.45}
        .brand-panel .orb.a{width:360px;height:360px;background:#16a34a;top:-90px;right:-80px}
        .brand-panel .orb.b{width:320px;height:320px;background:#0d9488;bottom:-100px;left:-60px;opacity:.4}
        .bp-inner{position:relative;z-index:1;max-width:430px}
        .bp-logo{width:74px;height:74px;border-radius:20px;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));
            display:flex;align-items:center;justify-content:center;font-size:38px;margin-bottom:22px;box-shadow:0 10px 30px rgba(34,197,94,.45)}
        .bp-inner h1{font-size:30px;font-weight:800;letter-spacing:-.5px}
        .bp-inner .tagline{color:#86efac;font-size:13px;font-weight:600;letter-spacing:2px;text-transform:uppercase;margin:6px 0 36px}
        .feat{display:flex;align-items:center;gap:16px;padding:15px 0;border-bottom:1px solid rgba(255,255,255,.08)}
        .feat .fi{width:46px;height:46px;border-radius:13px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .feat strong{display:block;font-size:15px}
        .feat span{font-size:13px;color:#a7c3b6}
        .bp-stats{display:flex;gap:14px;margin-top:36px}
        .bp-stats .s{flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:16px;text-align:center}
        .bp-stats .s .n{font-size:24px;font-weight:800}
        .bp-stats .s .l{font-size:12px;color:#a7c3b6;margin-top:2px}

        /* ---- Panel de formulario (derecha) ---- */
        .form-panel{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 28px;background:#fff}
        .form-box{width:100%;max-width:400px}
        .form-box h2{font-size:26px;font-weight:800;color:var(--ink)}
        .form-box .lead{color:var(--muted);font-size:14.5px;margin:6px 0 30px}
        .field{margin-bottom:18px}
        .field label{display:block;font-size:13px;font-weight:600;color:var(--ink);margin-bottom:7px}
        .input-ic{position:relative}
        .input-ic i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#9fb0bf}
        .input-ic input{width:100%;padding:13px 15px 13px 44px;border:1.5px solid var(--line);border-radius:12px;font-family:inherit;font-size:14.5px;transition:.15s;background:#f8fafb}
        .input-ic input:focus{outline:none;border-color:var(--brand-2);background:#fff;box-shadow:0 0 0 4px rgba(34,197,94,.13)}
        .row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;font-size:13.5px}
        .row-between label{display:flex;align-items:center;gap:7px;color:var(--muted);cursor:pointer}
        .row-between a{color:var(--brand-3);font-weight:600}
        .btn-login{width:100%;border:none;cursor:pointer;font-family:inherit;font-weight:700;font-size:15.5px;color:#fff;
            padding:14px;border-radius:12px;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));
            box-shadow:0 8px 22px rgba(34,197,94,.3);transition:.18s;display:flex;align-items:center;justify-content:center;gap:9px}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(34,197,94,.4)}
        .divider{display:flex;align-items:center;gap:14px;margin:26px 0 18px;color:#9fb0bf;font-size:12px;font-weight:600;letter-spacing:.5px;text-transform:uppercase}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--line)}
        .demo-card{border:1px solid var(--line);border-radius:14px;overflow:hidden}
        .demo-card .dh{background:#f8fafb;padding:9px 14px;font-size:12px;font-weight:700;color:var(--brand-3);display:flex;align-items:center;gap:7px;border-bottom:1px solid var(--line)}
        .demo-row{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;cursor:pointer;transition:.13s;border-bottom:1px solid var(--line)}
        .demo-row:last-child{border-bottom:none}
        .demo-row:hover{background:#f1f8f4}
        .demo-row .em{font-size:13.5px;color:var(--ink)}
        .demo-row .tag{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px}
        .t-admin{background:#dcfce7;color:#15803d}
        .t-doc{background:#dbeafe;color:#1e40af}
        .t-sec{background:#fef3c7;color:#92400e}
        .t-est{background:#f3e8ff;color:#6b21a8}
        .err{background:#fee2e2;color:#991b1b;border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
        .foot-note{text-align:center;color:#9fb0bf;font-size:12.5px;margin-top:26px}
        .back-home{position:absolute;top:22px;left:24px;color:#a7c3b6;font-size:13.5px;z-index:2;display:flex;align-items:center;gap:6px}
        .back-home:hover{color:#fff}
        a{text-decoration:none}
        .mobile-login-bar{display:none;background:#0B1A14;color:#fff;padding:14px 18px;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,.08)}
        .mobile-login-bar .m-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px;color:#fff}
        .mobile-login-bar .m-logo{width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));display:flex;align-items:center;justify-content:center;font-size:18px}
        .mobile-login-bar a.m-back{color:#86efac;font-size:13px;font-weight:600;display:flex;align-items:center;gap:5px;background:rgba(34,197,94,.14);padding:6px 12px;border-radius:999px}
        @media(max-width:880px){
            .brand-panel{display:none}
            .split{flex-direction:column;min-height:100vh;background:#f8fafb}
            .mobile-login-bar{display:flex}
            .form-panel{padding:28px 18px;align-items:flex-start}
            .form-box{background:#fff;padding:24px 20px;border-radius:18px;box-shadow:0 4px 20px rgba(0,0,0,.05);border:1px solid var(--line);margin:0 auto}
        }
    </style>
</head>
<body>
<div class="split">
    <!-- Barra superior exclusiva para Móvil -->
    <div class="mobile-login-bar">
        <a href="{{ route('home') }}" class="m-brand">
            <span class="m-logo">@if(optional($appSettings)->logo_url)<img src="{{ $appSettings->logo_url }}" alt="logo" style="width:100%;height:100%;object-fit:cover;border-radius:10px">@else🎓@endif</span>
            <span>{{ $appSettings->school_name ?? 'TuKardex' }}</span>
        </a>
        <a href="{{ route('home') }}" class="m-back"><i class="bi bi-house-door-fill"></i> Inicio</a>
    </div>

    <!-- Marca -->
    <div class="brand-panel">
        <a href="{{ route('home') }}" class="back-home"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
        <div class="orb a"></div><div class="orb b"></div>
        <div class="bp-inner">
            <div class="bp-logo">@if(optional($appSettings)->logo_url)<img src="{{ $appSettings->logo_url }}" alt="logo" style="width:100%;height:100%;object-fit:cover;border-radius:20px">@else🎓@endif</div>
            <h1>{{ $appSettings->school_name ?? 'TuKardex' }}</h1>
            <div class="tagline">Sistema de Gestión Escolar</div>

            <div class="feat"><div class="fi"><i class="bi bi-people-fill"></i></div><div><strong>Control escolar de alumnos</strong><span>Inscripciones, calificaciones, asistencia y boletas</span></div></div>
            <div class="feat"><div class="fi"><i class="bi bi-cash-stack"></i></div><div><strong>Pagos y colegiaturas</strong><span>Cobranzas, estados de cuenta y reportes</span></div></div>
            <div class="feat"><div class="fi"><i class="bi bi-megaphone"></i></div><div><strong>Comunicación</strong><span>Comunicados, correo y mensajería interna</span></div></div>
            <div class="feat"><div class="fi"><i class="bi bi-bar-chart-line"></i></div><div><strong>Reportes en tiempo real</strong><span>Indicadores y exportación a PDF/Excel</span></div></div>

            <div class="bp-stats">
                <div class="s"><div class="n">+25</div><div class="l">Módulos</div></div>
                <div class="s"><div class="n">4</div><div class="l">Roles</div></div>
                <div class="s"><div class="n">24/7</div><div class="l">Acceso</div></div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="form-panel">
        <div class="form-box">
            <h2>Bienvenido de vuelta 👋</h2>
            <div class="lead">Ingresa tus credenciales para acceder al sistema</div>

            @if(session('status_ok'))
                <div class="err" style="background:#dcfce7;color:#15803d"><i class="bi bi-check-circle"></i> {{ session('status_ok') }}</div>
            @endif
            @if($errors->any())
                <div class="err"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <label>Correo electrónico</label>
                    <div class="input-ic"><i class="bi bi-envelope"></i>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="tucorreo@colegio.test" required autofocus>
                    </div>
                </div>
                <div class="field">
                    <label>Contraseña</label>
                    <div class="input-ic"><i class="bi bi-lock"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="row-between">
                    <label><input type="checkbox" name="remember"> Recordarme</label>
                    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                </div>
                <button class="btn-login" type="submit"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</button>
            </form>

            @if (app()->environment() !== 'production')
            <div class="divider">Cuentas de demostración</div>

            <div class="demo-card">
                <div class="dh"><i class="bi bi-key-fill"></i> Acceso rápido (clic para autocompletar)</div>
                <div class="demo-row" onclick="fill('admin@colegio.test')"><span class="em">admin@colegio.test</span><span class="tag t-admin">Admin</span></div>
                <div class="demo-row" onclick="fill('docente@colegio.test')"><span class="em">docente@colegio.test</span><span class="tag t-doc">Docente</span></div>
                <div class="demo-row" onclick="fill('secretaria@colegio.test')"><span class="em">secretaria@colegio.test</span><span class="tag t-sec">Secretaría</span></div>
                <div class="demo-row" onclick="fill('estudiante@colegio.test')"><span class="em">estudiante@colegio.test</span><span class="tag t-est">Estudiante</span></div>
            </div>
            @endif

            <div class="foot-note">© {{ date('Y') }} {{ $appSettings->school_name ?? 'TuKardex' }} · Todos los derechos reservados</div>
        </div>
    </div>
</div>

<!-- Banner PWA discreto para instalar app en celular -->
<div id="pwaInstallBanner" style="display:none; position:fixed; bottom:16px; left:16px; right:16px; max-width:420px; margin:0 auto; z-index:9999; background:#0B1A14; color:#fff; border-radius:16px; padding:12px 16px; box-shadow:0 8px 30px rgba(0,0,0,0.45); align-items:center; justify-content:space-between; gap:12px; border:1px solid rgba(34,197,94,0.35);">
    <div style="display:flex; align-items:center; gap:12px; min-width:0;">
        <img src="/icons/icon-192.png" alt="TuKardex" style="width:40px; height:40px; border-radius:10px; flex-shrink:0;">
        <div style="min-width:0; line-height:1.2;">
            <div style="font-weight:700; font-size:13.5px; color:#fff;">Instalar TuKardex App</div>
            <small style="color:#94a3b8; font-size:11.5px;">Acceso rápido con ícono en tu teléfono</small>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:8px;">
        <button onclick="installPWA()" style="background:#16a34a;color:#fff;border:none;border-radius:999px;padding:7px 14px;font-weight:700;font-size:12px;cursor:pointer;">Instalar</button>
        <button onclick="dismissPwaInstall()" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;font-size:16px;"><i class="bi bi-x-lg"></i></button>
    </div>
</div>

<script>
function fill(email){
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
    document.getElementById('password').focus();
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').catch(function() {});
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
