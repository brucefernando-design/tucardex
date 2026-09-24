@extends('layouts.app')
@section('title', 'Captura de Calificaciones')

@section('content')
<div class="page-head">
    <div><h1>Planilla de calificaciones</h1><div class="breadcrumb-mini">Registra las calificaciones de todo un grupo de una sola vez</div></div>
    <a href="{{ route('grades.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small">Curso</label>
            <select name="course_id" class="form-select" onchange="this.form.submit()">
                <option value="">Seleccione...</option>
                @foreach($courses as $c)<option value="{{ $c->id }}" @selected($courseId==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
            </select></div>
        <div class="col-md-3"><label class="form-label small">Materia</label>
            <select name="subject_id" class="form-select" onchange="this.form.submit()">
                <option value="">Seleccione...</option>
                @foreach($subjects as $s)<option value="{{ $s->id }}" @selected($subjectId==$s->id)>{{ $s->name }}</option>@endforeach
            </select></div>
        <div class="col-md-3"><label class="form-label small">Período</label>
            <select name="period" class="form-select" onchange="this.form.submit()">
                @foreach($periods as $p)<option value="{{ $p }}" @selected($period==$p)>{{ $p }}</option>@endforeach
            </select></div>
        <div class="col-md-3"><label class="form-label small">Tipo</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
                @foreach($types as $t)<option value="{{ $t }}" @selected($type==$t)>{{ ucfirst($t) }}</option>@endforeach
            </select></div>
    </form>
</div></div>

@if($courseId && $subjectId)
    <form action="{{ route('grades.batchStore') }}" method="POST">@csrf
        <input type="hidden" name="course_id" value="{{ $courseId }}">
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="card">
            <div class="card-header">
                <span class="title"><i class="bi bi-pencil-square"></i> {{ $students->count() }} estudiantes · {{ $period }} · {{ ucfirst($type) }}</span>
                @if($students->count())<button class="btn btn-brand btn-sm btn-icon"><i class="bi bi-save"></i> Guardar todas</button>@endif
            </div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-3" width="60">#</th><th>Estudiante</th><th width="200">Nota (0-100)</th><th>Situación</th></tr></thead>
                <tbody>
                @forelse($students as $i => $s)
                    @php $val = optional($existing->get($s->id))->score; @endphp
                    <tr>
                        <td class="ps-3 text-muted">{{ $i+1 }}</td>
                        <td><span class="avatar-sm me-2">{{ mb_substr($s->first_name,0,1) }}{{ mb_substr($s->last_name,0,1) }}</span>{{ $s->full_name }}</td>
                        <td><input type="number" step="0.01" min="5" max="10" step="0.1" name="scores[{{ $s->id }}]" value="{{ $val !== null ? rtrim(rtrim(number_format($val,2,'.',''),'0'),'.') : '' }}" class="form-control form-control-sm score-input" placeholder="5.0 - 10.0" style="max-width:120px"></td>
                        <td><span class="sit text-muted">{{ $val !== null ? ($val>=6.0?'Aprobado':'Reprobado') : '' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-people"></i><p>Este curso no tiene estudiantes activos</p></div></td></tr>
                @endforelse
                </tbody>
            </table></div></div>
        </div>
    </form>
@else
    <div class="card"><div class="card-body empty-state"><i class="bi bi-clipboard-data"></i><p>Selecciona un curso y una materia para cargar la planilla</p></div></div>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.score-input').forEach(inp=>{
    inp.addEventListener('input',function(){
        const sit=this.closest('tr').querySelector('.sit');
        const v=parseFloat(this.value);
        if(isNaN(v)){sit.textContent='';sit.className='sit text-muted';return;}
        const ok=v>=6.0;
        sit.textContent=ok?'Aprobado':'Reprobado';
        sit.className='sit '+(ok?'text-success':'text-danger');
    });
});
</script>
@endpush
