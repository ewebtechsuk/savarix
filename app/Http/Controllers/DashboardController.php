<?php

namespace App\Http\Controllers;

use App\Core\Application;
use Framework\Http\Request;
use Framework\Http\Response;
use Illuminate\Support\Facades\Auth;

class DashboardController
{
    public function index(Request $request, array $context): Response
    {
        /** @var Application $app */
        $app = $context['app'];
        $user = Auth::user();
        $content = $app->view('dashboard.index', ['user' => $user]);
        return Response::view($content);
    }
}
