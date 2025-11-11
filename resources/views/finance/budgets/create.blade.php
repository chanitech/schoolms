@extends('adminlte::page')

@section('title', 'Create Budget')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-file-invoice-dollar"></i> Submit Budget</h1>
@stop

@section('content')
<form action="{{ route('finance.budgets.store') }}" method="POST">
    @csrf

    <div class="card shadow">
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Department</label>
                    <select name="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Month</label>
                    <input type="text" name="month" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Note</label>
                <textarea name="note" class="form-control"></textarea>
            </div>

            <h5 class="mt-4">Budget Items</h5>
            <table class="table table-bordered" id="budget-items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Price (TZS)</th>
                        <th><button type="button" class="btn btn-success btn-sm" id="add-item">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="items[0][item]" class="form-control" required></td>
                        <td><input type="text" name="items[0][description]" class="form-control"></td>
                        <td><input type="number" name="items[0][price]" class="form-control item-price" step="0.01" required></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-item">x</button></td>
                    </tr>
                </tbody>
            </table>

            <div class="text-right mb-3">
                <strong>Total: TZS <span id="total">0</span></strong>
            </div>

            <button type="submit" class="btn btn-primary">Submit Budget</button>
        </div>
    </div>
</form>
@stop

@section('js')
<script>
let itemIndex = 1;

// Add new item row
document.getElementById('add-item').addEventListener('click', function() {
    const tbody = document.querySelector('#budget-items-table tbody');
    const row = `<tr>
        <td><input type="text" name="items[${itemIndex}][item]" class="form-control" required></td>
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

document.addEventListener('input', function(e){
    if(e.target && e.target.classList.contains('item-price')) {
        calculateTotal();
    }
});
</script>
@stop
