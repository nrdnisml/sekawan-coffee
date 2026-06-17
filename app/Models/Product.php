<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'image_url',
        'stock',
        'is_active',
    ];

    public function getImageUrlAttribute($value)
    {
        return $value ? asset('storage/' . $value) : asset('assets/img/placeholder-product.png');
    }

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
