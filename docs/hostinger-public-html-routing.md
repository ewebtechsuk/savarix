# Hostinger public_html routing fix

Hostinger subdomains currently point to `/home/${HOSTINGER_USER}/domains/savarix.com/public_html/laravel_app/public`, which leaves `public_html` as a directory that **contains** the Laravel app. The document root should be the app's `public` directory itself, not a parent folder that wraps it.

The desired state is:

```
/home/${HOSTINGER_USER}/domains/savarix.com/public_html -> /home/${HOSTINGER_USER}/domains/savarix.com/laravel_app/public
```

## Manual fix (SSH)

Run these commands over SSH to reset `public_html` to a symlink that targets the Laravel `public` directory:

```bash
cd /home/${HOSTINGER_USER}/domains/savarix.com
rm -rf public_html
ln -s /home/${HOSTINGER_USER}/domains/savarix.com/laravel_app/public public_html
```

### ⚠️ Important safety warning

* Only run `rm -rf public_html` **inside** `/home/${HOSTINGER_USER}/domains/savarix.com`.
* This directory is expected to contain only Hostinger's default `public_html` folder, which we replace with a symlink.

## Verification

1. Check that `public_html` is now a symlink to the Laravel public directory:

   ```bash
   cd /home/${HOSTINGER_USER}/domains/savarix.com
   ls -la public_html
   ```

2. In hPanel, set all subdomains (e.g., `aktonz.savarix.com`, `londoncapitalinvestments.savarix.com`) to use the document root:

   ```
   /home/${HOSTINGER_USER}/domains/savarix.com/public_html
   ```

3. Hit the tenancy debug endpoints and confirm Laravel returns JSON (not a Hostinger 404):

   * https://aktonz.savarix.com/__tenancy-debug
   * https://londoncapitalinvestments.savarix.com/__tenancy-debug

If the symlink is correct and hPanel points to `public_html`, both URLs should respond with the Laravel tenancy debug payload. See [production-observability.md](./production-observability.md) for how deploy-time health checks now fail on broken tenancy and how Sentry captures per-tenant errors automatically.
