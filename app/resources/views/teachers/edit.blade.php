@extends('layouts.app')
@section('title', 'Editar docente')

@section('content')
<div class="page-head"><div><h1>Editar docente</h1><div class="breadcrumb-mini">Docentes / {{ $teacher->full_name }}</div></div>
    <a href="{{ route('teachers.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent">
    <div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos del docente</span></div>
    <div class="card-body"><form action="{{ route('teachers.update', $teacher) }}" method="POST">@method('PUT')@include('teachers._form')</form></div>
</div>
@endsection
