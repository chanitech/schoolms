@extends('adminlte::page')

@section('title', 'Record Payment')

@section('content_header')
    <h1>Record Payment</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form id="filterForm">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label>Academic Session</label>
                    <select name="session_id" id="session_id" class="form-control">
                        <option value="">Select session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Class</label>
                    <select name="class_id" id="class_id" class="form-control">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mt-4">
                    <button type="button" id="filterBtn" class="btn btn-primary mt-2">Filter Students</button>
                </div>
            </div>
        </form>

        <hr>

        <div id="studentsList">
            <!-- Filtered students will appear here -->
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#filterBtn').click(function() {
        let classId = $('#class_id').val();
        let sessionId = $('#session_id').val();

        if(!classId || !sessionId) {
            alert('Please select both session and class.');
            return;
        }

        $.ajax({
            
            url: "{{ route('finance.payments.students') }}",

            method: 'GET',
            data: { class_id: classId, session_id: sessionId },
            success: function(students) {
                if(students.length == 0) {
                    $('#studentsList').html('<p>No students found for selected class/session.</p>');
                    return;
                }

                let html = '<table class="table table-bordered">';
                html += '<thead><tr><th>Admission No</th><th>Name</th><th>Bills</th></tr></thead><tbody>';

                students.forEach(student => {
                    html += `<tr>
                        <td>${student.admission_no}</td>
                        <td>${student.first_name} ${student.middle_name ?? ''} ${student.last_name}</td>
                        <td>
                            <button class="btn btn-sm btn-info view-bills" data-id="${student.id}">View Bills</button>
                            <div class="student-bills mt-2" id="bills-${student.id}" style="display:none;"></div>
                        </td>
                    </tr>`;
                });

                html += '</tbody></table>';
                $('#studentsList').html(html);
            }
        });
    });

    // Handle View Bills click
    $(document).on('click', '.view-bills', function() {
        let studentId = $(this).data('id');
        let container = $(`#bills-${studentId}`);
        container.toggle();

        if(container.html().trim() === '') {
            $.ajax({
               url: "{{ route('finance.payments.student-bills') }}",

                method: 'GET',
                data: { student_id: studentId },
                success: function(bills) {
                    if(bills.length == 0) {
                        container.html('<p>No bills found.</p>');
                        return;
                    }

                    let billHtml = '<ul class="list-group">';
                    bills.forEach(bill => {
                        billHtml += `<li class="list-group-item">
                            ${bill.bill_name} - Total: ${bill.total_amount} | Paid: ${bill.amount_paid} | Balance: ${bill.balance} | Status: ${bill.status}
                            <a href="/finance/payments/${bill.id}/create" class="btn btn-sm btn-success float-right">Record Payment</a>
                        </li>`;
                    });
                    billHtml += '</ul>';
                    container.html(billHtml);
                }
            });
        }
    });
});
</script>
@stop
