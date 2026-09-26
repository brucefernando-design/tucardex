<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size:11px; color:#2c3e50; margin:18px 22px; }
        .head { border-bottom:2px solid #2ecc71; padding-bottom:8px; margin-bottom:12px; }
        .head h1 { margin:0; font-size:16px; }
        .head .brand { color:#27ae60; font-weight:bold; }
        .head .sub { color:#7b8a99; font-size:10px; }
        table { width:100%; border-collapse:collapse; }
        th { background:#1f2a36; color:#fff; padding:6px; font-size:10px; text-align:left; }
        td { border-bottom:1px solid #eceff2; padding:5px 6px; }
        tr:nth-child(even) td { background:#f7f9fb; }
    </style>
</head>
<body>
    <div class="head">
        <span class="brand">&#127891; TuKardex</span>
        <h1>Listado de Estudiantes</h1>
        <div class="sub">Total: {{ $students->count() }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>
    <table>
        <thead><tr><th>Matrícula</th><th>Alumno</th><th>CURP</th><th>Grado/Grupo</th><th>Padre o Tutor</th><th>Teléfono</th><th>Estado</th></tr></thead>
        <tbody>
        @foreach($students as $s)
            <tr>
                <td>{{ $s->code }}</td>
                <td>{{ $s->full_name }}</td>
                <td>{{ $s->curp ?? $s->dni ?? '—' }}</td>
                <td>{{ optional($s->course)->name ?? '—' }}</td>
                <td>{{ $s->guardian_name ?? '—' }}</td>
                <td>{{ $s->guardian_phone ?? '—' }}</td>
                <td>{{ ucfirst($s->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
