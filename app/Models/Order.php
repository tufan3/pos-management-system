<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'grand_total',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    // public function products()
    // {
    //     return $this->hasManyThrough(Product::class, OrderItem::class);
    // }
}
