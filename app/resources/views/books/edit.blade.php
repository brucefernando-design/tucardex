@extends('layouts.app')
@section('title', 'Editar libro')

@section('content')
<div class="page-head"><div><h1>Editar libro</h1><div class="breadcrumb-mini">Biblioteca / {{ $book->title }}</div></div>
    <a href="{{ route('books.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a></div>
<div class="card card-accent"><div class="card-header"><span class="title"><i class="bi bi-pencil-square"></i> Datos del libro</span></div>
    <div class="card-body"><form action="{{ route('books.update', $book) }}" method="POST">@method('PUT')@include('books._form')</form></div></div>
@endsection
