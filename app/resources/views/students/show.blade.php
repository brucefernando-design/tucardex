@extends('layouts.app')
@section('title', 'Ficha del estudiante')

@section('content')
<div class="page-head"><div><h1>{{ $student->full_name }}</h1><div class="breadcrumb-mini">Estudiantes / Ficha</div></div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-danger btn-icon dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-pdf"></i> Documentos</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('students.boletin', $student) }}"><i class="bi bi-clipboard-data me-2"></i>Boleta de calificaciones</a></li>
                <li><a class="dropdown-item" href="{{ route('students.constancia', $student) }}"><i class="bi bi-file-text me-2"></i>Constancia de estudios</a></li>
                <li><a class="dropdown-item" href="{{ route('students.carnet', $student) }}"><i class="bi bi-person-vcard me-2"></i>Credencial escolar</a></li>
                <li><a class="dropdown-item" href="{{ route('students.estadoCuenta', $student) }}"><i class="bi bi-receipt me-2"></i>Estado de cuenta</a></li>
            </ul>
        </div>
        <a href="{{ route('students.edit', $student) }}" class="btn btn-brand btn-icon"><i class="bi bi-pencil"></i> Editar</a>
        <a href="{{ route('students.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
    </div></div>

<div class="grid-2">
    <div>
        <div class="card">
            <div class="card-header"><span class="title"><i class="bi bi-person-vcard"></i> Datos personales</span><span class="badge-soft badge-{{ $student->status }}">{{ ucfirst($student->status) }}</span></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($student->photo_url)<img src="{{ $student->photo_url }}" class="avatar-sm" style="width:64px;height:64px;object-fit:cover">@else<div class="avatar-sm" style="width:64px;height:64px;font-size:22px">{{ $student->initials }}</div>@endif
                    <div><h4 class="mb-0">{{ $student->full_name }}</h4><span class="text-muted">{{ $student->code }}</span></div>
                </div>
                <div class="row">
                    <div class="col-6 mb-2"><small class="text-muted d-block">CURP</small>{{ $student->curp ?? $student->dni ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Nacimiento</small>{{ optional($student->birth_date)->format('d/m/Y') ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Género</small>{{ $student->gender ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Grado y Grupo</small>{{ optional($student->course)->name ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Teléfono</small>{{ $student->phone ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Correo</small>{{ $student->email ?? '—' }}</div>
                    <div class="col-12 mb-2"><small class="text-muted d-block">Dirección</small>{{ $student->address ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Padre o Tutor</small>{{ $student->guardian_name ?? '—' }}</div>
                    <div class="col-6 mb-2"><small class="text-muted d-block">Tel. Padre o Tutor</small>{{ $student->guardian_phone ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-header"><span class="title"><i class="bi bi-cash-stack"></i> Pagos</span></div>
            <div class="card-body p-0">
                <table class="table mb-0"><tbody>
                @forelse($student->payments as $p)
                    <tr><td class="ps-3">{{ $p->concept }}</td><td>{{ $appSettings->currency ?? '$' }} {{ number_format($p->amount,2) }}</td><td><span class="badge-soft badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td></tr>
                @empty<tr><td class="empty-state">Sin pagos</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="title"><i class="bi bi-clipboard-data"></i> Últimas calificaciones</span></div>
            <div class="card-body p-0">
                <table class="table mb-0"><tbody>
                @forelse($student->grades->take(6) as $g)
                    <tr><td class="ps-3">{{ optional($g->subject)->name }}</td><td class="text-muted">{{ $g->period }}</td><td><strong>{{ $g->score }}</strong></td></tr>
                @empty<tr><td class="empty-state">Sin calificaciones</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="title"><i class="bi bi-clipboard-check"></i> Conducta</span>
                <span class="fw-bold {{ $student->incidents->sum('points')>=0 ? 'text-success':'text-danger' }}">{{ $student->incidents->sum('points')>0?'+':'' }}{{ $student->incidents->sum('points') }} pts</span>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0"><tbody>
                @forelse($student->incidents->take(6) as $i)
                    @php $c = ['merito'=>'badge-activo','demerito'=>'badge-vencido','observacion'=>'badge-justificado'][$i->type]; @endphp
                    <tr><td class="ps-3"><span class="badge-soft {{ $c }}">{{ ucfirst($i->type) }}</span></td><td class="small">{{ \Illuminate\Support\Str::limit($i->description,40) }}</td><td class="text-muted small">{{ $i->date->format('d/m') }}</td></tr>
                @empty<tr><td class="empty-state">Sin registros de conducta</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
