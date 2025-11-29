# Production observability (tenancy + Sentry)

This guide explains the deploy-time tenancy health checks, runtime debug tools, and how Sentry is wired to include tenant context.

## CI tenancy health checks

The Hostinger deploy workflow (`.github/workflows/deploy_hostinger.yml`) now fails if tenancy is broken. After code is synced and domains are refreshed, the workflow:

1. Runs the CLI health command over SSH:
   * `php artisan savarix:tenancy-health --host=aktonz.savarix.com`
   * `php artisan savarix:tenancy-health --host=londoncapitalinvestments.savarix.com`

2. Calls the HTTP health endpoints from the GitHub runner to ensure Hostinger serves Laravel JSON instead of an HTML error page:
   * `https://savarix.com/__health/tenancy?host=aktonz.savarix.com`
   * `https://savarix.com/__health/tenancy?host=londoncapitalinvestments.savarix.com`

If any of these fail (non-zero exit code, 500 error, or Hostinger HTML), the deploy is stopped and marked failed.

## Runtime tenancy debug endpoint

Use the per-tenant debug endpoint to verify routing and tenant resolution:

* `https://aktonz.savarix.com/__tenancy-debug`
* `https://londoncapitalinvestments.savarix.com/__tenancy-debug`

This endpoint should return JSON that includes the resolved tenant ID, domains, and request host. If you see an HTML Hostinger error page, the domain routing is misconfigured.

## Sentry error monitoring by tenant

Sentry is installed via the Laravel SDK and configured in `config/sentry.php`. In production, performance tracing uses the sample rates defined by `SENTRY_TRACES_SAMPLE_RATE` and `SENTRY_PROFILES_SAMPLE_RATE`; non-production environments disable tracing by forcing zero sample rates.

Each Sentry event automatically includes tenant-aware metadata:

* Tags: `host`, `tenant`, and authenticated `role`
* Context: `tenant` object with `id` and `domain` when a tenant is active

To investigate an incident for a specific tenant, filter Sentry issues by the `tenant` tag (e.g., `tenant:aktonz`). You can also filter by `host` to isolate per-domain issues or by `role` to see what user roles are triggering errors.

## Deployment failure modes

Deploys to Hostinger now fail early if tenancy health checks fail. When debugging a failed deploy:

1. Open the GitHub Actions run for `deploy_hostinger.yml` and review the SSH health check and HTTP `__health/tenancy` steps.
2. Fix tenant/domain data (often by re-running `savarix:diagnose-tenancy-domains --sync`).
3. Re-run the deploy workflow once the health checks pass locally.

For broader troubleshooting, see the Hostinger recovery runbooks below.
