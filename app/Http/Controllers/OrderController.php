<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Ver historial de compras
    public function index(Request $request)
    {
        return $request->user()->orders()->with('items')->latest()->get();
    }

    // CHECKOUT: Comprar lo que hay en el carrito
    public function store(Request $request)
    {
        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío'], 400);
        }

        // Usamos transacciones SQL: O se hace todo, o no se hace nada (seguridad de datos)
        return DB::transaction(function () use ($user, $cartItems, $request) {
            
            $total = 0;
            
            // 1. Calcular total y verificar disponibilidad final
            foreach ($cartItems as $item) {
                if ($item->product->status !== 'disponible') {
                    throw new \Exception("El producto '{$item->product->title}' ya fue vendido a otro usuario.");
                }
                $total += $item->product->price;
            }

            // 2. Crear la Orden
            $commissionRate = 0.10; // 10% de comisión para la plataforma
            
            $order = Order::create([
                'buyer_id' => $user->id,
                'total_amount' => $total,
                'commission_amount' => $total * $commissionRate,
                'status' => 'pagado', // Simulado
                'paypal_order_id' => $request->paypal_order_id ?? 'SIMULATED_'.uniqid()
            ]);

            // 3. Mover items de Carrito -> Orden
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'seller_id' => $item->product->user_id,
                    'product_title_snapshot' => $item->product->title,
                    'price_snapshot' => $item->product->price
                ]);

                // 4. Marcar producto como VENDIDO
                $item->product->update(['status' => 'vendido']);
            }

            // 5. Vaciar carrito
            $user->cartItems()->delete();

            return response()->json(['message' => 'Compra exitosa', 'order' => $order], 201);
        });
    }
}