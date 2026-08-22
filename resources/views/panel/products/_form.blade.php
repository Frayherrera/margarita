@php $update = isset($product); @endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700" for="name">Nombre *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $product->name ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="inv_category_id">Categoría</label>
        <select id="inv_category_id" name="inv_category_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
            <option value="">— Sin categoría —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('inv_category_id', $product->inv_category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="unit">Unidad *</label>
        <select id="unit" name="unit" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
            @foreach ($units as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $product->unit ?? 'unidad') === $unit)>{{ ucfirst($unit) }}</option>
            @endforeach
        </select>
    </div>

    @unless ($update)
        <div>
            <label class="block text-sm font-medium text-gray-700" for="initial_stock">Stock inicial</label>
            <input id="initial_stock" name="initial_stock" type="number" step="0.01" min="0" value="{{ old('initial_stock', 0) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500">Se registra como movimiento «compra · inventario inicial».</p>
        </div>
    @endunless

    <div>
        <label class="block text-sm font-medium text-gray-700" for="min_stock">Stock mínimo (alerta)</label>
        <input id="min_stock" name="min_stock" type="number" step="0.01" min="0" value="{{ old('min_stock', $product->min_stock ?? 0) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="purchase_price">Precio de compra</label>
        <div class="relative mt-1">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">$</span>
            <input id="purchase_price" name="purchase_price" type="number" step="1" min="0" value="{{ old('purchase_price', $product->purchase_price ?? '') }}"
                   class="block w-full rounded-md border-gray-300 pl-6 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="sale_price">Precio de venta</label>
        <div class="relative mt-1">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">$</span>
            <input id="sale_price" name="sale_price" type="number" step="1" min="0" value="{{ old('sale_price', $product->sale_price ?? '') }}"
                   class="block w-full rounded-md border-gray-300 pl-6 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
        </div>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700" for="notes">Notas</label>
        <textarea id="notes" name="notes" rows="2"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">{{ old('notes', $product->notes ?? '') }}</textarea>
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input id="active" name="active" type="checkbox" value="1" @checked(old('active', $product->active ?? true))
               class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
        <label for="active" class="text-sm text-gray-700">Producto activo</label>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit"
            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
        {{ $update ? 'Guardar cambios' : 'Crear producto' }}
    </button>
    <a href="{{ route('panel.productos.index') }}"
       class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
</div>
