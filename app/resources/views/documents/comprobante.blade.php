<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @php
        use App\Services\Facturacion\Support\NumberToWords;
        $tipoNombre = strtoupper($invoice->tipoLabel());
        $docCliente = ['6'=>'RUC','1'=>'DNI','0'=>'DOC.'][$invoice->client_tipo_doc] ?? 'DOC.';
        $estadoMap = ['aceptado'=>['ACEPTADO POR SUNAT','#1e8e57','#e8f8f0'],'pendiente'=>['PENDIENTE DE ENVÍO','#b9770e','#fef5e7'],'enviando'=>['ENVIANDO','#b9770e','#fef5e7'],'observado'=>['ACEPTADO CON OBSERVACIONES','#1e40af','#dbeafe'],'rechazado'=>['RECHAZADO POR SUNAT','#c0392b','#fdecea'],'error'=>['ERROR DE EMISIÓN','#c0392b','#fdecea'],'anulado'=>['ANULADO','#64748b','#eef1f4']];
        $est = $estadoMap[$invoice->estado] ?? ['—','#64748b','#eef1f4'];
    @endphp
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color:#1f2a37; font-size:11px; margin:0; }
        .doc { border:2px solid #16a34a; border-radius:10px; margin:14px; }
        .head { padding:12px 16px; border-bottom:1px solid #e8edeb; }
        .head-left { display:inline-block; width:58%; vertical-align:top; }
        .head-right { display:inline-block; width:40%; vertical-align:top; text-align:center; }
        .brand { color:#15803d; font-weight:bold; font-size:15px; }
        .sub { color:#7b8a99; font-size:9.5px; margin-top:3px; line-height:1.5; }
        .cpe-box { border:1.5px solid #16a34a; border-radius:8px; padding:8px 10px; }
        .cpe-box .ruc { font-size:10px; color:#15803d; font-weight:bold; }
        .cpe-box .tt { font-size:12px; font-weight:bold; margin:3px 0; }
        .cpe-box .nn { font-size:14px; font-weight:bold; letter-spacing:.5px; }
        .body { padding:12px 16px; }
        table { width:100%; border-collapse:collapse; }
        .meta td { padding:3px 0; font-size:10.5px; vertical-align:top; }
        .meta .l { color:#7b8a99; width:70px; }
        table.items { margin-top:10px; }
        table.items th { background:#16a34a; color:#fff; padding:6px 7px; text-align:left; font-size:10px; }
        table.items td { border-bottom:1px solid #eceff2; padding:6px 7px; font-size:10.5px; }
        .r { text-align:right; }
        .tot { margin-top:10px; width:100%; }
        .tot td { padding:3px 7px; font-size:10.5px; }
        .tot .lbl { text-align:right; color:#5b6b7b; }
        .tot .grand { background:#16a34a; color:#fff; font-weight:bold; font-size:12px; border-radius:6px; }
        .letras { margin-top:8px; font-size:10px; background:#f3faf6; border:1px solid #dcfce7; border-radius:6px; padding:6px 9px; }
        .qr-wrap { margin-top:12px; }
        .qr-wrap td { vertical-align:top; }
        .qr-img { width:110px; height:110px; }
        .hashbox { font-size:9px; color:#5b6b7b; word-break:break-all; }
        .status { display:inline-block; padding:4px 12px; border-radius:20px; font-size:10px; font-weight:bold; }
        .foot { text-align:center; color:#9fb0bf; font-size:9px; padding:10px; border-top:1px solid #e8edeb; }
    </style>
</head>
<body>
<div class="doc">
    <div class="head">
        <div class="head-left">
            @if($school->logo_base64)<img src="{{ $school->logo_base64 }}" style="height:26px;vertical-align:middle;margin-right:6px"><span class="brand" style="vertical-align:middle">{{ $settings->razon_social ?: $school->school_name }}</span>
            @else<span class="brand">&#127891; {{ $settings->razon_social ?: $school->school_name }}</span>@endif
            <div class="sub">
                @if($settings->nombre_comercial){{ $settings->nombre_comercial }}<br>@endif
                {{ $settings->direccion_fiscal ?: $school->address }}<br>
                {{ trim(($settings->distrito ?? '').' '.($settings->provincia ?? '').' '.($settings->departamento ?? ''), ' ') }}
            </div>
        </div>
        <div class="head-right">
            <div class="cpe-box">
                <div class="ruc">R.U.C. {{ $settings->ruc ?: '—' }}</div>
                <div class="tt">{{ $tipoNombre }} ELECTRÓNICA</div>
                <div class="nn">{{ $invoice->full_number }}</div>
            </div>
        </div>
    </div>

    <div class="body">
        <table class="meta">
            <tr>
                <td class="l">Cliente:</td><td><strong>{{ $invoice->client_razon_social }}</strong></td>
                <td class="l">Fecha:</td><td>{{ optional($invoice->fecha_emision)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="l">{{ $docCliente }}:</td><td>{{ $invoice->client_num_doc ?: '—' }}</td>
                <td class="l">Moneda:</td><td>{{ $invoice->moneda }}</td>
            </tr>
            @if($invoice->client_direccion)
            <tr><td class="l">Dirección:</td><td colspan="3">{{ $invoice->client_direccion }}</td></tr>
            @endif
        </table>

        <table class="items">
            <thead><tr><th>Descripción</th><th class="r" style="width:14%">Cant.</th><th class="r" style="width:22%">V. Unit.</th><th class="r" style="width:22%">Importe</th></tr></thead>
            <tbody>
            @foreach(($invoice->items ?? []) as $it)
                @php $c=(float)($it['cantidad'] ?? 1); $vu=(float)($it['valor_unit'] ?? 0); @endphp
                <tr>
                    <td>{{ $it['descripcion'] ?? '' }}</td>
                    <td class="r">{{ number_format($c,2) }}</td>
                    <td class="r">{{ number_format($vu,2) }}</td>
                    <td class="r">{{ number_format($c*$vu,2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <table class="tot">
            <tr><td class="lbl">Op. Gravada:</td><td class="r" style="width:110px">{{ $invoice->moneda }} {{ number_format($invoice->op_gravadas,2) }}</td></tr>
            <tr><td class="lbl">IGV ({{ rtrim(rtrim(number_format($settings->igv_percent ?: 18,2),'0'),'.') }}%):</td><td class="r">{{ $invoice->moneda }} {{ number_format($invoice->igv,2) }}</td></tr>
            <tr><td class="lbl grand">IMPORTE TOTAL:</td><td class="r grand">{{ $invoice->moneda }} {{ number_format($invoice->total,2) }}</td></tr>
        </table>

        <div class="letras">{{ NumberToWords::toCurrency((float)$invoice->total, $invoice->moneda) }}</div>

        <table class="qr-wrap">
            <tr>
                <td style="width:120px">
                    @if($qr)<img src="{{ $qr }}" class="qr-img">
                    @else<div style="font-size:8.5px;color:#7b8a99">QR: instala <code>endroid/qr-code</code></div>@endif
                </td>
                <td>
                    <div><span class="status" style="color:{{ $est[1] }};background:{{ $est[2] }}">{{ $est[0] }}</span></div>
                    @if($invoice->sunat_code)<div class="sub" style="margin-top:6px">Respuesta SUNAT [{{ $invoice->sunat_code }}]: {{ $invoice->sunat_description }}</div>@endif
                    <div class="sub" style="margin-top:6px">Autorización: {{ $settings->environmentLabel() }}</div>
                    @if($invoice->hash)<div class="hashbox" style="margin-top:6px"><strong>Hash:</strong> {{ $invoice->hash }}</div>@endif
                </td>
            </tr>
        </table>
    </div>

    <div class="foot">
        Representación impresa del Comprobante de Pago Electrónico. Consulte su validez en www.sunat.gob.pe ·
        Emitido por {{ $settings->razon_social ?: $school->school_name }}.
    </div>
</div>
</body>
</html>
