@extends('layouts.app')
@section('title', 'Editar tarea')

@section('content')
<div class="page-head"><div><h1>Editar tarea</h1><div class="breadcrumb-mini">Tareas / {{ $assignment->title }}</div></div>
    <a href="{{ route('assignments.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos de la tarea</span></div>
    <div class="card-body"><form action="{{ route('assignments.update', $assignment) }}" method="POST">@method('PUT')@include('assignments._form')</form></div></div>
@endsection
