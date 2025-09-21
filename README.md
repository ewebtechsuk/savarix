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
