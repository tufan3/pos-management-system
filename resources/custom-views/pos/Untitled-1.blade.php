@extends('custom_views::layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Product List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Products</h4>
                    <div class="row">
                        <div class="">
                            <input type="text" class="form-control" id="product-search" placeholder="Search by name or SKU...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="product-list">
                        @foreach ($products as $product)
                                <div class="row row-cols-1 row-cols-md-6 g-2">
                                    <div class="col">
                                      <div class="card">
                                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top img-fluid" alt="{{ $product->name }}">
                                        <div class="card-body">
                                          <h6 class="card-title">{{ $product->name }} ({{ $product->sku }})</h6>
                                          {{-- <p class="card-text">
                                            @if ($product->discount > 0)
                                               <span class="text-muted text-decoration-line-through">{{ $product->selling_price }}</span>
                                               <span class="text-danger">{{ $product->discount }} BDT</span>
                                            @else
                                                <span class="text-muted">{{ $product->selling_price }} BDT</span>
                                            @endif
                                        </p> --}}

                                        @if ($product->variations->count() > 0)

                                        <p class="card-text" id="price-display-{{ $product->id }}">
                                            @if ($product->discount > 0)
                                                <span class="text-muted text-decoration-line-through"></span>
                                                <span class="text-danger"></span>
                                            @else
                                                <span class="text-muted"></span>
                                            @endif
                                        </p>

                                        @else
                                        <p class="card-text">
                                            @php
                                                $price = $product->selling_price;
                                                $discount = $product->discount;
                                                $finalPrice = $price - ($price * $discount / 100);
                                            @endphp
                                            @if ($product->discount > 0)
                                                <span class="text-muted text-decoration-line-through">{{ $price }} BDT</span>
                                                <span class="text-danger">{{ $finalPrice }} BDT</span>
                                            @else
                                                <span class="text-muted">{{ $finalPrice }} BDT</span>
                                            @endif
                                        </p>
                                        @endif

                                        <p class="card-text">
                                            @if ($product->variations->count() > 0)
                                            <select class="form-control variation-select" data-default-price="{{ $product->selling_price }}" data-discount="{{ $product->discount }}" data-product-id="{{ $product->id }}">
                                                @foreach ($product->variations as $variation)
                                                    <option
                                                        value="{{ $variation->id }}"
                                                        data-price="{{ $variation->selling_price }}"
                                                        data-discount="{{ $product->discount }}">
                                                        {{ $variation->variation_type }}: {{ $variation->variation_value }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @endif
                                        </p>
                                        <a href="" class="btn btn-primary">Add to Cart</a>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <nav aria-label="Page navigation">
                                {{ $products->links() }}
                                {{-- <ul class="pagination justify-content-center" id="pagination">
                                    <!-- Pagination will be loaded here -->
                                </ul> --}}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shopping Cart -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
                    <div id="cart-items">
                        <p class="text-muted">No items added</p>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Subtotal:</strong>
                        </div>
                        <div class="col-6 text-right">
                            <span id="subtotal">$0.00</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Discount:</strong>
                        </div>
                        <div class="col-6 text-right">
                            <span id="discount">$0.00</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Tax:</strong>
                        </div>
                        <div class="col-6 text-right">
                            <span id="tax">$0.00</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <h5>Total:</h5>
                        </div>
                        <div class="col-6 text-right">
                            <h5 id="total">$0.00</h5>
                        </div>
                    </div>
                    <button id="place-order" class="btn btn-primary btn-lg btn-block mt-3">Place Order</button>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    // $(document).ready(function() {
    //     $('#product-search').on('keyup', function() {
    //         let search = $(this).val();
    //         console.log(search);
    //         $.ajax({
    //             url: '{{ route('pos.index') }}',
    //             type: 'GET',
    //             data: {
    //                 search: search
    //             },
    //             success: function(response) {
    //                 // console.log(response);
    //             }
    //         });
    //     });
    // });

</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.variation-select').forEach(function (select) {
            const productId = select.dataset.productId;
            const priceDisplay = document.getElementById('price-display-' + productId);

            const updatePrice = () => {
                const selectedOption = select.options[select.selectedIndex];
                const price = parseFloat(selectedOption.dataset.price);
                const discount = parseFloat(selectedOption.dataset.discount);

                let finalPrice = price;
                let originalPrice = price;

                if (discount > 0) {
                    finalPrice = price - (price * discount / 100);
                    priceDisplay.innerHTML = `
                        <span class="text-muted text-decoration-line-through">${originalPrice.toFixed(2)} BDT</span>
                        <span class="text-danger">${finalPrice.toFixed(2)} BDT</span>
                    `;
                } else {
                    priceDisplay.innerHTML = `<span class="text-muted">${finalPrice.toFixed(2)} BDT</span>`;
                }
            };

            updatePrice();

            select.addEventListener('change', updatePrice);
        });
    });
</script>

@endpush

@endsection
