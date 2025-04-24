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
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center" id="pagination">
                                </ul>
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
                            <span id="subtotal">0.00 Tk</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Discount:</strong>
                        </div>
                        <div class="col-6 text-right">
                            <span id="discount">0.00 Tk</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Tax:</strong>
                        </div>
                        <div class="col-6 text-right">
                            <span id="tax">0.00 Tk</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <h5>Total:</h5>
                        </div>
                        <div class="col-6 text-right">
                            <h5 id="total">0.00 Tk</h5>
                        </div>
                    </div>
                    <button id="place-order" class="btn btn-primary btn-lg btn-block mt-3">Place Order</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Item-->
<div id="product-template" class="col-md-2 mb-2 d-none">
    <div class="card product-card">
        <img src="" class="card-img-top product-image img-fluid" alt="Product Image">
        <div class="card-body">
            <h6 class="card-title product-name"></h6>
            <p class="card-text product-sku text-muted small"></p>
            <div class="variations-container d-none">
                <select class="form-control form-control-sm variation-select mb-2">
                </select>
            </div>
            <div class="price-container">
                <p><span class="product-price font-weight-bold"></span>
                <span class="product-original-price text-muted small ml-2 text-decoration-line-through d-none"></span>
                <span class="product-discount badge badge-success ml-2 d-none"></span></p>
            </div>
            <div class="input-group mt-2">
                <input type="hidden" class="form-control form-control-sm quantity-input" value="1" min="1">
                <div class="input-group-append">
                    <button class="btn btn-primary btn-sm add-to-cart-btn" type="button">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Item  -->
