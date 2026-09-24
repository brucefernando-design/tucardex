@extends('layouts.app')
@section('title', 'Entregas')

@section('content')
<div class="page-head">
    <div><h1>Entregas de tarea</h1><div class="breadcrumb-mini">{{ $assignment->title }} · {{ optional($assignment->course)->name }} "{{ optional($assignment->course)->section }}" · {{ optional($assignment->subject)->name }}</div></div>
    <a href="{{ route('assignments.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

@php
    $entregados = $submissions->count();
    $revisados = $submissions->where('status','revisado')->count();
@endphp
<div class="stats-row">
    <div class="stat-card bg-teal"><div class="label">Estudiantes</div><div class="value">{{ $students->count() }}</div><i class="bi bi-people icon"></i></div>
    <div class="stat-card bg-green"><div class="label">Entregaron</div><div class="value">{{ $entregados }}</div><i class="bi bi-check-circle icon"></i></div>
    <div class="stat-card bg-blue"><div class="label">Revisadas</div><div class="value">{{ $revisados }}</div><i class="bi bi-clipboard-check icon"></i></div>
    <div class="stat-card bg-orange"><div class="label">Pendientes</div><div class="value">{{ $students->count() - $entregados }}</div><i class="bi bi-hourglass-split icon"></i></div>
</div>

<div class="card"><div class="card-body p-0">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th class="ps-3">Estudiante</th><th>Estado</th><th>Entrega</th><th>Comentario</th><th width="320">Calificar</th></tr></thead>
        <tbody>
        @forelse($students as $s)
            @php $sub = $submissions->get($s->id); @endphp
            <tr>
                <td class="ps-3">{{ $s->full_name }}</td>
                <td>
                    @if(!$sub)<span class="badge-soft badge-vencido">Sin entregar</span>
                    @elseif($sub->status==='revisado')<span class="badge-soft badge-activo">Revisado</span>
                    @else<span class="badge-soft badge-pendiente">Entregado</span>@endif
                </td>
                <td class="small text-muted">
                    {{ optional(optional($sub)->submitted_at)->format('d/m/Y H:i') ?? '—' }}
                    @if($sub && $sub->file_url)<a href="{{ $sub->file_url }}" target="_blank" class="ms-1"><i class="bi bi-paperclip"></i> archivo</a>@endif
                </td>
                <td class="small">{{ \Illuminate\Support\Str::limit(optional($sub)->comment, 40) ?: '—' }}</td>
                <td>
                    @if($sub)
                        <form action="{{ route('submissions.review', $sub) }}" method="POST" class="d-flex gap-1 align-items-center">@csrf @method('PUT')
                            <input type="number" step="0.01" min="0" max="100" name="score" value="{{ $sub->score }}" class="form-control form-control-sm" style="width:80px" placeholder="Nota">
                            <input name="feedback" value="{{ $sub->feedback }}" class="form-control form-control-sm" placeholder="Observación">
                            <button class="btn btn-sm btn-brand"><i class="bi bi-check2"></i></button>
                        </form>
                    @else<span class="text-muted small">—</span>@endif
                </td>
            </tr>
        @empty<tr><td colspan="5"><div class="empty-state"><i class="bi bi-people"></i><p>Sin estudiantes en el curso</p></div></td></tr>@endforelse
        </tbody>
    </table></div>
</div></div>
@endsection
