<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; }
        body { color:#2c3e50; font-size:12px; margin:0; }
        .header { background:#1f2a36; color:#fff; padding:16px 24px; }
        .header .brand { color:#2ecc71; font-weight:bold; font-size:18px; }
        .header h1 { margin:6px 0 0; font-size:14px; }
        .header .sub { color:#9fb0bf; font-size:10px; }
        .wrap { padding:0 24px; }
        .student-box { border:1px solid #e3e8ec; border-radius:6px; margin-top:16px; padding:10px 14px; background:#f9fbfc; }
        .student-box td { padding:3px 0; }
        .student-box .lbl { color:#7b8a99; width:120px; }
        table.pay { width:100%; border-collapse:collapse; margin-top:16px; }
        table.pay th { background:#2ecc71; color:#fff; padding:7px 6px; font-size:10px; text-align:left; }
        table.pay td { border-bottom:1px solid #eceff2; padding:6px; font-size:11px; }
        table.pay tr:nth-child(even) td { background:#f7f9fb; }
        .right { text-align:right; }
        .badge { padding:2px 7px; border-radius:10px; font-size:9px; font-weight:bold; }
        .b-pagado { background:#e8f8f0; color:#1e8e57; }
        .b-pendiente { background:#fef5e7; color:#b9770e; }
        .b-vencido { background:#fdecea; color:#c0392b; }
        .b-anulado { background:#eef1f4; color:#7b8a99; }
        .totals { margin-top:18px; width:55%; float:right; border-collapse:collapse; }
        .totals td { padding:6px 10px; border-bottom:1px solid #eceff2; font-size:11px; }
        .totals .tlbl { color:#7b8a99; }
        .totals .saldo td { background:#1f2a36; color:#fff; font-size:13px; font-weight:bold; border:none; }
        .footer { clear:both; padding:24px; color:#9fb0bf; font-size:10px; }
    </style>
</head>
<body>
    <div class="header">
        @if($setting->logo_base64)<img src="{{ $setting->logo_base64 }}" style="height:30px;vertical-align:middle;margin-right:8px;border-radius:6px"><span class="brand" style="vertical-align:middle">{{ $setting->school_name }}</span>@else<span class="brand">{{ $setting->school_name }}</span>@endif
        <h1>ESTADO DE CUENTA Y HISTORIAL FINANCIERO</h1>
        <div class="sub">{{ $setting->address }} — Tel: {{ $setting->phone }} — Ciclo Escolar {{ $setting->academic_year }} — Emitido {{ now()->format('d/m/Y') }}</div>
    </div>

    <div class="wrap">
        <table class="student-box">
            <tr><td class="lbl">Estudiante:</td><td><strong>{{ $student->full_name }}</strong></td><td class="lbl">Matrícula:</td><td>{{ $student->code }}</td></tr>
            <tr><td class="lbl">Grado y Grupo:</td><td>{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</td><td class="lbl">Padre o Tutor:</td><td>{{ $student->guardian_name ?? '—' }}</td></tr>
        </table>

        <table class="pay">
            <thead><tr><th>Folio</th><th>Concepto</th><th>Período</th><th>Vence</th><th>Fecha Pago</th><th class="right">Importe</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($student->payments as $p)
                <tr>
                    <td>{{ $p->invoice_number }}</td>
                    <td><strong>{{ $p->concept }}</strong></td>
                    <td>{{ $p->period ?? '—' }}</td>
                    <td>{{ optional($p->due_date)->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ optional($p->paid_date)->format('d/m/Y') ?? '—' }}</td>
                    <td class="right">{{ $setting->currency ?? '$' }} {{ number_format($p->amount,2) }} MXN</td>
                    <td><span class="badge b-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" style="padding:18px;color:#9fb0bf">Sin movimientos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr><td class="tlbl">Total cargos generados</td><td class="right">{{ $setting->currency ?? '$' }} {{ number_format($totals['total'],2) }} MXN</td></tr>
            <tr><td class="tlbl">Total pagado</td><td class="right">{{ $setting->currency ?? '$' }} {{ number_format($totals['pagado'],2) }} MXN</td></tr>
            <tr><td class="tlbl">Saldo pendiente</td><td class="right">{{ $setting->currency ?? '$' }} {{ number_format($totals['pendiente'],2) }} MXN</td></tr>
            <tr><td class="tlbl">Saldo vencido</td><td class="right">{{ $setting->currency ?? '$' }} {{ number_format($totals['vencido'],2) }} MXN</td></tr>
            <tr class="saldo"><td>SALDO POR LIQUIDAR</td><td class="right">{{ $setting->currency ?? '$' }} {{ number_format($totals['saldo'],2) }} MXN</td></tr>
        </table>
    </div>

    <div class="footer">Documento informativo oficial emitido por la administración de {{ $setting->school_name }}. Todos los importes expresados en Pesos Mexicanos (MXN).</div>
</body>
</html>
