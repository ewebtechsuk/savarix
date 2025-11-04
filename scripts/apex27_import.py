#!/usr/bin/env python3
"""Import Apex27 export data directly into a Savirix tenant.

The importer now understands raw Apex27 exports (CSV or JSON) and can
create contacts, properties, tenancies and payments in the correct
order.  Relationship fields are automatically resolved by keeping track
of the Savirix identifiers created for each record.  You can still feed
pre-shaped payloads that already match the Savirix API by switching the
``--apex-format`` flag to ``prepared``.

Typical usage against a freshly downloaded Apex27 export directory:

.. code-block:: bash

    python scripts/apex27_import.py \
        https://tenant.example.com \
        user@example.com \
        "super-secret" \
        --apex-format raw \
        --data-dir /path/to/apex/export

The exporter expects the directory to contain ``contacts``,
``properties``, ``tenancies`` and ``payments`` files in either CSV or
JSON format (case-insensitive).  Individual paths can be overridden
through dedicated ``--*-file`` arguments.

"""
from __future__ import annotations

import argparse
import csv
import json
import logging
import re
import sys

from datetime import date, datetime
from decimal import Decimal, InvalidOperation
from pathlib import Path
from typing import Dict, Iterable, List, Optional

import requests
from dateutil import parser as date_parser


LOGGER = logging.getLogger("apex27_import")


class ImportError(RuntimeError):
    """Raised when the import process fails."""


def parse_args(argv: Optional[List[str]] = None) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "base_url",
        help="Base URL of the Savirix tenant, e.g. https://tenant.example.com",
    )
    parser.add_argument("email", help="Savirix user email for API authentication")
    parser.add_argument("password", help="Savirix user password for API authentication")
    parser.add_argument(
        "--data-dir",
        default=Path("data/apex27"),
        type=Path,
        help=(
            "Directory containing Apex27 export files (default: data/apex27). "
            "Expected filenames are contacts.(json|csv), properties.(json|csv), "
            "tenancies.(json|csv) and payments.(json|csv) unless overridden"
        ),
    )
    parser.add_argument(
        "--contacts-file",
        type=Path,
        help="Explicit path to the Apex27 contacts export (CSV or JSON)",
    )
    parser.add_argument(
        "--properties-file",
        type=Path,
        help="Explicit path to the Apex27 properties export (CSV or JSON)",
    )
    parser.add_argument(
        "--tenancies-file",
        type=Path,
        help="Explicit path to the Apex27 tenancies/leases export (CSV or JSON)",
    )
    parser.add_argument(
        "--payments-file",
        type=Path,
        help="Explicit path to the Apex27 payments export (CSV or JSON)",
    )
    parser.add_argument(
        "--apex-format",
        default="prepared",
        choices=["prepared", "raw"],
        help=(
            "How to interpret the Apex27 files: 'raw' understands Apex27's native "
            "exports while 'prepared' expects Savirix-ready payloads (default: prepared)."

        ),
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Validate input files and log requests without sending them",
    )
    parser.add_argument(
        "--continue-on-error",
        action="store_true",
        help="Log HTTP errors and continue instead of aborting immediately",
    )
    parser.add_argument(
        "--timeout",
        type=float,
        default=30.0,
        help="Timeout (seconds) for HTTP requests (default: 30s)",
    )
    parser.add_argument(
        "--log-level",
        default="INFO",
        choices=["DEBUG", "INFO", "WARNING", "ERROR", "CRITICAL"],
        help="Logging level (default: INFO)",
    )
    return parser.parse_args(argv)


def configure_logging(level: str) -> None:
    logging.basicConfig(
        level=getattr(logging, level.upper()),
        format="%(asctime)s %(levelname)s %(message)s",
    )


def resolve_data_file(base: Path, explicit: Optional[Path], stem: str) -> Optional[Path]:
    if explicit:
        return explicit
    for suffix in (".json", ".JSON", ".csv", ".CSV"):
        candidate = base / f"{stem}{suffix}"
        if candidate.exists():
            return candidate
    return None


