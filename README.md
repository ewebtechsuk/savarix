# ressapp

This repository contains a Laravel application.

## Branching model

The canonical integration branch is `main`. The historic `master` branch is no
longer used in this repository, so you will not see it locally after cloning.
When the hosting instructions or older automation scripts mention `master`,
substitute `main` instead (for example, `git checkout main` or `git pull origin
main`). Keeping your local clone on the `main` branch ensures you receive the
latest code that powers production deployments.

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

> **Note:** `./setup.sh` and `deploy_hostinger.sh` download a project-local `composer.phar` automatically when Composer isn't available on your PATH, so you can bootstrap the dependencies even in minimal environments (including shared hosting accounts).


## Multi-tenancy configuration

The Stancl Tenancy package now reads its central host list and database connection from the environment so every deployment can declare the values appropriate for that tier:

```
TENANCY_CENTRAL_CONNECTION=${DB_CONNECTION}
TENANCY_CENTRAL_DOMAINS="app.localhost,staging.ressapp.io"
```

Use a comma- or whitespace-separated list for `TENANCY_CENTRAL_DOMAINS`, or supply a JSON array (for example `["app.example.com","staging.example.com"]`). The defaults cover typical local development hosts (`127.0.0.1`, `localhost`, `ressapp.localhost` and the host portion of `APP_URL`).

Update the variable in each environment (`.env`, GitHub Actions secrets, Hostinger control panel, etc.) so tenancy middleware resolves the central application correctly. Local developers can follow `docs/local_subdomain_setup.txt` for guidance on mapping subdomains via `/etc/hosts`.


### Identity verification (Onfido)

The onboarding flow now provisions an [Onfido](https://onfido.com/) verification session for each new tenant. Configure the following environment variables so the application can authenticate API calls and validate webhook signatures:

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

Pushes to `main` automatically trigger `.github/workflows/deploy-hostinger.yml`, which builds production assets and uploads the
application to your Hostinger account. Configure the following repository secrets before enabling the workflow (either alias
column may be used):

| Secret (choose one name per row) | Required | Description |
| --- | --- | --- |
| `HOSTINGER_FTP_HOST` **or** `HOSTINGER_FTP_SERVER` **or** `FTP_SERVER` **or** `FTP_HOST` | ✅ | Hostname of your Hostinger FTP/SFTP server. |
| `HOSTINGER_FTP_USERNAME` **or** `HOSTINGER_FTP_USER` **or** `FTP_USERNAME` **or** `FTP_USER` | ✅ | Username that has write access to the deployment directory. |
| `HOSTINGER_FTP_PASSWORD` **or** `HOSTINGER_FTP_PASS` **or** `FTP_PASSWORD` **or** `FTP_PASS` | ✅ | Password or app token for the account above. |

| `HOSTINGER_FTP_TARGET_DIR` **or** `FTP_TARGET_DIR` | ✅ | Remote path to your Laravel application's root (for example `domains/example.com/public_html/`). |
| `HOSTINGER_FTP_PORT` **or** `FTP_PORT` | ❌ | Override the default port (`21`). The workflow falls back to `22` when the protocol is set to SFTP. |
| `HOSTINGER_FTP_PROTOCOL` **or** `FTP_PROTOCOL` | ❌ | Transfer protocol (`ftps` by default). Accepts `ftp`, `ftps`, or `sftp` (case-insensitive). |

Always populate these secrets with the real values from your Hostinger control panel—placeholders such as `***`,
`your.hostinger.server`, or `ftp.example.com` will be rejected before any connection attempt. The workflow fails fast with a
clear error message when any required secret is missing so you can correct the configuration before an upload attempt.

> **Composer access tokens:** If Composer warns about missing GitHub authentication while resolving dependencies, add a
> `GITHUB_TOKEN` repository secret (or set it as an Actions variable). The workflow automatically picks it up so private
> packages can be installed during the build.


- **FTP/FTPS** deployments run through [`SamKirkland/FTP-Deploy-Action`](https://github.com/SamKirkland/FTP-Deploy-Action), which
  keeps a `.ftp-deploy-sync-state.json` file on the server to synchronise only changed files between runs.
- **SFTP** deployments automatically stage a scrubbed copy of the repository (matching the FTP exclude rules) and upload it with
  [`appleboy/scp-action`](https://github.com/appleboy/scp-action). Because SFTP uploads cannot reference the sync state file,
  deletions need to be handled manually on the server if files are removed from version control.

> **Tip:** Non-sensitive settings such as host, protocol, or target directory may be stored in GitHub Actions _variables_ as well
> as secrets—the workflow checks both contexts. Keep credentials (username/password) in secrets for security. Regardless of
> where you store the values, trim whitespace and use `ftp`, `ftps`, or `sftp` for the protocol. The deploy step normalises
> inputs like `SFTP://` automatically.


After the workflow finishes, the state file `.ftp-deploy-sync-state.json` stored on the server keeps future deployments fast by
syncing only changed files. Clean up any old log files or caches in `storage/` directly on the server if required—the workflow
omits them from uploads.

> **Hostinger tip:** If your shared plan does not expose a global `composer` command, SSH into the server and run `./deploy_hostinger.sh` from the project root after each pull. The script now bootstraps a local `composer.phar`, installs dependencies, and clears the caches so the public site won't fall back to a generic HTTP 500 error.

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

