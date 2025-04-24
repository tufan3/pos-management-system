<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'unit_value',
        'selling_price',
        'purchase_price',
        'discount',
        'tax',
        'image',
    ];

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }
}
