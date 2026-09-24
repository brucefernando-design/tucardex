@extends('layouts.app')
@section('title', 'Mensajes')

@section('content')
<div class="page-head">
    <div><h1>Mensajería</h1><div class="breadcrumb-mini">Bandeja de entrada</div></div>
    <a href="{{ route('messages.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-pencil-square"></i> Redactar</a>
</div>

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('messages.index') }}" class="btn {{ $box=='inbox' ? 'btn-brand':'btn-light' }} btn-icon"><i class="bi bi-inbox"></i> Recibidos</a>
    <a href="{{ route('messages.sent') }}" class="btn {{ $box=='sent' ? 'btn-brand':'btn-light' }} btn-icon"><i class="bi bi-send"></i> Enviados</a>
</div>

<div class="card"><div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
        <tbody>
        @forelse($messages as $m)
            <tr style="cursor:pointer" onclick="window.location='{{ route('messages.show', $m) }}'">
                <td class="ps-3" width="46"><span class="avatar-sm">{{ $m->sender->initials() }}</span></td>
                <td width="200"><strong>{{ $m->sender->name }}</strong><div class="small text-muted">{{ optional($m->sender->role)->name }}</div></td>
                <td>
                    @if($m->isUnread())<span class="badge-soft badge-activo me-1">Nuevo</span>@endif
                    <strong class="{{ $m->isUnread() ? '' : 'text-muted' }}">{{ $m->subject }}</strong>
                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($m->body, 60) }}</div>
                </td>
                <td class="text-muted small text-end pe-3">{{ $m->created_at->diffForHumans() }}</td>
            </tr>
        @empty<tr><td><div class="empty-state"><i class="bi bi-inbox"></i><p>No tienes mensajes</p></div></td></tr>@endforelse
        </tbody>
    </table>
</div></div>
{{ $messages->links() }}
@endsection
