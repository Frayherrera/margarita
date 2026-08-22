@extends('layouts.panel')
@section('title', 'Editar producto')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-1">Editar: {{ $product->name }}</h1>
<p class="mb-6 text-sm text-gray-500">
    Stock actual: <strong>{{ qty($product->current_stock) }} {{ $product->unit }}</strong>
    — modifícalo desde <a href="{{ route('panel.inventario.index') }}" class="text-emerald-600 hover:underline">Movimientos</a>.
</p>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <form action="{{ route('panel.productos.update', $product) }}" method="POST">
        @csrf
        @method('PUT')
        @include('panel.products._form')
    </form>
</div>
@endsection
