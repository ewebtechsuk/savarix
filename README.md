# Savirix

This repository contains a Laravel application.

## Branching model

The canonical integration branch is `main`. Every push to `main` triggers the
`.github/workflows/sync-master.yml` workflow, which fast-forwards the `master`
branch to the same commit so Hostinger always has a clean production branch to
track. The expectation is:

1. Land features on `main` via pull requests.
2. Allow the workflow to update `master` automatically.
3. Deploy (either through GitHub Actions or manually over SSH) from `master`.

Keeping your local clone on `main` ensures you receive the latest reviewed code,
while checking out `master` locally mirrors exactly what the Aktonz tenant runs
in production. If the Hostinger working copy ever drifts, run:

```bash
cd /home/u753768407/domains/savarix.com/laravel_app
git fetch origin master
git checkout master
git pull --ff-only origin master
```

and then rerun the manual sync script described in the Hostinger deployment
section below.

## Running Artisan Commands

All Artisan commands must be executed from the project root where the `artisan` file lives.
For example, to view the list of available commands run:

```bash
php artisan list
```

You may run any other Artisan command in the same way:

```bash
php artisan migrate
```

## Running Tests

Tests are written with PHPUnit. Run them from the project root after installing PHP dependencies:

```bash
composer install
php artisan test
```

The `php artisan test` wrapper bootstraps the application's testing environment before delegating to PHPUnit. If you prefer to
invoke PHPUnit directly you can use the binary in `vendor/bin`:

```bash
./vendor/bin/phpunit
```

With a global PHPUnit installation you may also run `phpunit` from the project root.

> **Note:** `./setup.sh` and `deploy_hostinger.sh` download a project-local `composer.phar` automatically when Composer isn't available on your PATH, so you can bootstrap the dependencies even in minimal environments (including shared hosting accounts).


## Multi-tenancy configuration

The Stancl Tenancy package now reads its central host list and database connection from the environment so every deployment can declare the values appropriate for that tier:

```
TENANCY_CENTRAL_CONNECTION=${DB_CONNECTION}
TENANCY_CENTRAL_DOMAINS="app.localhost,staging.savirix.com"
```

Use a comma- or whitespace-separated list for `TENANCY_CENTRAL_DOMAINS`, or supply a JSON array (for example `["app.example.com","staging.example.com"]`). The defaults cover typical local development hosts (`127.0.0.1`, `localhost`, `savirix.localhost` and the host portion of `APP_URL`).

Update the variable in each environment (`.env`, GitHub Actions secrets, Hostinger control panel, etc.) so tenancy middleware resolves the central application correctly. Local developers can follow `docs/local_subdomain_setup.txt` for guidance on mapping subdomains via `/etc/hosts`.

### Admin secret path troubleshooting

If the owner admin login unexpectedly 404s, use the playbook in `docs/admin-route-troubleshooting.md` to confirm the route registration, ensure the secret prefix is not double-applied, and verify you are hitting a domain listed in `TENANCY_CENTRAL_DOMAINS`.


### Identity verification (Onfido)

