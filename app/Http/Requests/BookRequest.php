<?php

namespace App\Http\Requests;

use App\Enums\BookType;
use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses sudah dijaga middleware "auth" pada route
    }

    /** Buku yang sedang diubah (null saat menambah). */
    private function editedBook(): ?Book
    {
        $book = $this->route('book');

        return $book instanceof Book ? $book : null;
    }

    /** Jenis buku: dari data buku saat mengubah, dari form saat menambah. */
    private function bookType(): ?string
    {
        $fromForm = is_string($this->input('type')) ? $this->input('type') : null;

        return $this->editedBook()?->type->value ?? $fromForm;
    }

    public function rules(): array
    {
        $type = $this->bookType();
        $physical = $type === BookType::Physical->value;
        $digital = $type === BookType::Digital->value;

        return [
            'type' => $this->editedBook() ? [] : ['required', Rule::enum(BookType::class)],

            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'year_published' => ['nullable', 'integer', 'between:1800,'.(now()->year + 1)],
            'isbn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9Xx\-\s]+$/'],
            'synopsis' => ['nullable', 'string', 'max:3000'],
            'is_featured' => ['nullable', 'boolean'],

            'cover' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover' => ['nullable', 'boolean'],

            // Khusus buku fisik
            'stock_total' => $physical ? ['required', 'integer', 'min:0', 'max:9999'] : ['nullable'],
            'shelf_location' => $physical ? ['nullable', 'string', 'max:50'] : ['nullable'],

            // Khusus buku digital
            'pdf' => $digital
                ? ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:30720']
                : ['nullable'],
        ];
    }

    /** Buku digital wajib punya PDF: baru diunggah atau yang sudah ada. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $needsPdf = $this->bookType() === BookType::Digital->value
                && ! $this->hasFile('pdf')
                && blank($this->editedBook()?->pdf_path);

            if ($needsPdf && ! $v->errors()->has('pdf')) {
                $v->errors()->add('pdf', 'File PDF wajib diunggah untuk buku digital.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'type' => 'jenis buku',
            'category_id' => 'kategori',
            'title' => 'judul',
            'author' => 'penulis',
            'publisher' => 'penerbit',
            'year_published' => 'tahun terbit',
            'isbn' => 'ISBN',
            'synopsis' => 'sinopsis',
            'cover' => 'sampul',
            'stock_total' => 'total eksemplar',
            'shelf_location' => 'nomor rak',
            'pdf' => 'file PDF',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'max' => ':Attribute terlalu panjang atau terlalu besar (maksimal :max).',
            'min' => ':Attribute minimal :min.',
            'integer' => ':Attribute harus berupa angka bulat.',
            'between' => ':Attribute harus antara :min dan :max.',
            'exists' => ':Attribute tidak valid.',
            'image' => 'Sampul harus berupa gambar.',
            'mimes' => ':Attribute harus berformat :values.',
            'mimetypes' => 'File yang diunggah bukan PDF yang valid.',
            'uploaded' => ':Attribute gagal diunggah. Periksa ukuran berkas.',
            'isbn.regex' => 'ISBN hanya boleh berisi angka, huruf X, spasi, dan tanda hubung.',
            'cover.max' => 'Ukuran sampul maksimal 2 MB.',
            'pdf.max' => 'Ukuran PDF maksimal 30 MB.',
        ];
    }
}