@extends('adminlte::page')

@section('title', 'Exam Details')

@section('content_header')
    <h1>Exam Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $exam->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('exams.edit', $exam) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('exams.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Name</th>
                    <td>{{ $exam->name }}</td>
                </tr>
                <tr>
                    <th>Term</th>
                    <td>{{ $exam->term }}</td>
                </tr>
                <tr>
                    <th>Academic Session</th>
                    <td>{{ $exam->academicSession->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $exam->created_at->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <th>Updated At</th>
                    <td>{{ $exam->updated_at->format('d M Y, H:i') }}</td>
                </tr>
            </table>
        </div>
    </div>
@stop
