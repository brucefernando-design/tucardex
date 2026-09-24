@extends('layouts.app')
@section('title', 'Promoción de año')

@section('content')
<div class="page-head">
    <div><h1>Promoción de año</h1><div class="breadcrumb-mini">Promueve estudiantes al siguiente grado o márcalos como egresados</div></div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-6">
            <label class="form-label small">Grado/Grupo de origen</label>
            <select name="source_course_id" class="form-select" onchange="this.form.submit()">
                <option value="">Seleccione un curso...</option>
                @foreach($courses as $c)<option value="{{ $c->id }}" @selected($sourceId==$c->id)>{{ $c->name }} "{{ $c->section }}" — {{ $c->students_count }} estud.</option>@endforeach
            </select>
        </div>
    </form>
</div></div>

@if($sourceId)
<form action="{{ route('promotions.store') }}" method="POST">@csrf
    <input type="hidden" name="source_course_id" value="{{ $sourceId }}">
    <div class="grid-2" style="grid-template-columns:1fr 360px">
        <div class="card">
            <div class="card-header">
                <span class="title"><i class="bi bi-people"></i> {{ $students->count() }} estudiantes activos</span>
                <label class="small"><input type="checkbox" id="checkAll" checked onclick="toggleAll(this)"> Seleccionar todos</label>
            </div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-3" width="50"></th><th>Estudiante</th><th>Código</th></tr></thead>
                <tbody>
                @forelse($students as $s)
                    <tr>
                        <td class="ps-3"><input type="checkbox" name="students[]" value="{{ $s->id }}" class="stu-check" checked></td>
                        <td>{{ $s->full_name }}</td>
                        <td class="text-muted">{{ $s->code }}</td>
                    </tr>
                @empty<tr><td colspan="3"><div class="empty-state"><i class="bi bi-people"></i><p>Sin estudiantes activos en este curso</p></div></td></tr>@endforelse
                </tbody>
            </table></div></div>
        </div>

        <div>
            <div class="card card-accent">
                <div class="card-header"><span class="title"><i class="bi bi-arrow-up-circle"></i> Acción</span></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">¿Qué deseas hacer?</label>
                        <select name="action" id="action" class="form-select" onchange="toggleTarget()">
                            <option value="promover">Promover al siguiente grado</option>
                            <option value="egresar">Marcar como egresados</option>
                        </select>
                    </div>
                    <div class="mb-3" id="targetWrap">
                        <label class="form-label">Grado/Grupo destino</label>
                        <select name="target_course_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($courses as $c)@if($c->id != $sourceId)<option value="{{ $c->id }}">{{ $c->name }} "{{ $c->section }}"</option>@endif @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ciclo Escolar destino</label>
                        <input name="academic_year" value="{{ $nextYear }}" class="form-control" required>
                    </div>
                    @error('students')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
                    @error('target_course_id')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
                    <button class="btn btn-brand btn-icon w-100" onclick="return confirm('¿Confirmas esta operación sobre los estudiantes seleccionados?')"><i class="bi bi-check-lg"></i> Aplicar</button>
                    <p class="text-muted small mt-3 mb-0"><i class="bi bi-info-circle"></i> Al promover se actualiza el grado del estudiante y se crea una nueva inscripción para el ciclo escolar destino.</p>
                </div>
            </div>
        </div>
    </div>
</form>
@else
    <div class="card"><div class="card-body empty-state"><i class="bi bi-mortarboard"></i><p>Selecciona un curso de origen para comenzar</p></div></div>
@endif
@endsection

@push('scripts')
<script>
function toggleAll(cb){ document.querySelectorAll('.stu-check').forEach(c=>c.checked=cb.checked); }
function toggleTarget(){ document.getElementById('targetWrap').style.display = document.getElementById('action').value==='promover' ? 'block':'none'; }
</script>
@endpush
