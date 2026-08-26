#!/usr/bin/env sh
set -eu

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="${DB_NAME:-woodin_db}"
DB_USER="${DB_USER:-root}"
BACKUP_DIR="${BACKUP_DIR:-backups}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

mysqldump --host="$DB_HOST" --user="$DB_USER" --password "$DB_NAME" > "$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql"
printf 'Backup created: %s\n' "$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql"
