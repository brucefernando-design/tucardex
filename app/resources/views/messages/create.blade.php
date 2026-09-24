@extends('layouts.app')
@section('title', 'Redactar mensaje')

@section('content')
<div class="page-head">
    <div><h1>Redactar mensaje</h1><div class="breadcrumb-mini">Mensajería interna</div></div>
    <a href="{{ route('messages.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card card-accent" style="max-width:760px">
    <div class="card-header"><span class="title"><i class="bi bi-envelope-paper"></i> Nuevo mensaje</span></div>
    <div class="card-body">
        <form action="{{ route('messages.store') }}" method="POST">@csrf
            <div class="mb-3">
                <label class="form-label">Para <span class="text-danger">*</span></label>
                <select name="recipient_id" class="form-select" required>
                    <option value="">Seleccione destinatario...</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('recipient_id', optional($reply)->sender_id)==$u->id)>{{ $u->name }} — {{ optional($u->role)->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Asunto <span class="text-danger">*</span></label>
                <input name="subject" value="{{ old('subject', $reply ? 'RE: '.$reply->subject : '') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control" rows="7" required>{{ old('body') }}</textarea>
            </div>
            <button class="btn btn-brand btn-icon"><i class="bi bi-send"></i> Enviar mensaje</button>
        </form>
    </div>
</div>
@endsection