def load_dataset(path: Optional[Path]) -> List[dict]:
    if path is None:
        return []
    if not path.exists():
        LOGGER.warning("%s not found; skipping", path)
        return []
    suffix = path.suffix.lower()
    if suffix == ".json":
        try:
            with path.open("r", encoding="utf-8") as handle:
                data = json.load(handle)
        except json.JSONDecodeError as exc:
            raise ImportError(f"Failed to parse JSON from {path}: {exc}") from exc
        if isinstance(data, dict):
            for key in ("data", "items", "results", "records"):
                if isinstance(data.get(key), list):
                    data = data[key]
                    break
        if not isinstance(data, list):
            raise ImportError(f"Expected a list in {path}, got {type(data).__name__}")
        LOGGER.info("Loaded %s records from %s", len(data), path)
        return [r if isinstance(r, dict) else {"value": r} for r in data]
    if suffix == ".csv":
        with path.open("r", encoding="utf-8-sig", newline="") as handle:
            reader = csv.DictReader(handle)
            rows = [dict(row) for row in reader]
        LOGGER.info("Loaded %s rows from %s", len(rows), path)
        return rows
    raise ImportError(f"Unsupported file format for {path}")


def first_value(record: dict, *keys: str) -> Optional[str]:
    for key in keys:
        if key in record:
            value = record[key]
            if value is None:
                continue
            if isinstance(value, str):
                value = value.strip()
                if not value:
                    continue
                return value
            return str(value)
    return None


def slugify(value: str, fallback: str) -> str:
    text = value.strip()
    if not text:
        return fallback
    slug = re.sub(r"[^a-z0-9]+", "_", text.lower()).strip("_")
    return slug or fallback


def parse_decimal(value: Optional[str]) -> Optional[float]:
    if value in (None, ""):
        return None
    if isinstance(value, (int, float)):
        return float(value)
    text = str(value).strip()
    if not text:
        return None
    text = text.replace(",", "")
    match = re.search(r"-?\d+(?:\.\d+)?", text)
    if not match:
        return None
    try:
        return float(Decimal(match.group(0)))
    except (InvalidOperation, ValueError):
        return None


def parse_date(value: object) -> Optional[str]:
    if value in (None, ""):
        return None
    if isinstance(value, date):
        return value.isoformat()
    if isinstance(value, datetime):
        return value.date().isoformat()
    text = str(value).strip()
    if not text:
        return None
    try:
        parsed = date_parser.parse(text, dayfirst=True, yearfirst=False, fuzzy=True)
    except (ValueError, OverflowError) as exc:
        LOGGER.warning("Unable to parse date '%s': %s", text, exc)
        return None
    return parsed.date().isoformat()


def combine_address(parts: Iterable[Optional[str]]) -> Optional[str]:
    cleaned = [str(part).strip() for part in parts if part and str(part).strip()]
    return ", ".join(cleaned) if cleaned else None


def ensure_external_id(entity: str, record: dict, *candidates: str) -> str:
    value = first_value(record, *candidates)
    if not value:
        raise ImportError(f"{entity} record is missing an identifier; looked for {candidates}")
    return value


def normalize_apex_contacts(records: List[dict]) -> List[dict]:
    normalized: List[dict] = []
    for record in records:
        external_id = ensure_external_id(
            "Contact",
            record,
            "id",
            "ID",
            "Id",
            "contact_id",
            "ContactID",
            "ContactId",
            "reference",
        )
        first_name = first_value(record, "first_name", "FirstName", "firstname")
        last_name = first_value(record, "last_name", "LastName", "lastname")
        name = first_value(record, "name", "Name")
        if not name:
            name = " ".join(part for part in [first_name, last_name] if part)
        if not name:
            raise ImportError(f"Contact {external_id} is missing a name")
        contact_type = first_value(record, "type", "Type", "category", "Category", "role")
        if not contact_type:
            # Try inferring from tags/labels
            tag_hint = first_value(record, "tags", "Tags", "label", "Label")
            contact_type = tag_hint or "contact"
        email = first_value(record, "email", "Email", "primaryEmail", "EmailAddress")
        phone = first_value(record, "mobile", "Mobile", "phone", "Phone", "telephone", "Telephone")
        address = combine_address(
            [
                first_value(record, "address", "Address", "Address1"),
                first_value(record, "address2", "Address2"),
                first_value(record, "town", "Town", "City"),
                first_value(record, "county", "County"),
                first_value(record, "postcode", "Postcode", "PostalCode"),
            ]
        )
        notes = first_value(record, "notes", "Notes", "comments", "Comments")
        payload = {
            "external_id": external_id,
            "type": slugify(contact_type, "contact"),
            "name": name,
        }
        if email:
            payload["email"] = email
        if phone:
            payload["phone"] = phone
        if address:
            payload["address"] = address
        if notes:
            payload["notes"] = notes
        if first_name:
            payload["first_name"] = first_name
        if last_name:
            payload["last_name"] = last_name
        normalized.append(payload)
    LOGGER.info("Normalised %s Apex27 contacts", len(normalized))
    return normalized


