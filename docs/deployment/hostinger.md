# Configuring Hostinger deployment secrets

GitHub automatically runs the **Deploy to Hostinger** workflow when commits land on `main`. The job uploads the built application to Hostinger by using standard FTP/FTPS/SFTP credentials that are stored as repository secrets. When those secrets are absent or empty the deployment job fails during the pre-upload cleanup step with an error similar to:

```
Missing required environment variable FTP_CLEANUP_HOST. Populate the repository secret HOSTINGER_FTP_HOST (or FTP_SERVER) with the FTP hostname shown in Hostinger's hPanel under Websites → Manage → FTP Accounts.
```

To fix the failure you must add the Hostinger connection details to the repository's secrets (or organisation level variables). The workflow looks for the following values, preferring the `HOSTINGER_` names but accepting the legacy `FTP_` names as a fallback:

| Secret/variable | Purpose | Where to find it in Hostinger |
| --- | --- | --- |
| `HOSTINGER_FTP_HOST` (or `FTP_SERVER`/`FTP_HOST`) | FTP hostname used by the deployment and cleanup scripts. | Log in to [hPanel](https://hpanel.hostinger.com/) → **Websites** → **Manage** for the correct site → **Files → FTP Accounts**. The hostname appears next to the account (for example, `ftp.yourdomain.com` or a server IP). |
| `HOSTINGER_FTP_USERNAME` (or `HOSTINGER_FTP_USER`/`FTP_USERNAME`/`FTP_USER`) | FTP username that has access to the deployment directory. | Same FTP Accounts page in hPanel. Use the username column or create a new FTP account if needed. |
| `HOSTINGER_FTP_PASSWORD` (or `HOSTINGER_FTP_PASS`/`FTP_PASSWORD`/`FTP_PASS`) | Password for the FTP user. | Either copy the password you set when creating the FTP account or click **Change account password** on the FTP Accounts page to generate a new one. |
| `HOSTINGER_FTP_TARGET_DIR` (or `FTP_TARGET_DIR`) | Remote directory to upload into. | On Hostinger shared hosting this is usually `public_html/` (include the trailing slash). Adjust if your application lives in a subdirectory. |
| `HOSTINGER_FTP_PROTOCOL` (or `FTP_PROTOCOL`) | Connection protocol. | Hostinger supports `ftps` (explicit TLS) for most plans. Use `sftp` only if you enabled SSH access and created an SFTP account. Leave blank to default to `ftps`. |
| `HOSTINGER_FTP_PORT` (or `FTP_PORT`) | Port for the selected protocol. | Hostinger uses port 21 for FTP/FTPS and 22 for SFTP. Set this only if Hostinger support instructs you to use a different port. |

Add each item as a **Repository secret** (Settings → Secrets and variables → Actions → Secrets → *New repository secret*). Organisation owners can also define them as organisation secrets or variables if the same credentials are shared across multiple repositories.

After the secrets are saved rerun the failed workflow from the **Actions** tab. The `prepare` job will now detect the credentials, the cleanup script will connect successfully, and the deployment will proceed.
