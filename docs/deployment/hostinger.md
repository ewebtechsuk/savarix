# Hostinger deployment guide

This document covers everything needed to deploy the Laravel application to Hostinger and recover from the most common runtime failures (for example, HTTP 500 errors). Follow the sections in order the first time you configure a new environment, then refer back to the troubleshooting checklist whenever something goes wrong after a deploy.

## Prerequisites

Before touching GitHub or Hostinger make sure you have:

- A Hostinger shared hosting or cloud account with the target domain added.
- FTP (or SFTP) credentials that can read and write the site directory.
- SSH access enabled in hPanel so you can run the deployment script from the server shell.
- A GitHub repository with the application code. The `main` branch should contain the version you want online.

Once those basics are in place continue with the steps below.

## 1. Configure GitHub secrets for the workflow

GitHub automatically runs the **Deploy to Hostinger** workflow when commits land on `main`. The job uploads the built application to Hostinger by using standard FTP/FTPS/SFTP credentials that are stored as repository secrets. When those secrets are absent or empty the deployment job fails during the pre-upload cleanup step with an error similar to:

```
Missing required environment variable FTP_CLEANUP_HOST. Populate the repository secret HOSTINGER_FTP_HOST (or FTP_SERVER) with the FTP hostname shown in Hostinger's hPanel under Websites → Manage → FTP Accounts.
```

To fix the failure you must add the Hostinger connection details to the repository's secrets (or organisation level variables). The workflow looks for the following values, preferring the `HOSTINGER_` names but accepting the legacy `FTP_` names as a fallback:

