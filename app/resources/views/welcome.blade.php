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
        .btn{display:inline-flex;align-items:center;gap:8px;border:none;cursor:pointer;
            font-family:inherit;font-weight:600;font-size:15px;padding:13px 24px;border-radius:12px;transition:.18s}
        .btn-primary{background:linear-gradient(135deg,var(--brand-2),var(--brand-3));color:#fff;box-shadow:0 8px 24px rgba(34,197,94,.3)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(34,197,94,.4)}
        .btn-ghost{background:rgba(255,255,255,.07);color:#fff;border:1px solid rgba(255,255,255,.14)}
        .btn-ghost:hover{background:rgba(255,255,255,.12)}
        .btn-sm{padding:9px 18px;font-size:14px}

        /* Fondo decorativo */
        .bg-orbs{position:fixed;inset:0;z-index:0;overflow:hidden}
        .orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.5}
        .orb.a{width:520px;height:520px;background:#15803d;top:-160px;left:-120px}
        .orb.b{width:460px;height:460px;background:#0d9488;top:120px;right:-140px;opacity:.4}
        .orb.c{width:400px;height:400px;background:#16a34a;bottom:-160px;left:30%;opacity:.3}
        .wrap{position:relative;z-index:1}

        /* Navbar */
        nav.top{display:flex;align-items:center;justify-content:space-between;padding:18px 0}
        .brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:19px}
        .brand .logo{width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));
            display:flex;align-items:center;justify-content:center;font-size:21px;box-shadow:0 6px 18px rgba(34,197,94,.4)}
        .nav-links{display:flex;align-items:center;gap:28px}
        .nav-links a.link{color:var(--muted);font-weight:500;font-size:15px}
        .nav-links a.link:hover{color:#fff}

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
        .sec-head{text-align:center;max-width:640px;margin:0 auto 56px}
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
        .plan.featured{background:linear-gradient(160deg,rgba(34,197,94,.16),rgba(13,148,136,.08));border-color:rgba(34,197,94,.45);position:relative}
        .plan .ptag{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--brand-2);color:#06281a;font-size:12px;font-weight:700;padding:4px 14px;border-radius:20px}
        .plan h3{font-size:20px;font-weight:700}
        .plan .price{font-size:42px;font-weight:800;margin:14px 0 4px}
        .plan .price span{font-size:15px;color:var(--muted);font-weight:500}
        .plan ul{list-style:none;margin:22px 0;flex:1}
        .plan li{display:flex;align-items:center;gap:10px;padding:8px 0;color:#cfe0d8;font-size:14.5px}
        .plan li i{color:var(--brand-2)}

        /* CTA band */
        .cta-band{background:linear-gradient(135deg,var(--brand-3),var(--teal));border-radius:24px;padding:56px;text-align:center;margin:40px 0}
        .cta-band h2{font-size:36px;font-weight:800;margin-bottom:14px}
        .cta-band p{color:rgba(255,255,255,.9);font-size:17px;margin-bottom:28px}
        .cta-band .btn-primary{background:#fff;color:var(--brand-3)}

        footer{border-top:1px solid rgba(255,255,255,.08);padding:34px 0;color:var(--muted);font-size:14px}
        .foot-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px}

        @media(max-width:900px){
            .features,.plans{grid-template-columns:1fr}
            .hero h1{font-size:44px}
            .nav-links .link{display:none}
            .stats{gap:34px}
            .cta-band{padding:36px 22px}
        }
    </style>
</head>
<body>
<div class="bg-orbs"><div class="orb a"></div><div class="orb b"></div><div class="orb c"></div></div>

<div class="wrap">
<div class="container">
    <nav class="top">
        <div class="brand"><span class="logo">@if(optional($appSettings)->logo_url)<img src="{{ $appSettings->logo_url }}" alt="logo" style="width:100%;height:100%;object-fit:cover;border-radius:13px">@else🎓@endif</span> {{ $appSettings->school_name ?? 'TuCardex' }}</div>
        <div class="nav-links">
            <a href="#funciones" class="link">Funciones</a>
            <a href="#precios" class="link">Precios</a>
            <a href="{{ route('login') }}" class="link">Iniciar sesión</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm"><i class="bi bi-rocket-takeoff"></i> Prueba gratis</a>
        </div>
    </nav>

    <!-- Hero -->
    <div class="hero">
        <div class="badge-pill">🚀 Plataforma integral de gestión escolar</div>
        <h1>Gestiona tu colegio<br><span class="grad">de forma inteligente</span></h1>
        <p class="sub">Todo lo que necesitas para administrar estudiantes, notas, asistencia, pagos y comunicación. Sin complicaciones, desde cualquier dispositivo.</p>
        <div class="hero-cta">
            <a href="{ route('register') }" class="btn btn-primary"><i class="bi bi-rocket-takeoff"></i> Comenzar gratis — 30 días</a>
            <a href="https://wa.me/5218671234567?text=Hola,%20me%20interesa%20conocer%20m%C3%A1s%20y%20agendar%20una%20demostraci%C3%B3n%20de%20TuCardex%20para%20mi%20colegio" target="_blank" class="btn" style="background:#25d366;color:#fff"><i class="bi bi-whatsapp"></i> Solicitar Demo WhatsApp</a>
            <a href="#funciones" class="btn btn-ghost"><i class="bi bi-grid"></i> Ver funciones</a>
        </div>
        <div class="stats">
            <div class="stat"><div class="num">+25</div><div class="lbl">Módulos integrados</div></div>
            <div class="stat"><div class="num">4</div><div class="lbl">Roles de usuario</div></div>
            <div class="stat"><div class="num">100%</div><div class="lbl">Responsive</div></div>
            <div class="stat"><div class="num">24/7</div><div class="lbl">Disponible</div></div>
        </div>
    </div>
</div>

<!-- Funciones -->
<section id="funciones">
    <div class="container">
        <div class="sec-head">
            <div class="tag">Funciones</div>
            <h2>Una plataforma, todo el colegio</h2>
            <p>Reúne la gestión académica, financiera y la comunicación de tu institución en un solo lugar.</p>
        </div>
        <div class="features">
            @php $feats = [
                ['bi-people-fill','#16a34a','Control Escolar de Alumnos','Inscripciones, CURP, expedientes completos, credenciales escolares y exportación masiva en PDF.'],
                ['bi-clipboard-data','#0d9488','Calificaciones y Boletas SEP','Registro por grupo o asignatura y boletas oficiales de calificaciones SEP en PDF.'],
                ['bi-calendar2-check','#3b82f6','Asistencia','Pase de lista diario por grupo y reporte mensual con porcentajes.'],
                ['bi-cash-stack','#f59e0b','Pagos y colegiaturas','Cobranzas, estados de cuenta, facturas y reportes financieros.'],
                ['bi-megaphone','#8b5cf6','Comunicación','Comunicados con envío por correo y mensajería interna entre usuarios.'],
                ['bi-bar-chart-line','#ef4444','Reportes y panel','Dashboard con indicadores en tiempo real y exportación de datos.'],
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

<!-- Precios -->
<section id="precios">
    <div class="container">
        <div class="sec-head">
            <div class="tag">Precios</div>
            <h2>Planes para cada institución</h2>
            <p>Elige el plan que se ajuste al tamaño de tu colegio. Sin costos ocultos.</p>
        </div>
        <div class="plans">
            <div class="plan">
                <h3>Básico</h3>
                <div class="price">$0 MXN<span>/mes</span></div>
                <div style="color:var(--muted);font-size:14px">Ideal para empezar</div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Hasta 100 estudiantes</li>
                    <li><i class="bi bi-check-circle-fill"></i> Notas y asistencia</li>
                    <li><i class="bi bi-check-circle-fill"></i> 1 administrador</li>
                    <li><i class="bi bi-check-circle-fill"></i> Soporte por correo</li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-ghost" style="justify-content:center">Comenzar</a>
            </div>
            <div class="plan featured">
                <div class="ptag">MÁS POPULAR</div>
                <h3>Profesional</h3>
                <div class="price">$1,499 MXN<span>/mes</span></div>
                <div style="color:var(--muted);font-size:14px">Para colegios en crecimiento</div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Estudiantes ilimitados</li>
                    <li><i class="bi bi-check-circle-fill"></i> Todos los módulos</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pagos y biblioteca</li>
                    <li><i class="bi bi-check-circle-fill"></i> Reportes y PDF</li>
                    <li><i class="bi bi-check-circle-fill"></i> Soporte prioritario</li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-primary" style="justify-content:center">Comenzar ahora</a>
            </div>
            <div class="plan">
                <h3>Institucional</h3>
                <div class="price">A medida</div>
                <div style="color:var(--muted);font-size:14px">Para redes de colegios</div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Multi-sede</li>
                    <li><i class="bi bi-check-circle-fill"></i> Integraciones a medida</li>
                    <li><i class="bi bi-check-circle-fill"></i> Capacitación incluida</li>
                    <li><i class="bi bi-check-circle-fill"></i> Gerente de cuenta</li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-ghost" style="justify-content:center">Contactar</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<div class="container">
    <div class="cta-band">
        <h2>Moderniza la gestión de tu colegio hoy</h2>
        <p>Crea tu cuenta gratis y descubre todo lo que puedes hacer.</p>
        <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-rocket-takeoff"></i> Comenzar gratis</a>
    </div>
</div>

<footer>
    <div class="container foot-row">
        <div class="brand" style="font-size:16px"><span class="logo" style="width:34px;height:34px;font-size:17px">🎓</span> {{ $appSettings->school_name ?? 'TuCardex' }}</div>
        <div>© {{ date('Y') }} {{ $appSettings->school_name ?? 'TuCardex' }} · Sistema de Gestión Escolar</div>
        <a href="{{ route('login') }}" class="link" style="color:var(--brand-2)">Iniciar sesión →</a>
    </div>
</footer>
</div>

<!-- Botón Flotante de WhatsApp para Ventas -->
<a href="https://wa.me/5218671234567?text=Hola,%20me%20interesa%20conocer%20m%C3%A1s%20y%20agendar%20una%20demostraci%C3%B3n%20de%20TuCardex%20para%20mi%20colegio" target="_blank" class="wa-float" title="Contactar por WhatsApp para una Demostración">
    <i class="bi bi-whatsapp"></i>
    <span class="wa-text">Solicitar Demo</span>
</a>
<style>
.wa-float{position:fixed;bottom:24px;right:24px;background:#25d366;color:#fff;border-radius:50px;
    padding:12px 22px;display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;
    box-shadow:0 8px 24px rgba(37,211,102,.45);z-index:9999;transition:.25s;text-decoration:none}
.wa-float:hover{transform:scale(1.05);background:#20ba5c;color:#fff;box-shadow:0 12px 30px rgba(37,211,102,.6)}
.wa-float i{font-size:22px}
@media(max-width:600px){.wa-text{display:none}.wa-float{padding:14px;border-radius:50%}}
</style>

</body>
</html>
