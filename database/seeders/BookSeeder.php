<?php

namespace Database\Seeders;

use App\Enums\BookType;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::pluck('id', 'slug');

        $books = [
            // ---- Buku Fisik ----
            [
                'category' => 'pelajaran-umum',
                'title' => 'Matematika Kelas X',
                'author' => 'Tim Penulis',
                'publisher' => 'Penerbit Contoh',
                'year_published' => 2021,
                'synopsis' => 'Buku pegangan matematika untuk siswa kelas X, memuat materi dan latihan soal.',
                'type' => BookType::Physical,
                'stock_total' => 5,
                'shelf_location' => 'A-01',
            ],
            [
                'category' => 'pelajaran-umum',
                'title' => 'Bahasa Indonesia Kelas XI',
                'author' => 'Tim Penulis',
                'publisher' => 'Penerbit Contoh',
                'year_published' => 2022,
                'synopsis' => 'Materi teks, tata bahasa, dan keterampilan menulis untuk kelas XI.',
                'type' => BookType::Physical,
                'stock_total' => 4,
                'shelf_location' => 'A-02',
            ],
            [
                'category' => 'produktif-smk',
                'title' => 'Dasar-Dasar Jaringan Komputer',
                'author' => 'Tim Penulis',
                'publisher' => 'Penerbit Contoh',
                'year_published' => 2020,
                'synopsis' => 'Pengantar konsep jaringan, topologi, pengalamatan IP, dan konfigurasi dasar.',
                'type' => BookType::Physical,
                'stock_total' => 3,
                'shelf_location' => 'B-01',
            ],
            [
                'category' => 'produktif-smk',
                'title' => 'Pemrograman Web Dasar',
                'author' => 'Tim Penulis',
                'publisher' => 'Penerbit Contoh',
                'year_published' => 2023,
                'synopsis' => 'Belajar HTML, CSS, dan JavaScript dari dasar untuk siswa jurusan komputer.',
                'type' => BookType::Physical,
                'stock_total' => 2,
                'shelf_location' => 'B-02',
            ],
            [
                'category' => 'fiksi',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'year_published' => 2005,
                'synopsis' => 'Novel tentang perjuangan sepuluh anak di Belitung untuk tetap bersekolah.',
                'type' => BookType::Physical,
                'stock_total' => 3,
                'shelf_location' => 'C-01',
            ],
            [
                'category' => 'fiksi',
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'year_published' => 1980,
                'synopsis' => 'Novel sejarah berlatar masa kolonial yang mengikuti kisah Minke.',
                'type' => BookType::Physical,
                'stock_total' => 1,
                'shelf_location' => 'C-02',
            ],
            [
                'category' => 'referensi',
                'title' => 'Kamus Besar Bahasa Indonesia',
                'author' => 'Badan Bahasa',
                'synopsis' => 'Kamus rujukan utama bahasa Indonesia. Dibaca di tempat, tidak untuk dipinjam bawa pulang.',
                'type' => BookType::Physical,
                'stock_total' => 1,
                'shelf_location' => 'R-01',
            ],

            // ---- Buku Digital (PDF diunggah belakangan lewat admin) ----
            [
                'category' => 'pelajaran-umum',
                'title' => 'Pendidikan Pancasila (E-Book)',
                'author' => 'Tim Penulis',
                'publisher' => 'Penerbit Contoh',
                'year_published' => 2022,
                'synopsis' => 'Versi digital buku Pendidikan Pancasila untuk dibaca langsung di browser.',
                'type' => BookType::Digital,
            ],
            [
                'category' => 'produktif-smk',
                'title' => 'Desain Grafis untuk Pemula (E-Book)',
                'author' => 'Tim Penulis',
                'publisher' => 'Penerbit Contoh',
                'year_published' => 2023,
                'synopsis' => 'Panduan dasar desain grafis, warna, tipografi, dan tata letak.',
                'type' => BookType::Digital,
            ],
            [
                'category' => 'referensi',
                'title' => 'Panduan Penulisan Karya Ilmiah (E-Book)',
                'author' => 'Tim Penulis',
                'year_published' => 2021,
                'synopsis' => 'Pedoman menyusun laporan dan karya tulis ilmiah untuk siswa.',
                'type' => BookType::Digital,
            ],
        ];

        foreach ($books as $data) {
            $categoryId = $cat[$data['category']];
            unset($data['category']);

            $isPhysical = $data['type'] === BookType::Physical;
            $stock = $isPhysical ? ($data['stock_total'] ?? 0) : 0;

            Book::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                $data + [
                    'category_id' => $categoryId,
                    'stock_total' => $stock,
                    'stock_available' => $stock,
                ]
            );
        }
        // Buku rekomendasi pustakawan (contoh)
        Book::whereIn('slug', [
            'pemrograman-web-dasar',
            'desain-grafis-untuk-pemula-e-book',
            'laskar-pelangi',
            'bahasa-indonesia-kelas-xi',
        ])->update(['is_featured' => true]);

        // File PDF contoh untuk semua buku digital
        Book::where('type', BookType::Digital->value)
            ->update(['pdf_path' => 'books/contoh.pdf']);
    }
}