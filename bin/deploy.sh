#!/bin/bash
# site/ の中身を、uminaibi-resin.jp の今のサーバーへ FTP で送る。
#   使い方:  bin/deploy.sh          … 送る前に「何が変わるか」だけ表示（実際には送らない）
#            bin/deploy.sh --go     … 実際に送る
# ★サーバー側にだけあるファイル（WordPress の名残など）は消さない（--delete を使わない）。
# ★サーバー側のファイルの権限（604 など）は変えない（--no-perms）。
set -euo pipefail
cd "$(dirname "$0")/.."
[ -f deploy.env ] || { echo "NG: deploy.env がありません（deploy.env.example を見てください）" >&2; exit 1; }
. ./deploy.env
: "${FTP_HOST:?}" "${FTP_USER:?}" "${FTP_PASS:?}" "${FTP_DIR:?}"
DRY="--dry-run"; [ "${1:-}" = "--go" ] && DRY=""
TLS="set ftp:ssl-force true; set ftp:ssl-protect-data true;"
[ "${FTP_TLS:-yes}" = "no" ] && TLS="set ftp:ssl-allow false;"
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" -e "
  set net:max-retries 2; set net:timeout 20; $TLS
  mirror --reverse --only-newer --no-perms --verbose $DRY --exclude-glob .git* --exclude-glob .DS_Store site/ $FTP_DIR/;
  bye" 2>&1 | sed "s#//[^@/]*@#//***@#g"   # ★画面・記録にパスワードを出さない
[ -z "$DRY" ] && echo "OK: 送りました" || echo "（確認のみ。実際に送るには bin/deploy.sh --go）"
