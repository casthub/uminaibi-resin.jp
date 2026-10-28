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
| line_items[].price | `stripe-config.php` の `items`（今はテスト用 `sample` → price_1UUznAKxCvDyFibolpwd3Ur9・1,000円・60サイズ） | 本物の作品ごとに Stripe で価格（税込・`tax_behavior=inclusive`）を作り、その Price ID を `items` に足す。テスト用 `sample` は本番前に消す。 |
| secret_key | テスト用 sk_test_…（サンドボックス） | 本番を始めるときに sk_live_… に替える。★会話・メールに貼らず、サーバーで直接書く。 |
| payment_method_options.konbini.expires_after_days | 3 | 設定済み（コンビニ払いの期限＝ご注文から3日。特定商取引法の表記と同じ）。 |
| shipping_rates | テスト用 25個（5地域 × 60/80/100/120/140サイズ）を作成済み | 本番キーに替えたら `bin/stripe-shipping-rates.py --go` で本番用を作り、表示された行を貼り替える。料金は `data/yupack-okinawa.json`。サイズの設定が無い商品は売らずに止まる（送料0円にならない）。 |

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
1. ✅ 2026-10-27 heteml に送った（オーナー了承）。テスト用 `sample` で本番サイトから決済ページへ移れることを確認。
2. Stripe の管理画面：コンビニ払いを申し込んで有効にし、支払い期限を3日に（特定商取引法の表記と同じ）。
3. Stripe の管理画面：会社の情報・本人確認・振込先口座の登録（本番の決済を受けられるように）。「公開情報」に特定商取引法の表記の URL（https://uminaibi-resin.jp/tokushoho.html）。
4. ✅ 2026-10-27 ゆうパックの送料：5地域 × 5サイズの金額表をショップ・特定商取引法の表記に掲載、テスト用の配送料を作成。商品ごとに `items` の `size` を決める。
5. 作品ができたら：Stripe に商品・価格（税込）→ `items` に追加 → shop.html のカードを本物に（写真は実物だけ）。
6. 体験教室の事前払い：Stripe の請求書（Invoicing）で送る。
7. 注文の管理：最初は Stripe の管理画面とメール通知で行う。件数が増えたら Webhook（支払い完了の知らせ）で自動化を考える。
8. 本番開始の前に：テスト用キーを作り直す（会話に貼ったため）→ 本番キー（sk_live_）に替える → テスト用 `sample` を消す。

## 決済手段のドメイン（payment method domain）
- 2026-10-28 オーナーから ID `pmd_1UVKePKKHiHiKe6OJQNUw06c` を受け取った。テスト用サンドボックス（acct_1UU4qgKxCvDyFibo）には無い ID なので、本番のアカウント側で登録したものとみられる（テスト用キーでは中身を見られない）。
- 今の形（Stripe Checkout・Stripe の決済ページへ移る）では、Apple Pay・Google Pay は checkout.stripe.com の上で動くので、uminaibi-resin.jp の登録は無くても支払える。登録が要るのは、将来サイトの中に支払い欄を埋め込む（Payment Element・Express Checkout Element）とき。
- 本番キーに替えたら、このドメインが「有効」になっているかを確かめる。

## Resources
- https://support.stripe.com
- https://docs.stripe.com/mcp

## Stripe アカウントで足りない情報（2026-10-27 確認）
今のキーはテスト用（サンドボックス）。サンドボックスの登録内容はサンプルのまま（会社名「サンプル株式会社」、電話 +81312345678、業種コード 5734＝パソコンソフト店、タイムゾーン UTC）で、本番のアカウントの審査には使われない。
本番（Stripe の管理画面で「本番環境」に切り替えて「アカウントを有効化」）で、次を登録する：

| 区分 | 登録するもの | このサイトでの値（そろえる） |
|---|---|---|
| 事業形態 | 法人（合同会社） | — |
| 会社 | 法人名（漢字・カナ・英語）、法人番号、本店の住所（漢字・カナ）、電話番号 | 合同会社LinkTOHOKU／3380003003364／〒963-0714 福島県郡山市中田町赤沼字細田84番地の13／050-7116-0133 |
| 代表者 | 代表社員の氏名（漢字・カナ）・生年月日・住所・電話・本人確認書類 | ★未定（運営責任者は鵜沼京子。代表社員と同じ人か確認） |
| 株主・役員 | 25%以上を持つ人（実質的支配者）、役員 | ★未定 |
| 事業の内容 | 業種（手づくりの美術品・工芸品の販売、体験教室）、商品の説明、ウェブサイト | https://uminaibi-resin.jp/ ※サンドボックスの link-tohoku.co.jp ではなく、売るサイトの URL |
| お客様向け表示 | 公開する事業者名・サポートのメール・電話・URL、カード明細の表記（漢字・カナ・ローマ字） | うみないび／info@uminaibi-resin.jp／050-7116-0133 |
| 特定商取引法 | 表記ページの URL | https://uminaibi-resin.jp/tokushoho.html |
| 入金 | 振込先の銀行口座（法人名義） | ★未定 |
| 安全 | 2段階認証 | — |

## コンビニ払いを有効にする（Stripe の審査あり）
1. 管理画面「設定 → 決済 → 決済手段」→「コンビニ決済」→「有効にする」（本番は申し込み → 金融機関・コンビニ各社の審査）。テスト環境（サンドボックス）でも同じ画面でオンにする。
2. 「設定 → カスタマーへのメール」で、コンビニ払いの支払い方法の案内メールをオンにする。
3. 審査で見られる点と、このサイトの今の状態：

| 審査で見られる点 | 今の状態 |
|---|---|
| 決済の流れの説明 | ✅ 特定商取引法の表記・ショップ・お礼のページに記載 |
| 事業者名・住所・メール・電話が申請と一致し、サイトに表示 | ✅ 特定商取引法の表記に記載（申請も同じ値で） |
| サポートの営業時間 | ✅ 10:00〜18:00 にそろった（2026-10-27 オーナーが会社サイト link-tohoku.co.jp を 10:00-18:00 に変更・確認済み）。定休日は不定休（アクセスページ） |
| 特定商取引法の完全な表記 | ✅ |
| **商品の説明と価格が完全** | ✗ ショップはサンプル1件だけ・価格なし。本物の作品（説明・価格）を載せてから申し込む |
| **送料・手数料などの追加料金を明記** | △ 「ゆうパックの料金（日本郵便へリンク）」のみ。地域別の金額表を載せるのが確実 |
| キャンセル条件 | ✅ |
| 公開されていて、リンク切れ・404 が無い | ✅ |
| 郵便番号 | ✅ |
- 支払いは 120円〜300,000円。返金は可能（Stripe がお客様に口座を聞くメールを送る）。

## 送料のしくみ（2026-10-27）
- 5地域：沖縄県内／九州／中国／四国・近畿・東海・関東／北陸・信越・東北・北海道（同じ地域で料金が違う所は高いほうにそろえた＝信越・北陸は北海道・東北の料金）。
- 決済ページでは、その商品のサイズの5地域分の送料が出て、お客様がお届け先の地域を選ぶ（Stripe の決済ページは住所から自動では選べない）。違う地域が選ばれていたら、発送前にお店から連絡する（ショップ・特定商取引法の表記・決済ページに記載）。
- 注文が入ったら、Stripe の管理画面で「お届け先の都道府県」と「選ばれた送料」が合っているかを見る。
