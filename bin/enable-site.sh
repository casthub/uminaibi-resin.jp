#!/bin/sh
# uminaibi-resin.jp を nginx に登録する（HTTP のみ。DNS がまだ別のサーバーを向いていても実行してよい）
#   ★設定に誤りがあれば元に戻す（R-ENTRY を止めないため）
set -eu
SRC=/opt/uminaibi-resin/infra/nginx/http-only.conf
DST=/etc/nginx/sites-enabled/uminaibi-resin.jp.conf
ln -sf "$SRC" "$DST"
if nginx -t 2>/dev/null; then
  systemctl reload nginx && echo "OK: 登録しました（HTTP）。確かめ方: curl -sI --resolve uminaibi-resin.jp:80:127.0.0.1 http://uminaibi-resin.jp/"
else
  rm -f "$DST"; nginx -t; echo "NG: 設定に誤りがあったので元に戻しました" >&2; exit 1
fi
