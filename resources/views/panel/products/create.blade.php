@extends('layouts.panel')
@section('title', 'Nuevo producto')

@section('content')
<h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">NUEVO PRODUCTO</h1>
<p class="mb-6 text-sm text-stone-500">Agrega una referencia al catálogo de la bodega.</p>

<div class="card max-w-3xl p-5 sm:p-6">
    <form action="{{ route('panel.productos.store') }}" method="POST">
        @csrf
        @include('panel.products._form')
    </form>
</div>
@endsection
