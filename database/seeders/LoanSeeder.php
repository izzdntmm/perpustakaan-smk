<?php

namespace Database\Seeders;

use App\Enums\LoanSource;
use App\Enums\LoanStatus;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        // Hindari data ganda jika seeder dijalankan ulang
        if (Loan::exists()) {
            return;
        }

        $book = fn (string $slug) => Book::where('slug', $slug)->firstOrFail();

        $samples = [
            // Menunggu diambil
            ['laskar-pelangi', 'Ahmad Fauzi', 'XI TKJ 1', LoanStatus::Pending, 0],
            // Sedang dipinjam, masih dalam batas waktu
            ['matematika-kelas-x', 'Siti Aisyah', 'X TKJ 2', LoanStatus::Borrowed, 2],
            // Sedang dipinjam, sudah TERLAMBAT (dipinjam 12 hari lalu)
            ['dasar-dasar-jaringan-komputer', 'Budi Santoso', 'XII TKJ 1', LoanStatus::Borrowed, 12],
            // Sudah dikembalikan
            ['bahasa-indonesia-kelas-xi', 'Nur Hidayah', 'XI TKJ 2', LoanStatus::Returned, 10],
            // Dipinjam guru
            ['bumi-manusia', 'Ustadz Rahmat', 'Guru', LoanStatus::Borrowed, 3],
        ];

        foreach ($samples as [$slug, $name, $class, $status, $daysAgo]) {
            $b = $book($slug);
            $loanDate = today()->subDays($daysAgo);

            $loan = [
                'book_id' => $b->id,
                'borrower_name' => $name,
                'borrower_class' => $class,
                'status' => $status,
                'source' => LoanSource::Online,
                'loan_date' => $loanDate,
                'duration_days' => 7,
            ];

            if ($status !== LoanStatus::Pending) {
                $loan['borrowed_at'] = $loanDate->copy()->setTime(9, 30);
                $loan['due_date'] = $loanDate->copy()->addDays(7);
            }

            if ($status === LoanStatus::Returned) {
                $loan['returned_at'] = $loanDate->copy()->addDays(5)->setTime(10, 0);
            }

            Loan::create($loan);

            // Buku yang sedang dipinjam mengurangi stok rak
            if ($status === LoanStatus::Borrowed) {
                $b->decrement('stock_available');
            }
        }
    }
}