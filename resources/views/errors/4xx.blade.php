@include('errors.page', [
    'code' => $exception->getStatusCode(),
    'icon' => 'exclamation-circle',
    'title' => 'Permintaan tidak dapat diproses',
    'message' => 'Ada masalah pada permintaan kamu. Silakan kembali dan coba lagi.',
])