@extends('layouts.app')
@section('title', 'Nueva materia')

@section('content')
<div class="page-head"><div><h1>Nueva materia</h1><div class="breadcrumb-mini">Materias / Registrar</div></div>
    <a href="{{ route('subjects.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-journal-plus"></i> Datos de la materia</span></div>
    <div class="card-body"><form action="{{ route('subjects.store') }}" method="POST">@include('subjects._form')</form></div></div>
@endsection
