@extends('adminlte::page')

@section('title', 'Create Exam')

@section('content_header')
    <h1>Create Exam</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('exams.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Exam Name</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="term">Term</label>
                    <select name="term" id="term"
                            class="form-control @error('term') is-invalid @enderror" required>
                        <option value="">-- Select Term --</option>
                        <option value="Term 1" {{ old('term') == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                        <option value="Term 2" {{ old('term') == 'Term 2' ? 'selected' : '' }}>Term 2</option>
                        <option value="Term 3" {{ old('term') == 'Term 3' ? 'selected' : '' }}>Term 3</option>
                    </select>
                    @error('term')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="academic_session_id">Academic Session</label>
                    <select name="academic_session_id" id="academic_session_id"
                            class="form-control @error('academic_session_id') is-invalid @enderror" required>
                        <option value="">-- Select Session --</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ old('academic_session_id') == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_session_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Checkboxes --}}
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" name="include_in_term_final" id="include_in_term_final"
                               class="form-check-input" {{ old('include_in_term_final') ? 'checked' : '' }}>
                        <label class="form-check-label" for="include_in_term_final">Include in Term Final Average</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="include_in_year_final" id="include_in_year_final"
                               class="form-check-input" {{ old('include_in_year_final') ? 'checked' : '' }}>
                        <label class="form-check-label" for="include_in_year_final">Include in Year/Annual Average</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_terminal_exam" id="is_terminal_exam"
                               class="form-check-input" {{ old('is_terminal_exam') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_terminal_exam">Mark as Terminal Exam</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_annual_exam" id="is_annual_exam"
                               class="form-check-input" {{ old('is_annual_exam') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_annual_exam">Mark as Annual Exam</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Save</button>
                <a href="{{ route('exams.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop
