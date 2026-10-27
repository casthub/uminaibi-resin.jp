# Stripe の導入：残っている作業（これを正とする）

2026-10-27 作成。うみないびのオンラインショップは **Stripe Checkout（Stripe の決済ページへ移る形）** で支払いを受けます。
ショップの「購入手続きへ」ボタン → `site/checkout.php`（heteml の PHP）が Stripe に決済ページを作らせる → お客様は Stripe の画面でカード等を入力 → 終わったら `shop-thanks.html` に戻ります。

## Values to Replace（置き換える値）

The following values are placeholders and must be updated before going live.

**Files containing placeholders:**
- [site/stripe-config.php](site/stripe-config.php)（git 対象外。ひな形は [stripe-config.example.php](stripe-config.example.php)）
- [site/shop.html](site/shop.html)（商品カード。今はサンプル1件・ボタンは「準備中」）

| Field | Current Value | What to Set |
|-------|--------------|-------------|
| mode | payment | 一回払いの販売なので `payment` のまま（定期購入はしない）。 |
| success_url | https://uminaibi-resin.jp/shop-thanks.html?session_id={CHECKOUT_SESSION_ID} | 設定済み（本物のページ）。変えるときも {CHECKOUT_SESSION_ID} は残す。 |
| cancel_url | https://uminaibi-resin.jp/shop.html | 設定済み（ショップに戻る）。 |
| line_items[].price | `stripe-config.php` の `items`（今はテスト用 `sample` → price_1UUznAKxCvDyFibolpwd3Ur9・1,000円） | 本物の作品ごとに Stripe で価格（税込・`tax_behavior=inclusive`）を作り、その Price ID を `items` に足す。テスト用 `sample` は本番前に消す。 |
| secret_key | テスト用 sk_test_…（サンドボックス） | 本番を始めるときに sk_live_… に替える。★会話・メールに貼らず、サーバーで直接書く。 |
| shipping_rates | （空） | ゆうパック（沖縄県から発送）の配送料を Stripe で作り、shr_… を最大5つ。空のままだと送料0円になるので、**販売開始前に必ず入れる**。 |

## Configured Parameters（Checkout Studio で決めた値・設定済み）

These parameters were configured in Checkout Studio and are already set correctly.

**Files containing these parameters:**
- [site/checkout.php](site/checkout.php)

| Parameter | Value |
|-----------|-------|
| ui_mode | hosted_page |
| billing_address_collection | auto |
| phone_number_collection | { enabled: false } |
| automatic_tax | { enabled: false }（価格は税込みで登録するため） |
| allow_promotion_codes | false |
| submit_type | auto |
| integration_identifier | hosted_web_0001 |
| origin_context | web |

※ `payment_method_collection` は定期購入（subscription）のときだけ使う決まりなので、入れていません。
※ ほかに、作品を送るために `shipping_address_collection`（日本のみ）と `shipping_options`（送料）を入れています。
※ 2026-10-27 テスト用サンドボックスで、上の値すべてで決済ページが作れることを確認済み（API のバージョンは指定せず、アカウントの既定を使う）。Stripe のライブラリは使わず API を直接呼ぶので、SDK のバージョンによる ui_mode の違いはありません。

## 設定のしかた
- キーの置き場所：
  - サイト用：`site/stripe-config.php`（heteml に送る。`.htaccess` で外から開けない。git に入れない）
  - このサーバーでの作業用：`/opt/uminaibi-resin/stripe.env`（git に入れない・送らない）
- 必要なもの：heteml の PHP 7.4 以上（追加のライブラリは不要）。
- `.htaccess` の CSP の `form-action` に `https://checkout.stripe.com` を足した（ボタンから決済ページへ移れるように）。

## 新しく作ったファイル
- `site/checkout.php` … 決済ページを作って移動させる
- `site/stripe-config.php` … キー・商品・送料の設定（git 対象外）
- `stripe-config.example.php` … その、ひな形
- `STRIPE_INTEGRATION_TODO.md` … このファイル

## しくみ（流れ）
1. shop.html の「購入手続きへ」（`<form action="checkout.php">`、hidden の `item` に商品の名前）
2. checkout.php が `item` を `stripe-config.php` の `items` で探す（無ければ「お求めいただけません」）。他のサイトからの送信は断る。
3. Stripe API で Checkout Session を作り、決済ページ（checkout.stripe.com）へ移動。金額は Price ID で決まるので、ブラウザ側で書き換えられない。
4. 支払いが終わると shop-thanks.html へ。領収書メールは Stripe から。注文はお店に Stripe からメールで届く（管理画面の通知設定）。

## テストのしかた（テストモード）
- カード番号 `4242 4242 4242 4242`、有効期限は未来の日付、セキュリティコードは好きな3桁。
- 断られるカード：`4000 0000 0000 0002`。3D セキュア：`4000 0027 6000 3184`。
- 実際のお金は動かない。結果は Stripe の管理画面（テストモード）の「支払い」で見られる。

## 次にやること
1. ★オーナーの了承を得て、`checkout.php`・`stripe-config.php`・`.htaccess`・`shop.html` を heteml に送る（`bin/deploy.sh` → `--go`）。送ったあと、テスト用 `sample` で決済ページへ移れるか確かめる。
2. Stripe の管理画面：コンビニ払いを申し込んで有効にし、支払い期限を3日に（特定商取引法の表記と同じ）。
3. Stripe の管理画面：会社の情報・本人確認・振込先口座の登録（本番の決済を受けられるように）。「公開情報」に特定商取引法の表記の URL（https://uminaibi-resin.jp/tokushoho.html）。
4. ゆうパックの送料（地域のまとめ方は5つまで）を決めて配送料を作り、`shipping_rates` に入れる。
5. 作品ができたら：Stripe に商品・価格（税込）→ `items` に追加 → shop.html のカードを本物に（写真は実物だけ）。
6. 体験教室の事前払い：Stripe の請求書（Invoicing）で送る。
7. 注文の管理：最初は Stripe の管理画面とメール通知で行う。件数が増えたら Webhook（支払い完了の知らせ）で自動化を考える。
8. 本番開始の前に：テスト用キーを作り直す（会話に貼ったため）→ 本番キー（sk_live_）に替える → テスト用 `sample` を消す。

## Resources
- https://support.stripe.com
- https://docs.stripe.com/mcp
