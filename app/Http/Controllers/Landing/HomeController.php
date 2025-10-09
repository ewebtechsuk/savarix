<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the marketing landing page.
     */
    public function __invoke(): View
    {
        return view('landing.home');
    }
}
