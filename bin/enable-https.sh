#!/bin/sh
# DNS（ムームードメイン）の向き先をこのサーバー（162.43.24.69）に切り替えたあとに実行する。
#   1. DNS がこのサーバーを向いているか確かめる  2. 証明書を取る  3. HTTPS の設定に差し替える
set -eu
IP=162.43.24.69
for h in uminaibi-resin.jp www.uminaibi-resin.jp; do
  got=$(dig +short A "$h" @8.8.8.8 | tail -1)
  [ "$got" = "$IP" ] || { echo "NG: $h はまだ $got を向いています（$IP にしてから再実行）" >&2; exit 1; }
done
certbot certonly --webroot -w /var/www/certbot -d uminaibi-resin.jp -d www.uminaibi-resin.jp \
  --non-interactive --agree-tos -m retrin.2023@gmail.com --keep-until-expiring
DST=/etc/nginx/sites-enabled/uminaibi-resin.jp.conf
ln -sf /opt/uminaibi-resin/infra/nginx/https.conf "$DST"
if nginx -t 2>/dev/null; then
  systemctl reload nginx && echo "OK: https://uminaibi-resin.jp/ で公開しました"
else
  ln -sf /opt/uminaibi-resin/infra/nginx/http-only.conf "$DST"; nginx -t; echo "NG: HTTPS の設定に誤り。HTTP に戻しました" >&2; exit 1
fi
