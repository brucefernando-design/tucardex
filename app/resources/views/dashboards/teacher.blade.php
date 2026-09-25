@extends('layouts.app')
@section('title', 'Inicio')

@section('content')
@php
    $hour = (int) now()->format('H'); 
    $saludo = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches'); 
    $diasEsp = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $hoyNombre = $diasEsp[now()->dayOfWeekIso] ?? 'Lunes';
    $horarioHoy = $schedule->get($hoyNombre, collect());
@endphp

{{-- Hero Docente 2026 --}}
<div class="hero-compact">
    <div class="hero-deco-1"></div>
    <div class="hero-deco-2"></div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1">
        <div>
            <div style="font-size:12.5px;opacity:.88;text-transform:capitalize;letter-spacing:0.02em">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </div>
            <h1 style="font-size:22px;font-weight:800;margin:4px 0 2px;letter-spacing:-0.02em">
                {{ $saludo }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
            </h1>
            <div style="opacity:.85;font-size:13px">
                Portal Docente · {{ auth()->user()->school?->name ?? 'Colegio' }} · Ciclo Escolar {{ date('Y') }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark rounded-pill px-3 py-1 fw-bold" style="font-size:12px">
                <i class="bi bi-clock-history me-1 text-success"></i> {{ $assignments->sum('hours_per_week') }} hrs / semana
            </span>
        </div>
    </div>
</div>

@unless($teacher)
    <div class="alert alert-warning mb-4"><i class="bi bi-info-circle me-2"></i>Tu usuario no está vinculado a un perfil docente en el sistema. Solicita a la administración que enlace tu cuenta.</div>
@endunless

{{-- 3 Botones Grandes Táctiles para Docentes --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('attendances.index') }}" class="teacher-action-btn">
            <div class="btn-icon-wrap" style="background:#dcfce7;color:#16a34a">
                <i class="bi bi-calendar2-check-fill"></i>
            </div>
            <div>
                <strong style="color:var(--ink)">Pasar Lista</strong>
                <span>Registrar asistencia escolar del día</span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('grades.batch') }}" class="teacher-action-btn">
            <div class="btn-icon-wrap" style="background:#e0f2fe;color:#0284c7">
                <i class="bi bi-clipboard2-check-fill"></i>
            </div>
            <div>
                <strong style="color:var(--ink)">Capturar Calificaciones</strong>
                <span>Registro masivo por grupo y periodo</span>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('courses.index') }}" class="teacher-action-btn">
            <div class="btn-icon-wrap" style="background:#fef3c7;color:#d97706">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
            </div>
            <div>
                <strong style="color:var(--ink)">Boletas del Grupo</strong>
                <span>Sábanas oficiales y actas de notas</span>
            </div>
        </a>
    </div>
</div>

