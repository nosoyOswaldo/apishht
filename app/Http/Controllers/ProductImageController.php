<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <--- Usamos el cliente HTTP nativo

class ProductImageController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        if ($request->user()->id !== $product->user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        try {
            // 1. Preparamos la imagen en base64 (formato que pide ImgBB)
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->path()));

            // 2. Enviamos la petición directa a la API de ImgBB
            $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
                'key' => env('IMGBB_API_KEY'),
                'image' => $imageData,
                'name' => 'shtt_' . $productId . '_' . time() // Nombre opcional
            ]);

            // 3. Verificamos si ImgBB respondió bien
            if ($response->successful()) {
                // Obtenemos la URL directa de la respuesta JSON
                $url = $response->json()['data']['url'];

                // 4. Guardamos en BD
                $image = $product->images()->create([
                    'image_url' => $url,
                    'is_primary' => $product->images()->doesntExist()
                ]);

                return response()->json($image, 201);
            } else {
                // Si ImgBB falló, mostramos su error
                return response()->json([
                    'message' => 'Error de ImgBB', 
                    'details' => $response->json()
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $image = ProductImage::findOrFail($id);
        $product = $image->product;

        if ($request->user()->id !== $product->user_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Solo borramos de la base de datos (ImgBB es gratis y tiene espacio ilimitado)
        $image->delete();

        return response()->json(['message' => 'Imagen eliminada']);
    }
}