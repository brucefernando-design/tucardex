@extends('layouts.app')
@section('title', 'Reporte de asistencia')

@section('content')
<div class="page-head">
    <div><h1>Reporte mensual de asistencia</h1><div class="breadcrumb-mini">Resumen de asistencia por curso y mes</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('attendances.index') }}" class="btn btn-light btn-icon"><i class="bi bi-calendar2-check"></i> Registrar</a>
        @if($courseId)<a href="{{ route('attendances.report.pdf', ['course_id'=>$courseId,'month'=>$month]) }}" class="btn btn-danger btn-icon"><i class="bi bi-file-earmark-pdf"></i> PDF</a>@endif
    </div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 align-items-end">
        <div class="col-md-5"><label class="form-label small">Curso</label>
            <select name="course_id" class="form-select" onchange="this.form.submit()">
                @foreach($courses as $c)<option value="{{ $c->id }}" @selected($courseId==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
            </select></div>
        <div class="col-md-4"><label class="form-label small">Mes</label>
            <input type="month" name="month" value="{{ $month }}" class="form-control" onchange="this.form.submit()"></div>
    </form>
</div></div>

<div class="card">
    <div class="card-header"><span class="title"><i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::createFromFormat('Y-m',$month)->translatedFormat('F Y') }} · {{ $students->count() }} estudiantes</span>
        <span class="small text-muted">P=Presente · F=Falta · T=Tardanza · J=Justificado</span>
    </div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered align-middle mb-0" style="font-size:12px">
        <thead><tr>
            <th class="ps-2" style="position:sticky;left:0;background:#f7f9fb;min-width:180px">Estudiante</th>
            @foreach($days as $d)<th class="text-center px-1">{{ $d }}</th>@endforeach
            <th class="text-center bg-light">%</th>
        </tr></thead>
        <tbody>
        @forelse($students as $s)
            @php $sm = $summary[$s->id]; @endphp
            <tr>
                <td class="ps-2" style="position:sticky;left:0;background:#fff">{{ $s->full_name }}</td>
                @foreach($days as $d)
                    @php $m = $matrix[$s->id][$d] ?? ''; @endphp
                    <td class="text-center px-1 fw-bold
                        @if($m==='P') text-success @elseif($m==='F') text-danger @elseif($m==='T') text-warning @elseif($m==='J') text-info @endif">{{ $m ?: '·' }}</td>
                @endforeach
                <td class="text-center fw-bold {{ $sm['pct']>=80 ? 'text-success' : ($sm['pct']>=60 ? 'text-warning':'text-danger') }}">{{ $sm['pct'] }}%</td>
            </tr>
        @empty
            <tr><td colspan="{{ count($days)+2 }}"><div class="empty-state"><i class="bi bi-calendar-x"></i><p>Sin estudiantes o sin datos para este mes</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
