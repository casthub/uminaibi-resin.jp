# uminaibi-resin.jp（うみないび）— Claude Code 用の決まりごと

沖縄県名護市のレジンアート専門店「うみないび」の公式サイト。体験教室・ギャラリー・アクセス・お問い合わせ。
★R-ENTRY（/opt/r-entry, r-entry.xvps.jp）とは**まったく別のプロジェクト**。コード・設定・DB・メモを混ぜないこと。

## 場所
- サイトのファイル: `site/`（いまは静的 HTML 6ページ + css/js。Tailwind は CDN 読み込み）
- nginx 設定: `infra/nginx/`（/etc/nginx/sites-enabled/uminaibi-resin.jp.conf はここへのリンク）
- 手順書: `README.md` / 課題: `docs/課題.md` / 決めたこと: `docs/決定ログ.md`
- GitHub: github.com/casthub/uminaibi-resin（SSH の別名 `github-uminaibi-resin`、鍵 /root/.ssh/uminaibi-resin-deploy）

## 決まり
- オーナーはプログラマーではない。説明・手順書はやさしい日本語で。
- サーバーは R-ENTRY と同居（162.43.24.69）。nginx を触るときは必ず `nginx -t` を通してから reload。
  R-ENTRY のファイル（/opt/r-entry、snippets/r-entry-*.conf、conf.d/r-entry-*.conf）は変更しない。
- `site/` を直したら、そのまま公開される（ビルドなし）。直したら `git commit` して `git push origin main`。
- 文章を変えるときは、景品表示法（言い過ぎ・根拠のない最上級）と特定商取引法（予約・料金の表示）に気をつける。
- 写真・画像は、店が権利を持つものだけを使う。
