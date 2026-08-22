<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · MARGARITA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-crema font-sans text-tinta antialiased">
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
<header class="md:hidden sticky top-0 z-40 border-b border-caoba/60 bg-tinta text-white shadow-lg shadow-tinta/20">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="{{ route('panel.dashboard') }}" class="font-display text-lg leading-none tracking-wide text-ambar">MARGARITA</a>
        <form action="{{ route('logout') }}" method="POST" aria-label="Cerrar sesión" title="{{ auth()->user()->name }}">
            @csrf
            <button type="submit" class="rounded-md px-3 py-1.5 font-mono text-xs font-semibold uppercase tracking-wider text-stone-300 hover:bg-caoba hover:text-white">Salir ⏻</button>
        </form>
    </div>
    <nav class="flex gap-1 overflow-x-auto px-2 pb-2 [-ms-overflow-style:none] [scrollbar-width:none]" aria-label="Secciones">
        @foreach ($nav as $pattern => [$label, $url])
            <a href="{{ $url }}"
               class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-sm transition {{ request()->routeIs($pattern) ? 'bg-ambar font-bold text-tinta' : 'text-stone-300 hover:bg-caoba hover:text-white' }}">{{ $label }}</a>
        @endforeach
    </nav>
</header>

<div class="md:flex">
    {{-- Sidebar escritorio --}}
    <aside class="fixed inset-y-0 left-0 hidden w-60 flex-col bg-tinta text-stone-300 md:flex">
        <a href="{{ route('panel.dashboard') }}" class="border-b border-caoba/70 px-5 py-6">
            <p class="font-display text-xl leading-none tracking-wide text-ambar">MARGARITA</p>
            <p class="mt-2 font-mono text-[11px] uppercase tracking-widest text-stone-400">Inventario · Caja</p>
        </a>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4" aria-label="Secciones">
            @foreach ($nav as $pattern => [$label, $url])
                <a href="{{ $url }}"
                   class="relative block rounded-lg px-3 py-2.5 text-sm transition {{ request()->routeIs($pattern) ? 'bg-caoba font-semibold text-white' : 'hover:bg-caoba/50 hover:text-white' }}">
                    @if (request()->routeIs($pattern))
                        <span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r bg-ambar" aria-hidden="true"></span>
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-caoba/70 px-4 py-4 text-sm">
            <p class="truncate text-stone-300">{{ auth()->user()->name }}</p>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="font-mono text-xs font-semibold uppercase tracking-wider text-ladrillo transition hover:brightness-125">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <main class="min-w-0 flex-1 md:ml-60">
        <div class="mx-auto max-w-6xl px-4 py-6 md:py-10">
            @if (session('success'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-billete/30 bg-billete/10 px-4 py-3 text-sm font-medium text-billete" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 flex items-start gap-2 rounded-lg border border-ladrillo/30 bg-ladrillo/10 px-4 py-3 text-sm font-medium text-ladrillo" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-ladrillo/40 bg-ladrillo/10 px-4 py-3 text-sm text-ladrillo" role="alert">
                    <p class="font-semibold">Revisa estos datos:</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
