<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // tambahkan route pattern yang mau dilepas dari CSRF
    protected $except = [
        'attachments/*',
        'tickets/*/attachments',
    ];
}
