#!/bin/bash
set -e

ENV_FILE="$(dirname "$0")/../.env"
if [ -f "$ENV_FILE" ]; then
    export $(grep -v '^#' "$ENV_FILE" | xargs)
fi

FILE="$1"
DB_TYPE="$2"

if [ -z "$FILE" ]; then
    echo "Usage: $0 <backup_file.sql[.gz]> [mysql|mariadb|postgres]"
    exit 1
fi

if [ ! -f "$FILE" ]; then
    echo "Error: Backup file '$FILE' not found."
    exit 1
fi

# Auto-detect db type if not specified
if [ -z "$DB_TYPE" ]; then
    if [[ "$FILE" =~ mysql ]]; then
        DB_TYPE="mysql"
    elif [[ "$FILE" =~ mariadb ]]; then
        DB_TYPE="mariadb"
    elif [[ "$FILE" =~ postgres ]]; then
        DB_TYPE="postgres"
    else
        echo "Please specify target database: [mysql|mariadb|postgres]"
        exit 1
    fi
fi

case "$DB_TYPE" in
    mysql)
        echo "Restoring to MySQL 8.x..."
        if [[ "$FILE" == *.gz ]]; then
            gunzip -c "$FILE" | docker exec -i localdev_mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD:-rootpass}"
        else
            cat "$FILE" | docker exec -i localdev_mysql mysql -u root -p"${MYSQL_ROOT_PASSWORD:-rootpass}"
        fi
        echo "MySQL restored successfully."
        ;;
    mariadb)
        echo "Restoring to MariaDB..."
        if [[ "$FILE" == *.gz ]]; then
            gunzip -c "$FILE" | docker exec -i localdev_mariadb mariadb -u root -p"${MARIADB_ROOT_PASSWORD:-rootpass}"
        else
            cat "$FILE" | docker exec -i localdev_mariadb mariadb -u root -p"${MARIADB_ROOT_PASSWORD:-rootpass}"
        fi
        echo "MariaDB restored successfully."
        ;;
    postgres|postgresql)
        echo "Restoring to PostgreSQL..."
        if [[ "$FILE" == *.gz ]]; then
            gunzip -c "$FILE" | docker exec -i -e PGPASSWORD="${POSTGRES_PASSWORD:-devpass}" localdev_postgresql psql -U "${POSTGRES_USER:-devuser}" -d "${POSTGRES_DB:-localdev_postgres}"
        else
            cat "$FILE" | docker exec -i -e PGPASSWORD="${POSTGRES_PASSWORD:-devpass}" localdev_postgresql psql -U "${POSTGRES_USER:-devuser}" -d "${POSTGRES_DB:-localdev_postgres}"
        fi
        echo "PostgreSQL restored successfully."
        ;;
    *)
        echo "Unknown database type: $DB_TYPE. Choose mysql, mariadb, or postgres."
        exit 1
        ;;
esac

