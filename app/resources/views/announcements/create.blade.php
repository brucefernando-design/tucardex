@extends('layouts.app')
@section('title', 'Nuevo comunicado')

@section('content')
<div class="page-head"><div><h1>Nuevo comunicado</h1><div class="breadcrumb-mini">Comunicados / Crear</div></div>
    <a href="{{ route('announcements.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-megaphone"></i> Datos del comunicado</span></div>
    <div class="card-body"><form action="{{ route('announcements.store') }}" method="POST">@include('announcements._form')</form></div></div>
@endsection
