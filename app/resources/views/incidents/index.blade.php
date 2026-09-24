@extends('layouts.app')
@section('title', 'Disciplina')

@section('content')
<div class="page-head">
    <div><h1>Disciplina y Conducta</h1><div class="breadcrumb-mini">Méritos, deméritos y observaciones del alumnado</div></div>
    <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mInc"><i class="bi bi-plus-lg"></i> Nuevo registro</button>
</div>

<div class="stats-row">
    <div class="stat-card bg-green"><div class="label">Méritos</div><div class="value">{{ $summary['merito'] }}</div><i class="bi bi-hand-thumbs-up icon"></i></div>
    <div class="stat-card bg-red"><div class="label">Deméritos</div><div class="value">{{ $summary['demerito'] }}</div><i class="bi bi-hand-thumbs-down icon"></i></div>
    <div class="stat-card bg-blue"><div class="label">Observaciones</div><div class="value">{{ $summary['observacion'] }}</div><i class="bi bi-eye icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Total registros</div><div class="value">{{ array_sum($summary) }}</div><i class="bi bi-journal-check icon"></i></div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-4"><select name="student_id" class="form-select"><option value="">Todos los estudiantes</option>@foreach($students as $s)<option value="{{ $s->id }}" @selected(request('student_id')==$s->id)>{{ $s->full_name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="type" class="form-select"><option value="">Todos los tipos</option>@foreach(['merito','demerito','observacion'] as $t)<option value="{{ $t }}" @selected(request('type')==$t)>{{ ucfirst($t) }}</option>@endforeach</select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Fecha</th><th>Estudiante</th><th>Tipo</th><th>Categoría</th><th>Descripción</th><th>Pts</th><th class="text-end pe-3"></th></tr></thead>
        <tbody>
        @forelse($incidents as $i)
            <tr>
                <td class="ps-3">{{ $i->date->format('d/m/Y') }}</td>
                <td>{{ optional($i->student)->full_name }}</td>
                <td>
                    @php $c = ['merito'=>'badge-activo','demerito'=>'badge-vencido','observacion'=>'badge-justificado'][$i->type]; @endphp
                    <span class="badge-soft {{ $c }}">{{ ucfirst($i->type) }}</span>
                </td>
                <td>{{ $i->category ?? '—' }}</td>
                <td class="small">{{ \Illuminate\Support\Str::limit($i->description, 70) }}</td>
                <td class="fw-bold {{ $i->points>0 ? 'text-success' : ($i->points<0 ? 'text-danger':'') }}">{{ $i->points>0 ? '+' : '' }}{{ $i->points }}</td>
                <td class="text-end pe-3"><form action="{{ route('incidents.destroy', $i) }}" method="POST" onsubmit="return confirm('¿Eliminar registro?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td>
            </tr>
        @empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-clipboard-check"></i><p>Sin registros de conducta</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $incidents->links() }}
</div></div>

<div class="modal fade" id="mInc"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('incidents.store') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title">Nuevo registro de conducta</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Estudiante <span class="text-danger">*</span></label>
                <select name="student_id" class="form-select" required><option value="">Seleccione...</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach</select></div>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">Tipo</label><select name="type" class="form-select"><option value="merito">Mérito (+)</option><option value="demerito">Demérito (−)</option><option value="observacion" selected>Observación</option></select></div>
                <div class="col-6"><label class="form-label">Fecha</label><input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Categoría</label><input name="category" class="form-control" placeholder="Ej: Puntualidad, Respeto"></div>
                <div class="col-6"><label class="form-label">Puntos (opcional)</label><input type="number" name="points" class="form-control" placeholder="Auto"></div>
                <div class="col-12"><label class="form-label">Descripción <span class="text-danger">*</span></label><textarea name="description" class="form-control" rows="3" required></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand">Guardar</button></div>
    </form>
</div></div></div>
@endsection
