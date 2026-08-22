<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · Administración</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">
@php
    $nav = [
        'panel.dashboard' => ['Resumen', route('panel.dashboard')],
        'panel.productos.*' => ['Productos', route('panel.productos.index')],
        'panel.categorias.*' => ['Categorías', route('panel.categorias.index')],
        'panel.inventario.*' => ['Movimientos', route('panel.inventario.index')],
        'panel.caja.*' => ['Caja', route('panel.caja.index')],
    ];
@endphp

{{-- Nav móvil --}}
<div class="md:hidden sticky top-0 z-40 bg-slate-900 text-white">
    <div class="px-4 py-3 flex items-center justify-between">
        <a href="{{ route('panel.dashboard') }}" class="font-bold tracking-tight">🍺 Panel</a>
        <form action="{{ route('logout') }}" method="POST" title="{{ auth()->user()->name }}">
            @csrf
            <button type="submit" class="text-xs text-slate-300 hover:text-white underline">Salir</button>
        </form>
    </div>
    <nav class="px-2 pb-2 flex gap-1 overflow-x-auto">
        @foreach ($nav as $pattern => [$label, $url])
            <a href="{{ $url }}"
               class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm {{ request()->routeIs($pattern) ? 'bg-white text-slate-900 font-semibold' : 'text-slate-300 hover:bg-slate-800' }}">{{ $label }}</a>
        @endforeach
    </nav>
</div>

<div class="md:flex">
    {{-- Sidebar escritorio --}}
    <aside class="hidden md:flex md:flex-col fixed inset-y-0 left-0 w-60 bg-slate-900 text-slate-300">
        <a href="{{ route('panel.dashboard') }}" class="px-5 py-5 border-b border-slate-800">
            <p class="text-lg font-bold text-white tracking-tight leading-none">🍺 Mi Bar</p>
            <p class="mt-1 text-xs text-slate-400">Inventario y caja</p>
        </a>
        <nav class="flex-1 px-3 py-4 space-y-1">
            @foreach ($nav as $pattern => [$label, $url])
                <a href="{{ $url }}"
                   class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs($pattern) ? 'bg-slate-800 text-white font-semibold' : 'hover:bg-slate-800/60 hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="px-4 py-4 border-t border-slate-800 text-sm">
            <p class="truncate text-slate-400">{{ auth()->user()->name }}</p>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="text-red-300 hover:text-red-200">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <main class="flex-1 md:ml-60">
        <div class="max-w-6xl mx-auto px-4 py-6 md:py-10">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc pl-4 space-y-0.5">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
