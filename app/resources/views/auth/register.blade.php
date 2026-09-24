<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta · TuCardex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root{--brand:#16a34a;--brand-2:#22c55e;--brand-3:#15803d;--ink:#1f2a37;--muted:#64748b;--line:#e8edeb}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif}
        .split{display:flex;min-height:100vh}
        .brand-panel{flex:1;position:relative;overflow:hidden;color:#fff;background:linear-gradient(160deg,#0f1f18,#15803d 130%);
            display:flex;flex-direction:column;justify-content:center;padding:54px 56px}
        .orb{position:absolute;border-radius:50%;filter:blur(70px);opacity:.45}
        .orb.a{width:360px;height:360px;background:#16a34a;top:-90px;right:-80px}
        .orb.b{width:320px;height:320px;background:#0d9488;bottom:-100px;left:-60px;opacity:.4}
        .bp{position:relative;z-index:1;max-width:430px}
        .bp .logo{width:74px;height:74px;border-radius:20px;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));display:flex;align-items:center;justify-content:center;font-size:38px;margin-bottom:22px;box-shadow:0 10px 30px rgba(34,197,94,.45)}
        .bp h1{font-size:30px;font-weight:800}
        .bp .tag{color:#86efac;font-size:13px;font-weight:600;letter-spacing:2px;text-transform:uppercase;margin:6px 0 30px}
        .pt{display:flex;align-items:center;gap:14px;padding:13px 0;border-bottom:1px solid rgba(255,255,255,.08)}
        .pt i{color:#86efac;font-size:20px}
        .pt span{font-size:14.5px}
        .form-panel{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 28px;background:#fff;overflow-y:auto}
        .box{width:100%;max-width:420px}
        .box h2{font-size:25px;font-weight:800;color:var(--ink)}
        .box .lead{color:var(--muted);font-size:14px;margin:6px 0 26px}
        .field{margin-bottom:15px}
        .field label{display:block;font-size:13px;font-weight:600;color:var(--ink);margin-bottom:6px}
        .field input,.field select{width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:11px;font-family:inherit;font-size:14.5px;background:#f8fafb}
        .field input:focus,.field select:focus{outline:none;border-color:var(--brand-2);background:#fff;box-shadow:0 0 0 4px rgba(34,197,94,.13)}
        .two{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn{width:100%;border:none;cursor:pointer;font-family:inherit;font-weight:700;font-size:15px;color:#fff;padding:13px;border-radius:12px;margin-top:6px;
            background:linear-gradient(135deg,var(--brand-2),var(--brand-3));box-shadow:0 8px 22px rgba(34,197,94,.3);display:flex;align-items:center;justify-content:center;gap:8px}
        .err{background:#fee2e2;color:#991b1b;border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:16px}
        .foot{text-align:center;margin-top:18px;font-size:13.5px;color:var(--muted)}
        .foot a{color:var(--brand-3);font-weight:600;text-decoration:none}
        a{text-decoration:none}
        @media(max-width:880px){.brand-panel{display:none}}
    </style>
</head>
<body>
<div class="split">
    <div class="brand-panel">
        <div class="orb a"></div><div class="orb b"></div>
        <div class="bp">
            <div class="logo">🎓</div>
            <h1>Crea tu colegio</h1>
            <div class="tag">Empieza en minutos</div>
            <div class="pt"><i class="bi bi-check-circle-fill"></i><span>Configura tu institución en segundos</span></div>
            <div class="pt"><i class="bi bi-check-circle-fill"></i><span>Datos aislados y seguros por colegio</span></div>
            <div class="pt"><i class="bi bi-check-circle-fill"></i><span>30 días de prueba, sin tarjeta</span></div>
            <div class="pt"><i class="bi bi-check-circle-fill"></i><span>Todos los módulos incluidos</span></div>
        </div>
    </div>
    <div class="form-panel">
        <div class="box">
            <h2>Registra tu institución</h2>
            <div class="lead">Crea la cuenta de administrador de tu colegio</div>

            @if($errors->any())<div class="err"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="field">
                    <label>Nombre del colegio</label>
                    <input name="school_name" value="{{ old('school_name') }}" placeholder="Ej: Colegio San Martín" required autofocus>
                </div>
                <div class="field">
                    <label>Plan</label>
                    <select name="plan">
                        @foreach($plans as $key=>$label)<option value="{{ $key }}" @selected(old('plan','pro')==$key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <hr style="border:none;border-top:1px solid var(--line);margin:18px 0">
                <div class="field">
                    <label>Tu nombre (administrador)</label>
                    <input name="name" value="{{ old('name') }}" placeholder="Nombre y apellido" required>
                </div>
                <div class="field">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" required>
                </div>
                <div class="two">
                    <div class="field"><label>Contraseña</label><input type="password" name="password" placeholder="••••••••" required></div>
                    <div class="field"><label>Confirmar</label><input type="password" name="password_confirmation" placeholder="••••••••" required></div>
                </div>
                <button class="btn"><i class="bi bi-rocket-takeoff"></i> Crear mi colegio</button>
            </form>
            <div class="foot">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></div>
        </div>
    </div>
</div>
</body>
</html>
