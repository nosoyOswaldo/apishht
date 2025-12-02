<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar tablas antes de iniciar (para no duplicar si corres esto 2 veces)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Category::truncate();
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Crear USUARIO PRINCIPAL (Para tus pruebas de Login)
        $user = User::create([
            'name' => 'Eddie Munson',
            'email' => 'rock@shtt.com',
            'password' => Hash::make('password123'), // Contraseña fácil para pruebas
            'role' => 'user',
            'paypal_email' => 'eddie@hellfire.com'
        ]);
        
        $admin = User::create([
            'name' => 'Admin Rock',
            'email' => 'admin@shtt.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        // 3. Crear CATEGORÍAS
        $cats = [
            ['name' => 'Instrumentos', 'slug' => 'instrumentos', 'image_url' => 'assets/icons/guitar.png'],
            ['name' => 'Vinilos & Casetes', 'slug' => 'vinilos', 'image_url' => 'assets/icons/vinyl.png'],
            ['name' => 'Ropa & Merch', 'slug' => 'ropa', 'image_url' => 'assets/icons/tshirt.png'],
            ['name' => 'Audio & Equipo', 'slug' => 'audio', 'image_url' => 'assets/icons/amp.png'],
            ['name' => 'Memorabilia', 'slug' => 'memorabilia', 'image_url' => 'assets/icons/ticket.png'],
        ];

        foreach ($cats as $cat) {
            Category::create($cat);
        }

        // 4. Crear PRODUCTOS DE EJEMPLO (S.H.T.T.)
        $products = [
            [
                'title' => 'Fender Stratocaster 1998 (Algo golpeada)',
                'description' => 'Tiene historia. Un par de golpes en el cuerpo pero suena increíble. Pastillas originales.',
                'price' => 12500.00,
                'category_id' => 1, // Instrumentos
                'condition' => 'usado_bueno',
                'user_id' => $user->id
            ],
            [
                'title' => 'Vinilo Dark Side of the Moon (Original)',
                'description' => 'Primera edición, la portada está algo desgastada pero el disco no tiene rayones.',
                'price' => 850.50,
                'category_id' => 2, // Vinilos
                'condition' => 'usado_bueno',
                'user_id' => $user->id
            ],
            [
                'title' => 'Pedal de Distorsión Boss DS-1',
                'description' => 'Clásico pedal naranja. Funciona perfecto, solo le falta una perilla de goma.',
                'price' => 900.00,
                'category_id' => 4, // Audio
                'condition' => 'como_nuevo',
                'user_id' => $user->id
            ],
            [
                'title' => 'Chamarra de Cuero tipo Ramones',
                'description' => 'Talla M. Cuero real, huele a concierto de los 80s.',
                'price' => 2100.00,
                'category_id' => 3, // Ropa
                'condition' => 'usado_bueno',
                'user_id' => $user->id
            ]
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
        
        // Creamos más productos aleatorios para rellenar
        // Aquí simulamos un loop simple si no queremos usar Factories complejos
        for ($i = 0; $i < 5; $i++) {
            Product::create([
                'title' => 'Lote de revistas Rolling Stone #' . ($i + 100),
                'description' => 'Revistas viejas con entrevistas exclusivas.',
                'price' => 50.00 * ($i + 1),
                'category_id' => 5, // Memorabilia
                'condition' => 'usado_bueno',
                'user_id' => $admin->id
            ]);
        }
    }
}