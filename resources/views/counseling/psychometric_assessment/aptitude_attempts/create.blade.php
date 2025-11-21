@extends('adminlte::page')

@section('title', 'Add Aptitude Test Attempt')

@section('content_header')
<h1><i class="fas fa-edit"></i> Record Student Attempt</h1>
@stop

@section('content')

<form action="{{ route('aptitude.store') }}" method="POST">
    @csrf
    <div class="card card-primary">
        <div class="card-body">
            <!-- Student Select -->
            <div class="form-group">
                <label>Select Student</label>
                <select name="student_id" class="form-control" required>
                    <option value="">--Select--</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->admission_no ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Questions -->
            @foreach($questions as $section => $sectionQuestions)
            <h4 class="mt-4">{{ $section }}</h4>
            <hr>
            @foreach($sectionQuestions as $question)
            <div class="form-group">
                <label>{!! $question->question !!} 
                    @if($question->marks) <small>({{ $question->marks }} marks)</small> @endif
                </label>
                @if($question->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$question->image) }}" width="150">
                </div>
                @endif

                @if($question->type == 'mcq')
                    @php $options = json_decode($question->options,true); @endphp
                    @foreach($options as $key => $opt)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" required>
                        <label class="form-check-label">
                            {{ $opt['text'] ?? '' }}
                            @if(isset($opt['image']))
                            <img src="{{ asset('storage/'.$opt['image']) }}" width="100">
                            @endif
                        </label>
                    </div>
                    @endforeach
                @elseif($question->type == 'true_false')
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="True" required> True
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="False" required> False
                    </div>
                @elseif($question->type == 'numerical')
                    <input type="number" name="answers[{{ $question->id }}]" class="form-control" required>
                @endif
            </div>
            @endforeach
            @endforeach

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Save Attempt</button>
            <a href="{{ route('aptitude.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

@stop
