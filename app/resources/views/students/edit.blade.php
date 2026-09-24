@extends('layouts.app')
@section('title', 'Editar estudiante')

@section('content')
<div class="page-head"><div><h1>Editar estudiante</h1><div class="breadcrumb-mini">Estudiantes / {{ $student->full_name }}</div></div>
    <a href="{{ route('students.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>

<div class="card card-accent">
    <div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos del estudiante</span></div>
    <div class="card-body">
        <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data">@method('PUT')@include('students._form')</form>
    </div>
</div>
@endsection
