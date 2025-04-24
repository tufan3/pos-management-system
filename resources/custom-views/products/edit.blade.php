@extends('custom_views::layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div class="">
                <h2>Edit Product</h2>
            </div>
            <div class="">
                <a href="{{ route('products.index') }}" class="btn btn-primary">Back to Products</a>
            </div>
        </div>
        <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $product->name) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sku">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku"
                            value="{{ old('sku', $product->sku) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <select class="form-control" id="unit" name="unit" required>
                            <option value="kg" {{ old('unit', $product->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="liter" {{ old('unit', $product->unit) == 'liter' ? 'selected' : '' }}>liter
                            </option>
                            <option value="piece" {{ old('unit', $product->unit) == 'piece' ? 'selected' : '' }}>piece
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit_value">Unit Value</label>
                        <input type="text" class="form-control" id="unit_value" name="unit_value"
                            value="{{ old('unit_value', $product->unit_value) }}" required>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        <label for="purchase_price">Purchase Price</label>
                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price"
                            value="{{ old('purchase_price', $product->purchase_price) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="selling_price">Selling Price</label>
                        <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price"
                            value="{{ old('selling_price', $product->selling_price) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discount">Discount (%)</label>
                        <input type="number" step="0.01" class="form-control" id="discount" name="discount"
                            value="{{ old('discount', $product->discount) }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tax">Tax (%)</label>
                        <input type="number" step="0.01" class="form-control" id="tax" name="tax"
                            value="{{ old('tax', $product->tax) }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                        @if ($product->image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Current Image"
                                    style="max-height: 100px;">
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            @php
                $variations = old('variations', $product->variations->count() ? $product->variations : [['variation_type' => '', 'variation_value' => '', 'purchase_price' => '', 'selling_price' => '']]);
            @endphp

            <div class="variations-container mt-4">
                <h4>Product Variations</h4>
                <div id="variations">

                    @foreach ($variations as $index => $variation)
                    {{-- @foreach (old('variations', $product->variations) as $index => $variation) --}}
                        <div class="variation-item mb-3 p-3 border">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Type (e.g., Color, Size, Weight)</label>
                                        <input type="text" class="form-control"
                                            name="variations[{{ $index }}][variation_type]"
                                            value="{{ $variation['variation_type'] ?? $variation->variation_type }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Value (e.g., Red, L, 1KG)</label>
                                        <input type="text" class="form-control"
                                            name="variations[{{ $index }}][variation_value]"
                                            value="{{ $variation['variation_value'] ?? $variation->variation_value }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Purchase Price</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="variations[{{ $index }}][purchase_price]"
                                            value="{{ $variation['purchase_price'] ?? $variation->purchase_price }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Selling Price</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="variations[{{ $index }}][selling_price]"
                                            value="{{ $variation['selling_price'] ?? $variation->selling_price }}">
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex justify-content-center align-items-center">
                                    @if ($loop->first)
                                        <button type="button" id="add-variation" class="btn btn-secondary btn-sm mt-3">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger remove-variation">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if (isset($variation->id))
                                <input type="hidden" name="variations[{{ $index }}][id]"
                                    value="{{ $variation->id }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add variation button
            document.getElementById('add-variation').addEventListener('click', function() {
                const container = document.getElementById('variations');
                const index = container.children.length;

                const div = document.createElement('div');
                div.className = 'variation-item mb-3 p-3 border';
                div.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Type (e.g., Color, Size)</label>
                            <input type="text" class="form-control" name="variations[${index}][variation_type]">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Value (e.g., Red, L)</label>
                            <input type="text" class="form-control" name="variations[${index}][variation_value]">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" name="variations[${index}][purchase_price]">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Selling Price</label>
                            <input type="number" step="0.01" class="form-control" name="variations[${index}][selling_price]">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex justify-content-center align-items-center">
                        <button type="button" class="btn btn-sm btn-danger remove-variation">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

                container.appendChild(div);
            });

            // Event delegation for remove buttons
            document.getElementById('variations').addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('remove-variation') ||
                        e.target.closest('.remove-variation'))) {
                    const btn = e.target.classList.contains('remove-variation') ?
                        e.target : e.target.closest('.remove-variation');
                    btn.closest('.variation-item').remove();
                }
            });
        });
    </script>
@endsection
