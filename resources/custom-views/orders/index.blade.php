@extends('custom_views::layouts.app')

@section('content')
<div class="container">
    <h1>Order List</h1>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Tax</th>
                            <th>Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>{{ number_format($order->total_amount, 2) }} TK</td>
                            <td>{{ number_format($order->discount_amount, 2) }} TK</td>
                            <td>{{ number_format($order->tax_amount, 2) }} TK</td>
                            <td>{{ number_format($order->grand_total, 2) }} TK</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center align-items-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection