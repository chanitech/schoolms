@extends('adminlte::page')

@section('title', 'Create Budget')

@section('content_header')
<h1 class="mb-3"><i class="fas fa-file-invoice-dollar"></i> Submit Budget</h1>
@stop

@section('content')
@if($errors->any())
<div class="alert alert-danger">
    <ul>
    @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif

<form action="{{ route('finance.budgets.store') }}" method="POST">
    @csrf
    <div class="card shadow">
        <div class="card-body">

            {{-- Department, Month, Year --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Department</label>
                    <select name="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                {{ $dep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Month</label>
                    <select name="month" class="form-control" required>
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                            <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ old('year', date('Y')) }}" required>
                </div>
            </div>

            {{-- Note --}}
            <div class="mb-3">
                <label>Note</label>
                <textarea name="note" class="form-control">{{ old('note') }}</textarea>
            </div>

            {{-- Budget Items --}}
            <h5 class="mt-4">Budget Items</h5>
            <table class="table table-bordered" id="budget-items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Price (TZS)</th>
                        <th>
                            <button type="button" class="btn btn-success btn-sm" id="add-item">+</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $oldItems = old('items', [['item'=>'','description'=>'','price'=>'']]);
                    @endphp
                    @foreach($oldItems as $i => $item)
                    <tr>
                        <td>
                            <input type="text" name="items[{{ $i }}][item]" class="form-control item-name" value="{{ $item['item'] }}" required>
                        </td>
                        <td>
                            <input type="text" name="items[{{ $i }}][description]" class="form-control" value="{{ $item['description'] }}">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $i }}][price]" class="form-control item-price" step="0.01" value="{{ $item['price'] }}" required>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-item">x</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Total --}}
            <div class="text-right mb-3">
                <strong>Total: TZS <span id="total">0</span></strong>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary">Submit Budget</button>
        </div>
    </div>
</form>
@stop

@section('js')
<script>
let itemIndex = {{ count($oldItems) }};

// Add new item row
document.getElementById('add-item').addEventListener('click', function() {
    const tbody = document.querySelector('#budget-items-table tbody');
    const row = `<tr>
        <td><input type="text" name="items[${itemIndex}][item]" class="form-control item-name" required></td>
        <td><input type="text" name="items[${itemIndex}][description]" class="form-control"></td>
        <td><input type="number" name="items[${itemIndex}][price]" class="form-control item-price" step="0.01" required></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item">x</button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
    itemIndex++;
    calculateTotal();
});

// Remove item row
document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('remove-item')) {
        e.target.closest('tr').remove();
        calculateTotal();
    }
});

// Calculate total dynamically
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.item-price').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('total').innerText = total.toFixed(2);
}

// Recalculate total on input change
document.addEventListener('input', function(e){
    if(e.target && e.target.classList.contains('item-price')) {
        calculateTotal();
    }
});

// Initial total calculation
calculateTotal();
</script>
@stop
