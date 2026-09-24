@extends('layouts.app')
@section('title', 'Reportes')

@section('content')
<div class="page-head"><div><h1>Reportes</h1><div class="breadcrumb-mini">Indicadores generales del colegio</div></div></div>

<div class="stats-row">
    <div class="stat-card bg-teal"><div class="label">Total estudiantes</div><div class="value">{{ $totals['students'] }}</div><i class="bi bi-people icon"></i></div>
    <div class="stat-card bg-green"><div class="label">Estudiantes activos</div><div class="value">{{ $totals['active'] }}</div><i class="bi bi-person-check icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Ingresos cobrados</div><div class="value">{{ $appSettings->currency ?? 'Bs' }} {{ number_format($totals['income'],0) }}</div><i class="bi bi-cash icon"></i></div>
</div>

<div class="grid-2">
    <div class="card"><div class="card-header"><span class="title"><i class="bi bi-bar-chart"></i> Estudiantes por curso</span></div>
        <div class="card-body"><div class="chart-box"><canvas id="byCourse"></canvas></div></div></div>
    <div class="card"><div class="card-header"><span class="title"><i class="bi bi-pie-chart"></i> Estado de cobranzas</span></div>
        <div class="card-body"><div class="chart-box"><canvas id="byPay"></canvas></div></div></div>
</div>

<div class="card"><div class="card-header"><span class="title"><i class="bi bi-table"></i> Detalle por curso</span></div>
    <div class="card-body p-0"><table class="table mb-0"><thead><tr><th class="ps-3">Curso</th><th>Nivel</th><th>Estudiantes</th></tr></thead><tbody>
    @foreach($studentsByCourse as $c)<tr><td class="ps-3">{{ $c->name }} "{{ $c->section }}"</td><td>{{ $c->level }}</td><td>{{ $c->students_count }}</td></tr>@endforeach
    </tbody></table></div></div>
@endsection

@push('scripts')
<script>
const rOpts={responsive:true,maintainAspectRatio:false};
new Chart(document.getElementById('byCourse'),{type:'bar',
    data:{labels:@json($studentsByCourse->map(fn($c)=>$c->name.' '.$c->section)),datasets:[{label:'Estudiantes',data:@json($studentsByCourse->pluck('students_count')),backgroundColor:'#1abc9c',borderRadius:6,maxBarThickness:50}]},
    options:{...rOpts,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
new Chart(document.getElementById('byPay'),{type:'doughnut',
    data:{labels:['Pagado','Pendiente','Vencido'],datasets:[{data:[{{ $paymentsByStatus['pagado'] }},{{ $paymentsByStatus['pendiente'] }},{{ $paymentsByStatus['vencido'] }}],backgroundColor:['#2ecc71','#e67e22','#e74c3c']}]},
    options:{...rOpts,cutout:'62%',plugins:{legend:{position:'bottom'}}}});
</script>
@endpush
