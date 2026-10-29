@extends('admin.layouts.app')

@section('title', 'Create Return — #' . $order->order_number)

@section('content')
<form method="POST" action="{{ route('admin.returns.store', $order) }}">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3>Select items being returned</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Product</th><th>Price</th><th>Ordered</th><th>Already returned</th><th style="width:140px">Return qty</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php $left = $item->quantity - ($returned[$item->id] ?? 0); @endphp
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ taka($item->price) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $returned[$item->id] ?? 0 }}</td>
                                <td><input type="number" name="items[{{ $item->id }}]" min="0" max="{{ $left }}" value="{{ old('items.' . $item->id, 0) }}" class="form-control" @disabled($left <= 0)></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Return details</h3></div>
            <div class="card-body">
                <div class="form-group"><label>Reason</label>
                    <select name="reason" class="form-control">@foreach(\App\Models\OrderReturn::REASONS as $r)<option @selected(old('reason') === $r)>{{ $r }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Resolution</label>
                    <select name="resolution" class="form-control">@foreach(\App\Models\OrderReturn::RESOLUTIONS as $k => $l)<option value="{{ $k }}" @selected(old('resolution') === $k)>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label>Note</label><textarea name="customer_note" rows="3" class="form-control">{{ old('customer_note') }}</textarea></div>
                <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-rotate-left"></i> Create Return</button>
                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light" style="width:100%;justify-content:center;margin-top:8px">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
