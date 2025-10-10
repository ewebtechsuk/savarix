#!/usr/bin/env python3
"""Remove leftover temporary FTP upload files created by basic-ftp (.in.*).

Some Hostinger FTP uploads occasionally leave behind hidden files with the
pattern `.in.<filename>` when a previous transfer aborts unexpectedly. The next
deployment then fails because the FTP server refuses to overwrite that
existing temporary file. This script walks the deployment target directory and
removes any stale `.in.*` files before the main upload step runs.
"""

from __future__ import annotations

import ftplib
import os
import sys
from contextlib import contextmanager
from typing import Iterable, Tuple


ENV_PREFIX = "FTP_CLEANUP_"

ENV_HINTS = {
    "HOST": (
        "Populate the repository secret HOSTINGER_FTP_HOST (or FTP_SERVER) "
        "with the FTP hostname shown in Hostinger's hPanel under Websites → "
        "Manage → FTP Accounts. Copy just the host (for example, "
        "`darkorange-chinchilla-918430.hostingersite.com`) without the "
        "`ftp://` prefix."
    ),
    "USERNAME": (
        "Populate HOSTINGER_FTP_USERNAME (or FTP_USERNAME) with the FTP "
        "username from Hostinger's FTP Accounts page."
    ),
    "PASSWORD": (
        "Populate HOSTINGER_FTP_PASSWORD (or FTP_PASSWORD) with the FTP "
        "password from Hostinger. Reset it in hPanel if you do not have it."
    ),
    "TARGET": (
        "Populate HOSTINGER_FTP_TARGET_DIR (or FTP_TARGET_DIR) with the "
        "remote directory path, typically `public_html/`."
    ),
    "PORT": (
        "Populate HOSTINGER_FTP_PORT (or FTP_PORT) when Hostinger instructs "
        "you to use a non-standard port."
    ),
    "PROTOCOL": (
        "Populate HOSTINGER_FTP_PROTOCOL (or FTP_PROTOCOL) with ftp, ftps, "
        "or sftp to match the Hostinger connection settings."
    ),
}


class CleanupError(RuntimeError):
    """Raised when the cleanup script encounters a fatal error."""


def env(name: str, default: str | None = None) -> str:
    value = os.environ.get(f"{ENV_PREFIX}{name}")
    if value is not None:
        stripped = value.strip()
        if stripped:
            return stripped
        # Treat empty strings as missing so we either fall back to defaults
        # or raise a useful error instead of passing "" further downstream.
        if default is not None:
            return default
        hint = ENV_HINTS.get(name)
        message = f"Missing required environment variable {ENV_PREFIX}{name}"
        if hint:
            message = f"{message}. {hint}"
        raise CleanupError(message)
    if default is not None:
        return default
    hint = ENV_HINTS.get(name)
    message = f"Missing required environment variable {ENV_PREFIX}{name}"
    if hint:
        message = f"{message}. {hint}"
    raise CleanupError(message)


def normalise_target(path: str) -> str | None:
    path = path.strip()
    if not path:
        return None
    if path in {".", "./", "/"}:
        return None
    if path.startswith("./"):
        path = path[2:]
    # Hostinger paths normally do not start with a leading slash, but handle it anyway.
    if path.startswith("//"):
        # prevent accidental double slash which some FTP servers reject
        while path.startswith("//"):
            path = path[1:]
    return path.rstrip("/") or None


def join_remote(base: str, name: str) -> str:
    # Ensure the output looks like an absolute remote path for logging purposes.
    if name.startswith("/"):
        return name
    if base.endswith("/"):
        base = base[:-1]
    if not base or base == ".":
        cleaned = name.lstrip("/")
        return f"/{cleaned}" if cleaned else "/"
    return f"{base}/{name}" if name else base


def normalise_host(raw_host: str) -> str:
    host = raw_host.strip()
    if not host:
        return host
    lowered = host.lower()
    for prefix in ("ftp://", "ftps://", "sftp://"):
        if lowered.startswith(prefix):
            host = host[len(prefix) :]
            break
    host = host.strip()
    while host.endswith("/"):
        host = host[:-1]
    return host


def mask_value(value: str, *, min_visible: int = 2) -> str:
    """Return a masked representation that preserves context without secrets."""

    stripped = value.strip()
    length = len(stripped)
    if length <= min_visible * 2:
        return "*" * length if length else ""
    prefix = stripped[:min_visible]
    suffix = stripped[-min_visible:]
    return f"{prefix}{'*' * (length - (min_visible * 2))}{suffix}"


