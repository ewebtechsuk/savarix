# Missing `domain` column on `agencies`

**Error message**

```
Unknown column 'domain' in 'WHERE' (SQL: select count(*) from agencies where domain = https://aktonz.savarix.com and id <> 1)
```

**Symptoms**
- Agency update form throws HTTP 500 when saving a domain.
- Logs show `Impersonation redirect missing domain {"agency_id":1}` because the `domain` field remains `NULL`.

**Root cause**
Laravel's validation calls `Rule::unique('agencies', 'domain')`, which issues a `SELECT` on the `domain` column. The production database on Hostinger is missing that column.

**Fix**
Run the existing migration that creates the column on the central connection.

```bash
# From the Laravel app root on Hostinger
cd /home/u753768407/domains/savarix.com/laravel_app

# Ensure the migration runs
php artisan migrate --force

# (Optional) target just this migration
php artisan migrate \
  --path=database/migrations/2026_01_01_000100_ensure_agency_domain_column_exists.php \
  --force
```

**Verify**
Confirm the column exists (phpMyAdmin or MySQL CLI):

```sql
DESCRIBE agencies;
```

Expected row:

```
domain  varchar(255)  YES  UNI  NULL  ...
```

**Retest**
1. Visit the admin UI → Agencies → Aktonz → Edit details.
2. Set Domain to `aktonz.savarix.com` (host only).
3. Click **Save changes**.

Result should be a successful save and the “Open in tenant app” link pointing to `https://aktonz.savarix.com/dashboard`.
