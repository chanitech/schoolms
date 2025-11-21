@extends('adminlte::page')

@section('title', 'New Interest Inventory')

@section('content_header')
    <h1><i class="fas fa-brain"></i> New Interest Inventory</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Fill Interest Inventory</h3></div>

    <form action="{{ route('interest-inventories.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Student</label>
                    <select name="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                        <option value="">Select student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="col-md-6">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}">
                </div>
            </div>

            <hr class="my-3">

            {{-- Questions q1..q17 with labels --}}
            @php
                $labels = [
                    1 => 'My most interesting subject is',
                    2 => 'My most challenging subject is',
                    3 => 'What I enjoy most about school is',
                    4 => 'What I find most challenging about school is',
                    5 => 'Books I read recently',
                    6 => 'Activities I do outside of school',
                    7 => 'Three words to describe me',
                    8 => 'Careers that interest me',
                    9 => 'An ideal job for one day would be',
                    10 => 'My favourite Web sites are',
                    11 => 'My questions about next year are',
                    12 => 'School situations that are stressful for me are',
                    13 => 'I deal with stress or frustration by',
                    14 => 'Some interesting places I’ve been to are',
                    15 => 'If I could travel anywhere, I would like to go to',
                    16 => 'If I can’t watch television, I like to',
                    17 => 'I would like to learn more about',
                ];
            @endphp

            @foreach(range(1,17) as $i)
                <div class="form-group mb-3">
                    <label for="q{{ $i }}">{{ $labels[$i] }}</label>
                    <textarea name="q{{ $i }}" id="q{{ $i }}" class="form-control" rows="2">{{ old("q{$i}") }}</textarea>
                </div>
            @endforeach
        </div>

        <div class="card-footer">
            <button class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <a href="{{ route('interest-inventories.index') }}" class="btn btn-default">Cancel</a>
        </div>
    </form>
</div>
@stop
