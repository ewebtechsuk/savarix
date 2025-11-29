# Hostinger deployment guide

Aktonz production runs from the Laravel application checked out on Hostinger at
`/home/u753768407/domains/savarix.com/laravel_app`. The domain
`aktonz.savarix.com` points at the companion document root
`/home/u753768407/domains/savarix.com/public_html`, which simply mirrors the
Laravel `public/` folder and bootstraps the framework that lives one directory
up. The old static workflow that SCP’d a `dist/` folder to Hostinger has been
retired; all deployments now happen from SSH by pulling the git repository and
running `scripts/hostinger_manual_sync.sh`.

## Server layout recap

```
/home/u753768407
└── domains/savarix.com
    ├── laravel_app          # Git clone that contains artisan, vendor/, etc.
    ├── laravel_app_core     # Legacy directory (renamed to *_backup_* during syncs)
    └── public_html          # Document root for aktonz.savarix.com
```

Keep `public_html` as the hPanel document root for the Aktonz subdomain. The
manual sync script copies the freshly-built `public/` assets into that folder and
rewrites `public_html/index.php` so it requires the `../laravel_app/` bootstrap
files.

## Automated GitHub Actions deploy

The `deploy_hostinger.yml` workflow builds Vite with `base: '/build/'`, rsyncs the
repository (excluding vendor/tests/docs/node_modules), and runs
`deploy/deploy_hostinger.sh` on the server. The script now:

- Verifies PHP 8.3+ is available at `/opt/alt/php83/usr/bin/php`.
- Downloads `composer.phar` if missing and runs a `--no-dev` install.
- Warns if `.env` is missing and refuses to continue without one.
- Clears and rebuilds Laravel caches, runs migrations, and executes
  `savarix:diagnose-tenancy-domains --sync` after deploy.
- Enforces the `public_html -> laravel_app/public` symlink via
  `scripts/hostinger-ensure-public-html-symlink.sh`.

Assets under `public/build/` are no longer committed; they are produced during the
GitHub Actions build and copied to Hostinger by rsync.

## Manual deployment workflow

Run the following checklist whenever you want to refresh production:

1. SSH into Hostinger on port `65002` using the Aktonz account and alias PHP to
   the PHP 8.4 binary so Composer and Artisan use the right interpreter:

   ```bash
   ssh -p 65002 u753768407@<host>
   alias php='/opt/alt/php84/usr/bin/php'
   ```

2. Move into the Laravel checkout and fast-forward `master` so it matches
   GitHub:

   ```bash
   cd /home/u753768407/domains/savarix.com/laravel_app
   git fetch origin master
   git checkout master
   git pull --ff-only origin master
   ```

3. Run the manual sync script. It is safe to execute multiple times and now
   enforces the `public_html` symlink instead of copying files:

   ```bash
   bash scripts/hostinger_manual_sync.sh
   ```

4. Confirm the app booted correctly by visiting
   https://aktonz.savarix.com/login, running `php artisan tenants:list`, and
   verifying `php artisan users:list --tenant=aktonz --json` still returns the
   admin (`info@aktonz.com`). Tail the production log as needed:

   ```bash
   tail -f /home/u753768407/domains/savarix.com/laravel_app/storage/logs/laravel.log
   ```

## What `scripts/hostinger_manual_sync.sh` does

The helper script mirrors the manual steps the team previously ran by hand. Each
section logs its progress so you can see which step failed:

1. **Sanity checks** – verifies that `laravel_app/` and `public_html/` exist
   before doing any work and prints the PHP/Composer binaries that will be used.
2. **Legacy backup** – moves any lingering `laravel_app_core` directory to
   `laravel_app_core_backup_YYYYMMDD_HHMM`.
3. **Git sync** – fetches from `origin`, checks out the `master` branch, warns if
   the remote URL is not `git@github.com:ewebtechsuk/savarix.git`, and pulls with
   `--ff-only`.
4. **Composer install** – optionally runs `scripts/composer-token.php`, then
   executes `/opt/alt/php84/usr/bin/php /usr/local/bin/composer install
   --no-dev --optimize-autoloader --no-interaction --prefer-dist`. Failures are
   logged as warnings so you can rerun the step manually if needed.
5. **Environment enforcement** – copies `.env.example` to `.env` if the file is
   missing and ensures the keys `APP_ENV=production`,
   `APP_URL=https://aktonz.savarix.com`, and `TENANT_DOMAIN=aktonz.savarix.com`
   exist via the `update_env_value` helper.
6. **Artisan maintenance** – runs the usual production commands (`key:generate`,
   `migrate --force --no-interaction`, `config:cache`, `route:cache`,
   `view:cache`, `optimize`). Each command is wrapped in `run_or_warn` so the
   script keeps going even if a cache step fails.
7. **public_html symlink** – uses `scripts/hostinger-ensure-public-html-symlink.sh`
   to recreate `/home/${HOST_USER}/domains/savarix.com/public_html` as a symlink
   that targets `laravel_app/public`.
8. **Permissions** – resets ownership to `u753768407`, applies `775` to
   `storage/` and `bootstrap/cache/`, and tightens `public_html/` to `755`.
9. **Domain sync** – runs `savarix:diagnose-tenancy-domains --sync` so newly
   added tenants pick up their domains after deploys.
10. **Summary** – prints the paths in use and reminds you to confirm hPanel’s
   document root, run `php artisan tenants:list`, and inspect the Aktonz users on
   the server.

The script avoids destructive operations and can be re-run immediately after a
failure to complete the remaining steps.

## Troubleshooting checklist

If the site still fails after a sync:

1. **Validate the document root** – ensure `aktonz.savarix.com` points to
   `/home/u753768407/domains/savarix.com/public_html` and that `index.php` loads
   `../laravel_app/vendor/autoload.php` and `../laravel_app/bootstrap/app.php`.
2. **Inspect Laravel logs** – tail the most recent file under
   `storage/logs/` while reproducing the error.
3. **Re-run migrations** – `php artisan migrate --force` catches schema drift.
4. **Clear caches** – `php artisan config:clear`, `route:clear`, `view:clear`,
   then re-run the manual sync script.
5. **Fix permissions** – ensure `storage/` and `bootstrap/cache/` remain writable
   by the Hostinger user.
6. **Verify tenants** – `php artisan tenants:list` and
   `php artisan users:list --tenant=aktonz` should show the expected data.

## Onboarding new tenant domains

1. Add the domain in Stancl tenancy (e.g., via `TenantDomainSynchronizer` or the
   admin UI).
2. In hPanel, point the new subdomain at
   `/home/${HOSTINGER_USER}/domains/savarix.com/public_html`.
3. Run `php artisan savarix:diagnose-tenancy-domains --sync` on the server (or
   wait for the deploy workflow step) to mirror the new domain into the
   application configuration.
4. Hit `https://<new-subdomain>/__tenancy-debug` to confirm Laravel serves the
   tenant rather than Hostinger’s default page.

## PHP version reminders

- Production uses `/opt/alt/php83/usr/bin/php`. Keep Hostinger’s “PHP Version”
  selector on 8.3+ and ensure the CLI default matches.
- If Composer fails with platform errors, confirm `$PHP_BIN` resolves to 8.3 and
  rerun `deploy/deploy_hostinger.sh`.

## Deprecated static workflow

`.github/workflows/deploy-hostinger.yml` previously built a static Vite bundle
and uploaded it via `appleboy/scp-action`. That job timed out consistently and no
longer reflects the production architecture, so it has been restricted to manual
runs only. Do not re-enable it for Aktonz—the Laravel deployment plus
`hostinger_manual_sync.sh` is the supported path going forward.
