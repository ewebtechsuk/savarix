<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof AuthorizationException || $e instanceof UnauthorizedException) {
            $this->logAuthorizationFailure($request);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $guard = $exception->guards()[0] ?? null;
        $adminPath = trim(env('SAVARIX_ADMIN_PATH', 'savarix-admin'), '/');

        if ($request->is($adminPath) || $request->is($adminPath.'/*') || $guard === 'web') {
            return redirect()->guest(route('admin.login'));
        }

        if ($guard === 'tenant') {
            return redirect()->guest(route('tenant.login'));
        }

        return redirect()->guest(route('marketing.home'));
    }

    protected function logAuthorizationFailure($request): void
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName)) {
            return;
        }

        if (! str_starts_with($routeName, 'properties.') && ! str_starts_with($routeName, 'contacts.')) {
            return;
        }

        $user = $request->user();

        Log::info('Authorization denied for tenant route.', [
            'tenant_id' => tenant('id'),
            'route_name' => $routeName,
            'user_id' => $user?->getAuthIdentifier(),
            'roles' => $user?->getRoleNames()->values()->all(),
        ]);
    }
}
