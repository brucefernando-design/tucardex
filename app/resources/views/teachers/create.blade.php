@extends('layouts.app')
@section('title', 'Nuevo docente')

@section('content')
<div class="page-head"><div><h1>Nuevo docente</h1><div class="breadcrumb-mini">Docentes / Registrar</div></div>
    <a href="{{ route('teachers.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent">
    <div class="card-header"><span class="title"><i class="bi bi-person-plus"></i> Datos del docente</span></div>
    <div class="card-body"><form action="{{ route('teachers.store') }}" method="POST">@include('teachers._form')</form></div>
</div>
@endsection