def normalize_apex_properties(records: List[dict]) -> List[dict]:
    normalized: List[dict] = []
    for record in records:
        external_id = ensure_external_id(
            "Property",
            record,
            "id",
            "ID",
            "Id",
            "property_id",
            "PropertyID",
            "PropertyId",
            "reference",
            "Reference",
        )
        title = first_value(
            record,
            "title",
            "Title",
            "displayAddress",
            "DisplayAddress",
            "shortAddress",
            "ShortAddress",
        )
        address = first_value(record, "address", "Address")
        if not address:
            address = combine_address(
                [
                    first_value(record, "address1", "Address1", "line1"),
                    first_value(record, "address2", "Address2", "line2"),
                    first_value(record, "town", "Town", "City"),
                    first_value(record, "county", "County"),
                    first_value(record, "postcode", "Postcode", "PostalCode"),
                ]
            )
        if not address:
            raise ImportError(f"Property {external_id} is missing an address")
        property_type = first_value(record, "type", "Type", "propertyType", "PropertyType")
        status = first_value(record, "status", "Status", "marketingStatus", "MarketingStatus")
        price = parse_decimal(first_value(record, "price", "Price", "rent", "Rent", "marketingPrice"))
        landlord_ext = first_value(
            record,
            "landlord_id",
            "LandlordID",
            "LandlordId",
            "landlordId",
            "LandlordReference",
        )
        vendor_ext = first_value(record, "vendor_id", "VendorID", "VendorId", "vendorId")
        applicant_ext = first_value(record, "applicant_id", "ApplicantID", "ApplicantId", "applicantId")
        owner_ext = first_value(record, "owner_id", "OwnerID", "OwnerId", "ownerId")
        payload = {
            "external_id": external_id,
            "address": address,
            "type": slugify(property_type or "residential", "residential"),
            "status": slugify(status or "available", "available"),
        }
        if title:
            payload["title"] = title
        if price is not None:
            payload["price"] = price
        if landlord_ext:
            payload["landlord_external_id"] = landlord_ext
        if vendor_ext:
            payload["vendor_external_id"] = vendor_ext
        if applicant_ext:
            payload["applicant_external_id"] = applicant_ext
        if owner_ext:
            payload["owner_external_id"] = owner_ext
        normalized.append(payload)
    LOGGER.info("Normalised %s Apex27 properties", len(normalized))
    return normalized


def normalize_apex_tenancies(records: List[dict]) -> List[dict]:
    normalized: List[dict] = []
    for record in records:
        external_id = ensure_external_id(
            "Tenancy",
            record,
            "id",
            "ID",
            "Id",
            "tenancy_id",
            "TenancyID",
            "TenancyId",
            "reference",
        )
        property_ext = ensure_external_id(
            "Tenancy property reference",
            record,
            "property_id",
            "PropertyID",
            "PropertyId",
            "propertyReference",
        )
        contact_ext = ensure_external_id(
            "Tenancy contact reference",
            record,
            "contact_id",
            "ContactID",
            "ContactId",
            "tenant_id",
            "TenantID",
            "TenantId",
        )
        start_date = parse_date(first_value(record, "start_date", "StartDate", "start"))
        if not start_date:
            raise ImportError(f"Tenancy {external_id} is missing a start date")
        end_date = parse_date(first_value(record, "end_date", "EndDate", "finish"))
        rent = parse_decimal(first_value(record, "rent", "Rent", "rentAmount", "Amount"))
        if rent is None:
            raise ImportError(f"Tenancy {external_id} is missing a rent amount")
        status = first_value(record, "status", "Status", "tenancyStatus", "TenancyStatus")
        notes = first_value(record, "notes", "Notes", "comments", "Comments")
        payload = {
            "external_id": external_id,
            "property_external_id": property_ext,
            "contact_external_id": contact_ext,
            "start_date": start_date,
            "end_date": end_date,
            "rent": rent,
            "status": slugify(status or "active", "active"),
        }
        if notes:
            payload["notes"] = notes
        normalized.append(payload)
    LOGGER.info("Normalised %s Apex27 tenancies", len(normalized))
    return normalized


