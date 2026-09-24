<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::query()
            ->when($request->search, fn ($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('title', 'like', "%{$s}%")
                    ->orWhere('author', 'like', "%{$s}%")
                    ->orWhere('isbn', 'like', "%{$s}%");
            }))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $categories = Book::whereNotNull('category')->distinct()->pluck('category');

        return view('books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        return view('books.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['available'] = $data['quantity'];

        Book::create($data);

        return redirect()->route('books.index')->with('success', 'Libro agregado al catálogo.');
    }

    public function edit(Book $book): View
    {
        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $this->validateData($request);

        // Ajustar disponibles según el cambio de stock total
        $diff = $data['quantity'] - $book->quantity;
        $data['available'] = max(0, $book->available + $diff);

        $book->update($data);

        return redirect()->route('books.index')->with('success', 'Libro actualizado.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Libro eliminado.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'author' => ['nullable', 'string', 'max:150'],
            'isbn' => ['nullable', 'string', 'max:30'],
            'category' => ['nullable', 'string', 'max:80'],
            'editorial' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:80'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);
    }
}
