# Savarix vs AKTONZ vs Apex27: Integration Overview

## Key Objectives for Savarix
- Consolidate AKTONZ onto the Savarix multi-tenant SaaS while preserving agency-level data isolation and avoiding cross-tenant leakage.
- Reach feature parity with what AKTONZ currently achieves through its Next.js admin + Apex27, delivered via Laravel Blade/Breeze UI components.
- Migrate AKTONZ historical data (contacts, properties, tenancies, offers, documents) into the Savarix AKTONZ tenant with auditability and rollback.
- Integrate Apex27 as the external CRM source of truth initially via imports, then evolve to two-way sync for contacts/properties/activities.
- Harden tenancy (Stancl Tenancy v3) across database, cache, filesystem, and permissions to keep AKTONZ/LCI and future agencies fully isolated.

## Feature Comparison (Savarix Tenant App vs AKTONZ Legacy Admin vs Apex27)
- **Contacts/CRM**: Savarix has basic CRUD per tenant but lacks richer CRM fields and tagging; AKTONZ admin mostly surfaces Apex27-sourced contacts; Apex27 provides full CRM (categories, notes, history).
- **Properties**: Savarix and AKTONZ both support basic property records; Apex27 is comprehensive (sales/lettings, media, portal uploads, rich fields).
- **Lead/Enquiry Management**: Savarix partial/missing; AKTONZ minimal and often deferred to Apex27; Apex27 offers full lead pipelines and automations.
- **Viewings/Calendar**: Savarix has rudimentary scheduling; AKTONZ may only list Apex27 data; Apex27 delivers full calendar integration and reminders.
- **Offers & Sales Pipeline**: Savarix currently minimal; AKTONZ relied on Apex27; Apex27 has full offer/chain management.
- **Tenancies & Rent**: Savarix has a light tenancy model; AKTONZ leaned on Apex27; Apex27 tracks leases, rent, renewals, and notices robustly.
- **Documents/ESign**: Savarix supports documents and signing; AKTONZ depended on Apex27/other tools; Apex27 includes document storage and workflows.
- **UI/UX**: Savarix uses Blade/Tailwind (Breeze-based); AKTONZ uses a custom Next.js UI; Apex27 has a polished, interactive web UI.

## Major Epics Highlighted in the Plan
- **Tenancy Isolation & Hardening**: Audit Stancl Tenancy usage, ensure strict tenant scoping (DB/cache/filesystem/permissions), and prevent cross-tenant leakage.
- **Feature Parity / AKTONZ Admin Merge**: Rebuild AKTONZ admin capabilities (contacts, properties, tenancies, offers, viewings) in Savarix Blade UI with Spatie roles.
- **Data Migration (AKTONZ → Savarix Tenant)**: Extract and clean legacy data (including Apex27-sourced content), map to Savarix schemas, and run rehearsed, reversible imports.
- **Apex27 Integration**: Phase 1 one-way imports for contacts/properties/viewings; Phase 2 two-way sync with conflict resolution and rate limiting.
- **Frontend Rewrite**: Replace AKTONZ Next.js admin with Laravel Blade/Breeze components, adopting Savarix’s design system while keeping functional parity.
- **Extended Roadmap Items**: Offers/sales pipeline, calendar/viewings, UI polish, client portal replacement, and third-party integrations as follow-on work.
