#!/bin/bash

echo "============================================"
echo "  NFT Thumbnail Backfill Tool"
echo "  Rebuilds missing/deleted thumbnails"
echo "============================================"
echo ""

cd "$(dirname "$0")/.."

echo "Collections:"
echo "  0. All collections"
php artisan tinker --execute="App\Models\Collection::query()->orderBy('id')->get(['id','name'])->each(function(\$c){echo '  '.\$c->id.'. '.\$c->name.PHP_EOL;});"
echo ""

read -p "Type collection number (0 = all): " COLLECTION_CHOICE
COLLECTION_CHOICE=${COLLECTION_CHOICE:-0}
echo ""

echo "Run mode:"
echo "  1. Missing only (also repairs deleted thumbnail files)"
echo "  2. Force re-generate all in selected scope"
read -p "Choose mode [1/2] (default 1): " MODE_CHOICE
MODE_CHOICE=${MODE_CHOICE:-1}

CMD="php artisan nfts:backfill-thumbnails"
if [ "$COLLECTION_CHOICE" != "0" ]; then
    CMD="$CMD --collection-id=$COLLECTION_CHOICE"
fi
if [ "$MODE_CHOICE" == "2" ]; then
    CMD="$CMD --force"
fi

echo ""
echo "Running: $CMD"
$CMD
echo ""
echo "Done."