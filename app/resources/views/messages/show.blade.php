@extends('layouts.app')
@section('title', 'Mensaje')

@section('content')
<div class="page-head">
    <div><h1>{{ $message->subject }}</h1><div class="breadcrumb-mini">Mensajería</div></div>
    <div class="d-flex gap-2">
        @if($message->recipient_id === auth()->id())
            <a href="{{ route('messages.create', ['reply_to'=>$message->id]) }}" class="btn btn-brand btn-icon"><i class="bi bi-reply"></i> Responder</a>
        @endif
        <a href="{{ url()->previous() }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="card" style="max-width:820px">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
            <span class="avatar-sm" style="width:48px;height:48px">{{ $message->sender->initials() }}</span>
            <div class="flex-grow-1">
                <strong>{{ $message->sender->name }}</strong> <span class="text-muted small">({{ optional($message->sender->role)->name }})</span>
                <div class="small text-muted">Para: {{ $message->recipient->name }} · {{ $message->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <form action="{{ route('messages.destroy', $message) }}" method="POST" onsubmit="return confirm('¿Eliminar mensaje?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
        </div>
        <div style="white-space:pre-line;line-height:1.8;font-size:14.5px">{{ $message->body }}</div>
    </div>
</div>
@endsection
