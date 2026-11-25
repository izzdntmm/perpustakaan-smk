<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // form publik, tanpa login
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($v) => is_string($v) ? Str::squish($v) : '';

        $this->merge([
            'borrower_name' => $clean($this->input('borrower_name')),
            'borrower_class' => $clean($this->input('borrower_class')),
        ]);
    }

    public function rules(): array
    {
        return [
            'borrower_name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\s.,\'\-]+$/u'],
            'borrower_class' => ['required', 'string', 'max:50', 'regex:/^[\pL\pN\s.\-\/]+$/u'],
            'loan_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.today()->addDays(7)->toDateString(),
            ],
            'duration_days' => ['required', 'integer', Rule::in(Loan::DURATIONS)],
        ];
    }

    public function attributes(): array
    {
        return [
            'borrower_name' => 'nama lengkap',
            'borrower_class' => 'kelas / jabatan',
            'loan_date' => 'tanggal pinjam',
            'duration_days' => 'durasi pinjam',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'min' => ':Attribute minimal :min karakter.',
            'max' => ':Attribute terlalu panjang (maksimal :max karakter).',
            'borrower_name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, apostrof, dan tanda hubung.',
            'borrower_class.regex' => 'Kelas / jabatan hanya boleh berisi huruf, angka, spasi, titik, tanda hubung, dan garis miring.',
            'loan_date.date_format' => 'Format tanggal pinjam tidak valid.',
            'loan_date.after_or_equal' => 'Tanggal pinjam tidak boleh sebelum hari ini.',
            'loan_date.before_or_equal' => 'Tanggal pinjam maksimal 7 hari dari hari ini.',
            'duration_days.in' => 'Pilihan durasi pinjam tidak valid.',
            'duration_days.integer' => 'Pilihan durasi pinjam tidak valid.',
        ];
    }
}