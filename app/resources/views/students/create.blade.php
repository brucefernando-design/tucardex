@extends('layouts.app')
@section('title', 'Nuevo estudiante')

@section('content')
<div class="page-head"><div><h1>Nuevo estudiante</h1><div class="breadcrumb-mini">Estudiantes / Registrar</div></div>
    <a href="{{ route('students.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>

<div class="card card-accent">
    <div class="card-header"><span class="title"><i class="bi bi-person-plus"></i> Datos del estudiante</span></div>
    <div class="card-body">
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">@include('students._form')</form>
    </div>
</div>
@endsection
