<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        // Marcar como vencidos los préstamos pasados de fecha
        Loan::where('status', 'prestado')
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'vencido']);

        $loans = Loan::with(['book', 'student'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'prestado' => Loan::where('status', 'prestado')->count(),
            'vencido' => Loan::where('status', 'vencido')->count(),
            'devuelto' => Loan::where('status', 'devuelto')->count(),
        ];

        $books = Book::where('available', '>', 0)->orderBy('title')->get();
        $students = Student::where('status', 'activo')->orderBy('last_name')->get();

        return view('loans.index', compact('loans', 'summary', 'books', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'student_id' => ['nullable', 'exists:students,id'],
            'borrower_name' => ['nullable', 'string', 'max:150'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
        ]);

        $book = Book::findOrFail($data['book_id']);
        if ($book->available < 1) {
            return back()->with('error', 'No hay ejemplares disponibles de ese libro.');
        }

        $data['status'] = 'prestado';
        Loan::create($data);
        $book->decrement('available');

        return redirect()->route('loans.index')->with('success', 'Préstamo registrado.');
    }

    public function returnBook(Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'devuelto') {
            $loan->update(['status' => 'devuelto', 'return_date' => now()]);
            $loan->book->increment('available');
        }

        return redirect()->route('loans.index')->with('success', 'Devolución registrada.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        // Si estaba prestado, devolver el ejemplar al stock
        if ($loan->status !== 'devuelto') {
            $loan->book->increment('available');
        }
        $loan->delete();

        return redirect()->route('loans.index')->with('success', 'Préstamo eliminado.');
    }
}
