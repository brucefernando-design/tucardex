<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; }
        body { font-size:12px; color:#2c3e50; margin:0; padding:20px; background:#f4f6f8; }
        .doc { background:#fff; border:1px solid #dce2e6; border-radius:8px; padding:28px; max-width:680px; margin:0 auto; box-shadow:0 2px 6px rgba(0,0,0,.04); }
        .head { border-bottom:2px solid #2ecc71; padding-bottom:14px; margin-bottom:20px; }
        .head .brand { font-size:18px; font-weight:bold; color:#1f2a36; }
        .head .sub { color:#7b8a99; font-size:11px; margin-top:2px; }
        .head .recibo { float:right; text-align:right; }
        .head .recibo .t { font-size:15px; font-weight:bold; color:#2ecc71; }
        .head .recibo .n { font-size:12px; color:#1f2a36; font-weight:bold; }
        table.meta { width:100%; border-collapse:collapse; margin-bottom:18px; }
        table.meta td { padding:5px 8px; font-size:11px; }
        table.meta td.l { color:#7b8a99; width:110px; }
        table.items { width:100%; border-collapse:collapse; margin-top:10px; }
        table.items th { background:#f7f9fb; border-bottom:2px solid #eceff2; padding:8px; text-align:left; font-size:11px; color:#4a5568; }
        table.items td { border-bottom:1px solid #eceff2; padding:9px 8px; font-size:11px; }
        .total { margin-top:16px; text-align:right; }
        .total .box { display:inline-block; background:#1f2a36; color:#fff; padding:12px 22px; border-radius:8px; font-size:15px; font-weight:bold; }
        .status { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:bold; }
        .pagado { background:#e8f8f0; color:#1e8e57; }
        .pendiente { background:#fef5e7; color:#b9770e; }
        .vencido { background:#fdecea; color:#c0392b; }
        .sign { margin-top:50px; text-align:center; }
        .sign .line { border-top:1px solid #2c3e50; width:230px; margin:0 auto; padding-top:6px; font-size:11px; }
        .foot { text-align:center; color:#9fb0bf; font-size:10px; padding:14px; }
    </style>
</head>
<body>
    <div class="doc">
        <div class="head">
            <div class="recibo">
                <div class="t">RECIBO DE PAGO OFICIAL</div>
                <div class="n">N° {{ $payment->invoice_number }}</div>
            </div>
            @if($setting->logo_base64)<img src="{{ $setting->logo_base64 }}" style="height:30px;vertical-align:middle;margin-right:8px"><span class="brand" style="vertical-align:middle">{{ $setting->school_name }}</span>@else<span class="brand">{{ $setting->school_name }}</span>@endif
            <div class="sub">{{ $setting->address }} — Tel: {{ $setting->phone }}</div>
        </div>
        <div class="body">
            <table class="meta">
                <tr><td class="l">Recibí de:</td><td><strong>{{ optional($payment->student)->full_name }}</strong></td>
                    <td class="l">Fecha de emisión:</td><td>{{ ($payment->paid_date ?? $payment->created_at)->format('d/m/Y') }}</td></tr>
                <tr><td class="l">Matrícula:</td><td>{{ optional($payment->student)->code }}</td>
                    <td class="l">Grado / Grupo:</td><td>{{ optional(optional($payment->student)->course)->name }} "{{ optional(optional($payment->student)->course)->section }}"</td></tr>
                <tr><td class="l">Estado:</td><td colspan="3"><span class="status {{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                    @if($payment->method) — Método: {{ ucfirst($payment->method) }}@endif</td></tr>
            </table>

            <table class="items">
                <thead><tr><th>Concepto</th><th>Período</th><th style="text-align:right">Importe (MXN)</th></tr></thead>
                <tbody>
                    @php $listItems = isset($items) && count($items) ? $items : collect([$payment]); @endphp
                    @foreach($listItems as $it)
                    <tr>
                        <td><strong>{{ $it->concept }}</strong></td>
                        <td>{{ $it->period ?? '—' }}</td>
                        <td style="text-align:right">{{ $setting->currency ?? '$' }} {{ number_format($it->amount, 2) }} MXN</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @php $calcTotal = isset($totalAmount) ? $totalAmount : $listItems->sum('amount'); @endphp
            <div class="total"><span class="box">TOTAL: {{ $setting->currency ?? '$' }} {{ number_format($calcTotal, 2) }} MXN</span></div>

            <div class="sign"><div class="line">Caja / Control Escolar<br>{{ $setting->school_name }}</div></div>
        </div>
        <div class="foot">Comprobante administrativo interno emitido por {{ $setting->school_name }}. Moneda: Pesos Mexicanos (MXN).</div>
    </div>
</body>
</html>
