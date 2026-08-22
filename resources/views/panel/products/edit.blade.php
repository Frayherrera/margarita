@extends('layouts.panel')
@section('title', 'Editar producto')

@section('content')
<h1 class="mb-1 font-display text-2xl tracking-wide md:text-3xl">EDITAR PRODUCTO</h1>
<p class="mb-6 text-sm text-stone-500">
    {{ $product->name }} · en bodega:
    <strong class="font-mono">{{ qty($product->current_stock) }} {{ $product->unit }}</strong>
    — el stock se ajusta desde <a href="{{ route('panel.inventario.index') }}" class="link-edit font-semibold">Movimientos</a>.
</p>

<div class="card max-w-3xl p-5 sm:p-6">
    <form action="{{ route('panel.productos.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        @include('panel.products._form')
    </form>
</div>
@endsection
