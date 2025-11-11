<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lending;
use App\Models\Book;
use App\Models\Student;
use App\Models\Staff;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LendingController extends Controller
{
    /**
     * Display a listing of the lendings.
     */
    public function index()
    {
        $lendings = Lending::with(['book', 'borrower'])
            ->orderBy('lend_date', 'desc')
            ->paginate(15);

        $lendingSummary = [
            'total' => Lending::count(),
            'students' => Lending::where('borrower_type', Student::class)->count(),
            'staff' => Lending::where('borrower_type', Staff::class)->count(),
            'books_lent' => Lending::sum('quantity'),
        ];

        return view('library.lendings.index', compact('lendings', 'lendingSummary'));
    }

    /**
     * Show the form for creating a new lending record.
     */
    public function create()
    {
        $books = Book::where('quantity', '>', 0)->get();
        $staffs = Staff::all();
        $classes = SchoolClass::all();

        return view('library.lendings.create', compact('books', 'staffs', 'classes'));
    }

    /**
     * Store a newly created lending in storage.
     */
    public function store(Request $request)
    {
        Log::info('Lending Store Request:', $request->all());

        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|integer',
            'borrower_type' => 'required|in:student,staff',
            'quantity' => 'required|integer|min:1',
            'lend_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:lend_date',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        // Prevent borrowing more than available
        if ($validated['quantity'] > $book->quantity) {
            return back()->withErrors(['quantity' => 'Cannot borrow more than available stock.'])->withInput();
        }

        // Convert type to full class name
        $validated['borrower_type'] = $validated['borrower_type'] === 'student'
            ? Student::class
            : Staff::class;

        DB::transaction(function () use ($validated, $book) {
            // Create lending
            Lending::create([
                'book_id' => $validated['book_id'],
                'user_id' => $validated['user_id'],
                'borrower_type' => $validated['borrower_type'],
                'quantity' => $validated['quantity'],
                'lend_date' => $validated['lend_date'],
                'return_date' => $validated['return_date'],
                'returned' => false,
            ]);

            // Decrement book quantity
            $book->decrement('quantity', $validated['quantity']);
        });

        return redirect()->route('library.lendings.index')
            ->with('success', 'Lending recorded successfully.');
    }

    /**
     * Mark a lending as returned.
     */
    public function returnBook(Lending $lending)
    {
        if ($lending->returned) {
            return back()->with('warning', 'This lending is already returned.');
        }

        DB::transaction(function () use ($lending) {
            // Mark as returned
            $lending->update([
                'returned' => true,
                'returned_at' => now(),
            ]);

            // Increment the book quantity accordingly
            if ($lending->book) {
                $lending->book->increment('quantity', $lending->quantity);
            }
        });

        return redirect()->route('library.lendings.index')
            ->with('success', 'Book returned successfully.');
    }

    /**
     * AJAX endpoint: get students by class.
     */
    public function getStudentsByClass($class_id)
    {
        try {
            $students = Student::where('class_id', $class_id)
                ->select('id', 'first_name', 'middle_name', 'last_name')
                ->orderBy('first_name')
                ->get()
                ->map(function ($student) {
                    $fullName = $student->first_name;
                    if ($student->middle_name) $fullName .= ' ' . $student->middle_name;
                    $fullName .= ' ' . $student->last_name;

                    return [
                        'id' => $student->id,
                        'name' => $fullName,
                    ];
                });

            return response()->json($students);
        } catch (\Throwable $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * AJAX endpoint: get staff by role.
     */
    public function getStaffByRole($role)
    {
        try {
            $staff = Staff::where('role', $role)->get();
            return response()->json($staff);
        } catch (\Throwable $e) {
            Log::error('Error fetching staff: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
