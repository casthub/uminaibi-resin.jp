# uminaibi-resin.jp 運用手順書

## いまの状態（2026-10-26）
- このサーバー（162.43.24.69）に、今のサイトと同じ内容を移した（HTTP のみ・まだ非公開）。
- ドメイン uminaibi-resin.jp の向き先は、まだ前のサーバー（157.7.189.64）。お客様には前のサーバーのサイトが見えている。
- 確かめ方（このサーバーの中で）:
  `curl -sI --resolve uminaibi-resin.jp:80:127.0.0.1 http://uminaibi-resin.jp/`

## 公開（切り替え）の手順
1. ムームードメインの「ムームーDNS」で、`uminaibi-resin.jp` と `www.uminaibi-resin.jp` の A レコードを `162.43.24.69` に変える
   （メールを使っている場合は MX などほかのレコードは**そのまま**にする）
2. 反映を待つ（数分〜数時間）
3. `/opt/uminaibi-resin/bin/enable-https.sh` を実行 → 証明書を取って https で公開
4. 証明書の更新は certbot の自動更新（R-ENTRY と共通の仕組み）で行われる

## よく使うこと
| したいこと | やり方 |
|---|---|
| 文章・画像を直す | `site/` のファイルを直す → すぐ反映（ビルド不要） |
| 変更を記録する | `cd /opt/uminaibi-resin && git add -A && git commit -m "内容" && git push origin main` |
| nginx に登録し直す | `bin/enable-site.sh`（HTTP）/ `bin/enable-https.sh`（HTTPS） |

## フォルダ
- `site/` … 公開するファイル
- `infra/nginx/` … nginx の設定（http-only.conf / https.conf / site-common.conf）
- `bin/` … 登録・公開のスクリプト
- `docs/` … 課題・決定ログ
