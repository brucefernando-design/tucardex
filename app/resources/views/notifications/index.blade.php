@extends('layouts.app')
@section('title', 'Notificaciones')

@section('content')
<div class="page-head">
    <div><h1>Notificaciones</h1><div class="breadcrumb-mini">Avisos y novedades para ti</div></div>
    <form action="{{ route('notifications.readAll') }}" method="POST">@csrf
        <button class="btn btn-outline-secondary btn-icon"><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
    </form>
</div>

<div class="card"><div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
        <tbody>
        @forelse($notifications as $n)
            <tr style="{{ $n->isUnread() ? 'background:#f1f8f4' : '' }}">
                <td class="ps-3" width="56">
                    <div class="mini-stat-ic" style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--brand-soft);color:var(--brand-3);font-size:18px"><i class="bi {{ $n->icon }}"></i></div>
                </td>
                <td>
                    <a href="{{ route('notifications.read', $n) }}" style="color:inherit">
                        <strong class="{{ $n->isUnread() ? '' : 'text-muted' }}">{{ $n->title }}</strong>
                        @if($n->isUnread())<span class="badge-soft badge-activo ms-1">Nuevo</span>@endif
                        <div class="small text-muted">{{ $n->body }}</div>
                    </a>
                </td>
                <td class="text-muted small text-end">{{ $n->created_at->diffForHumans() }}</td>
                <td class="text-end pe-3"><form action="{{ route('notifications.destroy', $n) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-x"></i></button></form></td>
            </tr>
        @empty<tr><td><div class="empty-state"><i class="bi bi-bell"></i><p>No tienes notificaciones</p></div></td></tr>@endforelse
        </tbody>
    </table>
</div></div>
{{ $notifications->links() }}
@endsection
