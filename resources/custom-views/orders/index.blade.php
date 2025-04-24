@extends('custom_views::layouts.app')

@section('content')
    <div class="container">

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6 d-flex align-items-center">
                        <h5>Order List</h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('orders.index') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="{{ $startDate }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="{{ $endDate }}">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
                            @foreach ($orders as $order)
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
                    {{ $orders->appends([
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ])->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
