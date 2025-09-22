# ressapp

This repository contains a Laravel application.

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

Tests are written with PHPUnit. After installing dependencies with Composer run the test suite from the project root:


```bash
./vendor/bin/phpunit
```

If you have PHPUnit installed globally you can simply run `phpunit` instead.


### Continuous Integration

A GitHub Actions workflow runs the PHPUnit suite on every push and pull request. The workflow is defined in `.github/workflows/ci.yml`.

## GitHub Pages preview

The static frontend located in `frontend/` is automatically published to GitHub Pages using the workflow in `.github/workflows/pages.yml`.
Push changes to `main` and visit the repository's Pages environment to view the site.

## Deploying to Hostinger

Pushes to `main` automatically trigger `.github/workflows/deploy-hostinger.yml`, which builds production assets and uploads the
application to your Hostinger account. Configure the following repository secrets before enabling the workflow (either alias
column may be used):

| Secret (choose one name per row) | Required | Description |
| --- | --- | --- |
| `HOSTINGER_FTP_HOST` **or** `FTP_SERVER` | ✅ | Hostname of your Hostinger FTP/SFTP server. |
| `HOSTINGER_FTP_USERNAME` **or** `FTP_USERNAME` | ✅ | Username that has write access to the deployment directory. |
| `HOSTINGER_FTP_PASSWORD` **or** `FTP_PASSWORD` | ✅ | Password or app token for the account above. |
| `HOSTINGER_FTP_TARGET_DIR` **or** `FTP_TARGET_DIR` | ✅ | Remote path to your Laravel application's root (for example `domains/example.com/public_html/`). |
| `HOSTINGER_FTP_PORT` **or** `FTP_PORT` | ❌ | Override the default port (`21`). The workflow falls back to `22` when the protocol is set to SFTP. |
| `HOSTINGER_FTP_PROTOCOL` **or** `FTP_PROTOCOL` | ❌ | Transfer protocol (`ftps` by default). Accepts `ftp`, `ftps`, or `sftp` (case-insensitive). |


The workflow fails fast with a clear error message when any required secret is missing so you can correct the configuration
before an upload attempt. Double-check that the resolved server host points to the correct Hostinger instance; an empty or
placeholder hostname causes the FTP action to abort with `getaddrinfo ENOTFOUND`.

> **Tip:** Non-sensitive settings such as host, protocol, or target directory may be stored in GitHub Actions _variables_ as well
> as secrets—the workflow checks both contexts. Keep credentials (username/password) in secrets for security. Regardless of
> where you store the values, trim whitespace and use `ftp`, `ftps`, or `sftp` for the protocol. The deploy step normalises
> inputs like `SFTP://` automatically.


After the workflow finishes, the state file `.ftp-deploy-sync-state.json` stored on the server keeps future deployments fast by
syncing only changed files. Clean up any old log files or caches in `storage/` directly on the server if required—the workflow
omits them from uploads.

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
that can ingest raw Apex27 exports (CSV or JSON) and post them to your Ressapp
tenant via the REST API, including automatic creation of contacts, properties,
tenancies and payments.

