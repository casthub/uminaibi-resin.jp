# uminaibi-resin.jp（うみないび）— Claude Code 用の決まりごと

沖縄県名護市のレジンアート専門店「うみないび」の公式サイト。体験教室・ギャラリー・アクセス・お問い合わせ。
★R-ENTRY（/opt/r-entry, r-entry.xvps.jp）とは**まったく別のプロジェクト**。コード・設定・DB・メモを混ぜないこと。

## 場所
- サイトのファイル: `site/`（いまは静的 HTML 6ページ + css/js。Tailwind は CDN 読み込み）
- 公開: `bin/deploy.sh --go` で、uminaibi-resin.jp の今のサーバー（157.7.189.64）へ FTP で送る。接続情報は `deploy.env`（git に入れない）
- 手順書: `README.md` / 課題: `docs/課題.md` / 決めたこと: `docs/決定ログ.md`
- GitHub: github.com/casthub/uminaibi-resin（SSH の別名 `github-uminaibi-resin`、鍵 /root/.ssh/uminaibi-resin-deploy）

## 決まり
- オーナーはプログラマーではない。説明・手順書はやさしい日本語で。
- ★このサーバー（R-ENTRY と同じ 162.43.24.69）では**作るだけ**。ここで公開しない（nginx に登録しない）。
  公開先は uminaibi-resin.jp の今のサーバー。送るのは FTP（`bin/deploy.sh`）。
- 送る前に必ず `bin/deploy.sh`（確認のみ）で何が変わるかを見てから `--go`。サーバーにだけあるファイルは消さない。
- 直したら `git commit` して `git push origin main`。
- R-ENTRY のファイル（/opt/r-entry）は変更しない。
- 文章を変えるときは、景品表示法（言い過ぎ・根拠のない最上級）と特定商取引法（予約・料金の表示）に気をつける。
- 写真・画像は、店が権利を持つものだけを使う。
