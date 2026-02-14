#!/usr/bin/env bash
set -euo pipefail

echo "Verificando PHP..."
if ! command -v php >/dev/null 2>&1; then
  echo "PHP no esta instalado o no esta en PATH." >&2
  exit 1
fi

if ! php -m | grep -iq '^PDO$'; then
  echo "Falta extension PDO." >&2
  exit 1
fi

if ! php -m | grep -iq '^pdo_sqlite$'; then
  echo "Falta extension pdo_sqlite." >&2
  exit 1
fi

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DATA_DIR="$PROJECT_ROOT/data"
NEW_DB="$DATA_DIR/recipes.sqlite"
LEGACY_DB="$PROJECT_ROOT/recipes.sqlite"

mkdir -p "$DATA_DIR"

if [[ ! -f "$NEW_DB" ]]; then
  if [[ -f "$LEGACY_DB" ]]; then
    cp "$LEGACY_DB" "$NEW_DB"
    echo "Base de datos migrada desde recipes.sqlite a data/recipes.sqlite."
  else
    php "$PROJECT_ROOT/scripts/init_db.php"
    echo "Base de datos inicializada en data/recipes.sqlite."
  fi
else
  echo "Base de datos ya existe en data/recipes.sqlite."
fi

echo
echo "Instalacion finalizada."
echo "Para iniciar el proyecto:"
echo "  bash scripts/dev.sh"
