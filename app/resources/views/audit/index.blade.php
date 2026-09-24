@extends('layouts.app')
@section('title', 'Bitácora')

@section('content')
<div class="page-head">
    <div><h1>Bitácora de auditoría</h1><div class="breadcrumb-mini">Registro de cambios sobre datos clave del colegio</div></div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="action" class="form-select"><option value="">Todas las acciones</option>
                @foreach(['creó','actualizó','eliminó'] as $a)<option value="{{ $a }}" @selected(request('action')==$a)>{{ ucfirst($a) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select"><option value="">Todas las entidades</option>
                @foreach($types as $t)<option value="{{ $t }}" @selected(request('type')==$t)>{{ \App\Models\AuditLog::LABELS[$t] ?? $t }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div>
    </form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Fecha</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Detalle</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="ps-3 small text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ optional($log->user)->name ?? 'Sistema' }}</td>
                <td>
                    @php $c = ['creó'=>'badge-activo','actualizó'=>'badge-justificado','eliminó'=>'badge-vencido'][$log->action] ?? 'badge-inactivo'; @endphp
                    <span class="badge-soft {{ $c }}">{{ ucfirst($log->action) }}</span>
                </td>
                <td>{{ $log->entity_label }}</td>
                <td class="small">{{ $log->description ?? '—' }}</td>
            </tr>
        @empty<tr><td colspan="5"><div class="empty-state"><i class="bi bi-clock-history"></i><p>Sin registros de auditoría todavía</p></div></td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $logs->links() }}
</div></div>
@endsection
