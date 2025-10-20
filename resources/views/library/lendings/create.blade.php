@extends('adminlte::page')

@section('title', 'Create Lending')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-book"></i> Create Lending</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form id="lendingForm" action="{{ route('library.lendings.store') }}" method="POST">
            @csrf

            {{-- Book --}}
            <div class="mb-3">
                <label for="book_id" class="form-label">Book</label>
                <select name="book_id" id="book_id" class="form-control" required>
                    <option value="">Select Book</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}" data-quantity="{{ $book->quantity }}">
                            {{ $book->title }} ({{ $book->quantity }} available)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Quantity --}}
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
            </div>

            {{-- Borrower Type --}}
            <div class="mb-3">
                <label class="form-label">Borrower Type</label>
                <select id="borrower_type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            {{-- Student Dropdown --}}
            <div class="mb-3" id="student_select" style="display:none;">
                <label for="class_id" class="form-label">Class</label>
                <select id="class_id" class="form-control">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>

                <label for="student_id" class="form-label mt-2">Student</label>
                <select id="student_id" class="form-control">
                    <option value="">Select Student</option>
                </select>
            </div>

            {{-- Staff Dropdown --}}
            <div class="mb-3" id="staff_select" style="display:none;">
                <label for="staff_id" class="form-label">Staff</label>
                <select id="staff_id" class="form-control">
                    <option value="">Select Staff</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Lend and Return Dates --}}
            <div class="mb-3">
                <label for="lend_date" class="form-label">Lend Date</label>
                <input type="date" name="lend_date" id="lend_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="return_date" class="form-label">Return Date</label>
                <input type="date" name="return_date" id="return_date" class="form-control">
            </div>

            {{-- Hidden inputs --}}
            <input type="hidden" name="borrower_type" id="hidden_borrower_type">
            <input type="hidden" name="user_id" id="hidden_user_id">

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Lending</button>
            <a href="{{ route('library.lendings.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const borrowerType = document.getElementById('borrower_type');
    const studentSelectDiv = document.getElementById('student_select');
    const staffSelectDiv = document.getElementById('staff_select');
    const hiddenBorrowerType = document.getElementById('hidden_borrower_type');
    const hiddenUserId = document.getElementById('hidden_user_id');
    const classSelect = document.getElementById('class_id');
    const studentDropdown = document.getElementById('student_id');
    const staffDropdown = document.getElementById('staff_id');
    const form = document.getElementById('lendingForm');
    const bookSelect = document.getElementById('book_id');
    const quantityInput = document.getElementById('quantity');

    // Show/hide dropdowns based on borrower type
    borrowerType.addEventListener('change', function() {
        const type = this.value;
        hiddenBorrowerType.value = type;

        if (type === 'student') {
            studentSelectDiv.style.display = 'block';
            staffSelectDiv.style.display = 'none';
            hiddenUserId.value = studentDropdown.value || '';
        } else if (type === 'staff') {
            staffSelectDiv.style.display = 'block';
            studentSelectDiv.style.display = 'none';
            hiddenUserId.value = staffDropdown.value || '';
        } else {
            studentSelectDiv.style.display = 'none';
            staffSelectDiv.style.display = 'none';
            hiddenUserId.value = '';
        }
    });

    // Fetch students when class changes
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        studentDropdown.innerHTML = '<option value="">Loading...</option>';

        if (!classId) {
            studentDropdown.innerHTML = '<option value="">Select Student</option>';
            hiddenUserId.value = '';
            return;
        }

        fetch(`/library/lendings/get-students/${classId}`)
            .then(res => res.json())
            .then(data => {
                studentDropdown.innerHTML = '<option value="">Select Student</option>';
                data.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = student.name;
                    studentDropdown.appendChild(option);
                });
                hiddenUserId.value = studentDropdown.value || '';
            })
            .catch(err => {
                console.error('Error loading students:', err);
                studentDropdown.innerHTML = '<option value="">Error loading students</option>';
                hiddenUserId.value = '';
            });
    });

    // Update hiddenUserId when selection changes
    studentDropdown.addEventListener('change', () => hiddenUserId.value = studentDropdown.value);
    staffDropdown.addEventListener('change', () => hiddenUserId.value = staffDropdown.value);

    // Limit quantity based on selected book stock
    bookSelect.addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];
        const maxQuantity = parseInt(selectedOption.dataset.quantity || 1);
        quantityInput.max = maxQuantity;
        if (parseInt(quantityInput.value) > maxQuantity) {
            quantityInput.value = maxQuantity;
        }
    });

    // Validate form before submit
    form.addEventListener('submit', function(e) {
        if (!hiddenUserId.value) {
            e.preventDefault();
            alert('Please select a student or staff member.');
            return;
        }

        if (!bookSelect.value) {
            e.preventDefault();
            alert('Please select a book.');
            return;
        }

        if (parseInt(quantityInput.value) < 1 || parseInt(quantityInput.value) > parseInt(quantityInput.max)) {
            e.preventDefault();
            alert('Invalid quantity selected.');
        }
    });
});
</script>
@stop
