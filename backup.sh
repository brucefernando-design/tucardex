#!/bin/bash
# =============================================================
# TuCardex — Backup Nocturno MariaDB con Sincronización a Cloudflare R2
# Generado: 2026-09-18 | Cron: 02:00 AM diario
# =============================================================
BACKUP_DIR="/opt/tucardex/backups"
CONTAINER="tucardex-db"
DB_NAME="tucardex"
DB_ROOT_PASS="RootCardex2026!"
DATE=$(date +%Y-%m-%d_%H%M)
KEEP_DAYS=30
LOG="$BACKUP_DIR/backup.log"

mkdir -p "$BACKUP_DIR"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] === Iniciando backup TuCardex ===" >> "$LOG"

BACKUP_FILE="$BACKUP_DIR/tucardex-$DATE.sql.gz"

docker exec "$CONTAINER" mariadb-dump \
  -u root \
  -p"$DB_ROOT_PASS" \
  --single-transaction \
  --routines \
  --triggers \
  --add-drop-table \
  "$DB_NAME" | gzip > "$BACKUP_FILE"

EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ]; then
  SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] OK Backup Local: tucardex-$DATE.sql.gz ($SIZE)" >> "$LOG"
  
  # Copiar temporalmente para que el contenedor lo suba a Cloudflare R2
  cp "$BACKUP_FILE" "/opt/tucardex/app/storage/app/backup_latest.sql.gz"
  docker exec tucardex-app php /var/www/html/sync_backup_r2.php /var/www/html/storage/app/backup_latest.sql.gz >> "$LOG" 2>&1
  rm -f "/opt/tucardex/app/storage/app/backup_latest.sql.gz"
  
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] OK Sincronizado con Cloudflare R2 (Offsite Disaster Recovery)" >> "$LOG"
else
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: Fallo el backup (exit code $EXIT_CODE)" >> "$LOG"
  exit 1
fi

# Conservar solo los ultimos $KEEP_DAYS dias en local
find "$BACKUP_DIR" -name "tucardex-*.sql.gz" -mtime +$KEEP_DAYS -delete
REMAINING=$(find "$BACKUP_DIR" -name "tucardex-*.sql.gz" | wc -l)
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backups locales conservados: $REMAINING archivos" >> "$LOG"
