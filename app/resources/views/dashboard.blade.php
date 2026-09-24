@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php $hour = (int) now()->format('H'); $saludo = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches'); @endphp
<div style="position:relative;overflow:hidden;border-radius:var(--radius);padding:26px 28px;margin-bottom:22px;
            background:linear-gradient(120deg,#0f1f18,#15803d 120%);color:#fff;box-shadow:var(--shadow)">
    <div style="position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06)"></div>
    <div style="position:absolute;right:60px;bottom:-60px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.05)"></div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1">
        <div>
            <div style="font-size:13px;opacity:.85;text-transform:capitalize">{{ now()->translatedFormat('l, d \d\e F Y') }}</div>
            <h1 style="font-size:24px;font-weight:800;margin:6px 0 4px">{{ $saludo }}, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <div style="opacity:.9;font-size:13.5px">{{ $appSettings->school_name ?? 'Colegio' }} · Resumen del ciclo escolar {{ $appSettings->academic_year ?? date('Y') }} — {{ $appSettings->active_period ?? '' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('students.create') }}" class="btn btn-sm btn-icon" style="background:#fff;color:var(--brand-3);font-weight:600"><i class="bi bi-person-plus"></i> Nuevo estudiante</a>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-icon" style="background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-bar-chart"></i> Reportes</a>
        </div>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card bg-teal">
        <div class="label">Estudiantes</div>
        <div class="value">{{ number_format($stats['students']) }}</div>
        <i class="bi bi-people-fill icon"></i>
        <div class="foot"><span>Matriculados</span><a href="{{ route('students.index') }}" class="text-white">Ver detalle <i class="bi bi-arrow-right"></i></a></div>
    </div>
    <div class="stat-card bg-green">
        <div class="label">Docentes</div>
        <div class="value">{{ number_format($stats['teachers']) }}</div>
        <i class="bi bi-person-badge icon"></i>
        <div class="foot"><span>Activos</span><a href="{{ route('teachers.index') }}" class="text-white">Ver detalle <i class="bi bi-arrow-right"></i></a></div>
    </div>
    <div class="stat-card bg-red">
        <div class="label">Pagos pendientes</div>
        <div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($income['pending'], 0) }}</div>
        <i class="bi bi-exclamation-circle icon"></i>
        <div class="foot"><span>Por cobrar</span><a href="{{ route('payments.index', ['status'=>'pendiente']) }}" class="text-white">Ver detalle <i class="bi bi-arrow-right"></i></a></div>
    </div>
    <div class="stat-card bg-dark">
        <div class="label">Ingresos cobrados</div>
        <div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($income['paid'], 0) }}</div>
        <i class="bi bi-graph-up-arrow icon"></i>
        <div class="foot"><span>Ciclo escolar actual</span><a href="{{ route('reports.index') }}" class="text-white">Ver detalle <i class="bi bi-arrow-right"></i></a></div>
    </div>
</div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-graph-up"></i> Ingresos mensuales ({{ $appSettings->currency ?? '$' }})</span></div>
        <div class="card-body"><div class="chart-box"><canvas id="incomeChart"></canvas></div></div>
    </div>
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-gender-ambiguous"></i> Distribución por género</span></div>
        <div class="card-body"><div class="chart-box"><canvas id="genderChart"></canvas></div></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-bar-chart"></i> Estudiantes por nivel</span></div>
        <div class="card-body"><div class="chart-box"><canvas id="levelChart"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-megaphone"></i> Comunicados recientes</span><a href="{{ route('announcements.index') }}" class="small text-muted">Ver todos</a></div>
        <div class="card-body">
            @forelse($announcements as $a)
                <div class="mini-stat border-bottom">
                    <div class="ic bg-green"><i class="bi bi-megaphone"></i></div>
                    <div>
                        <strong class="d-block">{{ $a->title }}</strong>
                        <span class="text-muted small">{{ \Illuminate\Support\Str::limit($a->body, 60) }} · {{ $a->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state"><i class="bi bi-inbox"></i><p>Sin comunicados aún</p></div>
            @endforelse
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="title"><i class="bi bi-people"></i> Últimos estudiantes registrados</span><a href="{{ route('students.index') }}" class="small text-muted">Ver todos</a></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Código</th><th>Estudiante</th><th>Grado/Grupo</th><th>Padre o Tutor</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($recentStudents as $s)
                    <tr>
                        <td class="ps-3"><span class="text-muted">{{ $s->code }}</span></td>
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar-sm">{{ mb_substr($s->first_name,0,1) }}{{ mb_substr($s->last_name,0,1) }}</span> {{ $s->full_name }}</div></td>
                        <td>{{ optional($s->course)->name ?? '—' }}</td>
                        <td>{{ $s->guardian_name ?? '—' }}</td>
                        <td><span class="badge-soft badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">Aún no hay estudiantes registrados</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const incomeData = @json($monthlyIncome);
const levelData = @json($studentsByLevel);
const genderData = @json($genderDistribution);

const baseOpts = {responsive:true, maintainAspectRatio:false};
new Chart(document.getElementById('incomeChart'), {
    type:'line',
    data:{labels:Object.keys(incomeData),datasets:[{label:'Ingresos',data:Object.values(incomeData),
        borderColor:'#2ecc71',backgroundColor:'rgba(46,204,113,.15)',fill:true,tension:.35,pointBackgroundColor:'#27ae60'}]},
    options:{...baseOpts,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
});
new Chart(document.getElementById('genderChart'), {
    type:'doughnut',
    data:{labels:['Masculino','Femenino'],datasets:[{data:[genderData.M||0,genderData.F||0],backgroundColor:['#3498db','#e74c3c']}]},
    options:{...baseOpts,cutout:'62%',plugins:{legend:{position:'bottom'}}}
});
new Chart(document.getElementById('levelChart'), {
    type:'bar',
    data:{labels:Object.keys(levelData),datasets:[{label:'Estudiantes',data:Object.values(levelData),
        backgroundColor:['#1abc9c','#3498db','#9b59b6','#e67e22'],borderRadius:6,maxBarThickness:60}]},
    options:{...baseOpts,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
});
</script>
@endpush
