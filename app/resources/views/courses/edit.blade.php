@extends('layouts.app')
@section('title', 'Editar curso')

@section('content')
<div class="page-head"><div><h1>Editar curso</h1><div class="breadcrumb-mini">Cursos / {{ $course->name }}</div></div>
    <a href="{{ route('courses.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos del curso</span></div>
    <div class="card-body"><form action="{{ route('courses.update', $course) }}" method="POST">@method('PUT')@include('courses._form')</form></div></div>
@endsection
