# Admin route troubleshooting (secret path + tenancy)

When the admin login unexpectedly 404s, use this checklist to confirm the correct route and domain are being served.

## 1) Confirm Laravel registered the admin login route
Run on the same host where the Laravel app is deployed:

```bash
php artisan route:list | grep admin.login
```

A healthy output will show the secret URI and `admin.login` name, e.g. `GET|HEAD  kjsdahfkjheruwq939201u1asd91/login  …  admin.login`.

- If the path still contains an extra prefix (for example `savarix-admin/kjsdah.../login`), see the next section and remove any outer prefixing.
- If nothing shows up, the admin block is not being loaded on this app/host; double-check you are on the central Laravel domain rather than the marketing frontend.

## 2) Verify the route block uses only the secret prefix
The admin routes should live in `routes/web.php` with a single dynamic prefix and no outer `Route::prefix('savarix-admin')` wrapper:

```php
// Secret Savarix admin path (set in .env as SAVARIX_ADMIN_PATH)
$secretAdminPath = env('SAVARIX_ADMIN_PATH', 'savarix-admin');

Route::prefix($secretAdminPath)->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

    Route::middleware(['auth', 'owner'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/agencies', [AdminAgencyController::class, 'index'])->name('admin.agencies.index');
        Route::post('/agencies', [AdminAgencyController::class, 'store'])->name('admin.agencies.store');
        Route::get('/agencies/{agency}', [AdminAgencyController::class, 'show'])->name('admin.agencies.show');
        Route::put('/agencies/{agency}', [AdminAgencyController::class, 'update'])->name('admin.agencies.update');
        Route::delete('/agencies/{agency}', [AdminAgencyController::class, 'destroy'])->name('admin.agencies.destroy');

        Route::get('/agencies/{agency}/users', [AgencyUserController::class, 'index'])->name('admin.agencies.users.index');
        Route::post('/agencies/{agency}/users', [AgencyUserController::class, 'store'])->name('admin.agencies.users.store');
        Route::delete('/agencies/{agency}/users/{user}', [AgencyUserController::class, 'destroy'])->name('admin.agencies.users.destroy');
    });
});
```

After edits, clear caches to refresh the route list:

```bash
php artisan config:clear
php artisan route:clear
php artisan route:list | grep admin.login
```

The URI should now be just your secret path plus `/login` (for example `kjsdahfkjheruwq939201u1asd91/login`).

## 3) Confirm you are on the central Laravel domain
With tenancy + marketing separation, the central Laravel app may run on a different host than the marketing frontend. Clues from `.env.example`:

- `MARKETING_DOMAINS=savarix.com` (marketing/Next.js)
- `TENANCY_CENTRAL_DOMAINS="127.0.0.1,localhost,savirix.localhost"` (central Laravel)

The marketing 404 page looks different from a Laravel 404; if you see that, you may be hitting the wrong host. Use the domain that serves other Laravel routes (like `/dashboard`) with your secret path, e.g. `https://<central-domain>/<secret>/login`.

If you want the central admin reachable on `savarix.com`, add it to `TENANCY_CENTRAL_DOMAINS` in the deployed `.env`, then clear config and route caches:

```bash
TENANCY_CENTRAL_DOMAINS="127.0.0.1,localhost,savirix.localhost,savarix.com"
php artisan config:clear
php artisan route:clear
```

## 4) Quick checklist
1. `php artisan route:list | grep admin.login`
2. Ensure only the secret prefix block exists in `routes/web.php`
3. Set `SAVARIX_ADMIN_PATH` in the server `.env`
4. Ensure the request is hitting a domain listed in `TENANCY_CENTRAL_DOMAINS`
5. Clear caches and retest the secret path on the central domain
