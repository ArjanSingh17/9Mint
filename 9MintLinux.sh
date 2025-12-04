#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"

TERM_APP=""
for t in gnome-terminal konsole xfce4-terminal xterm; do
  if command -v "$t" >/dev/null 2>&1; then
    TERM_APP="$t"
    break
  fi
done

if [ -n "$TERM_APP" ]; then
  "$TERM_APP" -- bash -lc "cd '$(pwd)'; php artisan serve; exec bash" &
  "$TERM_APP" -- bash -lc "cd '$(pwd)'; npm run dev; exec bash" &
else
  php artisan serve &
  npm run dev &
fi

until curl -sSf "http://127.0.0.1:8000" >/dev/null 2>&1; do
  sleep 2
done

if command -v xdg-open >/dev/null 2>&1; then
  xdg-open "http://127.0.0.1:8000" >/dev/null 2>&1 &
elif command -v open >/dev/null 2>&1; then
  open "http://127.0.0.1:8000" >/dev/null 2>&1 &
fi