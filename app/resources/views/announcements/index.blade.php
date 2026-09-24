@extends('layouts.app')
@section('title', 'Comunicados')

@section('content')
<div class="page-head"><div><h1>Comunicados</h1><div class="breadcrumb-mini">Avisos y circulares de la comunidad educativa</div></div>
    <a href="{{ route('announcements.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-megaphone"></i> Nuevo comunicado</a></div>

<div class="row">
@forelse($announcements as $a)
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="mb-0">{{ $a->title }}</h5>
                    <span class="badge-soft badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
                </div>
                <p class="text-muted">{{ \Illuminate\Support\Str::limit($a->body, 160) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted"><i class="bi bi-person-circle me-1"></i>{{ optional($a->author)->name }} · <i class="bi bi-people me-1"></i>{{ ucfirst($a->audience) }} · {{ $a->created_at->diffForHumans() }}</span>
                    <div>
                        <form action="{{ route('announcements.resend', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Enviar este comunicado por correo a la audiencia seleccionada?')">@csrf<button class="btn btn-sm btn-light text-success" title="Enviar por correo"><i class="bi bi-envelope"></i></button></form>
                        <a href="{{ route('announcements.edit', $a) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('announcements.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12"><div class="card"><div class="card-body empty-state"><i class="bi bi-megaphone"></i><p>No hay comunicados publicados</p></div></div></div>
@endforelse
</div>
{{ $announcements->links() }}
@endsection
