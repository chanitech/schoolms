@extends('adminlte::page')

@section('title', 'Request Stock')

@section('content_header')
    <h1 class="m-0 text-dark">Request Stock</h1>
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
            <p class="text-muted">This goes to the Procurement Officer, not straight to the Treasurer — they'll turn it into a formal purchase request if they agree it's needed.</p>

            <form action="{{ route('treasurer.stock-requests.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="inventory_item_id">Related Inventory Item (optional)</label>
                    <select name="inventory_item_id" id="inventory_item_id" class="form-control">
                        <option value="">— None / not yet in inventory —</option>
                        @foreach($inventoryItems as $item)
                            <option value="{{ $item->id }}" data-name="{{ $item->name }}">
                                {{ $item->name }} ({{ $item->category->name ?? 'Uncategorized' }}) — in stock: {{ $item->quantity_in_stock }}{{ $item->isLowStock() ? ' ⚠️ LOW' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="item">Item <span class="text-danger">*</span></label>
                    <input type="text" name="item" id="item" list="common-items" autocomplete="off"
                           placeholder="Start typing — pick a suggestion or write your own…"
                           class="form-control @error('item') is-invalid @enderror" value="{{ old('item') }}" required>
                    @include('partials.common-items-datalist')
                    @error('item') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                    @error('quantity') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="reason">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                    @error('reason') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit to Procurement Officer</button>
                <a href="{{ route('treasurer.stock-requests.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
{{-- Auto-fill the item name when a linked inventory item is selected --}}
<script>
document.getElementById('inventory_item_id')?.addEventListener('change', function () {
    const name = this.selectedOptions[0]?.dataset.name;
    const itemInput = document.getElementById('item');
    if (name && itemInput && !itemInput.value.trim()) itemInput.value = name;
});
</script>
@stop
