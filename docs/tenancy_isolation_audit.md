# Tenancy Isolation Audit (Savarix)

## What I Inspected
- Stancl Tenancy configuration (`config/tenancy.php`) and tenancy service provider (`app/Providers/TenancyServiceProvider.php`).
- Core domain models for tenant-owned data: `Contact`, `Property`, `SavarixTenancy` (tenancies/leases), plus supporting models (e.g., `ContactNote`, `ContactViewing`, `Invoice`, `Payment`, `Viewing`).
- HTTP route definitions to see where tenancy middleware is applied (`routes/web.php`).
- Example controllers handling tenant data (`ContactController`, `PropertyController`) to check query scoping.

## Current Patterns & Observations
- **Stancl setup:** `Tenant` extends `Stancl\Tenancy\Database\Models\Tenant` with `HasDatabase`; bootstrappers include database, cache, filesystem, and queue tenancy. Central domains are parsed from env/`APP_URL`, and tenancy middleware is prepended to kernel priority.
- **Routes:** Tenant-facing routes (contacts, properties, diary, accounts, inspections, documents, maintenance) are wrapped with `tenancy`, `preventAccessFromCentralDomains`, and `setTenantRouteDefaults` middleware, suggesting tenant context should be active during requests.
- **Models lack tenant scoping:** Key tenant-owned models (`Contact.php`, `Property.php`, `SavarixTenancy.php`, and related note/communication/viewing/offer/payment models) do **not** use Stancl’s `BelongsToTenant` trait or any global scope on `tenant_id`/`company_id`. Queries default to global tables.
- **Controllers use global queries:** `ContactController` builds queries via `Contact::query()` (filters, counts, group/tag breakdowns) without tenant constraints. Similar patterns likely exist in property/diary/inspection controllers, meaning data retrieval is not automatically limited to the current tenant context.
- **User/role data:** `User` model is central (no tenant scoping) and uses Spatie Roles. There is no explicit tenant-aware cache key configuration for permissions in code inspected, so permission caching could leak across tenants if shared cache is used.
- **Database layout:** Migrations show central tables for legacy data and a small `database/migrations/tenant` folder, implying the app might be running in single-DB tenancy with shared tables plus a `tenant_id`/`company_id` column (not present on the inspected models). This hybrid state increases leak risk unless every query is scoped manually.
- **File/storage:** Filesystem tenancy bootstrapper is enabled to suffix disk paths per tenant, but usage of tenant-specific disks in models/controllers was not confirmed.

## Specific Risk Points (Potential Cross-Tenant Leakage)
- **Unscoped models:** Contacts, properties, tenancies, invoices/payments, and viewings lack `tenant_id` scoping. Any `Model::all()`/`paginate()` can return cross-tenant data when the DB is shared.
- **Analytics/count queries:** Breakdown queries in `ContactController` (type/group/tag counts) aggregate across all tenants because they do not filter by tenant context.
- **Permission cache:** Spatie permission cache keys are not tenant-aware; if a shared cache store is used, permissions may bleed between tenants.
- **Central users & routes:** Central `auth:web` routes exist alongside tenant routes; without strict domain checks and tenant-binding on login, a central user could access tenant routes in the wrong context.

## Recommended Tenancy Strategy
- **Adopt explicit tenant scoping on domain models:** Add `BelongsToTenant` (or a custom global scope on `tenant_id`) to all tenant-owned models (contacts, properties, tenancies, invoices/payments, viewings, documents) and ensure migrations add the required `tenant_id` column plus composite unique indexes.
- **Enforce tenant context in data access:** Wrap tenant routes/controllers/jobs with tenancy middleware and replace raw model queries with tenant-aware equivalents (e.g., model traits or repository layer using `tenant()` helpers). Add automated tests to assert isolation.
- **Isolate Spatie permissions:** Configure permission tables per tenant (or add `tenant_id`) and set a tenant-specific cache key during tenancy bootstrap to avoid cross-tenant permission caching.
- **File and cache separation:** Keep filesystem tenancy suffixing enabled; audit uploads to ensure paths include tenant identifiers. Continue using cache tenancy bootstrapping and avoid sharing cache keys outside tenancy context.
- **Migration path:** Decide on single-DB vs per-tenant DB. If remaining on single DB, implement strict scoping and composite indexes; if moving to per-tenant DBs, migrate tenant-owned tables to tenant migrations and deprecate shared tables to reduce leak risk.
