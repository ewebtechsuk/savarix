# Troubleshooting HTTP 500 errors in production

When your deployment shows a generic HTTP 500 error it usually means that PHP hit a fatal exception before any response could be rendered. Follow the checklist below to identify and fix the root cause quickly.

## 1. Look at the Laravel log

```bash
php artisan tail
# or
./scripts/tail_laravel_log.sh 200
# or
cat storage/logs/laravel.log | tail -n 100
```

The stack trace written to `storage/logs/laravel.log` will tell you exactly which exception is being thrown. Always start by reproducing the failure and checking the most recent log entry, then share the relevant snippet when asking for help.

## 2. Verify Composer dependencies

A missing `vendor/autoload.php` is a very common reason for HTTP 500 responses. Make sure dependencies are installed after every deployment:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

If Composer is not available on the server, use the `deploy_hostinger.sh` script from the project root—it installs Composer dependencies automatically.

## 3. Confirm the environment configuration

Check that the production `.env` file exists and contains the correct values for your server. Pay special attention to the following keys:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=` (must not be empty)
- `LOG_CHANNEL=stack`
- Database credentials (`DB_*`), Redis (`REDIS_*`), mail settings, and any third-party API keys.

If `APP_KEY` is missing run:

```bash
php artisan key:generate
```

## 4. Clear stale caches

Cached configuration or routes referencing removed services can also throw 500 errors. After updating environment variables or code, clear Laravel caches:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
```

## 5. Run outstanding migrations

If the application relies on new tables or columns that were not created on the server yet, Laravel will throw database exceptions. Run migrations in production with the `--force` flag so they are executed non-interactively:

```bash
php artisan migrate --force
```

## 6. Fix file permissions

Ensure PHP can write to `storage` and `bootstrap/cache`:

```bash
chmod -R 775 storage bootstrap/cache
```

If you are using shared hosting, both directories may also need their group owner changed to the web server user (for example, `chgrp -R www-data storage bootstrap/cache`).

## 7. Re-deploy if necessary

When you cannot determine the root cause quickly, re-run the official deployment script to rebuild caches and re-publish assets:

```bash
bash deploy_hostinger.sh
```

This script will pull the latest code, install dependencies, generate the application key, run migrations, and set the proper permissions for you.

## 8. Escalate with detailed logs

If the issue persists after running through the checklist, gather the relevant log snippets and configuration details and share them with the team. The more context you provide (error message, stack trace, recent changes), the faster someone can help.

Following these steps should resolve the majority of HTTP 500 issues encountered after deploying this project.
