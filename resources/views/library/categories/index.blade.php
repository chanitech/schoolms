@extends('adminlte::page')

@section('title', 'Categories')

@section('content_header')
    <h1>Categories</h1>
@stop

@section('content')

    {{-- Info boxes --}}
    <div class="row mb-3">
        <x-adminlte-info-box title="Total Categories" text="{{ $librarySummary['total_categories'] }}" icon="fas fa-tags" theme="info"/>
        <x-adminlte-info-box title="Total Books" text="{{ $librarySummary['total_books'] }}" icon="fas fa-book" theme="primary"/>
        <x-adminlte-info-box title="Books Lent" text="{{ $librarySummary['books_lent'] }}" icon="fas fa-exchange-alt" theme="warning"/>
        <x-adminlte-info-box title="Books Available" text="{{ $librarySummary['books_available'] }}" icon="fas fa-book-open" theme="success"/>
    </div>

    {{-- Add Category button --}}
    @can('library.create')
        <a href="{{ route('library.categories.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Add Category
        </a>
    @endcan

    {{-- Categories Table --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>
                                @can('library.edit')
                                    <a href="{{ route('library.categories.edit', $category) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endcan

                                @can('library.delete')
                                    <form action="{{ route('library.categories.destroy', $category) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop
