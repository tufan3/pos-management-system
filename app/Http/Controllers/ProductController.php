<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::withCount('variations')
            ->with('variations')
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('custom_views::products.index', compact('products'));
    }

    public function create()
    {
        return view('custom_views::products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'unit' => 'required|string',
            'unit_value' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations' => 'nullable|array',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->unit = $request->unit;
        $product->unit_value = $request->unit_value;
        $product->purchase_price = $request->purchase_price;
        $product->selling_price = $request->selling_price;
        $product->discount = $request->discount ?? 0;
        $product->tax = $request->tax ?? 0;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->save();

        if ($request->has('variations') && is_array($request->variations)) {
            foreach ($request->variations as $variation) {
                if (
                    !empty($variation['type']) &&
                    !empty($variation['value']) &&
                    isset($variation['purchase_price']) &&
                    isset($variation['selling_price'])
                ) {
                    $productVariation = new ProductVariation();
                    $productVariation->product_id = $product->id;
                    $productVariation->variation_type = $variation['type'];
                    $productVariation->variation_value = $variation['value'];
                    $productVariation->purchase_price = $variation['purchase_price'];
                    $productVariation->selling_price = $variation['selling_price'];
                    $productVariation->save();
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }



    public function edit(Product $product)
    {
        return view('custom_views::products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'unit' => 'required|string',
            'unit_value' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations' => 'nullable|array',
            'variations.*.variation_type' => 'required_with:variations|string',
            'variations.*.variation_value' => 'required_with:variations|string',
            'variations.*.purchase_price' => 'required_with:variations|numeric|min:0',
            'variations.*.selling_price' => 'required_with:variations|numeric|min:0',
            'variations.*.id' => 'nullable|exists:product_variations,id',
        ]);

        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->unit = $request->unit;
        $product->unit_value = $request->unit_value;
        $product->purchase_price = $request->purchase_price;
        $product->selling_price = $request->selling_price;
        $product->discount = $request->discount ?? 0;
        $product->tax = $request->tax ?? 0;

        if ($request->hasFile('image')) {
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->save();

        $existingVariationIds = [];
        if (!empty($request->variations)) {
            foreach ($request->variations as $variationData) {
                if (isset($variationData['id'])) {
                    $variation = ProductVariation::find($variationData['id']);
                    $variation->update([
                        'variation_type' => $variationData['variation_type'],
                        'variation_value' => $variationData['variation_value'],
                        'purchase_price' => $variationData['purchase_price'],
                        'selling_price' => $variationData['selling_price'],
                    ]);
                    $existingVariationIds[] = $variation->id;
                } else {
                    $variation = $product->variations()->create([
                        'variation_type' => $variationData['variation_type'],
                        'variation_value' => $variationData['variation_value'],
                        'purchase_price' => $variationData['purchase_price'],
                        'selling_price' => $variationData['selling_price'],
                    ]);
                    $existingVariationIds[] = $variation->id;
                }
            }
        }

        $product->variations()->whereNotIn('id', $existingVariationIds)->delete();

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->variations()->delete();
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
