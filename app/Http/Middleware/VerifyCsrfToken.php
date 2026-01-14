<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * ملاحظة: هذا حل لتجاوز 419 مع Livewire/Filament عندما تكون الجلسة لا تُحفظ.
     * بعد ما نثبت السبب، ممكن نرجعه كما كان.
     */
    protected $except = [
        'livewire/*',
    ];
}
