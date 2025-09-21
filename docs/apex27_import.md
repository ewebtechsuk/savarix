# Apex27 to Ressapp Import Helper

This repository now includes `scripts/apex27_import.py`, a Python utility for
loading Apex27 export data into a Ressapp tenant via the public REST API.

## Prerequisites

1. Python 3.9+ installed locally.
2. Install the script dependencies:

   ```bash
   pip install -r requirements/apex27_import.txt
   ```

3. Prepare JSON files with the payloads you would like to import. Place them in
   a directory (default: `data/apex27/`) using the following filenames:

   * `properties.json`
   * `tenancies.json`
   * `payments.json`

   Each file should contain a list of objects that match Ressapp's API payloads.
   Include an `external_id` on records that need to be referenced by later
   imports. Tenancies can specify `property_external_id` to link to the
   corresponding property, and payments can specify `tenancy_external_id` to
   link to the tenancy they belong to.

## Running the Import

```bash
python scripts/apex27_import.py \
    https://your-tenant.example.com \
    user@example.com \
    "super-secret-password" \
    --data-dir /path/to/export
```

### Helpful Flags

* `--dry-run` – Validate JSON files and log the payloads without performing any
  API calls.
* `--continue-on-error` – Log HTTP errors and continue processing the remaining
  records.
* `--timeout` – Override the default request timeout (30 seconds).

### Execution Order

1. Properties (`/api/properties`)
2. Tenancies (`/api/tenancies`)
3. Payments (`/api/payments`)

Any `external_id` captured from properties and tenancies is recorded and used to
resolve relationships in subsequent steps.

## Example Data Snippet

```json
[
  {
    "external_id": "PROP-123",
    "name": "15 High Street",
    "address": "15 High Street, London",
    "type": "residential",
    "status": "active"
  }
]
```

Place this JSON inside `properties.json` to create a property that can be
referenced by tenancies using `"property_external_id": "PROP-123"`.
