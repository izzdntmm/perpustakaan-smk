<?php

namespace App\Http\Controllers;

use App\Enums\LoanSource;
use App\Enums\LoanStatus;
use App\Http\Requests\StoreLoanRequest;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function store(StoreLoanRequest $request, Book $book)
    {
        abort_unless($book->isPhysical(), 404);

        $data = $request->validated();

        $result = DB::transaction(function () use ($book, $data) {
            // Kunci baris buku: dua siswa yang mengirim bersamaan
            // untuk eksemplar terakhir akan diproses bergantian.
            $locked = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();

            if ($locked->borrowableStock() < 1) {
                return ['error' => 'Maaf, stok buku ini baru saja habis diajukan. Silakan coba buku lain.'];
            }

            $duplicate = $locked->loans()->pending()
                ->where('borrower_name', $data['borrower_name'])
                ->where('borrower_class', $data['borrower_class'])
                ->exists();

            if ($duplicate) {
                return ['error' => 'Kamu sudah mengajukan buku ini dan masih menunggu diambil di pustakawan.'];
            }

            $loan = $locked->loans()->create([
                'borrower_name' => $data['borrower_name'],
                'borrower_class' => $data['borrower_class'],
                'status' => LoanStatus::Pending,
                'source' => LoanSource::Online,
                'loan_date' => $data['loan_date'],
                'duration_days' => $data['duration_days'],
            ]);

            return ['loan' => $loan];
        });

        if (isset($result['error'])) {
            return redirect()->route('books.show', $book)->with('error', $result['error']);
        }

        $loan = $result['loan'];

        return redirect()->route('books.show', $book)->with('success', sprintf(
            'Pengajuan atas nama %s berhasil dicatat (kode %s). Silakan ambil buku di meja pustakawan pada %s.',
            $loan->borrower_name,
            $loan->code(),
            $loan->loan_date->translatedFormat('l, d F Y')
        ));
    }
}