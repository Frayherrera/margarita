@extends('layouts.panel')
@section('title', 'Nuevo producto')

@section('content')
<h1 class="text-2xl font-bold tracking-tight mb-6">Nuevo producto</h1>

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <form action="{{ route('panel.productos.store') }}" method="POST">
        @csrf
        @include('panel.products._form')
    </form>
</div>
@endsection
