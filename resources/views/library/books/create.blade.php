@extends('adminlte::page')

@section('title', 'Add Book')

@section('content_header')
    <h1>Add Book</h1>
@stop

@section('content')
    <form action="{{ route('library.books.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
        </div>
        <div class="form-group">
            <label>Author</label>
            <input type="text" name="author" class="form-control" value="{{ old('author') }}">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="form-control">
                <option value="">--Select--</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" class="form-control" value="{{ old('isbn') }}">
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" min="0" required value="{{ old('quantity',1) }}">
        </div>
        <button class="btn btn-success">Save</button>
        <a href="{{ route('library.books.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@stop
