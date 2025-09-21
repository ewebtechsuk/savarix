#!/usr/bin/env python3
"""Utility to import Apex27 export files into a Ressapp tenant.

The tool expects three JSON files inside a data directory:
- properties.json
- tenancies.json
- payments.json

Each file should contain a list of dictionaries representing the payloads
expected by the Ressapp API endpoints.  Every object may include an
``external_id`` field that will be used to cross-reference dependent
records.  For example, a tenancy can include ``property_external_id`` so it
can be linked to the Ressapp property created from the matching property
record.

The script logs each action, supports a dry-run mode, and stops on the
first HTTP error unless ``--continue-on-error`` is supplied.
"""
from __future__ import annotations

import argparse
import json
import logging
import sys
from pathlib import Path
from typing import Dict, Iterable, List, Optional

import requests

LOGGER = logging.getLogger("apex27_import")


class ImportError(RuntimeError):
    """Raised when the import process fails."""


def parse_args(argv: Optional[List[str]] = None) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "base_url",
        help="Base URL of the Ressapp tenant, e.g. https://tenant.example.com",
    )
    parser.add_argument("email", help="Ressapp user email for API authentication")
    parser.add_argument("password", help="Ressapp user password for API authentication")
    parser.add_argument(
        "--data-dir",
        default=Path("data/apex27"),
        type=Path,
        help=(
            "Directory containing properties.json, tenancies.json, and "
            "payments.json (default: data/apex27)"
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


def load_json(path: Path) -> List[dict]:
    if not path.exists():
        LOGGER.warning("%s not found; skipping", path)
        return []
    try:
        with path.open("r", encoding="utf-8") as handle:
            data = json.load(handle)
    except json.JSONDecodeError as exc:
        raise ImportError(f"Failed to parse JSON from {path}: {exc}") from exc
    if not isinstance(data, list):
        raise ImportError(f"Expected a list in {path}, got {type(data).__name__}")
    LOGGER.info("Loaded %s records from %s", len(data), path)
    return data


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
        LOGGER.info("Created record %s at %s (status %s)", index, endpoint, response.status_code)
    return results


def enrich_tenancy_payload(record: dict, property_ids: Dict[str, int]) -> dict:
    record = record.copy()
    external_property = record.pop("property_external_id", None)
    if external_property:
        property_id = property_ids.get(external_property)
        if property_id is None:
            raise ImportError(
                f"Unknown property_external_id '{external_property}' referenced in tenancy"
            )
        record.setdefault("property_id", property_id)
    return record


def enrich_payment_payload(record: dict, tenancy_ids: Dict[str, int]) -> dict:
    record = record.copy()
    external_tenancy = record.pop("tenancy_external_id", None)
    if external_tenancy:
        tenancy_id = tenancy_ids.get(external_tenancy)
        if tenancy_id is None:
            raise ImportError(
                f"Unknown tenancy_external_id '{external_tenancy}' referenced in payment"
            )
        record.setdefault("tenancy_id", tenancy_id)
    return record


def process_import(args: argparse.Namespace) -> None:
    configure_logging(args.log_level)
    data_dir = args.data_dir
    LOGGER.info("Using data directory %s", data_dir)

    properties = load_json(data_dir / "properties.json")
    tenancies = load_json(data_dir / "tenancies.json")
    payments = load_json(data_dir / "payments.json")

    session = requests.Session()
    session.headers.update({"Accept": "application/json"})

    token: Optional[str] = None
    if not args.dry_run:
        token = authenticate(args.base_url, args.email, args.password, args.timeout)
        session.headers["Authorization"] = f"Bearer {token}"

    property_ids: Dict[str, int] = {}
    tenancy_ids: Dict[str, int] = {}

    def extract_external_id(payload: dict) -> Optional[str]:
        external_id = payload.pop("external_id", None)
        if external_id:
            LOGGER.debug("Record includes external_id=%s", external_id)
        return external_id

    property_results = post_records(
        session,
        args.base_url,
        "/api/properties",
        (dict(record) for record in properties),
        args.timeout,
        args.dry_run,
        args.continue_on_error,
    )
    for source, created in zip(properties, property_results):
        external_id = extract_external_id(source)
        if external_id:
            created_id = None
            if isinstance(created, dict):
                if "id" in created:
                    created_id = created.get("id")
                elif isinstance(created.get("data"), dict):
                    created_id = created["data"].get("id")
            property_ids[external_id] = created_id

    tenancy_records = []
    for record in tenancies:
        tenancy_records.append(enrich_tenancy_payload(record, property_ids))

    tenancy_results = post_records(
        session,
        args.base_url,
        "/api/tenancies",
        tenancy_records,
        args.timeout,
        args.dry_run,
        args.continue_on_error,
    )
    for source, created in zip(tenancies, tenancy_results):
        external_id = extract_external_id(source)
        if external_id:
            created_id = None
            if isinstance(created, dict):
                if "id" in created:
                    created_id = created.get("id")
                elif isinstance(created.get("data"), dict):
                    created_id = created["data"].get("id")
            tenancy_ids[external_id] = created_id

    payment_records = []
    for record in payments:
        payment_records.append(enrich_payment_payload(record, tenancy_ids))

    post_records(
        session,
        args.base_url,
        "/api/payments",
        payment_records,
        args.timeout,
        args.dry_run,
        args.continue_on_error,
    )
    LOGGER.info("Import completed successfully")


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
