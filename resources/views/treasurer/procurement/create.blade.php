@extends('adminlte::page')

@section('title', 'New Procurement Request')

@section('content_header')
    <h1 class="m-0 text-dark">New Procurement Request</h1>
@stop

@section('content')
<div class="container-fluid">
    @if($stockRequest)
    <div class="alert alert-info">
        <i class="fas fa-people-arrows"></i>
        Converting <strong>{{ $stockRequest->requestedBy->name ?? 'a' }}</strong>'s stock request for
        <strong>{{ $stockRequest->quantity }} × {{ $stockRequest->item }}</strong> — fill in the cost and supplier below.
    </div>
    @endif

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
                @if($stockRequest)
                    <input type="hidden" name="stock_request_id" value="{{ $stockRequest->id }}">
                @endif

                <div class="form-group">
                    <label for="inventory_item_id">Related Inventory Item (optional)</label>
                    <select name="inventory_item_id" id="inventory_item_id" class="form-control">
                        <option value="">— None —</option>
                        @foreach($inventoryItems as $item)
                            <option value="{{ $item->id }}" {{ optional($stockRequest)->inventory_item_id === $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->category->name ?? 'Uncategorized' }}) — in stock: {{ $item->quantity_in_stock }}{{ $item->isLowStock() ? ' ⚠️ LOW' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if($inventoryItems->isEmpty())
                        <small class="form-text text-muted">
                            No inventory items exist yet. You don't need one to submit this request — but if you want to
                            link it to stock tracking, <a href="{{ url('inventory/items/create') }}" target="_blank">create an inventory item first <i class="fas fa-external-link-alt"></i></a>
                            (also found under the sidebar's <strong>Inventory → Items</strong> menu).
                        </small>
                    @else
                        <small class="form-text text-muted">
                            Don't see the item? <a href="{{ url('inventory/items/create') }}" target="_blank">Create a new inventory item <i class="fas fa-external-link-alt"></i></a>.
                        </small>
                    @endif
                </div>

                <div class="form-group">
                    <label for="item">Item <span class="text-danger">*</span></label>
                    <input type="text" name="item" id="item" class="form-control @error('item') is-invalid @enderror" value="{{ old('item', optional($stockRequest)->item) }}" required>
                    @error('item') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="quantity">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', optional($stockRequest)->quantity) }}" required>
                        @error('quantity') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label for="unit_cost">Unit Cost (TZS) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="unit_cost" id="unit_cost" class="form-control @error('unit_cost') is-invalid @enderror" value="{{ old('unit_cost') }}" required>
                        @error('unit_cost') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estimated Total (TZS)</label>
                        <div class="form-control-plaintext font-weight-bold" id="estimatedTotalLabel" style="font-size:1.1rem">—</div>
                        <small class="form-text text-muted">Quantity × Unit Cost — calculated automatically.</small>
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

@section('js')
<script>
    (function () {
        const qtyInput  = document.getElementById('quantity');
        const costInput = document.getElementById('unit_cost');
        const totalLabel = document.getElementById('estimatedTotalLabel');

        function updateTotal() {
            const qty  = parseFloat(qtyInput.value);
            const cost = parseFloat(costInput.value);
            if (!isNaN(qty) && !isNaN(cost) && qty > 0 && cost > 0) {
                totalLabel.textContent = (qty * cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                totalLabel.textContent = '—';
            }
        }

        qtyInput.addEventListener('input', updateTotal);
        costInput.addEventListener('input', updateTotal);
        updateTotal();
    })();
</script>
@stop
