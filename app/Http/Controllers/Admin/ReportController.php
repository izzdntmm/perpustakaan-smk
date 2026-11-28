<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Services\ReportService;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Batas baris pada tampilan layar dan cetak. CSV selalu lengkap. */
    private const VIEW_LIMIT = 1500;

    public function __construct(private ReportService $reports)
    {
    }

    public function index(ReportRequest $request)
    {
        return view('admin.reports.index', $this->data($request) + [
            'categories' => Category::orderBy('id')->get(),
            'academicYears' => $this->reports->academicYears(),
        ]);
    }

    public function print(ReportRequest $request)
    {
        return view('admin.reports.print', $this->data($request) + [
            'printedAt' => now(),
            'printedBy' => auth()->user()->name,
        ]);
    }

    public function export(ReportRequest $request): StreamedResponse
    {
        $in = $request->validated();

        if (($in['jenis'] ?? null) === 'buku') {
            $query = $this->reports->books($in['kategori'] ?? null, $in['tipe'] ?? null)->orderBy('title');
            $filename = 'laporan-daftar-buku-'.today()->toDateString().'.csv';

            $header = ['No', 'Judul', 'Penulis', 'Kategori', 'Jenis', 'Penerbit', 'Tahun Terbit',
                'ISBN', 'Nomor Rak', 'Total Eksemplar', 'Tersedia di Rak', 'Sedang Dipinjam'];

            $map = fn (Book $b, int $no) => [
                $no, $b->title, $b->author, $b->category->name, $b->type->label(),
                $b->publisher ?? '', $b->year_published ?? '', $b->isbn ?? '', $b->shelf_location ?? '',
                $b->isPhysical() ? $b->stock_total : '',
                $b->isPhysical() ? $b->stock_available : '',
                $b->isPhysical() ? $b->stock_total - $b->stock_available : '',
            ];
        } else {
            $period = $this->reports->period($in);
            $query = $this->reports->loans($period['from'], $period['to'], $in['status'] ?? 'all');
            $filename = 'laporan-peminjaman-'.Str::slug($period['label']).'.csv';

            $header = ['No', 'Kode', 'Tanggal Pinjam', 'Nama Peminjam', 'Kelas/Jabatan', 'Judul Buku',
                'Jatuh Tempo', 'Tanggal Kembali', 'Status', 'Terlambat (hari)', 'Sumber'];

            $map = fn (Loan $l, int $no) => [
                $no, $l->code(), $l->loan_date->toDateString(), $l->borrower_name, $l->borrower_class,
                $l->book->title, $l->due_date?->toDateString() ?? '', $l->returned_at?->toDateString() ?? '',
                $l->isOverdue() ? 'Terlambat' : $l->status->label(),
                $l->lateDays(), $l->source->label(),
            ];
        }

        return response()->streamDownload(function () use ($query, $header, $map) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // penanda UTF-8 agar Excel membaca huruf dengan benar

            $write = fn (array $row) => fputcsv($out, array_map($this->safeCell(...), $row), ';', '"', '');

            $write($header);

            $no = 0;
            foreach ($query->lazy(500) as $item) {
                $write($map($item, ++$no));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /* ---------- Helper ---------- */

    /** Data bersama untuk tampilan layar dan cetak. */
    private function data(ReportRequest $request): array
    {
        $in = $request->validated();
        $jenis = ($in['jenis'] ?? null) === 'buku' ? 'buku' : 'peminjaman';

        $params = array_filter($in, fn ($v) => filled($v));
        $params['jenis'] = $jenis;

        if ($jenis === 'buku') {
            $category = $in['kategori'] ?? null;
            $type = $in['tipe'] ?? null;

            $query = $this->reports->books($category, $type);
            $total = (clone $query)->count();

            return [
                'jenis' => 'buku',
                'title' => 'Laporan Daftar Buku',
                'subtitle' => 'Kondisi koleksi per '.today()->translatedFormat('d F Y'),
                'period' => null,
                'rows' => (clone $query)->orderBy('title')->limit(self::VIEW_LIMIT)->get(),
                'summary' => $this->reports->bookSummary($category, $type),
                'topBooks' => collect(),
                'total' => $total,
                'truncated' => $total > self::VIEW_LIMIT,
                'limit' => self::VIEW_LIMIT,
                'params' => $params,
                'form' => ['kategori' => $category, 'tipe' => $type],
            ];
        }

        $period = $this->reports->period($in);
        $status = $in['status'] ?? 'all';

        $query = $this->reports->loans($period['from'], $period['to'], $status);
        $total = (clone $query)->count();

        $subtitle = $period['label'];
        if ($status !== 'all') {
            $subtitle .= ' | Status: '.ReportService::STATUSES[$status];
        }

        return [
            'jenis' => 'peminjaman',
            'title' => 'Laporan Riwayat Peminjaman',
            'subtitle' => $subtitle,
            'period' => $period,
            'rows' => $query->limit(self::VIEW_LIMIT)->get(),
            'summary' => $this->reports->loanSummary($period['from'], $period['to']),
            'topBooks' => $this->reports->topBooks($period['from'], $period['to']),
            'total' => $total,
            'truncated' => $total > self::VIEW_LIMIT,
            'limit' => self::VIEW_LIMIT,
            'params' => $params,
            'form' => [
                'periode' => $period['mode'],
                'bulan' => $in['bulan'] ?? now()->format('Y-m'),
                'ta' => (int) ($in['ta'] ?? $this->reports->defaultAcademicYear()),
                'semester' => $in['semester'] ?? $this->reports->defaultSemester(),
                'dari' => $in['dari'] ?? now()->startOfMonth()->toDateString(),
                'sampai' => $in['sampai'] ?? today()->toDateString(),
                'status' => $status,
            ],
        ];
    }

    /** Cegah rumus berbahaya di Excel (CSV injection). */
    private function safeCell(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}