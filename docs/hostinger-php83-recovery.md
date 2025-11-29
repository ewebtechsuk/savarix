# Hostinger PHP 8.3 recovery runbook

Use these steps on the Hostinger SSH shell to restore the Laravel app with PHP 8.3, reinstall Composer dependencies, and sync tenancy data. Commands assume the project lives at `~/domains/savarix.com/laravel_app` and you are running them from that directory unless noted otherwise.

See also: [Hostinger public_html routing fix](hostinger-public-html-routing.md).

> Frontend assets (`public/build`) are built automatically by the GitHub Actions deploy workflow at [`.github/workflows/deploy_hostinger.yml`](../.github/workflows/deploy_hostinger.yml). Manual `npm run build` on Hostinger isn't normally required; if dashboards render unstyled, first check the workflow logs for the Vite build step.

## 0) Switch to the project directory

```bash
cd ~/domains/savarix.com/laravel_app
```

## 1) Confirm PHP 8.3 is used for CLI

```bash
php -v
/opt/alt/php83/usr/bin/php -v
```

* If `php -v` still shows 8.2, either add `alias php=/opt/alt/php83/usr/bin/php` to `~/.bashrc` and re-open the shell, or prefix all commands below with `/opt/alt/php83/usr/bin/php`.
* In hPanel, also set the **Web PHP version** to **8.3** so requests use the same runtime.

## 2) Reinstall Composer dependencies with PHP 8.3

From `~/domains/savarix.com/laravel_app` run:

```bash
rm -rf vendor
rm -f composer.lock
```

Check for a global Composer and its PHP context:

```bash
which composer
/opt/alt/php83/usr/bin/php $(which composer) --version
```

If there is no global Composer, download a local copy:

```bash
/opt/alt/php83/usr/bin/php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
/opt/alt/php83/usr/bin/php composer-setup.php --install-dir=. --filename=composer.phar
/opt/alt/php83/usr/bin/php -r "unlink('composer-setup.php');"
```

Install dependencies (use **one** of the following depending on whether you have `composer.phar` or a global Composer):

```bash
# Using local composer.phar
/opt/alt/php83/usr/bin/php composer.phar install --no-dev --optimize-autoloader

# Using global Composer
/opt/alt/php83/usr/bin/php $(which composer) install --no-dev --optimize-autoloader
```

> The install must finish without platform errors such as `requires PHP >= 8.3` before running Artisan commands.

## 3) Clear Laravel caches after install

With PHP 8.3 in use (either via alias or explicit path), run:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

(If `php` is not aliased, prefix each command with `/opt/alt/php83/usr/bin/php`.)

## 4) Sync agencies to tenants/domains

Populate Stancl tenancy data from agencies using the existing helper:

```bash
php artisan savarix:diagnose-tenancy-domains --sync
```

Expected output:

* Central/primary domains listed for reference.
* A table showing each agency, the tenant key it maps to, and domains that will be created.
* Log lines such as `Agency #1 (Aktonz) synced to tenant aktonz`, `Agency #2 (London Capital Investments) synced to tenant londoncapitalinvestments`, etc.

## 5) Verify tenancy state with Tinker

Open Tinker and confirm tenants/domains now exist:

```bash
php artisan tinker
```

Inside tinker:

```php
use Stancl\Tenancy\Database\Models\Domain;
Domain::pluck('domain')->all();

use Stancl\Tenancy\Database\Models\Tenant;
Tenant::all(['id'])->toArray();
```

Healthy output shows non-empty arrays with domains like `aktonz.savarix.com`, `londoncapitalinvestments.savarix.com` and matching tenant IDs.

## 6) Final smoke tests

1. Log into the central admin at https://savarix.com/login.
2. Impersonate a tenant (e.g., Aktonz or London Capital Investments).
3. Visit the dashboards:
   * https://aktonz.savarix.com/dashboard
   * https://londoncapitalinvestments.savarix.com/dashboard
4. If a dashboard fails, tail the log to confirm Laravel is handling the request and there are no PHP version/platform errors:

```bash
tail -n 100 storage/logs/laravel.log
```

## Optional: helper script

For a streamlined reinstall on Hostinger, you can run the helper script after reviewing it:

```bash
bash scripts/hostinger-php83-reinstall.sh
```

It removes `vendor` and `composer.lock`, reinstalls dependencies with PHP 8.3, and clears caches. It is intended for manual use only and is not part of CI/CD.
