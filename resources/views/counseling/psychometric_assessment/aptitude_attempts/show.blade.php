@extends('adminlte::page')

@section('title', 'Aptitude Test Attempt Details')

@section('content_header')
    <h1><i class="fas fa-eye"></i> Aptitude Test Attempt Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Attempt by {{ $aptitudeAttempt->student->name }} ({{ $aptitudeAttempt->student->admission_no }})</h3>
        <a href="{{ route('aptitude.pdf', $aptitudeAttempt->id) }}" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
    </div>

    <div class="card-body">
        <p><strong>Counselor:</strong> {{ $aptitudeAttempt->counselor->name }}</p>
        <p><strong>Date:</strong> {{ $aptitudeAttempt->created_at->format('d M Y, H:i') }}</p>
        <p><strong>Total Score:</strong> {{ $aptitudeAttempt->total_score }}</p>

        @foreach($aptitudeAttempt->answers->groupBy(function($a){ return $a->question->section; }) as $section => $answers)
            <h4 class="mt-4">{{ $section }}</h4>
            <hr>
            @foreach($answers as $answer)
                <div class="mb-3 border p-3 rounded">
                    <p><strong>Q{{ $loop->iteration }}. {!! $answer->question->question !!}</strong></p>

                    @if($answer->question->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$answer->question->image) }}" alt="Question Image" class="img-fluid" style="max-width:400px;">
                        </div>
                    @endif

                    <p><strong>Answer:</strong> 
                        @php
                            $ans = $answer->student_answer;
                            $display = '';
                            if(in_array($answer->question->type, ['mcq','true_false'])) {
                                $option = $answer->question->options[$ans] ?? null;
                                $display = $option['text'] ?? $ans;
                            } else {
                                $display = $ans;
                            }
                        @endphp
                        {!! $display !!}
                    </p>

                    <p><strong>Marks Obtained:</strong> {{ $answer->obtained_marks }} / {{ $answer->question->marks }}</p>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
@stop
