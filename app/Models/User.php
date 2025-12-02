<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Importante para la API

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'paypal_email',
    ];

    /**
     * Los atributos que deben ocultarse al devolver el usuario en JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversiones de tipos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELACIONES ---

    // Un usuario tiene muchos productos a la venta
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Un usuario tiene muchos favoritos
    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'favorites');
    }

    // Un usuario tiene items en su carrito
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Un usuario ha hecho muchas órdenes (compras)
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }
}