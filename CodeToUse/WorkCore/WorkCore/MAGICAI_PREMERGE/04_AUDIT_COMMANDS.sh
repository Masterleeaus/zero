#!/usr/bin/env bash
set -e

echo '=== PRE-MERGE AUDIT ==='
find app/ -name '*Auth*.php'
find app/ -name '*User*.php'
find app/Providers -name '*.php'
grep -R "Auth::" app/ || true
grep -R "Gate::" app/ || true
grep -R "Route::auth" routes/ || true
find config/ -type f -name '*.php'
find database/migrations -name '*user*' -o -name '*role*' -o -name '*permission*'
find routes/ -type f -name '*.php'
