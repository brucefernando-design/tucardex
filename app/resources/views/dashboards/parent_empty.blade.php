@extends('layouts.app')
@section('title', 'Portal Familiar')

@section('content')
<div class="page-head">
    <div>
        <h1>Hola, {{ auth()->user()->name }}</h1>
        <div class="breadcrumb-mini">Portal de Padres / Tutores</div>
    </div>
</div>

<div class="card p-5 text-center my-4">
    <div style="font-size: 56px; color: #1abc9c;"><i class="bi bi-people"></i></div>
    <h3 class="mt-3">Sin alumnos vinculados</h3>
    <p class="text-muted mx-auto" style="max-width: 500px;">
        Tu cuenta de tutor no tiene ningún estudiante asociado actualmente en el colegio.
        Por favor ponte en contacto con la Dirección o Secretaría Escolar de tu plantel para que vinculen a tu hijo(a) con tu correo electrónico (<strong>{{ auth()->user()->email }}</strong>).
    </p>
    <div class="mt-3">
        <a href="{{ route('messages.index') }}" class="btn btn-brand btn-icon"><i class="bi bi-chat-dots"></i> Enviar Mensaje a Secretaría</a>
    </div>
</div>
@endsection
