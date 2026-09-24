@extends('layouts.app')
@section('title', 'Editar comunicado')

@section('content')
<div class="page-head"><div><h1>Editar comunicado</h1><div class="breadcrumb-mini">Comunicados / {{ $announcement->title }}</div></div>
    <a href="{{ route('announcements.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos del comunicado</span></div>
    <div class="card-body"><form action="{{ route('announcements.update', $announcement) }}" method="POST">@method('PUT')@include('announcements._form')</form></div></div>
@endsection
