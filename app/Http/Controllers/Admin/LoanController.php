<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookType;
use App\Enums\LoanStatus;
use App\Exceptions\CirculationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalkInLoanRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Services\CirculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    public function __construct(private CirculationService $circulation)
    {
    }

    public function index(Request $request)
    {
        $q = Str::of($this->stringParam($request, 'q'))->squish()->limit(100, '')->toString();

        $status = in_array($request->query('status'), ['pending', 'borrowed', 'all'], true)
            ? $request->query('status')
            : 'pending';

        $query = Loan::with('book')->search($q);

        match ($status) {
            // Yang paling lama menunggu dilayani lebih dulu
            'pending' => $query->pending()->orderBy('created_at')->orderBy('id'),
            // Yang paling dekat/lewat jatuh tempo di atas
            'borrowed' => $query->borrowed()->orderBy('due_date')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        return view('admin.loans.index', [
            'loans' => $query->paginate(15)->withQueryString(),
            'status' => $status,
            'q' => $q,
            'counts' => [
                'pending' => Loan::pending()->count(),
                'borrowed' => Loan::borrowed()->count(),
                'all' => Loan::count(),
            ],
        ]);
    }

    public function create()
    {
        // Hanya buku fisik yang masih punya eksemplar belum dipesan pengajuan lain
        $books = Book::where('type', BookType::Physical->value)
            ->withCount(['loans as pending_count' => fn ($q) => $q->where('status', LoanStatus::Pending->value)])
            ->orderBy('title')
            ->get()
            ->filter(fn (Book $b) => $b->stock_available - $b->pending_count > 0)
            ->values();

        return view('admin.loans.create', compact('books'));
    }

    public function store(WalkInLoanRequest $request)
    {
        $data = $request->validated();

        try {
            $loan = $this->circulation->walkIn(
                (int) $data['book_id'],
                $data['borrower_name'],
                $data['borrower_class'],
                (int) $data['duration_days'],
            );
        } catch (CirculationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.loans.index', ['status' => 'borrowed'])->with('success', sprintf(
            'Peminjaman langsung dicatat: “%s” oleh %s (kode %s). Batas pengembalian %s.',
            $loan->book->title,
            $loan->borrower_name,
            $loan->code(),
            $loan->due_date->translatedFormat('l, d F Y')
        ));
    }

    public function confirm(Loan $loan)
    {
        try {
            $loan = $this->circulation->confirmPickup($loan);
        } catch (CirculationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Pengambilan “%s” oleh %s dikonfirmasi. Batas pengembalian %s.',
            $loan->book->title,
            $loan->borrower_name,
            $loan->due_date->translatedFormat('l, d F Y')
        ));
    }

    public function cancel(Loan $loan)
    {
        try {
            $loan = $this->circulation->cancel($loan);
        } catch (CirculationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Pengajuan {$loan->code()} atas nama {$loan->borrower_name} dibatalkan.");
    }

    private function stringParam(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}