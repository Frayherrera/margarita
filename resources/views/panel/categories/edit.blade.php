@extends('layouts.panel')
@section('title', 'Editar categoría')

@section('content')
<h1 class="mb-6 font-display text-2xl tracking-wide md:text-3xl">EDITAR CATEGORÍA</h1>

<div class="card max-w-md p-5 sm:p-6">
    <form action="{{ route('panel.categorias.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <label class="field-label" for="name">Nombre *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $category->name) }}" class="input">
        <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('panel.categorias.index') }}" class="btn-ghost">Cancelar</a>
            <button type="submit" class="btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
