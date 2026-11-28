@include('errors.page', [
    'code' => $exception->getStatusCode(),
    'icon' => 'exclamation-triangle',
    'title' => 'Terjadi kesalahan di server',
    'message' => 'Maaf, ada masalah di sistem kami. Coba lagi beberapa saat lagi.',
])