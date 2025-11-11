<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Lending;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:library.view')->only(['index', 'show']);
        $this->middleware('permission:library.create')->only(['create', 'store']);
        $this->middleware('permission:library.edit')->only(['edit', 'update']);
        $this->middleware('permission:library.delete')->only(['destroy']);
    }

    public function index()
{
    $books = Book::with('category')->orderBy('title')->get();

    $librarySummary = [
        'total_books' => Book::count(),
        'books_lent' => Lending::where('returned', false)->count(),
        'books_available' => Book::sum('quantity'),
        'total_categories' => Category::count(),
    ];

    return view('library.books.index', compact('books', 'librarySummary'));
}



    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('library.books.create', compact('categories'));
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'author' => 'nullable|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'isbn' => 'nullable|string|max:100',
        'quantity' => 'nullable|integer|min:1',
    ]);

    Book::create($request->only([
        'title',
        'author',
        'category_id',
        'isbn',
        'quantity',
    ]));

    return redirect()->route('library.books.index')->with('success', 'Book added successfully.');
}


    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get();
        return view('library.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'isbn' => 'nullable|string|max:50',
            'quantity' => 'required|integer|min:0',
        ]);

        $book->update($request->all());

        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }
}
