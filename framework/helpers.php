<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('auth')) {
    function auth(?string $guard = null) {
        return Auth::guard($guard);
    }
}
