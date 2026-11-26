<?php

namespace App\Http\Requests;

use Illuminate\Support\Arr;

class WalkInLoanRequest extends StoreLoanRequest
{
    public function rules(): array
    {
        return Arr::except(parent::rules(), 'loan_date') + [
            'book_id' => ['required', 'integer', 'exists:books,id'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['book_id' => 'buku'];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'exists' => ':Attribute tidak valid.',
        ];
    }
}