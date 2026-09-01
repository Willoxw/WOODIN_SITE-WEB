#!/usr/bin/env sh
set -eu

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="${DB_NAME:-woodin_db}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$SCRIPT_DIR/../backups}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
backupFile="$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql"
logFile="${LOG_FILE:-$SCRIPT_DIR/backup.log}"

if mysqldump --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" "$DB_NAME" > "$backupFile" && gzip -f "$backupFile"; then
	find "$BACKUP_DIR" -name '*.sql.gz' -mtime +7 -delete
	printf '[%s] Backup OK - %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$(du -h "$backupFile.gz" | cut -f1)" >> "$logFile"
	printf 'Backup created: %s\n' "$backupFile.gz"
else
	rm -f "$backupFile"
	printf '[%s] ERREUR BACKUP\n' "$(date '+%Y-%m-%d %H:%M:%S')" >> "$logFile"
	exit 1
fi
