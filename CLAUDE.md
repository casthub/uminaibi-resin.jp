# uminaibi-resin.jp（うみないび）— Claude Code 用の決まりごと

沖縄県名護市のレジンアート専門店「うみないび」の公式サイト。体験教室・ギャラリー・アクセス・お問い合わせ。
★R-ENTRY（/opt/r-entry, r-entry.xvps.jp）とは**まったく別のプロジェクト**。コード・設定・DB・メモを混ぜないこと（サイトの切り替え表は /root/CLAUDE.md）。

## 場所
- サイトのファイル: `site/`（静的 HTML + css/js）。Tailwind は CSS ファイル（`site/css/tailwind.css`）。設定は `tools/tailwind/tailwind.config.js`、作り直しは `bin/build-css.sh`（Docker・Node は使わない）
- 公開: `bin/deploy.sh --go` で、uminaibi-resin.jp の今のサーバー（157.7.189.64）へ FTP で送る。接続情報は `deploy.env`（git に入れない）
- 手順書: `README.md` / 課題: `docs/課題.md` / 決めたこと: `docs/決定ログ.md` / Stripe の残り作業: `STRIPE_INTEGRATION_TODO.md`
- Stripe：`site/checkout.php` が Checkout Session を作る。キー・商品は `site/stripe-config.php`（git に入れない）、作業用キーは `stripe.env`（git に入れない・送らない）。キーを会話・記録・画面に出さない
- GitHub: github.com/casthub/uminaibi-resin.jp（★公開リポジトリ）。https で送る。アクセストークンは /root/.ssh/uminaibi-github-token、git の core.askPass（/root/.ssh/uminaibi-askpass.sh）が渡す。`git push` だけで送れる。トークンを git の設定・ファイルに書かない。

## 決まり
- オーナーはプログラマーではない。説明・手順書はやさしい日本語で。
- ★このサーバー（R-ENTRY と同じ 162.43.24.69）では**作るだけ**。ここで公開しない（nginx に登録しない）。
  公開先は uminaibi-resin.jp の今のサーバー。送るのは FTP（`bin/deploy.sh`）。
- 送る前に必ず `bin/deploy.sh`（確認のみ）で何が変わるかを見てから `--go`。サーバーにだけあるファイルは消さない。
- 直したら `git commit` して `git push origin main`。
- R-ENTRY のファイル（/opt/r-entry）は変更しない。
- 文章を変えるときは、景品表示法（言い過ぎ・根拠のない最上級）と特定商取引法（予約・料金の表示）に気をつける。
- 写真・画像は、店が権利を持つものだけを使う。例外（オーナー了承）：Unsplash の写真（撮影者を写真の下に書く・作品のイメージは「当店の作品ではない」と書く）。イラスト・飾りの絵は使わない（2026-10-28 オーナー指示）。
- HTML の Tailwind クラスを変えたら `bin/build-css.sh`。`css/tailwind.css`・`css/style.css`・`js/main.js` を変えたら、全 HTML の `?v=日付` を新しい日付に変える（古いファイルがブラウザに残って、見た目が崩れるのを防ぐ）。