def normalize_apex_payments(records: List[dict]) -> List[dict]:
    normalized: List[dict] = []
    for record in records:
        external_id = ensure_external_id(
            "Payment",
            record,
            "id",
            "ID",
            "Id",
            "payment_id",
            "PaymentID",
            "PaymentId",
            "reference",
        )
        tenancy_ext = ensure_external_id(
            "Payment tenancy reference",
            record,
            "tenancy_id",
            "TenancyID",
            "TenancyId",
        )
        amount = parse_decimal(first_value(record, "amount", "Amount", "value", "Value"))
        if amount is None:
            raise ImportError(f"Payment {external_id} is missing an amount")
        status = first_value(record, "status", "Status", "paymentStatus", "PaymentStatus")
        reference = first_value(record, "stripe_reference", "StripeReference", "reference", "Reference")
        payload = {
            "external_id": external_id,
            "tenancy_external_id": tenancy_ext,
            "amount": amount,
            "status": slugify(status or "completed", "completed"),
        }
        if reference:
            payload["stripe_reference"] = reference
        normalized.append(payload)
    LOGGER.info("Normalised %s Apex27 payments", len(normalized))
    return normalized



def authenticate(base_url: str, email: str, password: str, timeout: float) -> str:
    url = base_url.rstrip("/") + "/api/login"
    LOGGER.debug("Authenticating at %s", url)
    response = requests.post(url, json={"email": email, "password": password}, timeout=timeout)
    if response.status_code != 200:
        raise ImportError(
            f"Authentication failed ({response.status_code}): {response.text.strip()}"
        )
    try:
        payload = response.json()
    except json.JSONDecodeError as exc:
        raise ImportError("Login response was not JSON") from exc
    token = payload.get("token") or payload.get("access_token")
    if not token:
        raise ImportError("Login response missing token field")
    LOGGER.info("Authentication successful")
    return token


def post_records(
    session: requests.Session,
    base_url: str,
    endpoint: str,
    records: Iterable[dict],
    timeout: float,
    dry_run: bool,
    continue_on_error: bool,
) -> List[dict]:
    results: List[dict] = []
    for index, record in enumerate(records, start=1):
        LOGGER.debug("Processing record %s for %s", index, endpoint)
        if dry_run:
            LOGGER.info("[DRY RUN] Would POST to %s: %s", endpoint, record)
            results.append({"id": None, "payload": record})
            continue
        url = base_url.rstrip("/") + endpoint
        response = session.post(url, json=record, timeout=timeout)
        if response.status_code >= 400:
            message = (
                f"POST {endpoint} failed with status {response.status_code}: "
                f"{response.text.strip()}"
            )
            if continue_on_error:
                LOGGER.error(message)
                continue
            raise ImportError(message)
        try:
            data = response.json()
        except json.JSONDecodeError:
            data = {"raw": response.text}
        results.append(data)
        LOGGER.info(
            "Created record %s at %s (status %s)", index, endpoint, response.status_code
        )
    return results


def _coerce_int(value: object) -> Optional[int]:
    if isinstance(value, int):
        return value
    if isinstance(value, float):
        if value.is_integer():
            return int(value)
        return None
    if isinstance(value, str):
        text = value.strip()
        if not text:
            return None
        try:
            return int(text)
        except ValueError:
            return None
    return None


