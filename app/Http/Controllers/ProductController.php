<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    // --- PÚBLICO ---

    // 1. VER TODOS
    public function index(Request $request)
    {
        $query = Product::with(['user', 'category', 'images'])
            ->where('status', 'disponible');

        // Filtro por Categoría
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Búsqueda por texto (Título o Descripción)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->get());
    }   

    // 2. VER UNO (DETALLE)
    public function show($id)
    {
        $product = Product::with(['user', 'category', 'images'])->find($id); // Agregué images aquí
        
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    // --- PRIVADO (Requiere Token) ---

    // 3. CREAR PUBLICACIÓN
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'condition' => 'required|in:nuevo,como_nuevo,usado_bueno,para_reparar',
        ]);

        $product = $request->user()->products()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category_id' => $validated['category_id'],
            'condition' => $validated['condition'],
            'status' => 'disponible'
        ]);

        return response()->json(['message' => 'Producto publicado', 'product' => $product], 201);
    }

    // 4. EDITAR PUBLICACIÓN (NUEVO)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // VALIDACIÓN DE DUEÑO: Solo el creador puede editarlo
        if ($request->user()->id !== $product->user_id) {
            return response()->json(['message' => 'No tienes permiso para editar esto.'], 403);
        }

        // Validamos solo lo que envíen (a veces solo quieren cambiar el precio)
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'category_id' => 'exists:categories,id',
            'condition' => 'in:nuevo,como_nuevo,usado_bueno,para_reparar',
            'status' => 'in:disponible,reservado,vendido'
        ]);

        $product->update($validated);

        return response()->json(['message' => 'Producto actualizado', 'product' => $product]);
    }

    // 5. ELIMINAR PUBLICACIÓN (NUEVO)
    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // VALIDACIÓN DE DUEÑO
        if ($request->user()->id !== $product->user_id) {
            return response()->json(['message' => 'No tienes permiso para borrar esto.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}