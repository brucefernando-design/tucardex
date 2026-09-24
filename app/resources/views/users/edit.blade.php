@extends('layouts.app')
@section('title', 'Editar usuario')

@section('content')
<div class="page-head"><div><h1>Editar usuario</h1><div class="breadcrumb-mini">Usuarios / {{ $user->name }}</div></div>
    <a href="{{ route('users.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos de la cuenta</span></div>
    <div class="card-body"><form action="{{ route('users.update', $user) }}" method="POST">@method('PUT')@include('users._form')</form></div></div>
@endsection
