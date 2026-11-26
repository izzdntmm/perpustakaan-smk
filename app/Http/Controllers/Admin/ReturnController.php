<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LoanStatus;
use App\Exceptions\CirculationException;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\CirculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReturnController extends Controller
{
    public function __construct(private CirculationService $circulation)
    {
    }

    public function index(Request $request)
    {
        $value = $request->query('q');
        $q = Str::of(is_string($value) ? $value : '')->squish()->limit(100, '')->toString();

        $tab = $request->query('tab') === 'history' ? 'history' : 'active';

        $query = Loan::with('book')->search($q);

        if ($tab === 'history') {
            $query->where('status', LoanStatus::Returned->value)->orderByDesc('returned_at');
        } else {
            // Yang paling lama terlambat berada di atas
            $query->borrowed()->orderBy('due_date')->orderBy('id');
        }

        return view('admin.returns.index', [
            'loans' => $query->paginate(15)->withQueryString(),
            'tab' => $tab,
            'q' => $q,
            'counts' => [
                'active' => Loan::borrowed()->count(),
                'overdue' => Loan::overdue()->count(),
                'history' => Loan::where('status', LoanStatus::Returned->value)->count(),
            ],
        ]);
    }

    public function update(Loan $loan)
    {
        // Hitung sebelum status berubah
        $daysLate = $loan->daysOverdue();

        try {
            $loan = $this->circulation->markReturned($loan);
        } catch (CirculationException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "Buku “{$loan->book->title}” dari {$loan->borrower_name} sudah dikembalikan.";

        if ($daysLate > 0) {
            $message .= " Terlambat {$daysLate} hari.";
        }

        return back()->with('success', $message);
    }
}