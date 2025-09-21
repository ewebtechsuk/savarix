# Apex27 to Ressapp Import Helper

`scripts/apex27_import.py` is a Python utility that can read a raw Apex27
export (CSV or JSON) and stream it into your Ressapp tenant by calling the
public REST API.  Contacts are created first, followed by properties,
tenancies and payments so that all cross-record references are resolved
automatically.

## Prerequisites

1. Python 3.9+ installed locally.
2. Install the script dependencies:

   ```bash
   pip install -r requirements/apex27_import.txt
   ```
3. Obtain an Apex27 export.  Place the files inside a directory (default:
   `data/apex27/`).  The importer looks for the following filenames, in either
   CSV or JSON format:

   * `contacts.(csv|json)`
   * `properties.(csv|json)`
   * `tenancies.(csv|json)`
   * `payments.(csv|json)`

   You can override each path with `--contacts-file`, `--properties-file`,
   `--tenancies-file` and `--payments-file`.

## Running the Import

When supplying raw Apex27 exports use the `raw` format so the script can map
Apex fields to Ressapp payloads automatically:

```bash
python scripts/apex27_import.py \
    https://your-tenant.example.com \
    user@example.com \
    "super-secret-password" \
    --apex-format raw \
    --data-dir /path/to/apex/export
```

If you prefer to provide Ressapp-ready JSON payloads (for example when the
mapping is handled elsewhere) keep the default `prepared` format and populate
`properties.json`, `tenancies.json`, `payments.json` and (optionally)
`contacts.json` with the fields expected by the API.  Include `external_id`
values wherever a later record needs to reference a previously created one
(e.g. tenancies can specify `property_external_id` and
`contact_external_id`).

### Helpful Flags

* `--dry-run` – Validate input files, resolve relationships and log the
  payloads without performing any API calls.
* `--continue-on-error` – Log HTTP errors and continue processing the remaining
  records.
* `--timeout` – Override the default request timeout (30 seconds).

### Execution Order

1. Contacts (`/api/contacts`)
2. Properties (`/api/properties`)
3. Tenancies (`/api/tenancies`)
4. Payments (`/api/payments`)

Any `external_id` captured from contacts, properties and tenancies is recorded
and used to resolve relationships in subsequent steps.  During a dry run the
script still performs all mapping logic so you can verify that every tenancy
and payment can be linked to the correct Ressapp record before making any
changes to your tenant.

## Raw Apex27 Example

Below is a simplified contacts CSV row that the importer understands when
`--apex-format raw` is selected:

```csv
ID,Type,FirstName,LastName,Email,Mobile,Address1,Town,Postcode
CNT-001,Landlord,Alex,Young,alex@example.com,+44123456789,12 Station Rd,Leeds,LS1 1AA
```

It is converted into a Ressapp contact payload with `external_id: "CNT-001"`,
`type: "landlord"` and `name: "Alex Young"`.  Properties, tenancies and
payments follow similar conventions, matching Apex27 identifiers to Ressapp
IDs automatically.