@contextmanager
def preserve_cwd(ftp: ftplib.FTP) -> Iterable[None]:
    current = ftp.pwd()
    try:
        yield
    finally:
        try:
            ftp.cwd(current)
        except ftplib.all_errors:
            # Ignore failures when trying to restore the directory – we're already bailing out.
            pass


def list_entries(ftp: ftplib.FTP) -> Iterable[Tuple[str, str | None]]:
    """Yield (name, type) pairs for items in the current directory."""
    try:
        for name, facts in ftp.mlsd():
            entry_type = facts.get("type") if isinstance(facts, dict) else None
            yield name, entry_type
        return
    except AttributeError:
        # Older Python without MLSD support – fallback handled below.
        pass
    except ftplib.error_perm as exc:
        # MLSD is optional; fall back to NLST if the server does not support it.
        if "MLSD" not in str(exc).upper():
            raise
    try:
        names = ftp.nlst()
    except ftplib.error_perm as exc:
        # 550 when directory is empty is normal – treat as no entries.
        message = str(exc)
        if not message.startswith("550"):
            raise
        names = []
    for name in names:
        yield name, None


def is_directory(ftp: ftplib.FTP, name: str) -> bool:
    current = ftp.pwd()
    try:
        ftp.cwd(name)
    except ftplib.error_perm:
        return False
    finally:
        try:
            ftp.cwd(current)
        except ftplib.all_errors:
            # We already logged in successfully; ignore failures when restoring.
            pass
    return True


def cleanup_directory(ftp: ftplib.FTP) -> int:
    removed = 0
    for raw_name, entry_type in list_entries(ftp):
        if raw_name in {".", ".."}:
            continue
        name_only = os.path.basename(raw_name)
        is_dir = entry_type in {"dir", "cdir"}
        if entry_type is None:
            is_dir = is_directory(ftp, raw_name)
        if is_dir:
            with preserve_cwd(ftp):
                ftp.cwd(raw_name)
                removed += cleanup_directory(ftp)
            continue
        if name_only.startswith(".in."):
            remote_path = join_remote(ftp.pwd(), raw_name)
            try:
                ftp.delete(raw_name)
            except ftplib.error_perm as exc:
                # If the file disappeared concurrently we can ignore it.
                if not str(exc).startswith("550"):
                    raise
            else:
                print(f"Removed stale temporary file: {remote_path}")
                removed += 1
    return removed


def connect() -> ftplib.FTP:
    protocol = env("PROTOCOL", "ftp").strip().lower()
    host = normalise_host(env("HOST"))
    if not host:
        raise CleanupError(
            "FTP hostname cannot be empty after removing any ftp:// prefix."
        )
    username = env("USERNAME")
    password = env("PASSWORD")
    port = int(env("PORT", "21"))
    timeout = int(env("TIMEOUT", "30"))

    if protocol not in {"ftp", "ftps"}:
        raise CleanupError(f"Unsupported protocol for cleanup: {protocol}")

    ftp: ftplib.FTP
    if protocol == "ftps":
        ftp_tls = ftplib.FTP_TLS()
        ftp_tls.connect(host, port, timeout=timeout)
        ftp_tls.login(username, password)
        ftp_tls.prot_p()  # switch to secure data connection
        ftp = ftp_tls
    else:
        ftp = ftplib.FTP()
        ftp.connect(host, port, timeout=timeout)
        ftp.login(username, password)

    ftp.encoding = "utf-8"

    target = normalise_target(env("TARGET", ""))
    if target:
        ftp.cwd(target)

    masked_host = mask_value(host, min_visible=3)
    print(
        "Connected to Hostinger FTP cleanup target:",
        f"host={masked_host}",
        f"protocol={protocol.upper()}",
        f"port={port}",
        f"directory={ftp.pwd()}",
    )

    return ftp


def main() -> int:
    try:
        ftp = connect()
    except CleanupError as exc:
        print(f"::error::{exc}")
        return 1
    except ftplib.all_errors as exc:
        print(f"::error::Failed to connect to FTP server: {exc}")
        return 1

    try:
        removed = cleanup_directory(ftp)
    except ftplib.all_errors as exc:
        print(f"::error::Failed to clean FTP temporary files: {exc}")
        try:
            ftp.quit()
        except ftplib.all_errors:
            pass
        return 1

    try:
        ftp.quit()
    except ftplib.all_errors:
        # Quitting gracefully is best-effort.
        pass

    if removed:
        print(f"Removed {removed} stale temporary file{'s' if removed != 1 else ''}.")
    else:
        print("No stale temporary FTP files found.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
