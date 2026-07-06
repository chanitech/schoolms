<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Book;
use App\Models\Lending;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Apply permissions middleware if using Spatie
        $this->middleware('permission:view categories')->only(['index', 'show']);
        $this->middleware('permission:create categories')->only(['create', 'store']);
        $this->middleware('permission:edit categories')->only(['edit', 'update']);
        $this->middleware('permission:delete categories')->only(['destroy']);
    }

    // List all categories
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        // Library summary
        $librarySummary = [
            'total_books' => Book::sum('quantity'),
            'books_lent' => Lending::where('returned', 0)->count(),
            'books_available' => Book::sum('quantity') - Lending::where('returned', 0)->count(),
            'total_categories' => Category::count(),
        ];

        // Category-specific summary
        $categorySummary = [
            'total_categories' => Category::count(),
            'total_books' => Book::count(),
            'books_lent' => Lending::where('returned', 0)->count(),
            'books_available' => Book::count() - Lending::where('returned', 0)->count(),
        ];

        return view('library.categories.index', compact('categories', 'librarySummary', 'categorySummary'));
    }

    // Show form to create a new category
    public function create()
    {
        return view('library.categories.create');
    }

    // Store a new category
    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->where('school_id', $schoolId)],
        ]);

        Category::create($request->only('name')); // Only pass 'name' for mass assignment

        return redirect()->route('library.categories.index')->with('success', 'Category created successfully!');
    }

    // Show form to edit a category
    public function edit(Category $category)
    {
        return view('library.categories.edit', compact('category'));
    }

    // Update a category
    public function update(Request $request, Category $category)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)->where('school_id', $schoolId)],
        ]);

        $category->update($request->only('name'));

        return redirect()->route('library.categories.index')->with('success', 'Category updated successfully!');
    }

    // Delete a category
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('library.categories.index')->with('success', 'Category deleted successfully!');
    }
}
