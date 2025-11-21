@extends('adminlte::page')

@section('title', 'Group Counseling Session Details')

@section('content_header')
    <h1><i class="fas fa-users"></i> Group Counseling Session Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ $group->group_name }}</h3>
        <div>
            <a href="{{ route('counseling.group.edit', $group->id) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('counseling.group.destroy', $group->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        {{-- Session Details --}}
        <div class="row mb-2">
            <div class="col-md-4"><strong>Date:</strong> {{ $group->date }}</div>
            <div class="col-md-4"><strong>Time:</strong> {{ $group->time }}</div>
            <div class="col-md-4"><strong>Session Number:</strong> {{ $group->session_number ?? '-' }}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><strong>Presenting Problem:</strong> {{ $group->presenting_problem ?? '-' }}</div>
            <div class="col-md-4"><strong>Work Done:</strong> {{ $group->work_done ?? '-' }}</div>
            <div class="col-md-4"><strong>Assessment / Progress:</strong> {{ $group->assessment_progress ?? '-' }}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><strong>Intervention Plan:</strong> {{ $group->intervention_plan ?? '-' }}</div>
            <div class="col-md-4"><strong>Follow Up:</strong> {{ $group->follow_up ?? '-' }}</div>
            <div class="col-md-4"><strong>Created By:</strong> {{ $group->user?->name ?? '-' }}</div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6"><strong>Created At:</strong> {{ $group->created_at->format('Y-m-d H:i') }}</div>
            <div class="col-md-6"><strong>Updated At:</strong> {{ $group->updated_at->format('Y-m-d H:i') }}</div>
        </div>

        {{-- Members --}}
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <strong>Members / Students</strong>
            </div>
            <div class="card-body">
                @if($group->students->isNotEmpty())
                    <ul>
                        @foreach($group->students as $student)
                            <li>{{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no ?? '-' }})</li>
                        @endforeach
                    </ul>
                @else
                    <p>No students assigned to this group.</p>
                @endif
            </div>
        </div>

        {{-- Biopsychosocial Formulation --}}
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <strong>Biopsychosocial Formulation (4P's)</strong>
            </div>
            <div class="card-body table-responsive">
                @php
                    $formulation = $group->biopsychosocial_formulation ?? [];
                    $pList = ['Predisposing', 'Precipitating', 'Perpetuating', 'Protecting'];
                    $factors = ['biological', 'psychological', 'social'];
                @endphp

                <table class="table table-bordered table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>4 P's</th>
                            @foreach($factors as $factor)
                                <th>{{ ucfirst($factor) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pList as $p)
                            <tr>
                                <td>{{ $p }}</td>
                                @foreach($factors as $factor)
                                    <td>{{ $formulation[$p][$factor] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <a href="{{ route('counseling.group.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>
@stop
