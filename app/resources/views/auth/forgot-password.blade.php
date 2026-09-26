<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña · {{ $appSettings->school_name ?? 'TuKardex' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--brand:#16a34a;--brand-2:#22c55e;--brand-3:#15803d;--ink:#1f2a37;--muted:#64748b;--line:#e8edeb}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:linear-gradient(160deg,#0f1f18,#15803d 130%);padding:20px}
        .card{width:100%;max-width:430px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.35)}
        .head{background:linear-gradient(135deg,var(--brand-2),var(--brand-3));color:#fff;padding:32px 30px;text-align:center}
        .head .ic{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:28px}
        .head h1{font-size:21px;margin-top:14px;font-weight:800}
        .head p{opacity:.9;font-size:13.5px;margin-top:4px}
        .body{padding:30px}
        .field label{display:block;font-size:13px;font-weight:600;color:var(--ink);margin-bottom:7px}
        .input-ic{position:relative;margin-bottom:18px}
        .input-ic i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#9fb0bf}
        .input-ic input{width:100%;padding:13px 15px 13px 44px;border:1.5px solid var(--line);border-radius:12px;font-family:inherit;font-size:14.5px;background:#f8fafb}
        .input-ic input:focus{outline:none;border-color:var(--brand-2);background:#fff;box-shadow:0 0 0 4px rgba(34,197,94,.13)}
        .btn{width:100%;border:none;cursor:pointer;font-family:inherit;font-weight:700;font-size:15px;color:#fff;padding:13px;border-radius:12px;
            background:linear-gradient(135deg,var(--brand-2),var(--brand-3));box-shadow:0 8px 22px rgba(34,197,94,.3);display:flex;align-items:center;justify-content:center;gap:8px}
        .alert{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:18px;display:flex;align-items:center;gap:8px}
        .ok{background:#dcfce7;color:#15803d} .err{background:#fee2e2;color:#991b1b}
        .back{display:block;text-align:center;margin-top:20px;color:var(--muted);font-size:13.5px;text-decoration:none}
        .back:hover{color:var(--brand-3)}
        a{text-decoration:none}
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <span class="ic"><i class="bi bi-shield-lock"></i></span>
            <h1>Recuperar contraseña</h1>
            <p>Te enviaremos un enlace para restablecerla</p>
        </div>
        <div class="body">
            @if(session('success'))<div class="alert ok"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert err"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="field">
                    <label>Correo electrónico</label>
                    <div class="input-ic"><i class="bi bi-envelope"></i>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@colegio.test" required autofocus>
                    </div>
                </div>
                <button class="btn"><i class="bi bi-send"></i> Enviar enlace</button>
            </form>
            <a href="{{ route('login') }}" class="back"><i class="bi bi-arrow-left"></i> Volver a iniciar sesión</a>
        </div>
    </div>
</body>
</html>
