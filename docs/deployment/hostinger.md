# Configuring Hostinger deployment secrets

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

Add each item as a **Repository secret** (Settings → Secrets and variables → Actions → Secrets → *New repository secret*). Organisation owners can also define them as organisation secrets or variables if the same credentials are shared across multiple repositories.

After the secrets are saved rerun the failed workflow from the **Actions** tab. The `prepare` job will now detect the credentials, the cleanup script will connect successfully, and the deployment will proceed.

### Matching the secrets to the hPanel fields

The Hostinger FTP Accounts screen (see the example screenshot the team shared) contains the exact values you need:

- **FTP hostname** → `HOSTINGER_FTP_HOST` (strip the `ftp://` prefix so only the host name remains).
- **FTP username** → `HOSTINGER_FTP_USERNAME`.
- **Password** → `HOSTINGER_FTP_PASSWORD` (click **Change password** if you no longer know the current value and paste the new password into the secret immediately).
- **FTP port** → optionally `HOSTINGER_FTP_PORT` when it differs from the default `21`.
- **Directory** (defaults to `public_html/`) → `HOSTINGER_FTP_TARGET_DIR`.

If you plan to use SFTP instead of FTP/FTPS, enable SSH access in hPanel and create an SFTP account first. The deployment workflow already understands the `HOSTINGER_FTP_PROTOCOL=sftp` combination, but the cleanup script is skipped for SFTP uploads because Hostinger does not leave `.in.*` temp files when using SFTP.

### Screenshot-to-secret checklist

If you have the hPanel FTP details open (like in the screenshot the team shared), you can copy each field straight into GitHub:

1. Open your repository on GitHub and navigate to **Settings → Secrets and variables → Actions → New repository secret**.
2. Add a secret named **`HOSTINGER_FTP_HOST`** using the value from the **FTP Hostname** field (for example `darkorange-chinchilla-918430.hostingersite.com`). Make sure you copy *only* the host—remove any `ftp://` prefix.
3. Add **`HOSTINGER_FTP_USERNAME`** with the username shown in the FTP list (`u1234567`, etc.).
4. Add **`HOSTINGER_FTP_PASSWORD`**. If you do not have it, click **Change password** in hPanel, set a new password, and paste that fresh value into the secret immediately.
5. Add **`HOSTINGER_FTP_TARGET_DIR`** with the **Directory**/root path from hPanel (`public_html/`, `domains/example.com/public_html/`, etc.). Keep the trailing `/` so uploads land in the correct folder.
6. Add **`HOSTINGER_FTP_PROTOCOL`** and set it to `ftps` unless Hostinger instructed you to use plain FTP (`ftp`) or you specifically configured SFTP access (`sftp`).
7. (Optional) Add **`HOSTINGER_FTP_PORT`** if Hostinger support gave you a non-standard port. Otherwise leave it unset so the workflow falls back to `21` for FTP/FTPS or `22` for SFTP.

After you add each secret, rerun the **Deploy to Hostinger** workflow. The `Resolve deployment configuration` job will surface any remaining missing values; when all four required secrets are present (`HOSTINGER_FTP_HOST`, `HOSTINGER_FTP_USERNAME`, `HOSTINGER_FTP_PASSWORD`, and `HOSTINGER_FTP_TARGET_DIR`), the deploy job proceeds and the cleanup step connects successfully.

When the cleanup helper connects it now prints a masked summary such as:

```
Connected to Hostinger FTP cleanup target: host=dar**************com protocol=FTPS port=21 directory=/public_html
```

Use that log line to confirm the workflow is using the hostname you just configured (the first and last few characters remain visible for easy verification).

### Do I need a separate `FTP_CLEANUP_HOST` secret?

No. The deploy workflow already passes the resolved `HOSTINGER_FTP_HOST` value into the cleanup helper as `FTP_CLEANUP_HOST` (along with the matching username, password, protocol, port, and target directory). Creating a standalone secret or environment named `FTP_CLEANUP_HOST` on GitHub does not change what the workflow uses and may cause confusion later. Focus on keeping the four required `HOSTINGER_FTP_*` secrets up to date—when those are correct the cleanup step will automatically report the right host in the masked log line above.

### HTTP 500 after deploying

If the public site is still returning an HTTP 500 error after a deploy, walk through the production checklist before retrying the GitHub Action:

1. **Inspect the Laravel log on Hostinger** – SSH into the account and stream `storage/logs/laravel.log` so you can see the exact exception being thrown:

   ```bash
   ssh <user>@<host>
   cd ~/domains/<your-domain>/public_html
   tail -f storage/logs/laravel.log
   ```

   Copy the latest stack trace when asking for help; it confirms whether the failure is due to missing environment variables, migrations, or file permissions.

2. **Run `deploy_hostinger.sh` on the server** – this script installs Composer dependencies, ensures `.env` exists, generates an `APP_KEY` when missing, clears caches, runs database migrations with `--force`, and fixes `storage/` permissions. Running it after each pull keeps the application bootable. When you prefer to execute the steps manually, run:

   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress
   php artisan migrate --force
   php artisan optimize:clear
   chmod -R 775 storage bootstrap/cache
   ```

3. **Double-check the `.env` file** – confirm values such as `APP_KEY` (must not be empty), database credentials, queue/cache drivers, and any API keys required for third-party integrations. After editing `.env`, repeat the cache clear step:

   ```bash
   php artisan optimize:clear
   ```

Once the log is clean and the application boots locally via `php artisan serve`, rerun the deploy workflow and reload the homepage.
