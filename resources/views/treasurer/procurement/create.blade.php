@extends('adminlte::page')

@section('title', 'New Procurement Request')

@section('content_header')
    <h1 class="m-0 text-dark">New Procurement Request</h1>
@stop

@section('content')
<div class="container-fluid">
    @if($lowStockItems->count())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> <strong>Low stock:</strong>
        {{ $lowStockItems->pluck('name')->join(', ') }}
    </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            <form action="{{ route('treasurer.procurement.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="inventory_item_id">Related Inventory Item (optional)</label>
                    <select name="inventory_item_id" id="inventory_item_id" class="form-control">
                        <option value="">— None —</option>
                        @foreach($lowStockItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} (in stock: {{ $item->quantity_in_stock }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="item">Item <span class="text-danger">*</span></label>
                    <input type="text" name="item" id="item" class="form-control @error('item') is-invalid @enderror" value="{{ old('item') }}" required>
                    @error('item') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="quantity">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                        @error('quantity') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="estimated_cost">Estimated Cost (TZS) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="estimated_cost" id="estimated_cost" class="form-control @error('estimated_cost') is-invalid @enderror" value="{{ old('estimated_cost') }}" required>
                        @error('estimated_cost') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="supplier">Supplier</label>
                    <input type="text" name="supplier" id="supplier" class="form-control" value="{{ old('supplier') }}">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit for Treasurer Approval</button>
                <a href="{{ route('treasurer.procurement.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@stop
