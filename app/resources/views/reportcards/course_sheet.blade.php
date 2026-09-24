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
        th, td { border:1px solid #d9dee3; padding:3px 4px; text-align:center; }
        th { background:#1f2a36; color:#fff; font-size:8px; }
        td.name, th.name { text-align:left; min-width:130px; }
        th.subj { font-size:7.5px; }
        .ok { color:#1e8e57; } .bad { color:#c0392b; }
        .avg { font-weight:bold; background:#f0fdf4; }
        .rank { font-weight:bold; background:#eef6ff; }
        .legend { margin-top:8px; color:#7b8a99; font-size:8px; }
    </style>
</head>
<body>
    <div class="head">
        @if($setting->logo_base64)<img src="{{ $setting->logo_base64 }}" style="height:26px;vertical-align:middle;margin-right:6px">@endif
        <span class="brand">{{ $setting->school_name }}</span>
        <h1>Acta de Calificaciones</h1>
        <div class="sub">
            Grado y Grupo: {{ $course->name }} "{{ $course->section }}" · Nivel: {{ $course->level }} ·
            Profesor Titular: {{ optional($course->tutor)->full_name ?? '—' }} ·
            Período: {{ $period }} · Ciclo Escolar {{ $setting->academic_year }} · {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:24px">Nº</th>
                <th class="name">Estudiante</th>
                @foreach($subjects as $sub)
                    <th class="subj">{{ \Illuminate\Support\Str::limit($sub->name, 14) }}</th>
                @endforeach
                <th>Prom.</th>
                <th>Puesto</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="name">{{ $r['student']->full_name }}</td>
                @foreach($subjects as $sub)
                    @php $v = $r['bySubject'][$sub->id]; @endphp
                    <td class="{{ $v!==null ? ($v>=6.0 ? 'ok' : 'bad') : '' }}">{{ $v ?? '—' }}</td>
                @endforeach
                <td class="avg {{ $r['overall']!==null ? ($r['overall']>=6.0 ? 'ok' : 'bad') : '' }}">{{ $r['overall'] ?? '—' }}</td>
                <td class="rank">{{ $r['rank'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $subjects->count()+4 }}" style="padding:18px;color:#9fb0bf">Este curso no tiene estudiantes o calificaciones.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="legend">Escala oficial SEP: 5.0 a 10.0 (Mínimo aprobatorio: 6.0). El puesto se calcula por el promedio general del período seleccionado.</div>
</body>
</html>
