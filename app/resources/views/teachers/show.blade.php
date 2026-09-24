@extends('layouts.app')
@section('title', 'Ficha del docente')

@section('content')
<div class="page-head"><div><h1>{{ $teacher->full_name }}</h1><div class="breadcrumb-mini">Docentes / Ficha</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-brand btn-icon"><i class="bi bi-pencil"></i> Editar</a>
        <a href="{{ route('teachers.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
    </div></div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-person-vcard"></i> Datos</span><span class="badge-soft badge-{{ $teacher->status }}">{{ ucfirst($teacher->status) }}</span></div>
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="avatar-sm" style="width:64px;height:64px;font-size:22px">{{ mb_substr($teacher->first_name,0,1) }}{{ mb_substr($teacher->last_name,0,1) }}</div>
                <div><h4 class="mb-0">{{ $teacher->full_name }}</h4><span class="text-muted">{{ $teacher->specialty ?? 'Docente' }}</span></div>
            </div>
            <div class="row">
                <div class="col-6 mb-2"><small class="text-muted d-block">Código</small>{{ $teacher->code }}</div>
                <div class="col-6 mb-2"><small class="text-muted d-block">CURP / INE</small>{{ $teacher->dni ?? '—' }}</div>
                <div class="col-6 mb-2"><small class="text-muted d-block">Correo</small>{{ $teacher->email ?? '—' }}</div>
                <div class="col-6 mb-2"><small class="text-muted d-block">Teléfono</small>{{ $teacher->phone ?? '—' }}</div>
                <div class="col-6 mb-2"><small class="text-muted d-block">Contratación</small>{{ optional($teacher->hire_date)->format('d/m/Y') ?? '—' }}</div>
                <div class="col-12 mb-2"><small class="text-muted d-block">Dirección</small>{{ $teacher->address ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-clock-history"></i> Carga horaria</span></div>
        <div class="card-body p-0">
            <table class="table mb-0"><thead><tr><th class="ps-3">Día</th><th>Materia</th><th>Curso</th><th>Hora</th></tr></thead><tbody>
            @forelse($teacher->schedules as $s)
                <tr><td class="ps-3">{{ $s->day_of_week }}</td><td>{{ optional($s->subject)->name }}</td><td>{{ optional($s->course)->name }}</td><td>{{ \Illuminate\Support\Str::of($s->start_time)->substr(0,5) }}</td></tr>
            @empty<tr><td colspan="4" class="empty-state">Sin horarios asignados</td></tr>@endforelse
            </tbody></table>
        </div>
    </div>
</div>
@endsection
