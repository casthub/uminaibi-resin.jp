#!/bin/bash
# Tailwind の CSS ファイル（site/css/tailwind.css）を作り直す。2026-10-28
#   使い方:  bin/build-css.sh
#   いつ使う: HTML や js/main.js で、Tailwind のクラス（文字の大きさ・余白・色など）を変えたとき。
#            作り直さないと、新しく書いたクラスが効かない。
# ★このサーバーは本番用なので、Docker も Node も使わない。Tailwind 公式の単体の道具（約40MB）を
#   一時フォルダに入れて使い、終わったら消す。
set -euo pipefail
cd "$(dirname "$0")/.."
VER=3.4.17
TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
curl -fsSL -o "$TMP/tailwindcss" "https://github.com/tailwindlabs/tailwindcss/releases/download/v$VER/tailwindcss-linux-x64"
chmod +x "$TMP/tailwindcss"
"$TMP/tailwindcss" -c tools/tailwind/tailwind.config.js -i tools/tailwind/input.css -o site/css/tailwind.css --minify
echo "OK: site/css/tailwind.css を作り直しました（$(wc -c < site/css/tailwind.css) バイト）"
echo "★全 HTML の tailwind.css?v=… を新しい日付に変えてから送ってください"
