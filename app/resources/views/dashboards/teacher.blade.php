@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <div><h1>Bienvenido(a), {{ auth()->user()->name }}</h1><div class="breadcrumb-mini">Panel del docente · Ciclo escolar {{ date('Y') }}</div></div>
</div>

@unless($teacher)
    <div class="alert alert-warning"><i class="bi bi-info-circle me-2"></i>Tu usuario no está vinculado a un registro de docente. Pide al administrador que lo asocie.</div>
@endunless

<div class="stats-row">
    <div class="stat-card bg-teal"><div class="label">Cursos a cargo (tutor)</div><div class="value">{{ $tutoredCourses->count() }}</div><i class="bi bi-collection icon"></i></div>
    <div class="stat-card bg-green"><div class="label">Materias que dicto</div><div class="value">{{ $assignments->count() }}</div><i class="bi bi-journal-bookmark icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Mis estudiantes</div><div class="value">{{ $studentsCount }}</div><i class="bi bi-people icon"></i></div>
    <div class="stat-card bg-blue"><div class="label">Horas semanales</div><div class="value">{{ $assignments->sum('hours_per_week') }}</div><i class="bi bi-clock icon"></i></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-collection"></i> Mis cursos (tutoría)</span></div>
        <div class="card-body p-0">
            <table class="table mb-0"><thead><tr><th class="ps-3">Curso</th><th>Nivel</th><th>Estudiantes</th><th class="text-end pe-3"></th></tr></thead><tbody>
            @forelse($tutoredCourses as $c)
                <tr><td class="ps-3"><strong>{{ $c->name }} "{{ $c->section }}"</strong></td><td>{{ $c->level }}</td><td>{{ $c->students_count }}</td>
                    <td class="text-end pe-3"><a href="{{ route('attendances.index', ['course_id'=>$c->id]) }}" class="btn btn-sm btn-light">Asistencia</a></td></tr>
            @empty<tr><td colspan="4" class="empty-state">No eres tutor de ningún curso</td></tr>@endforelse
            </tbody></table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-journal-bookmark"></i> Materias que dicto</span></div>
        <div class="card-body p-0">
            <table class="table mb-0"><thead><tr><th class="ps-3">Materia</th><th>Curso</th><th>Hrs</th></tr></thead><tbody>
            @forelse($assignments as $a)
                <tr><td class="ps-3">{{ $a->subject }}</td><td>{{ $a->course }} "{{ $a->section }}"</td><td>{{ $a->hours_per_week }}</td></tr>
            @empty<tr><td colspan="3" class="empty-state">Sin materias asignadas</td></tr>@endforelse
            </tbody></table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="title"><i class="bi bi-clock-history"></i> Mi horario semanal</span></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered text-center align-middle mb-0">
        <thead><tr>@foreach($days as $d)<th>{{ $d }}</th>@endforeach</tr></thead>
        <tbody><tr>
        @foreach($days as $d)
            <td style="vertical-align:top;min-width:140px">
                @forelse($schedule->get($d, collect()) as $s)
                    <div class="card mb-2" style="border-left:3px solid var(--brand)"><div class="card-body p-2 text-start">
                        <strong class="small d-block">{{ optional($s->subject)->name }}</strong>
                        <span class="text-muted small">{{ \Illuminate\Support\Str::of($s->start_time)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($s->end_time)->substr(0,5) }}</span>
                        <div class="small text-muted">{{ optional($s->course)->name }} "{{ optional($s->course)->section }}"</div>
                    </div></div>
                @empty<span class="text-muted small">—</span>@endforelse
            </td>
        @endforeach
        </tr></tbody>
    </table></div></div>
</div>

<div class="card">
    <div class="card-header"><span class="title"><i class="bi bi-megaphone"></i> Comunicados</span></div>
    <div class="card-body">
        @forelse($announcements as $a)
            <div class="mini-stat border-bottom"><div class="ic bg-green"><i class="bi bi-megaphone"></i></div>
                <div><strong class="d-block">{{ $a->title }}</strong><span class="text-muted small">{{ \Illuminate\Support\Str::limit($a->body,80) }} · {{ $a->created_at->diffForHumans() }}</span></div></div>
        @empty<div class="empty-state"><i class="bi bi-inbox"></i><p>Sin comunicados</p></div>@endforelse
    </div>
</div>
@endsection
