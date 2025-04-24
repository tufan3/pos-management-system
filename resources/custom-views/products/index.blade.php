@extends('custom_views::layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div class="">
                <h2>Product Management</h2>
            </div>
            <div class="">
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5>All Products</h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('products.index') }}" class="form-inline float-right">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search products..."
                                    value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Image</th>
                                <th width="20%">Product Name</th>
                                <th width="10%">SKU</th>
                                <th width="10%">Unit</th>
                                <th width="10%">Price</th>
                                {{-- <th width="10%">Stock</th> --}}
                                <th width="10%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>

                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                style="width: 60px; height: 60px;">
                                                <i class="fas fa-box-open text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if ($product->variations->count() > 0)
                                            <br>
                                            <small class="text-muted">{{ $product->variations->count() }} variations</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->unit_value }} {{ $product->unit }}</td>
                                    <td>
                                        @if ($product->variations->count() > 0)
                                            <span class="text-success">Varied Prices</span>
                                        @else
                                        @if ($product->discount > 0)
                                        <span class="text-success">{{ number_format($product->selling_price - ($product->selling_price * $product->discount) / 100, 2) }} Tk</span>
                                                <br>
                                                <small class="text-danger">
                                                    <del>{{ number_format($product->selling_price, 2) }} Tk</del>
                                                </small>
                                            @else
                                                <small class="text-success">
                                                    {{ number_format($product->selling_price, 2) }} Tk
                                                </small>
                                            @endif
                                        @endif
                                    </td>
                                    {{-- <td>
                                        @if ($product->variations->count() > 0)
                                            {{ $product->variations->sum('stock_quantity') }} in stock
                                        @else
                                            N/A
                                        @endif
                                    </td> --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center ">
                                            <a href="{{ route('products.edit', $product->id) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {{-- <button type="button" class="btn btn-sm btn-danger"
                                                href="{{ route('products.destroy', $product->id) }}"
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </button> --}}

                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No products found</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
    </script>
@endpush

@push('styles')
@endpush
