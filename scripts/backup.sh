#!/bin/bash
set -e

# Load environment
ENV_FILE="$(dirname "$0")/../.env"
if [ -f "$ENV_FILE" ]; then
    export $(grep -v '^#' "$ENV_FILE" | xargs)
fi

BACKUP_DIR="$(dirname "$0")/../backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
TARGET="${1:-all}"

mkdir -p "$BACKUP_DIR"

backup_mysql() {
    echo "Backing up MySQL 8.x..."
    docker exec localdev_mysql mysqldump -u root -p"${MYSQL_ROOT_PASSWORD:-rootpass}" --all-databases > "${BACKUP_DIR}/mysql_all_${TIMESTAMP}.sql"
    gzip -f "${BACKUP_DIR}/mysql_all_${TIMESTAMP}.sql"
    echo "MySQL backup created: ${BACKUP_DIR}/mysql_all_${TIMESTAMP}.sql.gz"
}

backup_mariadb() {
    echo "Backing up MariaDB..."
    docker exec localdev_mariadb mariadb-dump -u root -p"${MARIADB_ROOT_PASSWORD:-rootpass}" --all-databases > "${BACKUP_DIR}/mariadb_all_${TIMESTAMP}.sql"
    gzip -f "${BACKUP_DIR}/mariadb_all_${TIMESTAMP}.sql"
    echo "MariaDB backup created: ${BACKUP_DIR}/mariadb_all_${TIMESTAMP}.sql.gz"
}

backup_postgres() {
    echo "Backing up PostgreSQL..."
    docker exec -e PGPASSWORD="${POSTGRES_PASSWORD:-devpass}" localdev_postgresql pg_dumpall -U "${POSTGRES_USER:-devuser}" > "${BACKUP_DIR}/postgres_all_${TIMESTAMP}.sql"
    gzip -f "${BACKUP_DIR}/postgres_all_${TIMESTAMP}.sql"
    echo "PostgreSQL backup created: ${BACKUP_DIR}/postgres_all_${TIMESTAMP}.sql.gz"
}

case "$TARGET" in
    mysql)
        backup_mysql
        ;;
    mariadb)
        backup_mariadb
        ;;
    postgres|postgresql)
        backup_postgres
        ;;
    all)
        backup_mysql || echo "MySQL backup failed (container running?)"
        backup_mariadb || echo "MariaDB backup failed (container running?)"
        backup_postgres || echo "PostgreSQL backup failed (container running?)"
        ;;
    *)
        echo "Usage: $0 [all|mysql|mariadb|postgres]"
        exit 1
        ;;
esac

echo "Backup operation finished."

