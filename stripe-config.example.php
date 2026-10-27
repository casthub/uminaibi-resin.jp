<?php
// Stripe の設定（このファイルをコピーして site/stripe-config.php を作る）。
// ★site/stripe-config.php は git に入れない。.htaccess で外から読めないようにしてある。
// ★キーは会話・メール・画面に貼らない。本番を始めるときは sk_live_ のキーに替える。
return [
    // シークレットキー（Stripe の管理画面「開発者 → API キー」）。テスト中は sk_test_、本番は sk_live_
    'secret_key' => '',

    // 売る商品の一覧：ボタンの名前（item） => Price ID（Stripe の「商品カタログ」→ 商品 → 価格・税込）と、ゆうパックの大きさ
    //   shop.html の <input type="hidden" name="item" value="..."> と同じ名前にする。売り切れたら行を消す
    'items' => [
        'sample' => ['price' => 'price_...', 'size' => '60'],
    ],

    // 送料（ゆうパック・沖縄県から）：大きさごとに5地域の配送料 ID（shr_...）。bin/stripe-shipping-rates.py --go で作って貼る
    //   並び：沖縄県内／九州／中国／四国・近畿・東海・関東／北陸・信越・東北・北海道（料金は data/yupack-okinawa.json）
    'shipping_rates' => [
        '60'  => ['shr_...', 'shr_...', 'shr_...', 'shr_...', 'shr_...'],
        '80'  => [],
        '100' => [],
        '120' => [],
        '140' => [],
    ],
];
