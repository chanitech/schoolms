@extends('adminlte::page')

@section('title', 'New Transaction')

@section('content_header')
<h1 class="m-0"><i class="fas fa-exchange-alt mr-2 text-success"></i>Record Inventory Transaction</h1>
@endsection

@section('content')
<div class="container-fluid">
<div class="row justify-content-center">
<div class="col-lg-7">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">Transaction Details</h3>
        </div>
        <form action="{{ route('inventory.transactions.store') }}" method="POST">
            @csrf
            <div class="card-body">

                {{-- Type selector with colour cues --}}
                <div class="form-group">
                    <label>Transaction Type <span class="text-danger">*</span></label>
                    <div class="row" id="typeCards">
                        @foreach([
                            ['purchase',   'success', 'fas fa-cart-plus',       'Received / Bought',  'Increases stock'],
                            ['issue',      'warning', 'fas fa-sign-out-alt',     'Issue / Give Out',   'Decreases stock'],
                            ['return',     'info',    'fas fa-undo',             'Return',             'Increases stock'],
                            ['adjustment', 'secondary','fas fa-sliders-h',      'Adjustment',         'Increases stock'],
                            ['damage',     'danger',  'fas fa-exclamation-circle','Damage / Loss',     'Decreases stock'],
                            ['disposal',   'dark',    'fas fa-trash-alt',        'Disposal / Write-off','Decreases stock'],
                        ] as [$val, $color, $icon, $label, $hint])
                        <div class="col-6 col-md-4 mb-2">
                            <label class="type-card d-block border rounded p-2 text-center cursor-pointer {{ old('type', $selectedItem ? 'issue' : 'purchase') === $val ? 'border-'.$color.' bg-light' : '' }}"
                                style="cursor:pointer;transition:.15s">
                                <input type="radio" name="type" value="{{ $val }}" class="d-none type-radio"
                                    {{ old('type', $selectedItem ? 'issue' : 'purchase') === $val ? 'checked' : '' }}>
                                <i class="{{ $icon }} fa-lg text-{{ $color }} d-block mb-1"></i>
                                <strong style="font-size:.8rem">{{ $label }}</strong>
                                <small class="text-muted d-block" style="font-size:.7rem">{{ $hint }}</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label>Item <span class="text-danger">*</span></label>
                    <select name="item_id" id="item_id" class="form-control @error('item_id') is-invalid @enderror" required>
                        <option value="">— Select Item —</option>
                        @foreach($items as $it)
                        <option value="{{ $it->id }}"
                            data-stock="{{ $it->quantity_in_stock }}"
                            data-unit="{{ $it->unit }}"
                            {{ old('item_id', $selectedItem?->id) == $it->id ? 'selected' : '' }}>
                            {{ $it->name }} ({{ $it->category->name }}) — Stock: {{ $it->quantity_in_stock }} {{ $it->unit }}
                        </option>
                        @endforeach
                    </select>
                    <div id="stockInfo" class="text-muted mt-1" style="font-size:.83rem"></div>
                    @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                                value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control"
                                value="{{ old('transaction_date', now()->toDateString()) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Reference No</label>
                            <input type="text" name="reference_no" class="form-control"
                                value="{{ old('reference_no') }}" placeholder="LPO / Receipt no.">
                        </div>
                    </div>
                </div>

                <div class="form-group" id="issuedToGroup">
                    <label>Issued To <small class="text-muted">(for Issue transactions)</small></label>
                    <input type="text" name="issued_to" class="form-control" value="{{ old('issued_to') }}"
                        placeholder="Name of staff / department / class">
                </div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i>Save Transaction
                </button>
                <a href="{{ route('inventory.transactions') }}" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>

</div>
</div>
</div>

@push('js')
<script>
// Type card toggle
document.querySelectorAll('.type-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.type-card').forEach(c => {
            c.classList.remove('border-success','border-warning','border-info','border-secondary','border-danger','border-dark','bg-light');
        });
        const radio  = this.querySelector('.type-radio');
        radio.checked = true;
        const colors = {purchase:'success',issue:'warning',return:'info',adjustment:'secondary',damage:'danger',disposal:'dark'};
        this.classList.add('border-' + colors[radio.value], 'bg-light');

        // show/hide issued_to
        document.getElementById('issuedToGroup').style.display = radio.value === 'issue' ? '' : 'none';
    });
});

// Init issued_to visibility
(function () {
    const checked = document.querySelector('.type-radio:checked');
    if (checked && checked.value !== 'issue') {
        document.getElementById('issuedToGroup').style.display = 'none';
    }
})();

// Stock info
document.getElementById('item_id').addEventListener('change', function () {
    const opt  = this.options[this.selectedIndex];
    const info = document.getElementById('stockInfo');
    if (!opt.value) { info.textContent = ''; return; }
    const stock = opt.dataset.stock;
    const unit  = opt.dataset.unit;
    info.innerHTML = `<i class="fas fa-cubes mr-1"></i>Current stock: <strong>${stock} ${unit}</strong>`;
});
// Trigger on load if pre-selected
document.getElementById('item_id').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
