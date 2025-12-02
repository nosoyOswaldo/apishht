<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'condition',
        'status'
    ];

    // Pertenece a un vendedor (Usuario)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Tiene muchas imágenes
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Usuarios que le dieron like a este producto
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}