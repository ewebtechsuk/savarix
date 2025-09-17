<?php

namespace App\Http\Controllers;

use App\Core\Application;
use Framework\Http\Request;
use Framework\Http\Response;

class DashboardController
{
    public function index(Request $request, array $context): Response
    {
        /** @var Application $app */
        $app = $context['app'];
        $user = $app->auth()->user();
        $content = $app->view('dashboard.index', ['user' => $user]);
        return Response::view($content);
    }
}