| Secret/variable | Purpose | Where to find it in Hostinger |
| --- | --- | --- |
| `HOSTINGER_FTP_HOST` (or `FTP_SERVER`/`FTP_HOST`) | FTP hostname used by the deployment and cleanup scripts. | Log in to [hPanel](https://hpanel.hostinger.com/) → **Websites** → **Manage** for the correct site → **Files → FTP Accounts**. Copy the host **without** the `ftp://` prefix shown in hPanel (for example, `darkorange-chinchilla-918430.hostingersite.com`). |
| `HOSTINGER_FTP_USERNAME` (or `HOSTINGER_FTP_USER`/`FTP_USERNAME`/`FTP_USER`) | FTP username that has access to the deployment directory. | Same FTP Accounts page in hPanel. Use the username column or create a new FTP account if needed. |
| `HOSTINGER_FTP_PASSWORD` (or `HOSTINGER_FTP_PASS`/`FTP_PASSWORD`/`FTP_PASS`) | Password for the FTP user. | Either copy the password you set when creating the FTP account or click **Change account password** on the FTP Accounts page to generate a new one. |
| `HOSTINGER_FTP_TARGET_DIR` (or `FTP_TARGET_DIR`) | Remote directory to upload into. | On Hostinger shared hosting this is usually `public_html/` (include the trailing slash). Adjust if your application lives in a subdirectory. |
| `HOSTINGER_FTP_PROTOCOL` (or `FTP_PROTOCOL`) | Connection protocol. | Hostinger supports `ftps` (explicit TLS) for most plans. Use `sftp` only if you enabled SSH access and created an SFTP account. Leave blank to default to `ftps`. The cleanup script runs only for FTP/FTPS uploads. |
| `HOSTINGER_FTP_PORT` (or `FTP_PORT`) | Port for the selected protocol. | Hostinger uses port 21 for FTP/FTPS and 22 for SFTP. Set this only if Hostinger support instructs you to use a different port. |

Add each item as a **Repository secret** (Settings → Secrets and variables → Actions → *New repository secret*). Organisation owners can also define them as organisation secrets or variables if the same credentials are shared across multiple repositories.

If you have the hPanel FTP details open (like in the screenshot the team shared), copy each field straight into GitHub:

1. Open your repository on GitHub and navigate to **Settings → Secrets and variables → Actions → New repository secret**.
2. Add a secret named **`HOSTINGER_FTP_HOST`** using the value from the **FTP Hostname** field (for example `darkorange-chinchilla-918430.hostingersite.com`). Make sure you copy *only* the host—remove any `ftp://` prefix.
3. Add **`HOSTINGER_FTP_USERNAME`** with the username shown in the FTP list (`u1234567`, etc.).
4. Add **`HOSTINGER_FTP_PASSWORD`**. If you do not have it, click **Change password** in hPanel, set a new password, and paste that fresh value into the secret immediately.
5. Add **`HOSTINGER_FTP_TARGET_DIR`** with the **Directory**/root path from hPanel (`public_html/`, `domains/example.com/public_html/`, etc.). Keep the trailing `/` so uploads land in the correct folder.
6. Add **`HOSTINGER_FTP_PROTOCOL`** and set it to `ftps` unless Hostinger instructed you to use plain FTP (`ftp`) or you specifically configured SFTP access (`sftp`).
7. (Optional) Add **`HOSTINGER_FTP_PORT`** if Hostinger support gave you a non-standard port. Otherwise leave it unset so the workflow falls back to `21` for FTP/FTPS or `22` for SFTP.

After you add each secret, rerun the **Deploy to Hostinger** workflow. The `Resolve deployment configuration` job will surface any remaining missing values; when all four required secrets are present (`HOSTINGER_FTP_HOST`, `HOSTINGER_FTP_USERNAME`, `HOSTINGER_FTP_PASSWORD`, and `HOSTINGER_FTP_TARGET_DIR`), the deploy job proceeds and the cleanup step connects successfully. The cleanup helper prints a masked summary like:

```
Connected to Hostinger FTP cleanup target: host=dar**************com protocol=FTPS port=21 directory=/public_html
```

Use that log line to confirm the workflow is using the hostname you just configured (the first and last few characters remain visible for easy verification). You do **not** need to create a separate `FTP_CLEANUP_HOST` secret—the workflow derives it automatically from the host secret.

## 2. Prepare the Hostinger server once

Run these steps the first time you bring a Hostinger environment online:

1. Enable SSH access in hPanel (Websites → Manage → Advanced → SSH Access) and note the SSH username/host.
2. Connect over SSH and move to the directory where the application should live:

   ```bash
   ssh <user>@<host>
   cd ~/domains/<your-domain>/public_html
   ```

3. Ensure the directory is empty before cloning. If you already deployed the project manually, back up your files first.
4. Clone the repository or download the latest release:

   ```bash
   git clone -b main https://github.com/ewebtechsuk/savirix.git .
   ```

5. Make the deployment script executable:

   ```bash
   chmod +x deploy_hostinger.sh
   ```

6. Verify PHP and Composer are available. Hostinger provides them globally but you can check with `php -v` and `composer -V`.

Once this initial setup is complete you only need to pull and re-run the deployment script on subsequent releases.

## 3. Run the deployment script on Hostinger

From the project root on the Hostinger server run:

```bash
bash deploy_hostinger.sh
```

The script handles the full production refresh:

- Pulls the latest `main` branch (or clones the repository if missing).
- Installs Composer dependencies with `--no-dev --optimize-autoloader`.
- Copies `.env.example` to `.env` the first time and reminds you to edit it.
- Generates an `APP_KEY` if the key is empty.
- Clears cached configuration/routes/views.
- Runs database migrations with `--force`.
- Fixes permissions on `storage` and `bootstrap/cache`.

Re-run the script after each deployment or whenever the site behaves unexpectedly—it is idempotent and safe to execute multiple times.

## 4. Verify the deployment

After the script completes:

1. Visit the production URL in a browser to confirm the homepage loads.
2. Tail the Laravel log to make sure no fresh exceptions are being thrown:

   ```bash
   tail -f storage/logs/laravel.log
   ```

3. If you see new errors, capture the stack trace for debugging. Press <kbd>Ctrl</kbd>+<kbd>C</kbd> to stop tailing once you are done.

## 5. Troubleshooting HTTP 500 errors

If the public site is still returning an HTTP 500 error after a deploy, walk through this checklist:

1. **Validate the document root layout** – the deployment workflow uploads into `public_html/.deploy-sftp/` and the follow-up flatten step moves the files into `public_html/`. If a failed run leaves either `.deploy-sftp/` or a stray `dist/` folder behind, clean them up manually:

   ```bash
   ssh -p 65002 <user>@<host>
   cd ~/domains/<your-domain>/public_html
   mv .deploy-sftp/* . 2>/dev/null || true
   rm -rf .deploy-sftp
   if [ -d dist ]; then mv dist/* . && rmdir dist; fi
   ls -la
   ```

   The listing should show your built `index.html` and asset directories directly inside `public_html/`.

2. **Inspect the Laravel log on Hostinger** – set `APP_DEBUG=true` in `.env` temporarily if you need detailed error pages, then tail the latest stack trace while reproducing the error:

   ```bash
   tail -n 50 storage/logs/laravel.log
   ```

   Remember to revert `APP_DEBUG=false` afterwards.

3. **Fix storage permissions** – Laravel must be able to write to `storage/` and `bootstrap/cache/`. Reset ownership and permissions with:

   ```bash
   chown -R u753768407:www-data storage bootstrap/cache
   find storage bootstrap/cache -type d -exec chmod 755 {} \;
   find storage bootstrap/cache -type f -exec chmod 644 {} \;
   ```

   Replace `www-data` with the PHP-FPM user configured on your Hostinger plan if it differs.

4. **Regenerate caches and confirm the `APP_KEY`** – make sure the application key is populated (run `php artisan tinker` then `config('app.key')` to double-check) and clear any cached configuration:

   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan config:cache
   ```

5. **Re-run the deployment script** – `bash deploy_hostinger.sh` reinstalls Composer dependencies, ensures `.env` exists, generates an `APP_KEY` when missing, clears caches, runs database migrations with `--force`, and fixes `storage/` permissions. Running it after each pull keeps the application bootable. When you prefer to execute the steps manually, run:

   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress
   php artisan migrate --force
   php artisan optimize:clear
   chmod -R 775 storage bootstrap/cache
   ```

6. **Confirm dependencies shipped with the build** – verify `vendor/` exists and contains the Composer autoloader. If not, rerun the script or the `composer install` command above.

7. **Rebuild front-end assets if applicable** – if the issue is limited to missing compiled assets, run `npm ci && npm run build` locally and commit the generated files if they are supposed to be tracked, or configure the workflow to upload the `dist/` output.

8. **Escalate with context** – when the problem persists, share the log snippet, the commands you ran, and the time of the failure. That information drastically reduces the time-to-fix.

Following these steps resolves the majority of HTTP 500 issues encountered after deploying this project to Hostinger.

