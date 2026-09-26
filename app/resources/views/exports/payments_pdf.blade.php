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
        .cards { width:100%; margin-bottom:12px; }
        .cards td { width:33%; padding:8px; border-radius:6px; color:#fff; text-align:center; }
        .c1 { background:#2ecc71; } .c2 { background:#e67e22; } .c3 { background:#e74c3c; }
        .cards .v { font-size:16px; font-weight:bold; }
        table.list { width:100%; border-collapse:collapse; }
        table.list th { background:#1f2a36; color:#fff; padding:6px; font-size:10px; text-align:left; }
        table.list td { border-bottom:1px solid #eceff2; padding:5px 6px; }
        table.list tr:nth-child(even) td { background:#f7f9fb; }
    </style>
</head>
<body>
    <div class="head">
        <span class="brand">&#127891; TuKardex</span>
        <h1>Reporte de Pagos</h1>
        <div class="sub">Total registros: {{ $payments->count() }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table class="cards"><tr>
        <td class="c1">COBRADO<div class="v">{{ $setting->currency }} {{ number_format($summary['paid'],2) }}</div></td>
        <td class="c2" style="border-left:6px solid #fff;border-right:6px solid #fff">PENDIENTE<div class="v">{{ $setting->currency }} {{ number_format($summary['pending'],2) }}</div></td>
        <td class="c3">VENCIDO<div class="v">{{ $setting->currency }} {{ number_format($summary['overdue'],2) }}</div></td>
    </tr></table>

    <table class="list">
        <thead><tr><th>Factura</th><th>Estudiante</th><th>Concepto</th><th>Monto</th><th>Vence</th><th>Estado</th></tr></thead>
        <tbody>
        @foreach($payments as $p)
            <tr>
                <td>{{ $p->invoice_number }}</td>
                <td>{{ optional($p->student)->full_name }}</td>
                <td>{{ $p->concept }}</td>
                <td>{{ $setting->currency }} {{ number_format($p->amount,2) }}</td>
                <td>{{ optional($p->due_date)->format('d/m/Y') ?? '—' }}</td>
                <td>{{ ucfirst($p->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
