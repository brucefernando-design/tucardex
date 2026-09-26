<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boleta de Calificaciones</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color:#2c3e50; font-size:12px; margin:0; }
        .header { background:#1f2a36; color:#fff; padding:18px 24px; }
        .header h1 { margin:0; font-size:18px; }
        .header .sub { color:#9fb0bf; font-size:11px; margin-top:2px; }
        .brand { color:#2ecc71; font-weight:bold; font-size:20px; }
        .wrap { padding:0 24px; }
        .student-box { border:1px solid #e3e8ec; border-radius:6px; margin-top:18px; padding:12px 16px; background:#f9fbfc; }
        .student-box table { width:100%; }
        .student-box td { padding:3px 0; font-size:12px; }
        .student-box .lbl { color:#7b8a99; width:90px; }
        table.grades { width:100%; border-collapse:collapse; margin-top:18px; }
        table.grades th { background:#2ecc71; color:#fff; padding:8px 6px; font-size:11px; text-align:center; }
        table.grades th.left, table.grades td.left { text-align:left; padding-left:10px; }
        table.grades td { border-bottom:1px solid #eceff2; padding:7px 6px; text-align:center; }
        table.grades tr:nth-child(even) td { background:#f7f9fb; }
        .final-col { font-weight:bold; }
        .ok { color:#1e8e57; } .bad { color:#c0392b; }
        .summary { margin-top:18px; border:2px solid #2ecc71; border-radius:6px; padding:12px 16px; text-align:right; }
        .summary .big { font-size:22px; font-weight:bold; color:#1f2a36; }
        .footer { margin-top:14px; padding:0 24px; color:#7b8a99; font-size:9.5px; }
        .sign { margin-top:24px; width:100%; }
        .sign td { text-align:center; padding-top:6px; border-top:1px solid #2c3e50; font-size:11px; width:33%; }
        .sign-row td { border:none; padding-top:26px; }
    </style>
</head>
<body>
    <div class="header">
        @if(optional($appSettings)->logo_base64)<img src="{{ $appSettings->logo_base64 }}" style="height:32px;vertical-align:middle;margin-right:8px;border-radius:6px"> <span class="brand" style="vertical-align:middle">{{ $appSettings->school_name }}</span>@else<span class="brand">&#127891; {{ $appSettings->school_name ?? 'TuKardex' }}</span>@endif
        <h1 style="margin-top:6px">BOLETA DE CALIFICACIONES</h1>
        <div class="sub">Ciclo Escolar {{ $appSettings->academic_year ?? ($student->course->academic_year ?? date('Y')) }} · Emitido el {{ $date->format('d/m/Y') }}</div>
    </div>

    <div class="wrap">
        <div class="student-box">
            <table>
                <tr>
                    <td class="lbl">Estudiante:</td><td><strong>{{ $student->full_name }}</strong></td>
                    <td class="lbl">CURP:</td><td><strong>{{ $student->curp ?? $student->dni ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="lbl">Matrícula:</td><td>{{ $student->code }}</td>
                    <td class="lbl">CCT / RVOE:</td><td>{{ $appSettings->cct ?? 'CCT SEP' }} · {{ $appSettings->rvoe ?? 'RVOE' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Grado/Grupo:</td><td>{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</td>
                    <td class="lbl">Nivel:</td><td>{{ optional($student->course)->level }}</td>
                </tr>
                <tr>
                    <td class="lbl">Profesor Titular:</td><td>{{ optional(optional($student->course)->tutor)->full_name ?? '—' }}</td>
                    <td class="lbl">Turno:</td><td>{{ optional($student->course)->shift }}</td>
                </tr>
                <tr>
                    <td class="lbl">Asistencia:</td>
                    <td colspan="3">
                        @if($attendancePercent !== null)
                            <strong class="{{ $attendanceMeetsRequirement ? 'ok' : 'bad' }}">{{ $attendancePercent }}%</strong>
                            <small style="color:#7b8a99"> &mdash; {{ $totalDays }} días &middot; Mín. SEP: 80%
                            @if($attendanceMeetsRequirement) ✓ Cumple @else ✗ No cumple @endif</small>
                        @else
                            <span style="color:#9fb0bf;font-size:10px">Sin registros de asistencia</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="grades">
            <thead>
                <tr>
                    <th class="left">Asignatura</th>
                    @foreach($periods as $p)<th>{{ $p }}</th>@endforeach
                    <th>Promedio</th>
                    <th>Situación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td class="left">{{ $r['subject'] }}</td>
                        @foreach($periods as $p)
                            <td>{{ $r['periods'][$p] ?? '—' }}</td>
                        @endforeach
                        <td class="final-col">{{ $r['final'] ?? '—' }}</td>
                        <td class="{{ $r['status']==='Aprobado' ? 'ok' : ($r['status']==='Reprobado' ? 'bad' : '') }}">{{ $r['status'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($periods)+3 }}" style="padding:20px;color:#9fb0bf">Este estudiante aún no tiene calificaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary">
            Promedio general &nbsp; <span class="big {{ $generalAverage!==null ? (($generalAverage>=6.0 ? 'ok' : 'bad')) : '' }}">{{ $generalAverage ?? '—' }}</span>
        </div>

        <table class="sign">
            <tr class="sign-row"><td></td><td></td><td></td></tr>
            <tr><td>Profesor Titular</td><td>{{ $appSettings->director ?? 'Director(a)' }}<br><small style='color:#9fb0bf;font-size:9px'>Cédula Prof.: {{ $appSettings->cedula_profesional ?? '_______________' }}</small></td><td>Padre de Familia o Tutor</td></tr>
        </table>

        @if(!empty($qrData))
        <table style="width:100%;margin-top:20px;border-top:1px solid #cbd5e1;padding-top:8px;">
            <tr>
                <td style="width:50px;vertical-align:middle;text-align:left;">
                    <img src="{{ $qrData }}" style="width:48px;height:48px;display:block;">
                </td>
                <td style="vertical-align:middle;font-size:8.5px;color:#64748b;line-height:1.35;padding-left:8px;">
                    <strong style="color:#1f2a36;">DOCUMENTO OFICIAL VALIDADOR SEP / TUCARDEX</strong><br>
                    Alumno: <strong>{{ $student->full_name }}</strong> &middot; Matrícula: <strong>{{ $student->code }}</strong> &middot; CURP: <strong>{{ $student->curp ?? $student->dni ?? 'N/D' }}</strong><br>
                    Promedio General: <strong>{{ $generalAverage !== null ? number_format($generalAverage, 1) : '—' }}</strong> &middot; Escala oficial SEP: 5.0 a 10.0 (Mínimo aprobatorio: 6.0)
                </td>
                <td style="width:115px;vertical-align:middle;text-align:right;font-size:8px;color:#94a3b8;line-height:1.3;">
                    Validación QR Escolar<br>
                    Emitido: {{ $date->format('d/m/Y') }}
                </td>
            </tr>
        </table>
        @endif
    </div>

    <div class="footer">
        Escala oficial SEP: 5.0 a 10.0 (Mínimo aprobatorio: 6.0). Documento generado por el Sistema de Gestión Escolar TuKardex.
    </div>
</body>
</html>
