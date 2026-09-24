<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin:0; }
        .card { width:242px; height:153px; position:relative; }
        .top { background:#1f2a36; color:#fff; padding:8px 10px; }
        .top .brand { color:#2ecc71; font-weight:bold; font-size:11px; }
        .top .sub { font-size:7px; color:#9fb0bf; }
        .green-bar { height:4px; background:#2ecc71; }
        .content { padding:8px 10px; }
        .photo { width:46px; height:54px; background:#eef1f4; border:1px solid #d6dde3; float:left; margin-right:10px; text-align:center; color:#9fb0bf; font-size:20px; line-height:54px; }
        .name { font-size:12px; font-weight:bold; color:#1f2a36; }
        .role { font-size:8px; color:#27ae60; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
        table.d { font-size:8px; color:#2c3e50; border-collapse:collapse; }
        table.d td { padding:1px 0; }
        table.d .l { color:#7b8a99; padding-right:6px; }
        .footer { position:absolute; bottom:4px; left:10px; right:10px; font-size:6.5px; color:#9fb0bf; border-top:1px solid #eceff2; padding-top:2px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <span class="brand">&#127891; {{ \Illuminate\Support\Str::limit($setting->school_name, 28) }}</span>
            <div class="sub">CREDENCIAL ESCOLAR · CICLO ESCOLAR {{ $setting->academic_year }}</div>
        </div>
        <div class="green-bar"></div>
        <div class="content">
            <div class="photo">@if($student->photo_base64)<img src="{{ $student->photo_base64 }}" style="width:100%;height:100%;object-fit:cover">@else{{ $student->initials }}@endif</div>
            <div class="name">{{ $student->full_name }}</div>
            <div class="role">Estudiante</div>
            <table class="d">
                <tr><td class="l">Código:</td><td>{{ $student->code }}</td></tr>
                <tr><td class="l">CURP:</td><td><strong>{{ $student->curp ?? $student->dni ?? '—' }}</strong></td></tr>
                <tr><td class="l">Grado/Gpo:</td><td>{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</td></tr>
                <tr><td class="l">Nivel:</td><td>{{ optional($student->course)->level }}</td></tr>
            </table>
        </div>
        <div class="footer">{{ $setting->phone }} · Documento válido solo para el ciclo escolar {{ $setting->academic_year }}</div>
    </div>
</body>
</html>
