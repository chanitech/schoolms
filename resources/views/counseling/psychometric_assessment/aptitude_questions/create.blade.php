@extends('adminlte::page')

@section('title', 'Add Aptitude Question')

@section('content_header')
<h1><i class="fas fa-plus-circle"></i> Add Aptitude Question</h1>
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

<form action="{{ route('aptitude.questions.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card card-primary">
        <div class="card-body">
            <!-- Section -->
            <div class="form-group">
                <label>Section</label>
                <select name="section" class="form-control" required>
                    @foreach($sections as $section)
                    <option value="{{ $section }}">{{ $section }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Question Text -->
            <div class="form-group">
                <label>Question Text</label>
                <textarea name="question_text" class="form-control" rows="3" required>{{ old('question_text') }}</textarea>
            </div>

            <!-- Question Image -->
            <div class="form-group">
                <label>Question Image (optional)</label>
                <input type="file" name="image" class="form-control">
            </div>

            <!-- Type -->
            <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control" id="question_type" required>
                    @foreach($types as $key => $type)
                    <option value="{{ $key }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- MCQ Options -->
            <div id="mcq_options" style="display:none;">
                <label>Options</label>
                <div id="options_wrapper">
                    <div class="form-group option_row">
                        <input type="text" name="options[a][text]" placeholder="Option A text" class="form-control mb-1" />
                        <input type="file" name="options[a][image]" class="form-control mb-2" />
                    </div>
                    <div class="form-group option_row">
                        <input type="text" name="options[b][text]" placeholder="Option B text" class="form-control mb-1" />
                        <input type="file" name="options[b][image]" class="form-control mb-2" />
                    </div>
                    <div class="form-group option_row">
                        <input type="text" name="options[c][text]" placeholder="Option C text" class="form-control mb-1" />
                        <input type="file" name="options[c][image]" class="form-control mb-2" />
                    </div>
                    <div class="form-group option_row">
                        <input type="text" name="options[d][text]" placeholder="Option D text" class="form-control mb-1" />
                        <input type="file" name="options[d][image]" class="form-control mb-2" />
                    </div>
                </div>
            </div>

            <!-- Correct Answer -->
            <div class="form-group">
                <label>Correct Answer</label>
                <input type="text" name="correct_answer" class="form-control" required placeholder="Enter correct answer (e.g., a, b, 12, True)">
            </div>

            <!-- Marks -->
            <div class="form-group">
                <label>Marks</label>
                <input type="number" name="marks" class="form-control" min="1" value="1" required>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">Save Question</button>
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
