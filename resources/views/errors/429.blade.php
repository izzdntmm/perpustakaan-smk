@include('errors.page', [
    'code' => 429,
    'icon' => 'clock',
    'title' => 'Terlalu banyak permintaan',
    'message' => 'Kamu terlalu sering mengirim permintaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.',
])