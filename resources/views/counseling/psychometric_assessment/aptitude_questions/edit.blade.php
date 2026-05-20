@extends('adminlte::page')

@section('title', 'Edit Aptitude Question')

@section('content_header')
<h1><i class="fas fa-edit"></i> Edit Aptitude Question</h1>
@stop

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('aptitude.questions.update', $aptitudeQuestion->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card card-primary">
        <div class="card-body">
            <!-- Section -->
            <div class="form-group">
                <label>Section</label>
                <select name="section" class="form-control" required>
                    @foreach($sections as $section)
                    <option value="{{ $section }}" {{ $aptitudeQuestion->section == $section ? 'selected' : '' }}>{{ $section }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Question Text -->
            <div class="form-group">
                <label>Question Text</label>
                <textarea name="question" class="form-control" rows="3" required>{{ $aptitudeQuestion->question }}</textarea>
            </div>

            <!-- Question Image -->
            <div class="form-group">
                <label>Question Image (optional)</label>
                @if($aptitudeQuestion->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$aptitudeQuestion->image) }}" alt="Question Image" width="150">
                </div>
                @endif
                <input type="file" name="image" class="form-control">
            </div>

            <!-- Type -->
            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" id="question_type" required>
                    @foreach($types as $key => $type)
                    <option value="{{ $key }}" {{ $aptitudeQuestion->type == $key ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- MCQ Options -->
            <div id="mcq_options" style="display:none;">
                <label>Options</label>
                <div id="options_wrapper">
                    @php
                        $options = $aptitudeQuestion->options ? json_decode($aptitudeQuestion->options, true) : [];
                    @endphp
                    @foreach(['a','b','c','d'] as $key)
                    <div class="form-group option_row">
                        <input type="text" name="options[{{ $key }}][text]" class="form-control mb-1" placeholder="Option {{ strtoupper($key) }} text" value="{{ $options[$key]['text'] ?? '' }}">
                        <input type="file" name="options[{{ $key }}][image]" class="form-control mb-2">
                        @if(isset($options[$key]['image']))
                        <img src="{{ asset('storage/'.$options[$key]['image']) }}" width="100" class="mb-2">
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Correct Answer -->
            <div class="form-group">
                <label>Correct Answer</label>
                <input type="text" name="correct_answer" class="form-control" required value="{{ $aptitudeQuestion->correct_answer }}">
            </div>

            <!-- Marks -->
            <div class="form-group">
                <label>Marks</label>
                <input type="number" name="marks" class="form-control" min="1" value="{{ $aptitudeQuestion->marks }}" required>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">Update Question</button>
            <a href="{{ route('aptitude.questions.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

@stop

@section('js')
<script>
    function toggleOptions() {
        const type = document.getElementById('question_type').value;
        document.getElementById('mcq_options').style.display = (type === 'mcq') ? 'block' : 'none';
    }

    document.getElementById('question_type').addEventListener('change', toggleOptions);
    window.onload = toggleOptions;
</script>
@stop
