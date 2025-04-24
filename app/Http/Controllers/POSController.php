<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Str;

class POSController extends Controller
{
    public function index(Request $request)
    {
        // $search = $request->input('search');

        // $query = Product::with('variations')
        //     ->when($search, function($q) use ($search) {
        //         $q->where('name', 'like', "%{$search}%")
        //           ->orWhere('sku', 'like', "%{$search}%");
        //     });

        // $products = $query->paginate(10);
        return view('custom_views::pos.index');
    }

    public function getProducts(Request $request)
    {
        $search = $request->input('search');
        // $perPage = $request->input('per_page', 10);

        $query = Product::with('variations')
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });

        $products = $query->paginate(12);

        return response()->json($products);
    }


    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'items.*.product_name' => 'required|string',
            'items.*.variation_name' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.discount' => 'required|numeric',
            'items.*.tax' => 'required|numeric',
            'items.*.total_price' => 'required|numeric',

            'subtotal' => 'required|numeric',
            'discount' => 'required|numeric',
            'tax' => 'required|numeric',
            'grand_total' => 'required|numeric',
        ]);


        $items = [];
        $total = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);

            if ($item['product_variation_id']) {
                $variation = ProductVariation::find($item['product_variation_id']);
                $price = (float) $item['unit_price'];
            } else {
                $price = (float) $item['unit_price'];
            }

            $itemTotal = $price * $item['quantity'];
            $itemDiscount = (float) $item['discount'];
            $itemTax = (float) $item['tax'];

            $items[] = [
                'product_id' => $item['product_id'],
                'product_variation_id' => $item['product_variation_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'discount' => $itemDiscount,
                'tax' => $itemTax,
                'total_price' => $item['total_price'],
                'product_name' => $item['product_name'],
                'variation_name' => $item['variation_name'] ?? null,
            ];

            $total += $itemTotal;
            $discountTotal += $itemDiscount;
            $taxTotal += $itemTax;
        }


        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'total_amount' => $validated['subtotal'],
            'discount_amount' => $validated['discount'],
            'tax_amount' => $validated['tax'],
            'grand_total' => $validated['grand_total'],
        ]);

        foreach ($items as $item) {
            $order->items()->create($item);
        }



        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'message' => 'Order created successfully',
        ]);
    }

}
