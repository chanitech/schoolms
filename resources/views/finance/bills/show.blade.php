@extends('adminlte::page')

@section('title', 'Bill Details')

@section('content_header')
    <h1>Bill Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h4>{{ $bill->title }}</h4>
        <p><strong>Description:</strong> {{ $bill->description }}</p>
        <p><strong>Class:</strong> {{ $bill->schoolClass?->name ?? 'N/A' }}</p>
        <p><strong>Amount:</strong> {{ number_format($bill->amount, 2) }}</p>
        <p><strong>Status:</strong> {{ $bill->status }}</p>
        <p><strong>Due Date:</strong> {{ $bill->due_date?->format('Y-m-d') ?? '-' }}</p>

        <h5>Assigned Students</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->studentBills as $sb)
                    <tr>
                        <td>{{ $sb->student->full_name }}</td>
                        <td>{{ number_format($sb->amount, 2) }}</td>
                        <td>{{ number_format($sb->paid_amount, 2) }}</td>
                        <td>{{ $sb->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
