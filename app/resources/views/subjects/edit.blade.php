@extends('layouts.app')
@section('title', 'Editar materia')

@section('content')
<div class="page-head"><div><h1>Editar materia</h1><div class="breadcrumb-mini">Materias / {{ $subject->name }}</div></div>
    <a href="{{ route('subjects.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos de la materia</span></div>
    <div class="card-body"><form action="{{ route('subjects.update', $subject) }}" method="POST">@method('PUT')@include('subjects._form')</form></div></div>
@endsection
