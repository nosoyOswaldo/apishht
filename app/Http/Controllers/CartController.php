<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Traemos los items del carrito con la info del producto
        return $request->user()->cartItems()->with('product.images')->get();
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $product = Product::find($request->product_id);

        if ($product->status !== 'disponible') {
            return response()->json(['message' => 'Producto no disponible'], 400);
        }
        
        if ($product->user_id === $request->user()->id) {
             return response()->json(['message' => 'No puedes comprar tu propio producto'], 400);
        }

        // Evitar duplicados en carrito
        $cartItem = $request->user()->cartItems()->firstOrCreate(
            ['product_id' => $request->product_id]
        );

        return response()->json(['message' => 'Agregado al carrito', 'item' => $cartItem]);
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->cartItems()->where('id', $id)->delete();
        return response()->json(['message' => 'Eliminado del carrito']);
    }
}