@extends('layouts.app')
@section('title', 'Asistencia')

@section('content')
<div class="page-head"><div><h1>Control de Asistencia</h1><div class="breadcrumb-mini">Registro diario por curso</div></div>
    <a href="{{ route('attendances.report') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-calendar3"></i> Reporte mensual</a></div>

<div class="card"><div class="card-body">
    <form class="row g-2 align-items-end">
        <div class="col-md-5"><label class="form-label small">Curso</label>
            <select name="course_id" class="form-select" onchange="this.form.submit()">
                @foreach($courses as $c)<option value="{{ $c->id }}" @selected($courseId==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
            </select></div>
        <div class="col-md-4"><label class="form-label small">Fecha</label><input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()"></div>
    </form>
</div></div>

<form action="{{ route('attendances.store') }}" method="POST">@csrf
    <input type="hidden" name="course_id" value="{{ $courseId }}">
    <input type="hidden" name="date" value="{{ $date }}">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="title"><i class="bi bi-calendar2-check"></i> Lista de estudiantes — {{ \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F Y') }}</span>
            <div class="d-flex align-items-center gap-3">
                @if($students->count())
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="notify_absents_whatsapp" id="notify_absents_whatsapp" value="1" checked>
                        <label class="form-check-label small fw-bold text-success" for="notify_absents_whatsapp">
                            <i class="bi bi-whatsapp"></i> Avisar inasistencias por WhatsApp
                        </label>
                    </div>
                    <button class="btn btn-brand btn-sm btn-icon"><i class="bi bi-save"></i> Guardar asistencia</button>
                @endif
            </div>
        </div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th class="ps-3">Estudiante</th><th width="420">Estado</th><th>Observación</th></tr></thead>
            <tbody>
            @forelse($students as $s)
                @php $cur = optional($existing->get($s->id))->status ?? 'presente'; @endphp
                <tr>
                    <td class="ps-3">
                        <span class="avatar-sm me-2">{{ mb_substr($s->first_name,0,1) }}{{ mb_substr($s->last_name,0,1) }}</span>
                        <strong>{{ $s->full_name }}</strong>
                        @if(optional($existing->get($s->id))->notified_at)
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-2" title="Aviso enviado el {{ optional($existing->get($s->id))->notified_at->format('d/m H:i') }}">
                                <i class="bi bi-whatsapp"></i> Avisado {{ optional($existing->get($s->id))->notified_at->format('H:i') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            @foreach(['presente'=>'success','ausente'=>'danger','tardanza'=>'warning','justificado'=>'info'] as $st=>$col)
                                <input type="radio" class="btn-check" name="status[{{ $s->id }}]" id="{{ $st }}{{ $s->id }}" value="{{ $st }}" @checked($cur==$st)>
                                <label class="btn btn-outline-{{ $col }}" for="{{ $st }}{{ $s->id }}">{{ ucfirst($st) }}</label>
                            @endforeach
                        </div>
                    </td>
                    <td><input name="remarks[{{ $s->id }}]" value="{{ optional($existing->get($s->id))->remarks }}" class="form-control form-control-sm" placeholder="—"></td>
                </tr>
            @empty<tr><td colspan="3"><div class="empty-state"><i class="bi bi-people"></i><p>Este curso no tiene estudiantes activos</p></div></td></tr>@endforelse
            </tbody>
        </table></div></div>
    </div>
</form>
@endsection
