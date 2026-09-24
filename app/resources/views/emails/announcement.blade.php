<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="margin:0;background:#eef2f5;font-family:Segoe UI,Arial,sans-serif;color:#2c3e50">
    <div style="max-width:600px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)">
        <div style="background:#1f2a36;padding:20px 24px">
            <span style="color:#2ecc71;font-weight:bold;font-size:18px">&#127891; {{ $schoolName }}</span>
        </div>
        <div style="background:linear-gradient(90deg,#27ae60,#2ecc71);height:5px"></div>
        <div style="padding:28px 24px">
            <span style="display:inline-block;background:#e8f8f0;color:#1e8e57;padding:3px 12px;border-radius:12px;font-size:12px">Comunicado · {{ ucfirst($announcement->audience) }}</span>
            <h2 style="margin:14px 0 6px;color:#1f2a36">{{ $announcement->title }}</h2>
            <p style="color:#7b8a99;font-size:12px;margin:0 0 18px">{{ optional($announcement->published_at ?? $announcement->created_at)->format('d/m/Y H:i') }}</p>
            <div style="font-size:14px;line-height:1.7;color:#3c4858">{!! nl2br(e($announcement->body)) !!}</div>
        </div>
        <div style="padding:16px 24px;background:#f7f9fb;color:#9fb0bf;font-size:11px;border-top:1px solid #eceff2">
            Este es un mensaje automático del Sistema de Gestión Escolar de {{ $schoolName }}. Por favor no responda a este correo.
        </div>
    </div>
</body>
</html>