def extract_created_id(created: dict) -> Optional[int]:
    if not isinstance(created, dict):
        return None
    direct = _coerce_int(created.get("id")) if "id" in created else None
    if direct is not None:
        return direct
    data = created.get("data") if isinstance(created.get("data"), dict) else None
    if isinstance(data, dict):
        nested = _coerce_int(data.get("id"))
        if nested is not None:
            return nested
    return None


def map_external_ids(
    sources: Iterable[dict],
    results: Iterable[dict],
    store: Dict[str, Optional[int]],
    allow_missing: bool = False,
) -> None:
    for source, created in zip(sources, results):
        external_id = source.pop("external_id", None)
        if not external_id:
            continue
        created_id = extract_created_id(created)
        if created_id is None and allow_missing:
            created_id = len(store) + 1
        store[external_id] = created_id
        LOGGER.debug("Captured mapping %s -> %s", external_id, created_id)


def enrich_property_payload(
    record: dict,
    contact_ids: Dict[str, Optional[int]],
    allow_missing_ids: bool = False,
) -> dict:
    payload = {k: v for k, v in record.items() if not k.endswith("_external_id") and k != "external_id"}
    for key, field in (
        ("landlord_external_id", "landlord_id"),
        ("vendor_external_id", "vendor_id"),
        ("applicant_external_id", "applicant_id"),
        ("owner_external_id", "owner_id"),
    ):
        external = record.get(key)
        if external:
            contact_id = contact_ids.get(external)
            if contact_id is None:
                if allow_missing_ids:
                    LOGGER.debug(
                        "Skipping unresolved contact %s on property during dry-run",
                        external,
                    )
                    continue
                raise ImportError(
                    f"Unknown contact external id '{external}' referenced in property"
                )
            payload[field] = contact_id
    return payload


def enrich_tenancy_payload(
    record: dict,
    property_ids: Dict[str, Optional[int]],
    contact_ids: Dict[str, Optional[int]],
    allow_missing_ids: bool = False,
) -> dict:
    payload = {k: v for k, v in record.items() if k not in {"external_id", "property_external_id", "contact_external_id"}}
    external_property = record.get("property_external_id")
    if external_property:
        property_id = property_ids.get(external_property)
        if property_id is None:
            if allow_missing_ids:
                LOGGER.debug(
                    "Skipping unresolved property %s on tenancy during dry-run",
                    external_property,
                )
            else:
                raise ImportError(
                    f"Unknown property_external_id '{external_property}' referenced in tenancy"
                )
        payload["property_id"] = property_id
    external_contact = record.get("contact_external_id")
    if external_contact:
        contact_id = contact_ids.get(external_contact)
        if contact_id is None:
            if allow_missing_ids:
                LOGGER.debug(
                    "Skipping unresolved contact %s on tenancy during dry-run",
                    external_contact,
                )
            else:
                raise ImportError(
                    f"Unknown contact_external_id '{external_contact}' referenced in tenancy"
                )
        payload["contact_id"] = contact_id
    return payload


def enrich_payment_payload(
    record: dict,
    tenancy_ids: Dict[str, Optional[int]],
    allow_missing_ids: bool = False,
) -> dict:
    payload = {k: v for k, v in record.items() if k not in {"external_id", "tenancy_external_id"}}
    external_tenancy = record.get("tenancy_external_id")
    if external_tenancy:
        tenancy_id = tenancy_ids.get(external_tenancy)
        if tenancy_id is None:
            if allow_missing_ids:
                LOGGER.debug(
                    "Skipping unresolved tenancy %s on payment during dry-run",
                    external_tenancy,
                )
            else:
                raise ImportError(
                    f"Unknown tenancy_external_id '{external_tenancy}' referenced in payment"
                )
        payload["tenancy_id"] = tenancy_id
    return payload



