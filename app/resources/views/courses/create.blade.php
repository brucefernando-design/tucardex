@extends('layouts.app')
@section('title', 'Nuevo curso')

@section('content')
<div class="page-head"><div><h1>Nuevo curso</h1><div class="breadcrumb-mini">Cursos / Registrar</div></div>
    <a href="{{ route('courses.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-collection"></i> Datos del curso</span></div>
    <div class="card-body"><form action="{{ route('courses.store') }}" method="POST">@include('courses._form')</form></div></div>
@endsection
