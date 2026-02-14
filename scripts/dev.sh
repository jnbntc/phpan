#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo "Iniciando servidor en http://127.0.0.1:8000"
php -S 127.0.0.1:8000 -t .
