<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\InvCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = InvCategory::query()
            ->withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('panel.categories.index', compact('categories'));
    }

    public function store(CategoryRequest $request)
    {
        InvCategory::create(['name' => $request->name]);

        return redirect()->route('panel.categorias.index')->with('success', 'Categoría creada.');
    }

    public function edit(InvCategory $categoria)
    {
        return view('panel.categories.edit', ['category' => $categoria]);
    }

    public function update(CategoryRequest $request, InvCategory $categoria)
    {
        $categoria->update(['name' => $request->name]);

        return redirect()->route('panel.categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(InvCategory $categoria)
    {
        if ($categoria->products()->exists()) {
            return redirect()
                ->route('panel.categorias.index')
                ->with('error', "«{$categoria->name}» tiene productos asociados. No se puede eliminar.");
        }

        $categoria->delete();

        return redirect()->route('panel.categorias.index')->with('success', 'Categoría eliminada.');
    }
}
