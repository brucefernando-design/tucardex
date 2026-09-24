<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size:9px; color:#2c3e50; margin:14px 16px; }
        .head { border-bottom:2px solid #2ecc71; padding-bottom:6px; margin-bottom:10px; }
        .head .brand { color:#27ae60; font-weight:bold; font-size:13px; }
        .head h1 { margin:2px 0 0; font-size:12px; }
        .head .sub { color:#7b8a99; font-size:9px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #d9dee3; padding:2px 3px; text-align:center; }
        th { background:#1f2a36; color:#fff; }
        td.name, th.name { text-align:left; min-width:120px; }
        .P { color:#1e8e57; font-weight:bold; } .F { color:#c0392b; font-weight:bold; }
        .T { color:#b9770e; font-weight:bold; } .J { color:#2470b3; font-weight:bold; }
        .legend { margin-top:8px; color:#7b8a99; font-size:8px; }
    </style>
</head>
<body>
    <div class="head">
        <span class="brand">&#127891; {{ $setting->school_name }}</span>
        <h1>Reporte Mensual de Asistencia</h1>
        <div class="sub">
            Curso: {{ optional($course)->name }} "{{ optional($course)->section }}" ·
            Mes: {{ \Carbon\Carbon::createFromFormat('Y-m',$month)->translatedFormat('F Y') }} ·
            Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table>
        <thead><tr>
            <th class="name">Estudiante</th>
            @foreach($days as $d)<th>{{ $d }}</th>@endforeach
            <th>P</th><th>F</th><th>T</th><th>J</th><th>%</th>
        </tr></thead>
        <tbody>
        @forelse($students as $s)
            @php $sm = $summary[$s->id]; @endphp
            <tr>
                <td class="name">{{ $s->full_name }}</td>
                @foreach($days as $d)
                    @php $m = $matrix[$s->id][$d] ?? ''; @endphp
                    <td class="{{ $m }}">{{ $m ?: '·' }}</td>
                @endforeach
                <td>{{ $sm['counts']['P'] }}</td>
                <td>{{ $sm['counts']['F'] }}</td>
                <td>{{ $sm['counts']['T'] }}</td>
                <td>{{ $sm['counts']['J'] }}</td>
                <td><strong>{{ $sm['pct'] }}%</strong></td>
            </tr>
        @empty
            <tr><td colspan="{{ count($days)+6 }}">Sin datos para este mes.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="legend">P = Presente · F = Falta · T = Tardanza · J = Justificado · (·) sin registro</div>
</body>
</html>
