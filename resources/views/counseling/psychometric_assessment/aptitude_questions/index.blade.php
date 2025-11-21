@extends('adminlte::page')

@section('title', 'Aptitude Questions')

@section('content_header')
    <h1><i class="fas fa-list"></i> Aptitude Questions</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <a href="{{ route('aptitude.questions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Question
        </a>
    </div>

    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Section</th>
                    <th>Question</th>
                    <th>Type</th>
                    <th>Marks</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($questions as $q)
                    <tr>
                        <td>{{ $q->id }}</td>
                        <td>{{ $q->section }}</td>
                        <td>{{ Str::limit($q->question_text, 50) }}</td>
                        <td>{{ strtoupper($q->type) }}</td>
                        <td>{{ $q->marks }}</td>
                        <td>
                            <a href="{{ route('aptitude.questions.edit', $q->id) }}" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('aptitude.questions.destroy', $q->id) }}" 
                                  method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
                            No questions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $questions->links() }}
    </div>
</div>

@stop
