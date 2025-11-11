@extends('adminlte::page')

@section('title', 'Books')

@section('content_header')
    <h1>Books</h1>
@stop

@section('content')
    <div class="row mb-3">
        <x-adminlte-info-box title="Total Books" text="{{ $librarySummary['total_books'] }}" icon="fas fa-book" theme="primary"/>
        <x-adminlte-info-box title="Books Lent" text="{{ $librarySummary['books_lent'] }}" icon="fas fa-exchange-alt" theme="warning"/>
        <x-adminlte-info-box title="Books Available" text="{{ $librarySummary['books_available'] }}" icon="fas fa-book-open" theme="success"/>
        <x-adminlte-info-box title="Categories" text="{{ $librarySummary['total_categories'] }}" icon="fas fa-tags" theme="info"/>
    </div>

    @can('library.create')
        <a href="{{ route('library.books.create') }}" class="btn btn-primary mb-2">Add Book</a>
    @endcan

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>ISBN</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $book)
            <tr>
                <td>{{ $book->title }}</td>
                <td>{{ $book->author ?? '-' }}</td>
                <td>{{ $book->category->name ?? '-' }}</td>
                <td>{{ $book->isbn ?? '-' }}</td>
                <td>{{ $book->quantity }}</td>
                <td>
                    @can('library.edit')
                        <a href="{{ route('library.books.edit', $book) }}" class="btn btn-sm btn-warning">Edit</a>
                    @endcan
                    @can('library.delete')
                        <form action="{{ route('library.books.destroy', $book) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@stop