<div id="cart-item-template" class="d-none">
    <div class="cart-item mb-2 border-bottom pb-2">
        <div class="row">
            <div class="col-6">
                <h6 class="cart-item-name"></h6>
                <small class="text-muted cart-item-variation"></small>
            </div>
            <div class="col-2">
                <input type="number" class="form-control form-control-sm cart-item-quantity" min="1" value="1">
            </div>
            <div class="col-2 text-right">
                <span class="cart-item-price"></span>
            </div>
            <div class="col-2 text-right">
                <button class="btn btn-sm btn-danger cart-item-remove">&times;</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let cart = [];

    function loadProducts(page = 1) {
        const search = $('#product-search').val();

        $.ajax({
            url: '/pos/products',
            method: 'GET',
            data: {
                search: search,
                page: page,
            },
            success: function(response) {
                renderProducts(response.data);
                renderPagination(response);
            }
        });
    }

    function renderProducts(products) {
        $('#product-list').empty();

        if (products.length === 0) {
            $('#product-list').html('<div class="col-12"><p class="text-muted">No products found</p></div>');
            return;
        }

        products.forEach(function(product) {
            const template = $('#product-template').clone();
            template.removeClass('d-none');
            template.removeAttr('id');

            template.find('.product-name').text(product.name);
            template.find('.product-sku').text(product.sku);

            if (product.image) {
                template.find('.product-image').attr('src', '/storage/' + product.image);
            } else {
                template.find('.product-image').attr('src', 'https://via.placeholder.com/150');
            }

            if (product.variations && product.variations.length > 0) {
                const variationsContainer = template.find('.variations-container');
                variationsContainer.removeClass('d-none');

                const select = variationsContainer.find('.variation-select');
                product.variations.forEach(function(variation) {
                    select.append(`<option value="${variation.id}"
                        data-purchase-price="${variation.purchase_price}"
                        data-selling-price="${variation.selling_price}">
                        ${variation.variation_type}: ${variation.variation_value}
                    </option>`);
                });

                select.on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    if (selectedOption.val()) {
                        const sellingPrice = parseFloat(selectedOption.data('selling-price'));
                        updateProductPrice(template, sellingPrice, product.discount);
                    }
                });

                const firstVariation = product.variations[0];
                updateProductPrice(template, firstVariation.selling_price, product.discount);
            } else {
                updateProductPrice(template, product.selling_price, product.discount);
            }

            template.find('.add-to-cart-btn').click(function() {
                const quantity = parseInt(template.find('.quantity-input').val());
                const variationSelect = template.find('.variation-select');
                let variationId = null;
                let variationText = '';
                let price = product.selling_price;

                if (variationSelect.length > 0 && variationSelect.val()) {
                    variationId = variationSelect.val();
                    variationText = variationSelect.find('option:selected').text();
                    price = parseFloat(variationSelect.find('option:selected').data('selling-price'));
                }

                addToCart({
                    product_id: product.id,
                    variation_id: variationId,
                    name: product.name,
                    variation: variationText,
                    price: price,
                    discount: product.discount,
                    tax: product.tax,
                    quantity: quantity
                });
            });

            $('#product-list').append(template);
        });
    }


    function updateProductPrice(element, price, discount) {
        price = parseFloat(price);
        discount = parseFloat(discount);

        const priceElement = element.find('.product-price');
        const originalPriceElement = element.find('.product-original-price');
        const discountElement = element.find('.product-discount');

        if (!isNaN(discount) && discount > 0) {
            const discountedPrice = price - (price * discount / 100);
            priceElement.text(discountedPrice.toFixed(2) + ' Tk');
            originalPriceElement.text(price.toFixed(2) + ' Tk').removeClass('d-none');
            discountElement.text(discount + '% off').removeClass('d-none');
        } else {
            priceElement.text(price.toFixed(2) + ' Tk');
            originalPriceElement.addClass('d-none');
            discountElement.addClass('d-none');
        }
    }


    function renderPagination(response) {
        const pagination = $('#pagination');
        pagination.empty();

        const currentPage = response.current_page;
        const lastPage = response.last_page;

        pagination.append(`<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`);

        for (let i = 1; i <= lastPage; i++) {
            pagination.append(`<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`);
        }

        pagination.append(`<li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`);

        $('.page-link').click(function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            loadProducts(page);
        });
    }

    function addToCart(item) {
        const existingItemIndex = cart.findIndex(cartItem =>
            cartItem.product_id === item.product_id && cartItem.variation_id === item.variation_id
        );

        if (existingItemIndex >= 0) {
            cart[existingItemIndex].quantity += item.quantity;
        } else {
            cart.push(item);
        }

        updateCart();
    }

    function updateCart() {
        const cartContainer = $('#cart-items');
        cartContainer.empty();

        if (cart.length === 0) {
            cartContainer.html('<p class="text-muted">No items added</p>');
            updateCartTotals();
            return;
        }

        let subtotal = 0;
        let totalDiscount = 0;
        let totalTax = 0;

        cart.forEach((item, index) => {
            const template = $('#cart-item-template').clone();
            template.removeClass('d-none');
            template.removeAttr('id');

            template.find('.cart-item-name').text(item.name);

            if (item.variation) {
                template.find('.cart-item-variation').text(item.variation);
            } else {
                template.find('.cart-item-variation').text('');
            }

            template.find('.cart-item-quantity').val(item.quantity);
            template.find('.cart-item-price').text((item.price * item.quantity).toFixed(2) + ' Tk');

            template.find('.cart-item-quantity').on('change', function() {
                const newQuantity = parseInt($(this).val());
                if (newQuantity > 0) {
                    cart[index].quantity = newQuantity;
                    updateCart();
                }
            });

            template.find('.cart-item-remove').click(function() {
                cart.splice(index, 1);
                updateCart();
            });

            cartContainer.append(template);

            const itemTotal = item.price * item.quantity;
            const itemDiscount = itemTotal * (item.discount / 100);
            const itemTax = (itemTotal - itemDiscount) * (item.tax / 100);

            subtotal += itemTotal;
            totalDiscount += itemDiscount;
            totalTax += itemTax;
        });

        updateCartTotals(subtotal, totalDiscount, totalTax);
    }

    function updateCartTotals(subtotal = 0, discount = 0, tax = 0) {
        const grandTotal = subtotal - discount + tax;

        $('#subtotal').text(subtotal.toFixed(2) + ' Tk');
        $('#discount').text(discount.toFixed(2) + ' Tk');
        $('#tax').text(tax.toFixed(2) + ' Tk');
        $('#total').text(grandTotal.toFixed(2) + ' Tk');
    }


    // Place order
    $('#place-order').click(function() {
        if (cart.length === 0) {
            alert('Please add items to cart before placing order');
            return;
        }

        let subtotal = $('#subtotal').text();
        let discount = $('#discount').text();
        let tax = $('#tax').text();
        let grandTotal = $('#total').text();

        subtotal = parseFloat(subtotal);
        discount = parseFloat(discount);
        tax = parseFloat(tax);
        grandTotal = parseFloat(grandTotal);

        const orderItems = cart.map(item => ({
            product_id: item.product_id,
            product_variation_id: item.variation_id,
            product_name: item.name,
            variation_name: item.variation,
            quantity: item.quantity,
            unit_price: item.price,
            total_price: item.price * item.quantity,
            discount: item.discount,
            tax: item.tax,
        }));


        $.ajax({
            url: '/pos/order',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                items: orderItems,
                subtotal: subtotal,
                discount: discount,
                tax: tax,
                grand_total: grandTotal,
                _token: '{{ csrf_token() }}'
            }),
            success: function(response) {
                if (response.success) {
                    toastr.success(`Order #${response.order_number} placed successfully!`);
                    cart = [];
                    updateCart();
                } else {
                    toastr.error('Failed to place order. Please try again.');
                }
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
            }
        });

    });

    $('#product-search').on('keyup', function() {
        loadProducts();
    });

    loadProducts();
});
</script>
@endpush

@push('styles')
<style>
    .product-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-image {
    object-fit: contain;
    height: 80px;
}
</style>
@endpush
@endsection
