@extends('layouts.app')
@section('title', 'Biblioteca · Préstamos')

@section('content')
<div class="page-head">
    <div><h1>Préstamos de libros</h1><div class="breadcrumb-mini">Control de préstamos y devoluciones</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-book"></i> Catálogo</a>
        <button class="btn btn-brand btn-icon" data-bs-toggle="modal" data-bs-target="#mLoan"><i class="bi bi-plus-lg"></i> Nuevo préstamo</button>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card bg-blue"><div class="label">Prestados</div><div class="value">{{ $summary['prestado'] }}</div><i class="bi bi-arrow-up-right-circle icon"></i></div>
    <div class="stat-card bg-red"><div class="label">Vencidos</div><div class="value">{{ $summary['vencido'] }}</div><i class="bi bi-exclamation-octagon icon"></i></div>
    <div class="stat-card bg-green"><div class="label">Devueltos</div><div class="value">{{ $summary['devuelto'] }}</div><i class="bi bi-check-circle icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Total movimientos</div><div class="value">{{ array_sum($summary) }}</div><i class="bi bi-arrow-left-right icon"></i></div>
</div>

<div class="card"><div class="card-body">
    <form class="row g-2 mb-3"><div class="col-md-3">
        <select name="status" class="form-select"><option value="">Todos</option>@foreach(['prestado','vencido','devuelto'] as $st)<option value="{{ $st }}" @selected(request('status')==$st)>{{ ucfirst($st) }}</option>@endforeach</select>
    </div><div class="col-md-2 d-grid"><button class="btn btn-outline-secondary btn-icon"><i class="bi bi-funnel"></i> Filtrar</button></div></form>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Libro</th><th>Prestatario</th><th>Préstamo</th><th>Devolución</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
        <tbody>
        @forelse($loans as $l)
            <tr>
                <td class="ps-3"><strong>{{ optional($l->book)->title }}</strong></td>
                <td>{{ $l->borrower_label }}</td>
                <td>{{ $l->loan_date->format('d/m/Y') }}<div class="small text-muted">Vence {{ $l->due_date->format('d/m/Y') }}</div></td>
                <td>{{ optional($l->return_date)->format('d/m/Y') ?? '—' }}</td>
                <td><span class="badge-soft badge-{{ $l->status }}">{{ ucfirst($l->status) }}</span></td>
                <td class="text-end pe-3">
                    @if($l->status !== 'devuelto')
                        <form action="{{ route('loans.return', $l) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-success btn-icon"><i class="bi bi-check2"></i> Devolver</button></form>
                    @endif
                    <form action="{{ route('loans.destroy', $l) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar préstamo?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-arrow-left-right"></i><p>Sin préstamos registrados</p></div></td></tr>@endforelse
        </tbody></table></div>
    {{ $loans->links() }}
</div></div>

<div class="modal fade" id="mLoan"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('loans.store') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title">Nuevo préstamo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label">Libro <span class="text-danger">*</span></label>
                <select name="book_id" class="form-select" required><option value="">Seleccione...</option>@foreach($books as $b)<option value="{{ $b->id }}">{{ $b->title }} ({{ $b->available }} disp.)</option>@endforeach</select></div>
            <div class="mb-2"><label class="form-label">Estudiante</label>
                <select name="student_id" class="form-select"><option value="">— Externo / otro —</option>@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->full_name }}</option>@endforeach</select></div>
            <div class="mb-2"><label class="form-label">Nombre del prestatario (si no es estudiante)</label><input name="borrower_name" class="form-control" placeholder="Opcional"></div>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">Fecha préstamo</label><input type="date" name="loan_date" value="{{ date('Y-m-d') }}" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Fecha devolución</label><input type="date" name="due_date" value="{{ now()->addDays(7)->format('Y-m-d') }}" class="form-control" required></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-brand">Registrar préstamo</button></div>
    </form>
</div></div></div>
@endsection
