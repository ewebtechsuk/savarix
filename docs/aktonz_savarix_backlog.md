# AKTONZ → Savarix Migration Backlog

## Epics

### Epic: Tenancy Isolation & Hardening
- **Description:** Audit and reinforce Stancl Tenancy usage so each agency’s data (AKTONZ, LCI, future tenants) is fully isolated across DB, cache, filesystem, permissions, and file storage.
- **Dependencies:** Tenancy configuration review, knowledge of current models/routes; coordination with ops on database mode (single vs per-tenant DBs).
- **Risk:** High (data leakage or downtime if misapplied).
- **Checklist:**
  - [ ] Task 1: First tenant isolation fix (candidate for next PR).
  - [ ] Add BelongsToTenant/global scope coverage to all tenant-owned models (contacts, properties, tenancies, invoices, viewings, payments).
  - [ ] Enforce tenancy middleware on all tenant routes and background jobs that touch tenant data.
  - [ ] Scope Spatie permission cache/DB tables per tenant; verify unique constraints include tenant_id.
  - [ ] Isolate file uploads with tenant-specific paths/disks and review signed URL exposure.

### Epic: Merge AKTONZ Admin Features into Savarix
- **Description:** Rebuild AKTONZ’s legacy admin capabilities inside the Savarix tenant app (Blade/Breeze), covering contacts, properties, viewings, offers, rent/tenancy basics, and reporting.
- **Dependencies:** Tenancy isolation groundwork; UX alignment with Savarix design system; clarity on AKTONZ feature expectations.
- **Risk:** Medium (scope creep, user retraining).
- **Checklist:**
  - [ ] Fill CRM field gaps (lead status, categories/tags, source) and search/filter parity.
  - [ ] Expand property schema (status, portal flags, owner links) and Blade CRUD/UI parity with legacy flows.
  - [ ] Implement offers + minimal sales pipeline states with status transitions and audit trails.
  - [ ] Add basic tenancies UI (link property/contact, rent, dates) and rent log entry forms.
  - [ ] Add viewing scheduler basics (date/time, attendees, notes) with calendar/list views.

### Epic: Data Migration (AKTONZ → Savarix Tenant)
- **Description:** Clean, map, and import AKTONZ legacy data (including Apex27-sourced records) into the AKTONZ Savarix tenant with repeatable scripts and verification.
- **Dependencies:** Target schema stability from feature merge; tenancy isolation to avoid cross-tenant writes; access to source exports/APIs.
- **Risk:** High (data integrity, downtime, reversible cutover).
- **Checklist:**
  - [ ] Define source-to-target mapping (contacts, properties, tenancies, offers, documents) with field-level decisions.
  - [ ] Build dry-run import scripts with validation and duplicate detection scoped to AKTONZ tenant.
  - [ ] Create migration rehearsals with checksums/UAT sign-off and rollback plan.
  - [ ] Execute production import with monitoring and post-import QA dashboards.

### Epic: Apex27 Integration
- **Description:** Implement Apex27 ingestion first (contacts/properties/viewings), then evolve to two-way sync with conflict handling and rate limits.
- **Dependencies:** Stable tenancy isolation; available Apex27 API credentials and sandbox; migrated AKTONZ tenant data.
- **Risk:** Medium (API changes, sync conflicts).
- **Checklist:**
  - [ ] Build one-way import jobs (incremental + backfill) into tenant-scoped models with idempotent keys.
  - [ ] Add field-level mapping and normalization (statuses, categories, users) with audit logs of imports.
  - [ ] Implement two-way sync contract (webhooks/polling), conflict resolution, and retry policies.
  - [ ] Add monitoring/alerting and manual replay tools per tenant.

### Epic: Frontend Rewrite (AKTONZ Next.js → Savarix Blade)
- **Description:** Replace the AKTONZ Next.js admin screens with Blade/Tailwind/Breeze components using Savarix’s design system and role model.
- **Dependencies:** Feature parity definitions from AKTONZ admin; design tokens/components in Savarix; tenancy middleware in place.
- **Risk:** Medium (UX regression risk, tight coupling to backend readiness).
- **Checklist:**
  - [ ] Inventory AKTONZ Next.js screens and map to Savarix routes/components.
  - [ ] Build reusable Blade partials for contact/property cards, modals, and tables mirroring AKTONZ workflows.
  - [ ] Replace remaining Next.js entry points/links with Savarix URLs; remove legacy routing references.
  - [ ] Add accessibility/UX regression checks (keyboard focus, responsive layouts).

## Priority
1. **Tenancy Isolation & Hardening** – prevent cross-tenant leakage before migrating or exposing AKTONZ data.
2. **Merge AKTONZ Admin Features into Savarix** – deliver required functionality inside Savarix for AKTONZ without breaking LCI.
3. **Data Migration (AKTONZ → Savarix Tenant)** – move legacy data once features/isolation are ready.
4. **Apex27 Integration** – start with imports for freshness, then two-way sync after stability.
5. **Frontend Rewrite (AKTONZ Next.js → Savarix Blade)** – finalize UI convergence after core functionality is reliable.
