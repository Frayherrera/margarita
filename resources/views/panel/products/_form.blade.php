@php $update = isset($product); @endphp

<div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="field-label" for="name">Nombre *</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $product->name ?? '') }}" class="input">
    </div>

    <div>
        <label class="field-label" for="inv_category_id">Categoría</label>
        <select id="inv_category_id" name="inv_category_id" class="input">
            <option value="">— Sin categoría —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('inv_category_id', $product->inv_category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="field-label" for="unit">Unidad *</label>
        <select id="unit" name="unit" required class="input capitalize">
            @foreach ($units as $unit)
                <option value="{{ $unit }}" @selected(old('unit', $product->unit ?? 'unidad') === $unit)>{{ ucfirst($unit) }}</option>
            @endforeach
        </select>
    </div>

    @unless ($update)
        <div>
            <label class="field-label" for="initial_stock">Stock inicial</label>
            <input id="initial_stock" name="initial_stock" type="number" step="0.01" min="0" inputmode="decimal" value="{{ old('initial_stock', 0) }}" class="input font-mono">
            <p class="mt-1 text-xs text-stone-400">Queda registrado como compra «Inventario inicial».</p>
        </div>
    @endunless

    <div>
        <label class="field-label" for="min_stock">Stock mínimo (alerta)</label>
        <input id="min_stock" name="min_stock" type="number" step="0.01" min="0" inputmode="decimal" value="{{ old('min_stock', $product->min_stock ?? 0) }}" class="input font-mono">
    </div>

    <div>
        <label class="field-label" for="purchase_price">Precio de compra</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 pt-1.5 font-mono text-sm text-stone-400">$</span>
            <input id="purchase_price" name="purchase_price" type="number" step="1" min="0" inputmode="numeric" value="{{ old('purchase_price', $product->purchase_price ?? '') }}" class="input pl-7 font-mono">
        </div>
    </div>

    <div>
        <label class="field-label" for="sale_price">Precio de venta</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 pt-1.5 font-mono text-sm text-stone-400">$</span>
            <input id="sale_price" name="sale_price" type="number" step="1" min="0" inputmode="numeric" value="{{ old('sale_price', $product->sale_price ?? '') }}" class="input pl-7 font-mono">
        </div>
    </div>

    <div class="sm:col-span-2">
        <label class="field-label" for="notes">Notas</label>
        <textarea id="notes" name="notes" rows="2" class="input">{{ old('notes', $product->notes ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-2.5 sm:col-span-2">
        <input id="active" name="active" type="checkbox" value="1" @checked(old('active', $product->active ?? true))
               class="h-4 w-4 rounded border-stone-300 text-ambar focus:ring-ambar">
        <label for="active" class="text-sm">Producto activo — aparece en el catálogo y admite movimientos</label>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('panel.productos.index') }}" class="btn-ghost">Cancelar</a>
    <button type="submit" class="btn-primary">{{ $update ? 'Guardar cambios' : 'Crear producto' }}</button>
</div>
