<?php
// Stripe の設定（このファイルをコピーして site/stripe-config.php を作る）。
// ★site/stripe-config.php は git に入れない。.htaccess で外から読めないようにしてある。
// ★キーは会話・メール・画面に貼らない。本番を始めるときは sk_live_ のキーに替える。
return [
    // シークレットキー（Stripe の管理画面「開発者 → API キー」）。テスト中は sk_test_、本番は sk_live_
    'secret_key' => '',

    // 売る商品の一覧：ボタンの名前（item） => Stripe の Price ID（Stripe の管理画面「商品カタログ」→ 商品 → 価格）
    //   shop.html の <input type="hidden" name="item" value="..."> と同じ名前にする。売り切れたら行を消す
    'items' => [
        'sample' => 'price_...',
    ],

    // 送料（ゆうパック）：Stripe の管理画面「配送料」で作った ID（shr_...）。5つまで。お客様が決済ページで選ぶ
    'shipping_rates' => [
        // 'shr_...',
    ],
];
