#!/bin/bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

if [ -z "${1:-}" ]; then
  echo "Usage: ./db.sh <env> <dump|restore> [dump_file]"
  echo "  env:   local, prod, prod.armenia …"
  echo ""
  echo "Examples:"
  echo "  ./db.sh local dump                  # -> dumps/loadex_2026-03-17_143000.sql.gz"
  echo "  ./db.sh local restore dumps/loadex_2026-03-17_143000.sql.gz"
  exit 1
fi

env_file=".env.$1"
if [ ! -f "${SCRIPT_DIR}/${env_file}" ]; then
  echo "❌ Env file not found: ${SCRIPT_DIR}/${env_file}"
  exit 1
fi

source "${SCRIPT_DIR}/${env_file}"

ACTION="${2:-}"
DUMP_DIR="${SCRIPT_DIR}/dumps"
mkdir -p "${DUMP_DIR}"

case "${ACTION}" in
  dump)
    TIMESTAMP=$(date +%Y-%m-%d_%H%M%S)
    DUMP_FILE="${DUMP_DIR}/${DB_DATABASE}_${TIMESTAMP}.sql.gz"

    echo "📦 Dumping database '${DB_DATABASE}' from container ${APP_NAME}_postgres …"
    docker exec "${APP_NAME}_postgres" \
      pg_dump -U "${DB_USERNAME}" -d "${DB_DATABASE}" --no-owner --no-acl \
      | gzip > "${DUMP_FILE}"

    echo "✅ Dump saved: ${DUMP_FILE} ($(du -h "${DUMP_FILE}" | cut -f1))"
    ;;

  restore)
    DUMP_FILE="${3:-}"
    if [ -z "${DUMP_FILE}" ]; then
      echo "❌ Please specify dump file to restore."
      echo "Available dumps:"
      ls -1t "${DUMP_DIR}"/*.sql.gz 2>/dev/null || echo "  (none)"
      exit 1
    fi

    # Resolve: try as-is, then relative to DUMP_DIR, then by basename in DUMP_DIR
    if [ ! -f "${DUMP_FILE}" ]; then
      if [ -f "${DUMP_DIR}/${DUMP_FILE}" ]; then
        DUMP_FILE="${DUMP_DIR}/${DUMP_FILE}"
      elif [ -f "${DUMP_DIR}/$(basename "${DUMP_FILE}")" ]; then
        DUMP_FILE="${DUMP_DIR}/$(basename "${DUMP_FILE}")"
      else
        echo "❌ File not found: ${DUMP_FILE}"
        echo "Available dumps:"
        ls -1t "${DUMP_DIR}"/*.sql.gz 2>/dev/null || echo "  (none)"
        exit 1
      fi
    fi

    echo "⚠️  This will DROP and recreate database '${DB_DATABASE}'!"
    read -r -p "Continue? [y/N] " confirm
    if [[ ! "${confirm}" =~ ^[Yy]$ ]]; then
      echo "Aborted."
      exit 0
    fi

    echo "🗑️  Dropping and recreating database '${DB_DATABASE}' …"
    docker exec "${APP_NAME}_postgres" \
      psql -U "${DB_USERNAME}" -d postgres \
      -c "DROP DATABASE IF EXISTS \"${DB_DATABASE}\";" \
      -c "CREATE DATABASE \"${DB_DATABASE}\" OWNER \"${DB_USERNAME}\";"

    echo "📥 Restoring from ${DUMP_FILE} …"
    gunzip -c "${DUMP_FILE}" \
      | docker exec -i "${APP_NAME}_postgres" \
        psql -U "${DB_USERNAME}" -d "${DB_DATABASE}" --quiet

    echo "✅ Database '${DB_DATABASE}' restored from ${DUMP_FILE}"
    ;;

  *)
    echo "❌ Unknown action: '${ACTION}'. Use 'dump' or 'restore'."
    exit 1
    ;;
esac
