@extends('layouts.app')
@section('title', 'Nueva tarea')

@section('content')
<div class="page-head"><div><h1>Nueva tarea</h1><div class="breadcrumb-mini">Tareas / Crear</div></div>
    <a href="{{ route('assignments.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-journal-plus"></i> Datos de la tarea</span></div>
    <div class="card-body"><form action="{{ route('assignments.store') }}" method="POST">@include('assignments._form')</form></div></div>
@endsection
