# uminaibi-resin.jp 運用手順書

## しくみ
- このサーバー（R-ENTRY と同じ VPS）で**作る・直すだけ**。公開はしない。
- 公開先は uminaibi-resin.jp の今のサーバー（157.7.189.64・ムームードメインで向き先を設定済み）。
- 送り方は FTP。`bin/deploy.sh` が `site/` の中身を、サーバーの公開フォルダへ送る。

## 初回の準備
1. `cp deploy.env.example deploy.env && chmod 600 deploy.env`
2. `deploy.env` に FTP のホスト・ユーザー名・パスワード・公開フォルダを書く
3. `bin/deploy.sh`（確認のみ）で、つながるか・何が送られるかを見る

## 直して公開する
| 手順 | やり方 |
|---|---|
| 1. 直す | `site/` のファイルを直す（ビルド不要） |
| 2. 確かめる | `bin/deploy.sh` … 送られるファイルの一覧が出る（まだ送らない） |
| 3. 送る | `bin/deploy.sh --go` |
| 4. 記録 | `git add -A && git commit -m "内容" && git push origin main` |

★サーバーにだけあるファイル（前の WordPress の名残など）は消さない作りにしてある。

## フォルダ
- `site/` … 公開するファイル
- `bin/deploy.sh` … FTP で送るスクリプト
- `deploy.env.example` … 接続情報のひな形（本物の `deploy.env` は git に入れない）
- `docs/` … 課題・決定ログ
