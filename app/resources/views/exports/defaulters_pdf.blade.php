<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size:11px; color:#2c3e50; margin:18px 22px; }
        .head { border-bottom:2px solid #2ecc71; padding-bottom:6px; margin-bottom:12px; }
        .head .brand { color:#27ae60; font-weight:bold; font-size:14px; }
        .head h1 { margin:2px 0 0; font-size:13px; }
        .head .sub { color:#7b8a99; font-size:10px; }
        table { width:100%; border-collapse:collapse; }
        th { background:#1f2a36; color:#fff; padding:7px 6px; font-size:10px; text-align:left; }
        td { border-bottom:1px solid #eceff2; padding:6px; }
        tr:nth-child(even) td { background:#f7f9fb; }
        .right { text-align:right; }
        .tot { margin-top:14px; text-align:right; font-size:14px; font-weight:bold; }
    </style>
</head>
<body>
    <div class="head">
        @if($setting->logo_base64)<img src="{{ $setting->logo_base64 }}" style="height:26px;vertical-align:middle;margin-right:6px">@endif
        <span class="brand">{{ $setting->school_name }}</span>
        <h1>Reporte de Colegiaturas Pendientes y Vencidas</h1>
        <div class="sub">{{ $rows->count() }} estudiantes con deuda · Ciclo Escolar {{ $setting->academic_year }} · {{ now()->format('d/m/Y H:i') }}</div>
    </div>
    <table>
        <thead><tr><th>Alumno</th><th>Matrícula</th><th>Grado/Grupo</th><th>Padre o Tutor</th><th>Meses</th><th class="right">Adeudo</th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td>{{ $r['student']->full_name }}</td>
                <td>{{ $r['student']->code }}</td>
                <td>{{ optional($r['student']->course)->name }} "{{ optional($r['student']->course)->section }}"</td>
                <td>{{ $r['student']->guardian_name ?? '—' }}</td>
                <td>{{ $r['count'] }}</td>
                <td class="right">{{ $setting->currency }} {{ number_format($r['total'],2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="padding:18px;color:#9fb0bf">No hay estudiantes morosos.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="tot">Adeudo total: {{ $setting->currency }} {{ number_format($grandTotal,2) }}</div>
</body>
</html>
