<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class SanitizeInput extends TransformsRequest
{
    /**
     * Field yang dikecualikan dari sanitasi.
     * Field password tidak boleh di-strip karena bisa mengandung karakter khusus.
     *
     * @var array<int, string>
     */
    protected $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
    ];

    /**
     * Transform the given value.
     *
     * Membersihkan input string dari tag HTML, trim whitespace,
     * dan mengkonversi string kosong menjadi null.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        // Skip field yang dikecualikan
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        // Hanya proses nilai string
        if (! is_string($value)) {
            return $value;
        }

        // Strip HTML tags untuk mencegah XSS
        $value = strip_tags($value);

        // Trim whitespace
        $value = trim($value);

        // Konversi string kosong menjadi null
        return $value === '' ? null : $value;
    }
}
