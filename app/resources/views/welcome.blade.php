<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appSettings->school_name ?? 'TuCardex' }} · Control Escolar Inteligente para Colegios de México</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- PWA Settings & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B1A14">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TuCardex">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">

    <style>
        :root{
            --brand:#16a34a; --brand-2:#22c55e; --brand-3:#15803d; --teal:#0d9488;
            --bg:#0b1712; --bg-2:#0f1f18; --ink:#e8f0ec; --muted:#9fb4a8;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--ink);
            -webkit-font-smoothing:antialiased;overflow-x:hidden}
        a{text-decoration:none;color:inherit}
        .container{max-width:1140px;margin:0 auto;padding:0 22px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:none;cursor:pointer;
            font-family:inherit;font-weight:600;font-size:15px;padding:13px 24px;border-radius:12px;transition:.18s}
        .btn-primary{background:linear-gradient(135deg,var(--brand-2),var(--brand-3));color:#fff;box-shadow:0 8px 24px rgba(34,197,94,.3)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(34,197,94,.4)}
        .btn-ghost{background:rgba(255,255,255,.07);color:#fff;border:1px solid rgba(255,255,255,.14)}
        .btn-ghost:hover{background:rgba(255,255,255,.12)}
        .btn-sm{padding:9px 18px;font-size:14px}

        /* Fondo decorativo */
        .bg-orbs{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none}
        .orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.5}
        .orb.a{width:520px;height:520px;background:#15803d;top:-160px;left:-120px}
        .orb.b{width:460px;height:460px;background:#0d9488;top:120px;right:-140px;opacity:.4}
        .orb.c{width:400px;height:400px;background:#16a34a;bottom:-160px;left:30%;opacity:.3}
        .wrap{position:relative;z-index:1}

        /* Navbar & Hamburger */
        .top-header-wrap{position:sticky;top:0;z-index:1050;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);background:rgba(11,23,18,.86);border-bottom:1px solid rgba(255,255,255,.07)}
        nav.top{display:flex;align-items:center;justify-content:space-between;padding:14px 0;position:relative}
        .brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:19px}
        .brand .logo{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));
            display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 6px 18px rgba(34,197,94,.4);flex-shrink:0}
        .nav-links{display:flex;align-items:center;gap:26px}
        .nav-links a.link{color:var(--muted);font-weight:500;font-size:15px;transition:.15s}
        .nav-links a.link:hover{color:#fff}

        /* Hamburger Button */
        .nav-hamburger{display:none;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;
            background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:24px;cursor:pointer;transition:.18s}
        .nav-hamburger:hover,.nav-hamburger.active{background:rgba(34,197,94,.2);border-color:var(--brand-2);color:var(--brand-2)}

        /* Mobile Dropdown Drawer */
        .mobile-nav-drawer{display:none;background:#0f1f18;border:1px solid rgba(34,197,94,.25);border-radius:18px;
            padding:18px;margin-top:6px;margin-bottom:14px;box-shadow:0 20px 40px rgba(0,0,0,.55);flex-direction:column;gap:8px}
        .mobile-nav-drawer.open{display:flex;animation:fadeDown .2s ease}
        @keyframes fadeDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
        .mobile-nav-drawer a.m-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;
            color:#e8f0ec;font-weight:600;font-size:15px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)}
        .mobile-nav-drawer a.m-link i{color:var(--brand-2);font-size:18px;width:22px;text-align:center}
        .mobile-nav-drawer a.m-link:active,.mobile-nav-drawer a.m-link:hover{background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.3)}
        .mobile-nav-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px;padding-top:12px;border-top:1px solid rgba(255,255,255,.08)}

        /* Hero */
        .hero{text-align:center;padding:70px 0 90px}
        .badge-pill{display:inline-flex;align-items:center;gap:8px;background:rgba(34,197,94,.12);
            border:1px solid rgba(34,197,94,.3);color:#86efac;padding:8px 18px;border-radius:30px;font-size:13.5px;font-weight:500;margin-bottom:30px}
        .hero h1{font-size:64px;line-height:1.05;font-weight:900;letter-spacing:-1.5px;margin-bottom:24px}
        .grad{background:linear-gradient(120deg,var(--brand-2),#4ade80 40%,#5eead4);-webkit-background-clip:text;background-clip:text;color:transparent}
        .hero p.sub{font-size:19px;color:var(--muted);max-width:620px;margin:0 auto 38px;line-height:1.6}
        .hero-cta{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:64px}

        /* Stats */
        .stats{display:flex;justify-content:center;gap:60px;flex-wrap:wrap}
        .stat .num{font-size:38px;font-weight:800;background:linear-gradient(120deg,#fff,#86efac);-webkit-background-clip:text;background-clip:text;color:transparent}
        .stat .lbl{color:var(--muted);font-size:14px;margin-top:2px}

        /* Sections */
        section{padding:80px 0}
        .sec-head{text-align:center;max-width:680px;margin:0 auto 56px}
        .sec-head .tag{color:var(--brand-2);font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:1.5px}
        .sec-head h2{font-size:40px;font-weight:800;letter-spacing:-.8px;margin:10px 0 14px}
        .sec-head p{color:var(--muted);font-size:17px;line-height:1.6}

        .features{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
        .feature{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:18px;padding:28px;transition:.2s}
        .feature:hover{background:rgba(255,255,255,.06);transform:translateY(-4px);border-color:rgba(34,197,94,.3)}
        .feature .fi{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;margin-bottom:18px}
        .feature h3{font-size:18px;font-weight:700;margin-bottom:8px}
        .feature p{color:var(--muted);font-size:14.5px;line-height:1.6}

        /* Pricing */
        .plans{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;align-items:stretch}
        .plan{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:32px 28px;display:flex;flex-direction:column}
        .plan.featured{background:linear-gradient(160deg,rgba(34,197,94,.16),rgba(13,148,136,.08));border-color:rgba(34,197,94,.45);position:relative;box-shadow:0 12px 32px rgba(34,197,94,.15)}
        .plan .ptag{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--brand-2);color:#06281a;font-size:12px;font-weight:800;padding:4px 14px;border-radius:20px;letter-spacing:.5px;white-space:nowrap}
        .plan h3{font-size:24px;font-weight:800}
        .plan .subtitle{color:var(--muted);font-size:14px;margin-top:4px}
        .plan .price{font-size:38px;font-weight:900;margin:18px 0 4px;color:#fff}
        .plan .price span{font-size:14px;color:var(--muted);font-weight:500}
        .plan .special-note{font-size:12.5px;color:#86efac;background:rgba(34,197,94,.1);padding:6px 10px;border-radius:8px;margin-top:8px}
        .plan ul{list-style:none;margin:22px 0;flex:1}
        .plan li{display:flex;align-items:flex-start;gap:10px;padding:8px 0;color:#cfe0d8;font-size:14px;line-height:1.4}
        .plan li i{color:var(--brand-2);flex-shrink:0;margin-top:2px}
        .plan .discreet-note{font-size:11.5px;color:var(--muted);margin-top:14px;line-height:1.4;border-top:1px solid rgba(255,255,255,.08);padding-top:10px}

        /* Comparative table */
        .comp-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin-top:50px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:24px}
        .comp-table{width:100%;border-collapse:collapse;text-align:left;font-size:14px;min-width:580px}
        .comp-table th,.comp-table td{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.06)}
        .comp-table th{font-weight:800;color:#fff;background:rgba(255,255,255,.02);font-size:15px}
        .comp-table th.center,.comp-table td.center{text-align:center;width:18%}
        .comp-table tr:hover td{background:rgba(255,255,255,.02)}
        .comp-table td i.bi-check-circle-fill{color:var(--brand-2);font-size:16px}
        .comp-table td i.bi-dash{color:rgba(255,255,255,.2);font-size:20px}

        /* Institution types */
        .inst-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-top:36px}
        .inst-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:34px}
        .inst-card h3{font-size:22px;font-weight:800;margin-bottom:10px;display:flex;align-items:center;gap:10px}
        .inst-card p.desc{color:var(--muted);font-size:15px;line-height:1.6;margin-bottom:20px}
        .inst-card ul{list-style:none}
        .inst-card li{display:flex;align-items:center;gap:10px;padding:7px 0;color:#cfe0d8;font-size:14.5px}
        .inst-card li i{color:var(--brand-2)}
        .inst-card .highlight-box{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:12px;padding:14px;margin-top:20px;font-size:13.5px;color:#a7f3d0}

        /* CTA band */
        .cta-band{background:linear-gradient(135deg,var(--brand-3),var(--teal));border-radius:24px;padding:56px;text-align:center;margin:40px 0}
        .cta-band h2{font-size:36px;font-weight:800;margin-bottom:14px}
        .cta-band p{color:rgba(255,255,255,.9);font-size:17px;margin-bottom:28px}
        .cta-band .btn-primary{background:#fff;color:var(--brand-3)}

        footer{border-top:1px solid rgba(255,255,255,.08);padding:34px 0;color:var(--muted);font-size:14px}
        .foot-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}

        @media(max-width:900px){
            .features,.plans,.inst-grid{grid-template-columns:1fr}
            .hero h1{font-size:42px}
            .nav-links{display:none}
            .nav-hamburger{display:inline-flex}
            .stats{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
            .stat{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px 12px}
            .stat .num{font-size:28px}
            .cta-band{padding:34px 20px}
            .cta-band h2{font-size:26px}
            section{padding:52px 0}
            .sec-head{margin-bottom:34px}
            .sec-head h2{font-size:30px}
        }

        @media(max-width:576px){
            .container{padding:0 16px}
            .hero{padding:34px 0 46px}
            .badge-pill{font-size:12px;padding:6px 14px;margin-bottom:18px}
            .hero h1{font-size:32px;line-height:1.12;letter-spacing:-.8px;margin-bottom:16px}
            .hero p.sub{font-size:15.5px;margin-bottom:26px}
            .hero-cta{flex-direction:column;gap:12px;margin-bottom:38px}
            .hero-cta .btn{width:100%;padding:14px 20px}
            .feature,.plan,.inst-card{padding:22px 18px}
            .plan .price{font-size:32px}
            .comp-table-wrap{padding:14px;border-radius:16px}
            .mobile-nav-actions{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
<div class="bg-orbs"><div class="orb a"></div><div class="orb b"></div><div class="orb c"></div></div>

<div class="wrap">
<div class="top-header-wrap">
    <div class="container">
        <nav class="top">
            <a href="/" class="brand">
                <span class="logo">@if(optional($appSettings)->logo_url)<img src="{{ $appSettings->logo_url }}" alt="logo" style="width:100%;height:100%;object-fit:cover;border-radius:12px">@else🎓@endif</span>
                <span>{{ $appSettings->school_name ?? 'TuCardex' }}</span>
            </a>
            <div class="nav-links">
                <a href="#funciones" class="link">Funciones</a>
                <a href="#instituciones" class="link">Instituciones</a>
                <a href="#precios" class="link">Precios</a>
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm"><i class="bi bi-rocket-takeoff"></i> Prueba gratis</a>
            </div>
            <button type="button" class="nav-hamburger" id="mobileMenuBtn" aria-label="Abrir menú de navegación" onclick="toggleMobileMenu()">
                <i class="bi bi-list" id="mobileMenuIcon"></i>
            </button>
        </nav>

        <!-- Menú Móvil Desplegable (Hamburguesa) -->
        <div class="mobile-nav-drawer" id="mobileNavDrawer">
            <a href="#funciones" class="m-link" onclick="closeMobileMenu()"><i class="bi bi-grid-1x2-fill"></i> Módulos y Funciones</a>
            <a href="#instituciones" class="m-link" onclick="closeMobileMenu()"><i class="bi bi-building-check"></i> Niveles Educativos (SEP)</a>
            <a href="#precios" class="m-link" onclick="closeMobileMenu()"><i class="bi bi-tag-fill"></i> Planes y Precios</a>
            <div class="mobile-nav-actions">
                <a href="{{ route('login') }}" class="btn btn-ghost" style="width:100%"><i class="bi bi-person-check-fill"></i> Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn btn-primary" style="width:100%"><i class="bi bi-rocket-takeoff-fill"></i> Prueba gratis 30 días</a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Hero -->
    <div class="hero">
        <div class="badge-pill"><i class="bi bi-shield-check"></i> Diseñado para el Sistema Educativo Mexicano</div>
        <h1>Control escolar que <span class="grad">revoluciona</span> tu colegio</h1>
        <p class="sub">Calificaciones oficiales SEP, boletas con QR, cobranza de colegiaturas, portales para alumnos y tutores. Todo lo que tu plantel necesita en una sola plataforma SaaS.</p>
        <div class="hero-cta">
            <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-rocket-takeoff"></i> Comenzar prueba de 30 días</a>
            <a href="{{ route('login') }}" class="btn btn-ghost"><i class="bi bi-box-arrow-in-right"></i> Acceder a mi cuenta</a>
        </div>

        <div class="stats">
            <div class="stat"><div class="num">100%</div><div class="lbl">Formato Oficial SEP</div></div>
            <div class="stat"><div class="num">Multi-Rol</div><div class="lbl">Alumnos, Padres y Docentes</div></div>
            <div class="stat"><div class="num">En la Nube</div><div class="lbl">Acceso 24/7 seguro</div></div>
            <div class="stat"><div class="num">CFDI 4.0</div><div class="lbl">Facturación y SPEI</div></div>
        </div>
    </div>
</div>

<!-- Funciones -->
<section id="funciones" style="background:var(--bg-2)">
    <div class="container">
        <div class="sec-head">
            <div class="tag">Módulos</div>
            <h2>Todo lo que tu colegio necesita</h2>
            <p>Una suite integral que digitaliza desde el expediente del alumno hasta la cobranza y entrega de boletas.</p>
        </div>
        <div class="features">
            @php $feats = [
                ['bi-journal-bookmark','#10b981','Boletas y Kárdex Oficial','Generación de boletas SEP con códigos QR verificables, cálculo automático de promedios y actas consolidadas.'],
                ['bi-person-badge','#06b6d4','Credenciales y Constancias','Emisión de credenciales escolares con fotografía en formato oficial y constancias de estudio foliadas.'],
                ['bi-calendar2-check','#3b82f6','Asistencia y Pase de Lista','Pase de lista diario por grupo, seguimiento de faltas justificadas y reportes mensuales automatizados.'],
                ['bi-cash-stack','#f59e0b','Colegiaturas y Pagos','Caja, recibos foliados, estados de cuenta familiares, semáforos de cobranza y pasarelas de pago SPEI.'],
                ['bi-people-fill','#8b5cf6','Portales Alumno y Familiar','Portal independiente para estudiantes y portal multi-hijo para padres y tutores en un solo inicio de sesión.'],
                ['bi-megaphone','#ef4444','Oficios y Comunicados','Envío de citatorios con código QR y avisos escolares directamente a los tutores por correo electrónico.'],
            ]; @endphp
            @foreach($feats as $f)
                <div class="feature">
                    <div class="fi" style="background:linear-gradient(135deg,{{ $f[1] }},{{ $f[1] }}cc)"><i class="bi {{ $f[0] }}"></i></div>
                    <h3>{{ $f[2] }}</h3>
                    <p>{{ $f[3] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Sección Tipos de Institución -->
<section id="instituciones">
    <div class="container">
        <div class="sec-head">
            <div class="tag">Flexibilidad Institucional</div>
            <h2>TuCardex se adapta a tu institución</h2>
            <p>Ya sea una escuela pública o un colegio privado, nuestra arquitectura ofrece exactamente las herramientas que tu modelo operativo requiere.</p>
        </div>

        <div class="inst-grid">
            <div class="inst-card">
                <h3><i class="bi bi-bank2 text-success"></i> Escuelas Públicas</h3>
                <p class="desc">Control escolar ágil y confiable enfocado en la gestión académica, asistencia, calificaciones y expedientes, sin forzar módulos de cobranza ni pagos.</p>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Control escolar centralizado y matrícula</li>
                    <li><i class="bi bi-check-circle-fill"></i> Directorio de alumnos, docentes y grupos</li>
                    <li><i class="bi bi-check-circle-fill"></i> Asistencia diaria y porcentaje SEP</li>
                    <li><i class="bi bi-check-circle-fill"></i> Evaluaciones periódicas y kárdex histórico</li>
                    <li><i class="bi bi-check-circle-fill"></i> Boletas oficiales con código QR</li>
                    <li><i class="bi bi-check-circle-fill"></i> Comunicación y avisos con familias</li>
                    <li><i class="bi bi-check-circle-fill"></i> 100% libre de módulos financieros forzosos</li>
                </ul>
                <div class="highlight-box">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Convenio Institucional:</strong> Tarifa preferencial de $10 MXN por alumno/mes con un mínimo garantizado de $2,000 MXN mensuales por plantel.
                </div>
            </div>

            <div class="inst-card">
                <h3><i class="bi bi-buildings text-primary"></i> Escuelas Privadas</h3>
                <p class="desc">La suite integral 360° para colegios privados que buscan excelencia académica, control administrativo estricto y cobranza oportuna de colegiaturas.</p>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Todo el control escolar y académico SEP</li>
                    <li><i class="bi bi-check-circle-fill"></i> Facturación electrónica CFDI 4.0 y pasarelas SPEI</li>
                    <li><i class="bi bi-check-circle-fill"></i> Estados de cuenta en tiempo real para padres</li>
                    <li><i class="bi bi-check-circle-fill"></i> Control de adeudos, recargos y becas</li>
                    <li><i class="bi bi-check-circle-fill"></i> Recuperación de costos mediante cuota digital</li>
                    <li><i class="bi bi-check-circle-fill"></i> Reportes financieros y cortes de caja ejecutivos</li>
                    <li><i class="bi bi-check-circle-fill"></i> Integración disponible con Canvas LMS y APIs</li>
                </ul>
                <div class="highlight-box" style="background:rgba(59,130,246,.1);border-color:rgba(59,130,246,.25);color:#bfdbfe">
                    <i class="bi bi-stars me-1"></i> <strong>Plan Profesional:</strong> Diseñado específicamente para colegios que administran cobranza y estados de cuenta familiares.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Precios -->
<section id="precios" style="background:var(--bg-2)">
    <div class="container">
        <div class="sec-head">
            <div class="tag">Planes Comerciales</div>
            <h2>Elige el plan ideal para tu plantel</h2>
            <p>Planes en pesos mexicanos calculados por alumno al mes. Sin plazos forzosos y adaptados al volumen real de tu colegio.</p>
        </div>

        <div class="plans">
            <!-- Plan Básico -->
            <div class="plan">
                <h3>Básico</h3>
                <div class="subtitle">Control escolar esencial</div>
                <div class="price">$15 <span>MXN por alumno / mes</span></div>
                <div class="special-note">
                    <i class="bi bi-info-circle-fill me-1"></i> Planes institucionales desde $10 por alumno/mes (mínimo $2,000/mes)
                </div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Gestión de alumnos, grupos y materias</li>
                    <li><i class="bi bi-check-circle-fill"></i> Portal del alumno y portal de tutores</li>
                    <li><i class="bi bi-check-circle-fill"></i> Asistencia diaria y porcentajes SEP</li>
                    <li><i class="bi bi-check-circle-fill"></i> Calificaciones, boletas y kárdex con QR</li>
                    <li><i class="bi bi-check-circle-fill"></i> Credenciales escolares y constancias</li>
                    <li><i class="bi bi-check-circle-fill"></i> Avisos y comunicados escolares</li>
                    <li><i class="bi bi-check-circle-fill"></i> Reportes académicos básicos</li>
                </ul>
                <a href="https://wa.me/525644117635?text=Hola,%20solicito%20informaci%C3%B3n%20sobre%20el%20Plan%20B%C3%A1sico%20de%20TuCardex" target="_blank" class="btn btn-ghost" style="justify-content:center">Solicitar información</a>
            </div>

            <!-- Plan Profesional -->
            <div class="plan featured">
                <div class="ptag">MÁS POPULAR</div>
                <h3>Profesional</h3>
                <div class="subtitle">Control académico, administrativo y financiero</div>
                <div class="price">$30 <span>MXN por alumno / mes</span></div>
                <div style="color:var(--muted);font-size:13.5px;margin-top:6px">Control académico, administrativo y financiero en una sola plataforma.</div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>Todo lo incluido en el Plan Básico</strong></li>
                    <li><i class="bi bi-check-circle-fill"></i> Colegiaturas y registro de pagos en caja</li>
                    <li><i class="bi bi-check-circle-fill"></i> Estados de cuenta y control de adeudos</li>
                    <li><i class="bi bi-check-circle-fill"></i> Becas, descuentos y recargos automáticos</li>
                    <li><i class="bi bi-check-circle-fill"></i> Recibos foliados y facturación CFDI 4.0</li>
                    <li><i class="bi bi-check-circle-fill"></i> Reportes financieros y de cartera vencida</li>
                    <li><i class="bi bi-check-circle-fill"></i> Notificaciones y citatorios oficiales con QR</li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-primary" style="justify-content:center">Probar TuCardex</a>
            </div>

            <!-- Plan Integral -->
            <div class="plan">
                <h3>Integral</h3>
                <div class="subtitle">Tu ecosistema escolar conectado</div>
                <div class="price">$45 <span>MXN por alumno / mes</span></div>
                <div style="color:var(--muted);font-size:13.5px;margin-top:6px">Ecosistema escolar completo con Canvas, automatizaciones e integraciones avanzadas.</div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>Todo lo incluido en el Plan Profesional</strong></li>
                    <li><i class="bi bi-check-circle-fill"></i> Integración disponible con Canvas LMS</li>
                    <li><i class="bi bi-check-circle-fill"></i> Sincronización de alumnos, cursos y notas</li>
                    <li><i class="bi bi-check-circle-fill"></i> API de TuCardex, Webhooks y automatizaciones</li>
                    <li><i class="bi bi-check-circle-fill"></i> Auditoría de cambios y bitácora avanzada</li>
                    <li><i class="bi bi-check-circle-fill"></i> Soporte multi-plantel y multi-sede</li>
                    <li><i class="bi bi-check-circle-fill"></i> Configuraciones a la medida por institución</li>
                </ul>
                <a href="https://wa.me/525644117635?text=Hola,%20me%20interesa%20el%20Plan%20Integral%20con%20Canvas%20LMS%20para%20mi%20instituci%C3%B3n" target="_blank" class="btn btn-ghost" style="justify-content:center">Hablar con ventas</a>

                <div class="discreet-note">
                    * La licencia institucional de Canvas, cuando sea requerida, es contratada directamente por la institución educativa. El Plan Integral de TuCardex incluye las funciones de integración disponibles.
                </div>
            </div>
        </div>

        <div style="text-align:center;margin-top:20px;font-size:13.5px;color:var(--muted)">
            Precios en MXN más IVA cuando aplique. Aplican condiciones comerciales según volumen de matrícula.
        </div>

        <!-- Tabla Comparativa -->
        <div class="comp-table-wrap">
            <h3 style="margin-bottom:18px;font-size:20px;font-weight:800"><i class="bi bi-table text-success me-2"></i> Tabla comparativa detallada de funciones</h3>
            <table class="comp-table">
                <thead>
                    <tr>
                        <th>Funcionalidad / Módulo</th>
                        <th class="center">Básico<div style="font-size:12px;font-weight:500;color:var(--muted)">$15/alumno</div></th>
                        <th class="center" style="color:#86efac">Profesional<div style="font-size:12px;font-weight:500;color:#86efac">MÁS POPULAR · $30</div></th>
                        <th class="center">Integral<div style="font-size:12px;font-weight:500;color:var(--muted)">$45/alumno</div></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Gestión de alumnos y expedientes</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Padres y tutores (relación multi-hijo)</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Docentes y asignaciones de materia</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Grupos, grados y secciones</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Materias y planes curriculares</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Asistencia diaria y porcentajes oficiales</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Calificaciones por periodo y promedios</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Boletas oficiales de calificaciones SEP</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Kárdex académico con código QR</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Portal del alumno</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Portal de padres o tutores</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Avisos y comunicados escolares</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Reportes académicos básicos</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Colegiaturas y conceptos de cobro</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Registro de pagos y recibos foliados</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Estados de cuenta para familias</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Control de adeudos y cartera vencida</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Becas y descuentos automáticos</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Facturación electrónica SAT CFDI 4.0 (con Complemento IEDU)</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Cobranza Automatizada por WhatsApp y Correo (con enlaces de pago)</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Avisos de Inasistencias Matutinos por WhatsApp</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Portal Público de Admisiones y Preinscripciones con Documentación Digital</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Constancias y credenciales escolares</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Reportes financieros y cortes de caja</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Notificaciones y oficios citatorios</td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Integración con Canvas LMS</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>API de TuCardex</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Webhooks y eventos en tiempo real</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Automatizaciones de procesos</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Integraciones externas</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Auditoría y bitácora de movimientos</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Roles y permisos avanzados</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                    <tr><td>Multi-plantel y multi-sede</td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-dash"></i></td><td class="center"><i class="bi bi-check-circle-fill"></i></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- CTA -->
<div class="container">
    <div class="cta-band">
        <h2>Moderniza la gestión de tu colegio hoy</h2>
        <p>Inicia tu prueba de 30 días con acceso a todos los módulos y comprueba cómo TuCardex simplifica el día a día en tu institución.</p>
        <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-rocket-takeoff"></i> Comenzar prueba gratis</a>
    </div>
</div>

<footer>
    <div class="container foot-row">
        <div class="brand" style="font-size:16px"><span class="logo" style="width:34px;height:34px;font-size:17px">🎓</span> {{ $appSettings->school_name ?? 'TuCardex' }}</div>
        <div>© {{ date('Y') }} {{ $appSettings->school_name ?? 'TuCardex' }} · Control Escolar SaaS en México</div>
        <a href="{{ route('login') }}" class="link" style="color:var(--brand-2)">Iniciar sesión →</a>
    </div>
</footer>
</div>

<!-- Botón Flotante de WhatsApp para Ventas -->
<a href="https://wa.me/525644117635?text=Hola,%20me%20interesa%20conocer%20m%C3%A1s%20y%20agendar%20una%20demostraci%C3%B3n%20de%20TuCardex%20para%20mi%20colegio" target="_blank" class="wa-float" title="Contactar por WhatsApp para una Demostración">
    <i class="bi bi-whatsapp"></i>
    <span class="wa-text">Solicitar Demo</span>
</a>
<style>
.wa-float{position:fixed;bottom:24px;right:24px;background:#25d366;color:#fff;border-radius:50px;
    padding:12px 22px;display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;
    box-shadow:0 8px 24px rgba(37,211,102,.45);z-index:9999;transition:.25s;text-decoration:none}
.wa-float:hover{transform:scale(1.05);background:#20ba5c;color:#fff;box-shadow:0 12px 30px rgba(37,211,102,.6)}
.wa-float i{font-size:22px}
@media(max-width:600px){.wa-text{display:none}.wa-float{padding:14px;border-radius:50%;bottom:84px;right:16px}}
</style>

<!-- Banner PWA discreto para instalar app en celular -->
<div id="pwaInstallBanner" style="display:none; position:fixed; bottom:16px; left:16px; right:16px; max-width:420px; margin:0 auto; z-index:9999; background:#0B1A14; color:#fff; border-radius:16px; padding:12px 16px; box-shadow:0 8px 30px rgba(0,0,0,0.5); align-items:center; justify-content:space-between; gap:12px; border:1px solid rgba(34,197,94,0.35);">
    <div style="display:flex; align-items:center; gap:12px; min-width:0;">
        <img src="/icons/icon-192.png" alt="TuCardex" style="width:40px; height:40px; border-radius:10px; flex-shrink:0;">
        <div style="min-width:0; line-height:1.2;">
            <div style="font-weight:700; font-size:13.5px; color:#fff;">Instalar TuCardex App</div>
            <small style="color:#94a3b8; font-size:11.5px;">Acceso rápido con ícono en tu celular</small>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:8px;">
        <button onclick="installPWA()" class="btn btn-primary btn-sm" style="border-radius:999px; padding:7px 14px; font-size:12px;">Instalar</button>
        <button onclick="dismissPwaInstall()" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;font-size:16px;"><i class="bi bi-x-lg"></i></button>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const drawer = document.getElementById('mobileNavDrawer');
    const btn = document.getElementById('mobileMenuBtn');
    const icon = document.getElementById('mobileMenuIcon');
    if (!drawer) return;
    const isOpen = drawer.classList.toggle('open');
    btn.classList.toggle('active', isOpen);
    icon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
}

function closeMobileMenu() {
    const drawer = document.getElementById('mobileNavDrawer');
    const btn = document.getElementById('mobileMenuBtn');
    const icon = document.getElementById('mobileMenuIcon');
    if (drawer) drawer.classList.remove('open');
    if (btn) btn.classList.remove('active');
    if (icon) icon.className = 'bi bi-list';
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
