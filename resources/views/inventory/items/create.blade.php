@extends('adminlte::page')

@section('title', 'Add Inventory Item')

@section('content_header')
<h1 class="m-0"><i class="fas fa-plus-circle mr-2 text-primary"></i>Add Inventory Item</h1>
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
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">Item Details</h3>
        </div>
        <form action="{{ route('inventory.items.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Item Code / SKU</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code') }}" placeholder="Optional">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">— Select Category —</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control @error('unit') is-invalid @enderror">
                                @foreach(['piece','box','ream','set','kg','litre','bottle','pack','pair','roll','meter'] as $u)
                                <option value="{{ $u }}" {{ old('unit','piece') == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Condition <span class="text-danger">*</span></label>
                            <select name="condition" class="form-control">
                                <option value="good" {{ old('condition','good') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Opening Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_in_stock" class="form-control @error('quantity_in_stock') is-invalid @enderror"
                                value="{{ old('quantity_in_stock', 0) }}" min="0" required>
                            @error('quantity_in_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Minimum Stock Level</label>
                            <input type="number" name="minimum_stock" class="form-control"
                                value="{{ old('minimum_stock', 0) }}" min="0">
                            <small class="text-muted">Alert when stock drops to/below this</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Unit Cost (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror"
                                value="{{ old('unit_cost', 0) }}" min="0" step="0.01" required>
                            @error('unit_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Storage Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}"
                                placeholder="e.g. Store Room A, Shelf 3">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="1">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Save Item
                </button>
                <a href="{{ route('inventory.items') }}" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>

</div>
</div>
</div>
@endsection
