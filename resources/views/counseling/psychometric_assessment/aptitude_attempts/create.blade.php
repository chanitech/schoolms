@extends('adminlte::page')
@section('title', 'Add Aptitude Test Attempt')
@section('content_header')
<h1><i class="fas fa-edit"></i> Record Student Attempt</h1> @stop
@section('content')
<form action="{{ route('aptitude.store') }}" method="POST"> @csrf <div class="card card-primary"> <div class="card-body">
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
        <div class="form-group mb-4">

            <!-- Question Text -->
            <label>
                {{ $question->question_text }}
                @if($question->marks)
                    <small>({{ $question->marks }} marks)</small>
                @endif
            </label>

            <!-- Question Image (clickable) -->
            @if($question->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$question->image) }}" width="150" class="img-thumbnail clickable-image" data-bs-toggle="modal" data-bs-target="#questionImageModal{{ $question->id }}">
                </div>

                <div class="modal fade" id="questionImageModal{{ $question->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Question Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="{{ asset('storage/'.$question->image) }}" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- MCQ QUESTIONS --}}
            @if($question->type == 'mcq')
                @php $options = json_decode($question->options, true) ?? []; @endphp

                @foreach($options as $key => $opt)
                <div class="form-check mb-2">
                    <input class="form-check-input" 
                           type="radio" 
                           name="answers[{{ $question->id }}]" 
                           value="{{ $key }}" required
                           id="q{{ $question->id }}_option{{ $key }}">

                    <label class="form-check-label" for="q{{ $question->id }}_option{{ $key }}">
                        {{ $opt['text'] ?? 'Option '.$key }}
                    </label>

                    @if(!empty($opt['image']))
                        <br>
                        <img src="{{ asset('storage/'.$opt['image']) }}" width="100" class="img-thumbnail clickable-image" data-bs-toggle="modal" data-bs-target="#optionImageModal{{ $question->id }}_{{ $key }}">

                        <div class="modal fade" id="optionImageModal{{ $question->id }}_{{ $key }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Option Image</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="{{ asset('storage/'.$opt['image']) }}" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @endforeach

            {{-- TRUE/FALSE --}}
            @elseif($question->type == 'true_false')
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="True" id="q{{ $question->id }}_true" required>
                    <label class="form-check-label" for="q{{ $question->id }}_true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="False" id="q{{ $question->id }}_false" required>
                    <label class="form-check-label" for="q{{ $question->id }}_false">False</label>
                </div>

            {{-- NUMERICAL --}}
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
@section('css')
<style> .img-thumbnail { cursor: pointer; margin-right: 10px; } </style>
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stop