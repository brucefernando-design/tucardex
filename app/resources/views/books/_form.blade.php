@csrf
<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Título <span class="text-danger">*</span></label><input name="title" value="{{ old('title', $book->title ?? '') }}" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Autor</label><input name="author" value="{{ old('author', $book->author ?? '') }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">ISBN</label><input name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Categoría</label><input name="category" value="{{ old('category', $book->category ?? '') }}" class="form-control" placeholder="Ej: Literatura, Ciencias"></div>
    <div class="col-md-4"><label class="form-label">Editorial</label><input name="editorial" value="{{ old('editorial', $book->editorial ?? '') }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Ubicación / Estante</label><input name="location" value="{{ old('location', $book->location ?? '') }}" class="form-control" placeholder="Ej: Estante B-3"></div>
    <div class="col-md-4"><label class="form-label">Cantidad de ejemplares <span class="text-danger">*</span></label><input type="number" name="quantity" value="{{ old('quantity', $book->quantity ?? 1) }}" min="1" class="form-control" required></div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar</button>
    <a href="{{ route('books.index') }}" class="btn btn-light">Cancelar</a>
</div>
