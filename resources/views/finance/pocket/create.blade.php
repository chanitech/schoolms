@extends('adminlte::page')

@section('title', 'Record Pocket Money Transaction')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-wallet"></i> Record Pocket Money Transaction</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('finance.pocket.store') }}" method="POST">
            @csrf

            <!-- Select Class -->
            <div class="form-group mb-3">
                <label for="class_id">Class</label>
                <select id="class_id" class="form-control">
                    <option value="">-- Select Class --</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Select Student -->
            <div class="form-group mb-3">
                <label for="student_id">Student</label>
                <select name="student_id" id="student_id" class="form-control" required>
                    <option value="">-- Select Student --</option>
                    <!-- Populated by AJAX -->
                </select>
            </div>

            <!-- Transaction Type -->
            <div class="form-group mb-3">
                <label for="type">Transaction Type</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                </select>
            </div>

            <!-- Amount -->
            <div class="form-group mb-3">
                <label for="amount">Amount</label>
                <input type="number" name="amount" class="form-control" id="amount" step="0.01" min="0.01" required>
            </div>

            <!-- Current Balance -->
            <div class="form-group mb-3">
                <label>Current Balance</label>
                <input type="text" class="form-control" id="current_balance" readonly value="0.00">
            </div>

            <!-- Note -->
            <div class="form-group mb-3">
                <label for="note">Note (optional)</label>
                <textarea name="note" id="note" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Record Transaction</button>
            <a href="{{ route('finance.pocket.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // When class is selected, fetch students
    $('#class_id').change(function() {
        let classId = $(this).val();
        $('#student_id').html('<option>Loading...</option>');
        $('#current_balance').val('0.00');

        if (classId) {
            $.get("{{ route('finance.pocket.students-by-class') }}", { class_id: classId }, function(data) {
                let options = '<option value="">-- Select Student --</option>';
                data.forEach(function(student) {
                    options += `<option value="${student.id}">${student.first_name} ${student.last_name}</option>`;
                });
                $('#student_id').html(options);
            });
        } else {
            $('#student_id').html('<option value="">-- Select Student --</option>');
        }
    });

    // When student is selected, fetch last balance
    $('#student_id').change(function() {
        let studentId = $(this).val();
        if (!studentId) return $('#current_balance').val('0.00');

        $.get("/finance/pocket/last-balance", { student_id: studentId }, function(data) {
            $('#current_balance').val(parseFloat(data.balance).toFixed(2));
        });
    });
});
</script>
@stop
