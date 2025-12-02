<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'commission_amount',
        'status',
        'paypal_order_id'
    ];

    // El comprador
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Los items comprados
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}