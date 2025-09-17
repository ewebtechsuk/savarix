<?php

namespace App\Http\Controllers\Auth;

use App\Core\Application;
use Framework\Http\Request;
use Framework\Http\Response;

class LoginController
{
    public function show(Request $request, array $context): Response
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        /** @var Application $app */
        $app = $context['app'];
        $content = $app->view('auth.login');
        return Response::view($content);
    }
}
