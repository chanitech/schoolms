@extends('adminlte::page')

@section('title', 'Individual Session Report Details')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Individual Session Report</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            {{ $individualSessionReport->student->first_name ?? '-' }} 
            {{ $individualSessionReport->student->last_name ?? '' }}
        </h3>
        <div>
            <a href="{{ route('counseling.individual.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('counseling.individual.edit', $individualSessionReport->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- Session Details --}}
        <table class="table table-bordered table-striped">
            <tr>
                <th>Student Name</th>
                <td>{{ $individualSessionReport->student->first_name ?? '-' }} {{ $individualSessionReport->student->last_name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ \Carbon\Carbon::parse($individualSessionReport->date)->format('d M, Y') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Time</th>
                <td>{{ $individualSessionReport->time ?? '-' }}</td>
            </tr>
            <tr>
                <th>Session Number</th>
                <td>{{ $individualSessionReport->session_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>Presenting Problem</th>
                <td>{{ $individualSessionReport->presenting_problem ?? '-' }}</td>
            </tr>
            <tr>
                <th>Work Done</th>
                <td>{{ $individualSessionReport->work_done ?? '-' }}</td>
            </tr>
            <tr>
                <th>Assessment & Progress</th>
                <td>{{ $individualSessionReport->assessment_progress ?? '-' }}</td>
            </tr>
            <tr>
                <th>Intervention Plan</th>
                <td>{{ $individualSessionReport->intervention_plan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Follow Up</th>
                <td>{{ $individualSessionReport->follow_up ?? '-' }}</td>
            </tr>
            <tr>
                <th>Counselor</th>
                <td>{{ $individualSessionReport->counselor->name ?? '-' }}</td>
            </tr>
        </table>

        {{-- Biopsychosocial Formulation (4P's) --}}
        @if(!empty($individualSessionReport->biopsychosocial_formulation))
            @php
                $formulation = $individualSessionReport->biopsychosocial_formulation;
                $pList = ['Predisposing', 'Precipitating', 'Perpetuating', 'Protecting'];
                $factors = ['biological', 'psychological', 'social'];
                $isNested = false;

                foreach ($pList as $p) {
                    if(isset($formulation[$p]) && is_array($formulation[$p])) {
                        $isNested = true;
                        break;
                    }
                }
            @endphp

            <h4 class="mt-4">Biopsychosocial Formulation (4P's)</h4>

            @if($isNested)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>P Factor</th>
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
            @else
                <ul>
                    @foreach($formulation as $key => $value)
                        <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                    @endforeach
                </ul>
            @endif
        @endif

        <div class="mt-3">
            <a href="{{ route('counseling.individual.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@stop
