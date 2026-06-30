@extends('adminlte::page')

@section('title', 'Edit Item')

@section('content_header')
<h1 class="m-0"><i class="fas fa-edit mr-2 text-warning"></i>Edit: {{ $item->name }}</h1>
@endsection

@section('content')
<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-8">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title">Edit Item Details</h3>
            <div class="card-tools">
                <span class="badge badge-info">Current Stock: {{ $item->quantity_in_stock }} {{ $item->unit }}</span>
            </div>
        </div>
        <form action="{{ route('inventory.items.update', $item) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $item->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Item Code / SKU</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code', $item->code) }}">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control">
                                @foreach(['piece','box','ream','set','kg','litre','bottle','pack','pair','roll','meter'] as $u)
                                <option value="{{ $u }}" {{ old('unit', $item->unit) == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Condition <span class="text-danger">*</span></label>
                            <select name="condition" class="form-control">
                                <option value="good"  {{ old('condition', $item->condition) == 'good'  ? 'selected' : '' }}>Good</option>
                                <option value="fair"  {{ old('condition', $item->condition) == 'fair'  ? 'selected' : '' }}>Fair</option>
                                <option value="poor"  {{ old('condition', $item->condition) == 'poor'  ? 'selected' : '' }}>Poor</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Minimum Stock Level</label>
                            <input type="number" name="minimum_stock" class="form-control"
                                value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Unit Cost (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_cost" class="form-control"
                                value="{{ old('unit_cost', $item->unit_cost) }}" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Storage Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $item->location) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $item->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $item->notes) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info py-2 px-3 mb-0" style="font-size:.85rem">
                    <i class="fas fa-info-circle mr-1"></i>
                    To change the stock quantity, use a <a href="{{ route('inventory.transactions.create', ['item'=>$item->id]) }}">transaction</a> (Purchase / Adjustment).
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save mr-1"></i>Update Item
                </button>
                <a href="{{ route('inventory.items') }}" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>

</div>
</div>
</div>
@endsection
