<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kesalahan yang aman ditampilkan ke pustakawan (stok habis, status sudah berubah, dsb).
 */
class CirculationException extends RuntimeException
{
}