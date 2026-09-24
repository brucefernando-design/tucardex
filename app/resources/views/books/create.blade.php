@extends('layouts.app')
@section('title', 'Nuevo libro')

@section('content')
<div class="page-head"><div><h1>Nuevo libro</h1><div class="breadcrumb-mini">Biblioteca / Catálogo</div></div>
    <a href="{{ route('books.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-book"></i> Datos del libro</span></div>
    <div class="card-body"><form action="{{ route('books.store') }}" method="POST">@include('books._form')</form></div></div>
@endsection