{{-- Grupos y Materias Asignadas --}}
<div class="row g-3 mb-4">
    {{-- Grupos a Cargo / Tutoría --}}
    <div class="col-lg-7">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-collection"></i> Mis grupos asignados
                </span>
                <span class="badge bg-light text-secondary border">{{ $tutoredCourses->count() }} a cargo</span>
            </div>
            <div class="card-body">
                @if($tutoredCourses->isEmpty())
                    <div class="empty-state py-4">
                        <i class="bi bi-mortarboard"></i>
                        <p class="mb-0">No tienes grupos asignados como tutor(a)</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($tutoredCourses as $c)
                            <div class="col-md-6">
                                <div class="p-3 rounded-4" style="background:#f8fafc;border:1px solid var(--line-light)">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong class="d-block text-dark" style="font-size:15px">
                                                {{ $c->name }} "{{ $c->section }}"
                                            </strong>
                                            <span class="badge bg-white text-secondary border rounded-pill" style="font-size:10.5px">
                                                Nivel {{ $c->level }}
                                            </span>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                            <i class="bi bi-people me-1"></i>{{ $c->students_count }} alumnos
                                        </span>
                                    </div>
                                    <div class="d-flex gap-1 flex-wrap mt-3 pt-2 border-top">
                                        <a href="{{ route('attendances.index', ['course_id' => $c->id]) }}" class="btn btn-sm btn-light border py-1 px-2" style="font-size:11.5px">
                                            <i class="bi bi-calendar-check text-success me-1"></i> Lista
                                        </a>
                                        <a href="{{ route('grades.batch', ['course_id' => $c->id]) }}" class="btn btn-sm btn-light border py-1 px-2" style="font-size:11.5px">
                                            <i class="bi bi-clipboard-data text-primary me-1"></i> Calificaciones
                                        </a>
                                        <a href="{{ route('courses.boletas_masivas', $c) }}" target="_blank" class="btn btn-sm btn-light border py-1 px-2" style="font-size:11.5px">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i> Boletas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Horario de Hoy Limpio --}}
    <div class="col-lg-5">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-clock-history"></i> Clases de hoy ({{ $hoyNombre }})
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $horarioHoy->count() }} clases</span>
            </div>
            <div class="card-body p-0">
                @if($horarioHoy->isEmpty())
                    <div class="empty-state py-4">
                        <div style="font-size:32px;margin-bottom:6px">☕</div>
                        <strong class="d-block text-dark" style="font-size:14px">Sin clases hoy</strong>
                        <span class="small text-muted">No tienes bloques de horario registrados para {{ $hoyNombre }}.</span>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($horarioHoy as $s)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-center px-2 py-1 rounded" style="background:#f1f5f9;border:1px solid var(--line-light);min-width:76px">
                                        <div class="fw-bold text-dark" style="font-size:12px">{{ \Illuminate\Support\Str::of($s->start_time)->substr(0,5) }}</div>
                                        <div class="text-muted" style="font-size:10.5px">{{ \Illuminate\Support\Str::of($s->end_time)->substr(0,5) }}</div>
                                    </div>
                                    <div>
                                        <strong class="d-block text-dark" style="font-size:13.5px">{{ optional($s->subject)->name ?? 'Materia' }}</strong>
                                        <span class="text-muted small">{{ optional($s->course)->name }} "{{ optional($s->course)->section }}"</span>
                                    </div>
                                </div>
                                <a href="{{ route('attendances.index', ['course_id' => $s->course_id]) }}" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size:11.5px">
                                    Pase de lista
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Materias que dicto + Comunicados --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-journal-bookmark"></i> Materias que imparto
                </span>
                <span class="badge bg-light text-secondary border">{{ $assignments->count() }} materias</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Materia</th>
                                <th>Grado y grupo</th>
                                <th class="text-end pe-3">Horas / semana</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $a)
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark" style="font-size:13px">{{ $a->subject }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $a->course }} "{{ $a->section }}"</span>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge-soft badge-activo fw-semibold">{{ $a->hours_per_week }} hrs</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state py-4">Sin materias asignadas</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <span class="title">
                    <i class="bi bi-megaphone"></i> Avisos escolares
                </span>
                <a href="{{ route('announcements.index') }}" class="small text-muted fw-semibold">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($announcements as $an)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark" style="font-size:13px">{{ $an->title }}</strong>
                                <small class="text-muted">{{ optional($an->created_at)->diffForHumans() }}</small>
                            </div>
                            <p class="small text-muted mb-0">{{ \Illuminate\Support\Str::limit($an->body, 90) }}</p>
                        </div>
                    @empty
                        <div class="empty-state py-4 text-muted">Sin comunicados escolares pendientes.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Horario Semanal Completo (Colapsable) --}}
<div class="accordion accordion-modern mb-4" id="accordionTeacherSchedule">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingTeacherSchedule">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeacherSchedule" aria-expanded="false" aria-controls="collapseTeacherSchedule">
                <i class="bi bi-calendar-week me-2 text-success"></i>
                <span>Ver horario escolar semanal completo</span>
            </button>
        </h2>
        <div id="collapseTeacherSchedule" class="accordion-collapse collapse" aria-labelledby="headingTeacherSchedule" data-bs-parent="#accordionTeacherSchedule">
            <div class="accordion-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle mb-0">
                        <thead>
                            <tr>
                                @foreach($days as $d)
                                    <th class="{{ $d === $hoyNombre ? 'bg-success text-white' : '' }}">{{ $d }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            @foreach($days as $d)
                                <td style="vertical-align:top;min-width:140px;background:{{ $d === $hoyNombre ? 'rgba(22, 163, 74, 0.04)' : '#fff' }}">
                                    @forelse($schedule->get($d, collect()) as $s)
                                        <div class="card mb-2 text-start" style="border-left:3px solid var(--brand);border-radius:10px">
                                            <div class="card-body p-2">
                                                <strong class="small d-block text-dark">{{ optional($s->subject)->name }}</strong>
                                                <span class="text-muted small" style="font-size:11px">
                                                    {{ \Illuminate\Support\Str::of($s->start_time)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($s->end_time)->substr(0,5) }}
                                                </span>
                                                <div class="small text-muted" style="font-size:11px">
                                                    {{ optional($s->course)->name }} "{{ optional($s->course)->section }}"
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted small">—</span>
                                    @endforelse
                                </td>
                            @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
