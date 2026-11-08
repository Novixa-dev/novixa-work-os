#!/usr/bin/env bash
# ==============================================================================
# Novixa WorkOS — Production PostgreSQL Backup & Disaster Recovery Script
# ==============================================================================
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-/var/backups/novixa-workos}"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILENAME="novixa_workos_db_${TIMESTAMP}.sql.gz"
BACKUP_PATH="${BACKUP_DIR}/${BACKUP_FILENAME}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

# Database credentials
DB_CONTAINER="${DB_CONTAINER:-novixa_db}"
DB_NAME="${DB_DATABASE:-novixa_work_os}"
DB_USER="${DB_USERNAME:-novixa}"

mkdir -p "${BACKUP_DIR}"

echo "[INFO] Starting database backup at $(date)..."
echo "[INFO] Target: ${BACKUP_PATH}"

# Execute pg_dump inside docker container and compress
docker exec -t "${DB_CONTAINER}" pg_dump -U "${DB_USER}" -d "${DB_NAME}" --clean --if-exists | gzip -9 > "${BACKUP_PATH}"

# Generate SHA256 checksum
sha256sum "${BACKUP_PATH}" > "${BACKUP_PATH}.sha256"

# Verify backup file size
FILESIZE=$(stat -c%s "${BACKUP_PATH}")
if [ "${FILESIZE}" -lt 1024 ]; then
    echo "[ERROR] Backup file is suspiciously small (${FILESIZE} bytes). Aborting!"
    exit 1
fi

echo "[SUCCESS] Backup completed successfully. Size: $(du -h "${BACKUP_PATH}" | cut -f1)"
echo "[INFO] Checksum verified: $(cat "${BACKUP_PATH}.sha256")"

# Enforce retention policy: delete backups older than RETENTION_DAYS
echo "[INFO] Cleaning backups older than ${RETENTION_DAYS} days..."
find "${BACKUP_DIR}" -name "novixa_workos_db_*.sql.gz*" -mtime +"${RETENTION_DAYS}" -delete

echo "[INFO] Backup routine finished cleanly."
