<?php

namespace App\Http\Controllers;

use App\Core\Application;
use App\Tenancy\TenantDirectory;
use Framework\Http\Request;
use Framework\Http\Response;
use Illuminate\Support\Facades\Auth;

class TenantPortalController
{
    public function login(Request $request, array $context): Response
    {
        /** @var Application $app */
        $app = $context['app'];

        $content = $app->view('tenant.login');

        return Response::view($content);
    }

    public function dashboard(Request $request, array $context): Response
    {
        /** @var Application $app */
        $app = $context['app'];
        $user = Auth::guard('tenant')->user();

        $content = $app->view('tenant.dashboard', [
            'user' => $user,
        ]);

        return Response::view($content);
    }

    public function list(Request $request, array $context): Response
    {
        $directory = new TenantDirectory();
        $tenants = $directory->all();

        /** @var Application $app */
        $app = $context['app'];

        $content = $app->view('tenant.list', [
            'tenants' => $tenants,
        ]);

        return Response::view($content);
    }
}
