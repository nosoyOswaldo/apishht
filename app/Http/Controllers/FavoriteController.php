<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // Ver mis favoritos
    public function index(Request $request)
    {
        // Devuelve los productos que el usuario marcó
        return $request->user()->favorites()->with(['images', 'category'])->get();
    }

    // Dar/Quitar Favorito (Toggle)
    public function toggle(Request $request, $productId)
    {
        $user = $request->user();
        
        // El método toggle agrega si no existe, y quita si ya existe
        $attached = $user->favorites()->toggle($productId);

        if (count($attached['attached']) > 0) {
            return response()->json(['message' => 'Añadido a favoritos', 'status' => 'added']);
        } else {
            return response()->json(['message' => 'Eliminado de favoritos', 'status' => 'removed']);
        }
    }
}