def process_import(args: argparse.Namespace) -> None:
    configure_logging(args.log_level)
    data_dir = args.data_dir
    LOGGER.info("Using data directory %s", data_dir)

    contacts_path = resolve_data_file(data_dir, args.contacts_file, "contacts")
    properties_path = resolve_data_file(data_dir, args.properties_file, "properties")
    tenancies_path = resolve_data_file(data_dir, args.tenancies_file, "tenancies")
    payments_path = resolve_data_file(data_dir, args.payments_file, "payments")

    raw_contacts = load_dataset(contacts_path)
    raw_properties = load_dataset(properties_path)
    raw_tenancies = load_dataset(tenancies_path)
    raw_payments = load_dataset(payments_path)

    if args.apex_format == "raw":
        contacts = normalize_apex_contacts(raw_contacts)
        properties = normalize_apex_properties(raw_properties)
        tenancies = normalize_apex_tenancies(raw_tenancies)
        payments = normalize_apex_payments(raw_payments)
    else:
        contacts = raw_contacts
        properties = raw_properties
        tenancies = raw_tenancies
        payments = raw_payments

    session = requests.Session()
    session.headers.update({"Accept": "application/json"})


    if not args.dry_run:
        token = authenticate(args.base_url, args.email, args.password, args.timeout)
        session.headers["Authorization"] = f"Bearer {token}"

    contact_ids: Dict[str, Optional[int]] = {}
    property_ids: Dict[str, Optional[int]] = {}
    tenancy_ids: Dict[str, Optional[int]] = {}

    success_counts = {
        "contacts": 0,
        "properties": 0,
        "tenancies": 0,
        "payments": 0,
    }

    if contacts:
        contact_payloads = [
            {k: v for k, v in record.items() if k != "external_id"}
            for record in contacts
        ]
        contact_results = post_records(
            session,
            args.base_url,
            "/api/contacts",
            contact_payloads,
            args.timeout,
            args.dry_run,
            args.continue_on_error,
        )
        map_external_ids(contacts, contact_results, contact_ids, allow_missing=args.dry_run)
        success_counts["contacts"] = len(contact_results)
    else:
        LOGGER.info("No contacts supplied; tenancy/contact relationships may fail if required")

    property_payloads = [
        enrich_property_payload(record, contact_ids, allow_missing_ids=args.dry_run)
        for record in properties
    ]

    property_results = post_records(
        session,
        args.base_url,
        "/api/properties",
        property_payloads,

        args.timeout,
        args.dry_run,
        args.continue_on_error,
    )
    map_external_ids(
        properties,
        property_results,
        property_ids,
        allow_missing=args.dry_run,
    )
    success_counts["properties"] = len(property_results)

    tenancy_payloads = [
        enrich_tenancy_payload(
            record,
            property_ids,
            contact_ids,
            allow_missing_ids=args.dry_run,
        )
        for record in tenancies
    ]

    tenancy_results = post_records(
        session,
        args.base_url,
        "/api/tenancies",
        tenancy_payloads,

        args.timeout,
        args.dry_run,
        args.continue_on_error,
    )
    map_external_ids(
        tenancies,
        tenancy_results,
        tenancy_ids,
        allow_missing=args.dry_run,
    )
    success_counts["tenancies"] = len(tenancy_results)

    payment_payloads = [
        enrich_payment_payload(
            record,
            tenancy_ids,
            allow_missing_ids=args.dry_run,
        )
        for record in payments
    ]

    payment_results = post_records(
        session,
        args.base_url,
        "/api/payments",
        payment_payloads,

        args.timeout,
        args.dry_run,
        args.continue_on_error,
    )

    success_counts["payments"] = len(payment_results)

    totals = {
        "contacts": len(contacts),
        "properties": len(properties),
        "tenancies": len(tenancies),
        "payments": len(payments),
    }

    summary_parts = [
        f"{name}: {success_counts[name]}/{totals[name]}"
        for name in ("contacts", "properties", "tenancies", "payments")
    ]

    processed_total = sum(success_counts.values())
    expected_total = sum(totals.values())

    LOGGER.info("Import completed successfully")
    LOGGER.info("Summary -> %s", ", ".join(summary_parts))
    LOGGER.info("Total records processed: %s/%s", processed_total, expected_total)


def main(argv: Optional[List[str]] = None) -> int:
    try:
        args = parse_args(argv)
        process_import(args)
    except ImportError as exc:
        LOGGER.error("Import failed: %s", exc)
        return 1
    except requests.RequestException as exc:
        LOGGER.error("HTTP request failed: %s", exc)
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main())
