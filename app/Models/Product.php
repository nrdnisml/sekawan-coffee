<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

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
        return $value && Route::has('products.thumbnail')
            ? route('products.thumbnail', ['path' => $value])
            : 'data:image/svg+xml;base64,'.base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120" fill="none">'
                .'<rect width="120" height="120" rx="16" fill="#F4F4F5"/>'
                .'<path d="M38 82h44a6 6 0 0 0 6-6V44a6 6 0 0 0-6-6H38a6 6 0 0 0-6 6v32a6 6 0 0 0 6 6Z" stroke="#A1A1AA" stroke-width="4"/>'
                .'<circle cx="49" cy="52" r="6" fill="#A1A1AA"/>'
                .'<path d="m40 74 12-12 10 10 8-8 10 10" stroke="#A1A1AA" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>'
                .'</svg>'
            );
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
