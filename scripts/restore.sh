#!/usr/bin/env bash
# ==============================================================================
# Novixa WorkOS — Production Database Restoration Drill Script
# ==============================================================================
set -euo pipefail

if [ $# -lt 1 ]; then
    echo "Usage: $0 <path-to-backup-file.sql.gz>"
    echo "Example: $0 /var/backups/novixa-workos/novixa_workos_db_20261108_060000.sql.gz"
    exit 1
fi

BACKUP_FILE="$1"
DB_CONTAINER="${DB_CONTAINER:-novixa_db}"
DB_NAME="${DB_DATABASE:-novixa_work_os}"
DB_USER="${DB_USERNAME:-novixa}"

if [ ! -f "${BACKUP_FILE}" ]; then
    echo "[ERROR] Backup file not found: ${BACKUP_FILE}"
    exit 1
fi

# Verify Checksum if present
if [ -f "${BACKUP_FILE}.sha256" ]; then
    echo "[INFO] Verifying SHA256 checksum..."
    sha256sum -c "${BACKUP_FILE}.sha256"
fi

echo "[WARNING] You are about to restore the database '${DB_NAME}' from: ${BACKUP_FILE}"
echo "[INFO] Commencing restoration drill..."

# Decompress and feed to psql inside container
gunzip -c "${BACKUP_FILE}" | docker exec -i "${DB_CONTAINER}" psql -U "${DB_USER}" -d "${DB_NAME}"

echo "[SUCCESS] Database restoration completed successfully at $(date)."
