<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;

class OrderController extends Controller
{
    public function index()
    {

        $orders = Order::with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('custom_views::orders.index', compact('orders'));
    }
}