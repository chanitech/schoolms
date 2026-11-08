@extends('adminlte::page')

@section('title', 'Edit Book')

@section('content_header')
    <h1>Edit Book</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('library.books.update', $book) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $book->title) }}">
        </div>
        <div class="form-group">
            <label>Author</label>
            <input type="text" name="author" class="form-control" value="{{ old('author', $book->author) }}">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="form-control">
                <option value="">--Select--</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $book->category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}">
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" min="0" required value="{{ old('quantity', $book->quantity) }}">
        </div>
        <button class="btn btn-success">Update</button>
        <a href="{{ route('library.books.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@stop
