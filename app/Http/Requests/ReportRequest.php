<?php

namespace App\Http\Requests;

use App\Enums\BookType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    /** Jika parameter tidak valid, kembali ke halaman laporan dengan pesan. */
    protected $redirectRoute = 'admin.reports.index';

    public function authorize(): bool
    {
        return true; // akses dijaga middleware "auth" pada route
    }

    public function rules(): array
    {
        return [
            'jenis' => ['nullable', Rule::in(['peminjaman', 'buku'])],
            'periode' => ['nullable', Rule::in(['bulan', 'semester', 'rentang'])],
            'bulan' => ['nullable', 'date_format:Y-m'],
            'ta' => ['nullable', 'integer', 'between:2000,2100'],
            'semester' => ['nullable', Rule::in(['ganjil', 'genap'])],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in(['all', 'pending', 'borrowed', 'overdue', 'returned', 'cancelled'])],
            'kategori' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', Rule::enum(BookType::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'bulan' => 'bulan',
            'ta' => 'tahun ajaran',
            'dari' => 'tanggal awal',
            'sampai' => 'tanggal akhir',
            'kategori' => 'kategori',
            'tipe' => 'jenis buku',
        ];
    }

    public function messages(): array
    {
        return [
            'in' => 'Pilihan :attribute tidak valid.',
            'enum' => 'Pilihan :attribute tidak valid.',
            'date_format' => 'Format :attribute tidak valid.',
            'integer' => ':Attribute harus berupa angka.',
            'between' => ':Attribute di luar rentang yang diizinkan.',
            'max' => ':Attribute terlalu panjang.',
        ];
    }
}