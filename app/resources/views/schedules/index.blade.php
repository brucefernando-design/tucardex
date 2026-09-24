@extends('layouts.app')
@section('title', 'Horarios')

@section('content')
<div class="page-head"><div><h1>Horarios</h1><div class="breadcrumb-mini">Carga horaria semanal por curso</div></div>
    <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mSched"><i class="bi bi-plus-lg"></i> Agregar bloque</button></div>

<div class="card"><div class="card-body">
    <form class="row g-2"><div class="col-md-4">
        <select name="course_id" class="form-select" onchange="this.form.submit()">
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected($courseId==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
        </select></div></form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered align-middle mb-0 text-center">
    <thead><tr>@foreach($days as $d)<th>{{ $d }}</th>@endforeach</tr></thead>
    <tbody><tr>
        @foreach($days as $d)
            <td style="vertical-align:top;min-width:150px">
                @forelse($schedules->get($d, collect()) as $s)
                    <div class="card mb-2" style="border-left:3px solid var(--brand)">
                        <div class="card-body p-2 text-start">
                            <strong class="small d-block">{{ optional($s->subject)->name }}</strong>
                            <span class="text-muted small">{{ \Illuminate\Support\Str::of($s->start_time)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($s->end_time)->substr(0,5) }}</span>
                            <div class="small text-muted">{{ optional($s->teacher)->full_name }}</div>
                            @if($s->classroom)<div class="small"><i class="bi bi-door-closed"></i> {{ $s->classroom }}</div>@endif
                            <form action="{{ route('schedules.destroy', $s) }}" method="POST" class="mt-1" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger py-0"><i class="bi bi-x"></i></button></form>
                        </div>
                    </div>
                @empty<span class="text-muted small">—</span>@endforelse
            </td>
        @endforeach
    </tr></tbody>
</table></div></div></div>

<div class="modal fade" id="mSched"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('schedules.store') }}" method="POST">@csrf
        <input type="hidden" name="course_id" value="{{ $courseId }}">
        <div class="modal-header"><h5 class="modal-title">Agregar bloque horario</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Materia</label><select name="subject_id" class="form-select" required>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-2"><label class="form-label">Docente</label><select name="teacher_id" class="form-select"><option value="">—</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach</select></div>
            <div class="row g-2">
                <div class="col-12"><label class="form-label">Día</label><select name="day_of_week" class="form-select">@foreach($days as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach</select></div>
                <div class="col-6"><label class="form-label">Hora inicio</label><input type="time" name="start_time" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Hora fin</label><input type="time" name="end_time" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Aula</label><input name="classroom" class="form-control" placeholder="Ej: Aula 12"></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand">Agregar</button></div>
    </form>
</div></div></div>
@endsection