The onboarding flow now provisions an [Onfido](https://onfido.com/) verification session for each new agent company workspace. Configure the following environment variables so the application can authenticate API calls and validate webhook signatures:

```
ONFIDO_API_TOKEN=token_xxx
ONFIDO_WORKFLOW_ID=your-workflow-id
ONFIDO_WEBHOOK_SECRET=whsec_xxx
ONFIDO_BASE_URL=https://api.eu.onfido.com    # Optional when using the EU shard
ONFIDO_API_VERSION=v3.6                     # Optional, defaults to v3.6
ONFIDO_SHARE_LINK_TTL=3600                  # Optional TTL (seconds) for generated share links
```

Use the same webhook secret configured in the Onfido dashboard so callbacks are accepted. Point Onfido webhooks at `https://your-domain/webhooks/onfido`.


### Continuous Integration

A GitHub Actions workflow runs the PHPUnit suite on every push and pull request. The workflow is defined in `.github/workflows/ci.yml`.

## GitHub Pages preview

The static frontend located in `frontend/` is automatically published to GitHub Pages using the workflow in `.github/workflows/pages.yml`.
Push changes to `main` and visit the repository's Pages environment to view the site.

## Marketing bundle builds

Run `npm run build:marketing` to compile the standalone marketing microsite. The marketing Vite config writes the generated HTML, assets, and the duplicated `404.html` fallback to the project root's `dist/` directory. The folder is ignored by Git so you can hand the contents off to the marketing team without polluting commits.

## Deploying to Hostinger

Aktonz production now runs entirely from the Laravel app stored in
`/home/u753768407/domains/savarix.com/laravel_app`. The legacy
`.github/workflows/deploy-hostinger.yml` workflow that pushed a static `dist/`
bundle via `appleboy/scp-action` has been disabled and exists only for
historical reference. The canonical deployment path is the on-server git clone
plus `scripts/hostinger_manual_sync.sh`.

Refer to [docs/deployment/hostinger.md](docs/deployment/hostinger.md) for a full
playbook. The short version:

1. SSH into Hostinger (port `65002`) as `u753768407` and alias PHP to the PHP 8.4
   binary if desired:

   ```bash
   ssh -p 65002 u753768407@<host>
   alias php='/opt/alt/php84/usr/bin/php'
   ```

2. Move to the Laravel app directory and sync `master` so the working copy
   matches GitHub:

   ```bash
   cd /home/u753768407/domains/savarix.com/laravel_app
   git fetch origin master
   git checkout master
   git pull --ff-only origin master
   ```

3. Run the manual sync helper to refresh Composer dependencies, caches, and the
   document root mirror:

   ```bash
   bash scripts/hostinger_manual_sync.sh
   ```

   The script validates the Hostinger directory layout, backs up any
   `laravel_app_core` directory to a timestamped `_backup_YYYYMMDD_HHMM`
   folder, installs dependencies with `/opt/alt/php84/usr/bin/php` and
   `/usr/local/bin/composer`, ensures `.env` contains
   `APP_ENV=production`, `APP_URL=https://aktonz.savarix.com`, and
   `TENANT_DOMAIN=aktonz.savarix.com`, runs the usual artisan cache/migration
   commands, mirrors `public/` into `public_html/`, rewrites
   `public_html/index.php` to bootstrap `../laravel_app/`, and resets
   permissions.

4. Visit https://aktonz.savarix.com/login and tail
   `/home/u753768407/domains/savarix.com/laravel_app/storage/logs/laravel.log`
   after each run to confirm the tenant boots cleanly.

### Tenant and user inspection commands

The following Artisan commands make it easy to confirm that tenancy is configured correctly on Hostinger:

```bash
php artisan tenant:list             # or php artisan tenants:list
php artisan tenant:list --json      # machine-readable tenant summary
php artisan users:list --tenant=aktonz
php artisan users:list --tenant=aktonz --json
```

`users:list` automatically uses the `tenant_id`/`company_id` columns if they exist on the server schema and falls back to a full dump when the columns are missing.

### Aktonz tenant seeding and verification

The `Database\Seeders\AktonzTenantSeeder` class provisions the tenant, domain, company metadata, and an administrator account (`info@aktonz.com` / `AktonzTempPass123!`). Run it locally (or in staging) with:

```bash
php artisan db:seed --class=AktonzTenantSeeder
```

Because production already contains live data, run the seeder there only when you need to recreate the tenant from scratch, and immediately change the temporary password via the UI.

After every deploy, run the following Hostinger checks before handing over to QA:

```bash
alias php='/opt/alt/php84/usr/bin/php'
cd /home/u753768407/domains/savarix.com/laravel_app
php artisan migrate:status
php artisan tenants:list
php artisan users:list --tenant=aktonz
```

Then visit https://aktonz.savarix.com/login, sign in as `info@aktonz.com` with the seeded password, and confirm https://aktonz.savarix.com/dashboard loads without a 500. The feature test `tests/Feature/AktonzTenantLoginTest.php` replicates the same flow locally:

```bash
php artisan test --filter=AktonzTenantLoginTest
```

## Setting up in the Codex environment

To initialize the project when working in Codex or any fresh development container:

1. Ensure the container has internet access to install dependencies. When GitHub rate limits unauthenticated requests, set a
   personal access token in the `GITHUB_TOKEN` environment variable (or add it to `.env`). The setup script and Composer hooks
   will automatically read the token and configure Composer authentication before downloading packages.
2. From the project root make the setup script executable (if it isn't already) and run it:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

The script installs Composer and Node dependencies, copies `.env.example` to `.env` if necessary, generates an application key, runs database migrations and clears caches.
After it finishes you can run tests or start the application with `php artisan serve`.

## Apex27 Import Helper

See `docs/apex27_import.md` for instructions on using the bundled Python script
that can ingest raw Apex27 exports (CSV or JSON) and post them to your Savirix
agent company via the REST API, including automatic creation of contacts, properties,
tenancies and payments.

