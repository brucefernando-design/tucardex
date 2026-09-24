@extends('layouts.app')
@section('title', 'Morosos')

@section('content')
<div class="page-head">
    <div><h1>Reporte de morosos</h1><div class="breadcrumb-mini">Estudiantes con saldo pendiente o vencido</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('payments.defaulters', ['format'=>'pdf']) }}" class="btn btn-danger btn-icon"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('payments.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="stats-row">
    <div class="stat-card bg-red"><div class="label">Estudiantes con deuda</div><div class="value">{{ $rows->count() }}</div><i class="bi bi-exclamation-octagon icon"></i></div>
    <div class="stat-card bg-dark"><div class="label">Adeudo total</div><div class="value">{{ $appSettings->currency ?? '$' }} {{ number_format($grandTotal,0) }}</div><i class="bi bi-cash-stack icon"></i></div>
</div>

<div class="card"><div class="card-body p-0">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th class="ps-3">Estudiante</th><th>Grado/Grupo</th><th>Padre o Tutor</th><th>Meses</th><th>Deuda</th><th class="text-end pe-3"></th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            <tr>
                <td class="ps-3"><strong>{{ $r['student']->full_name }}</strong><div class="small text-muted">{{ $r['student']->code }}</div></td>
                <td>{{ optional($r['student']->course)->name }} "{{ optional($r['student']->course)->section }}"</td>
                <td>{{ $r['student']->guardian_name ?? '—' }}<div class="small text-muted">{{ $r['student']->guardian_phone }}</div></td>
                <td><span class="badge-soft badge-pendiente">{{ $r['count'] }}</span></td>
                <td><strong class="text-danger">{{ $appSettings->currency ?? '$' }} {{ number_format($r['total'],2) }}</strong></td>
                <td class="text-end pe-3"><a href="{{ route('students.estadoCuenta', $r['student']) }}" class="btn btn-sm btn-light"><i class="bi bi-receipt"></i> Estado</a></td>
            </tr>
        @empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-emoji-smile"></i><p>¡No hay morosos! Todos al día.</p></div></td></tr>@endforelse
        </tbody>
    </table></div>
</div></div>
@endsection
