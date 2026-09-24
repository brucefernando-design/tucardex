<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color:#2c3e50; font-size:13px; margin:0; }
        .doc { border:2px solid #2ecc71; border-radius:10px; margin:24px; }
        .head { background:#1f2a36; color:#fff; padding:18px 22px; border-radius:8px 8px 0 0; }
        .head .brand { color:#2ecc71; font-weight:bold; font-size:16px; }
        .head .sub { color:#9fb0bf; font-size:10px; margin-top:2px; }
        .head .recibo { float:right; text-align:right; }
        .head .recibo .t { font-size:15px; font-weight:bold; }
        .head .recibo .n { color:#9fb0bf; font-size:11px; }
        .body { padding:22px; }
        .row { display:flex; justify-content:space-between; }
        table { width:100%; border-collapse:collapse; margin-top:14px; }
        .meta td { padding:4px 0; font-size:12px; }
        .meta .l { color:#7b8a99; width:130px; }
        table.items th { background:#2ecc71; color:#fff; padding:8px; text-align:left; font-size:11px; }
        table.items td { border-bottom:1px solid #eceff2; padding:9px 8px; }
        .total { margin-top:16px; text-align:right; }
        .total .box { display:inline-block; background:#1f2a36; color:#fff; padding:12px 22px; border-radius:8px; font-size:16px; font-weight:bold; }
        .status { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:bold; }
        .pagado { background:#e8f8f0; color:#1e8e57; }
        .pendiente { background:#fef5e7; color:#b9770e; }
        .vencido { background:#fdecea; color:#c0392b; }
        .sign { margin-top:60px; text-align:center; }
        .sign .line { border-top:1px solid #2c3e50; width:230px; margin:0 auto; padding-top:6px; font-size:11px; }
        .foot { text-align:center; color:#9fb0bf; font-size:10px; padding:14px; }
    </style>
</head>
<body>
    <div class="doc">
        <div class="head">
            <div class="recibo">
                <div class="t">RECIBO DE PAGO</div>
                <div class="n">N° {{ $payment->invoice_number }}</div>
            </div>
            @if($setting->logo_base64)<img src="{{ $setting->logo_base64 }}" style="height:30px;vertical-align:middle;margin-right:8px"><span class="brand" style="vertical-align:middle">{{ $setting->school_name }}</span>@else<span class="brand">&#127891; {{ $setting->school_name }}</span>@endif
            <div class="sub">{{ $setting->address }} · Tel: {{ $setting->phone }}</div>
        </div>
        <div class="body">
            <table class="meta">
                <tr><td class="l">Recibí de:</td><td><strong>{{ optional($payment->student)->full_name }}</strong></td>
                    <td class="l">Fecha:</td><td>{{ ($payment->paid_date ?? $payment->created_at)->format('d/m/Y') }}</td></tr>
                <tr><td class="l">Código:</td><td>{{ optional($payment->student)->code }}</td>
                    <td class="l">Curso:</td><td>{{ optional(optional($payment->student)->course)->name }} "{{ optional(optional($payment->student)->course)->section }}"</td></tr>
                <tr><td class="l">Estado:</td><td colspan="3"><span class="status {{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                    @if($payment->method) · Método: {{ ucfirst($payment->method) }}@endif</td></tr>
            </table>

            <table class="items">
                <thead><tr><th>Concepto</th><th>Período</th><th style="text-align:right">Importe</th></tr></thead>
                <tbody>
                    <tr>
                        <td>{{ $payment->concept }}</td>
                        <td>{{ $payment->period ?? '—' }}</td>
                        <td style="text-align:right">{{ $setting->currency }} {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="total"><span class="box">TOTAL: {{ $setting->currency }} {{ number_format($payment->amount, 2) }}</span></div>

            <div class="sign"><div class="line">Caja / Administración<br>{{ $setting->school_name }}</div></div>
        </div>
        <div class="foot">Gracias por su pago. Este recibo es un comprobante interno emitido por el sistema de gestión escolar.</div>
    </div>
</body>
</html>
