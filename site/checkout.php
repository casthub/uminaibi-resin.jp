<?php
// オンラインショップの「購入手続きへ」ボタンの送り先（shop.html から呼ばれる）。2026-10-27
//   ・ボタンで選ばれた商品（item）を stripe-config.php の一覧で探し、Stripe Checkout（Stripe の決済ページ）を作って、そこへ移動させる
//   ・価格は Stripe の Price ID で決まる（ブラウザから送られた金額は使わない＝書き換えられても安全）
//   ・カード番号は Stripe の決済ページで入力され、このサーバーには届かない
// ★シークレットキーと商品の一覧は stripe-config.php（git に入れない・.htaccess で外から読めない）
// ★PHP 7.4 以上で動く書き方にしてある（heteml）。Stripe のライブラリは使わず、API を直接呼ぶ。

declare(strict_types=1);
header('X-Content-Type-Options: nosniff');

const ALLOWED_HOSTS = ['uminaibi-resin.jp', 'www.uminaibi-resin.jp'];
const SITE_URL = 'https://uminaibi-resin.jp';

/** うまくいかなかったときの画面（お客様向け） */
function fail(int $status, string $message): void {
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    $m = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex">
<title>お手続きができませんでした | うみないび</title>
<style>body{font-family:"Zen Kaku Gothic New","Noto Sans JP",sans-serif;background:#f7efdd;color:#16323f;line-height:1.9;margin:0;padding:4rem 1.5rem}main{max-width:36rem;margin:0 auto;background:#fff;border-radius:1.5rem;padding:2rem}h1{font-size:1.25rem;color:#0a3d52}a{color:#0f6c8a}</style>
</head><body><main><h1>お手続きができませんでした</h1><p>{$m}</p><p><a href="shop.html">ショップに戻る</a>　<a href="contact.html">お問い合わせ</a></p></main></body></html>
HTML;
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . SITE_URL . '/shop.html', true, 303);
    exit;
}

// 他のサイトからのボタン送信は断る
$from = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$fromHost = $from !== '' ? (string) parse_url($from, PHP_URL_HOST) : '';
if ($fromHost !== '' && !in_array($fromHost, ALLOWED_HOSTS, true)) {
    fail(403, 'このページからはお手続きできません。');
}

$cfgFile = __DIR__ . '/stripe-config.php';
$cfg = is_file($cfgFile) ? (require $cfgFile) : null;
if (!is_array($cfg) || empty($cfg['secret_key'])) {
    error_log('checkout: stripe-config.php が無いか、secret_key が空');
    fail(503, 'ただいまオンラインでのご購入を準備中です。お手数ですが、お問い合わせからご連絡ください。');
}

$item = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($_POST['item'] ?? '')));
$price = $cfg['items'][$item] ?? '';
if ($item === '' || !is_string($price) || strpos($price, 'price_') !== 0 || $price === 'price_...') {
    fail(404, 'この商品は、ただいまお求めいただけません（売り切れ・準備中）。');
}

// Checkout Session の設定（★Checkout Studio で決めた値。変えるときは STRIPE_INTEGRATION_TODO.md も直す）
$params = [
    'ui_mode' => 'hosted_page',
    'mode' => 'payment',
    'billing_address_collection' => 'auto',
    'phone_number_collection' => ['enabled' => 'false'],
    'automatic_tax' => ['enabled' => 'false'],
    'allow_promotion_codes' => 'false',
    'submit_type' => 'auto',
    'integration_identifier' => 'hosted_web_0001',
    'origin_context' => 'web',
    'success_url' => SITE_URL . '/shop-thanks.html?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => SITE_URL . '/shop.html',
    'line_items' => [['price' => $price, 'quantity' => 1]],
    // 作品を送るので、お届け先（日本国内）を入れてもらう。送料（ゆうパック）は stripe-config.php の shipping_rates
    'shipping_address_collection' => ['allowed_countries' => ['JP']],
    'metadata' => ['item' => $item],
];
$rates = array_values(array_filter((array) ($cfg['shipping_rates'] ?? []), function ($r) {
    return is_string($r) && strpos($r, 'shr_') === 0 && $r !== 'shr_...';
}));
if ($rates) {
    $params['shipping_options'] = array_map(function ($r) { return ['shipping_rate' => $r]; }, array_slice($rates, 0, 5));
}

$ctx = stream_context_create(['http' => [
    'method' => 'POST',
    'header' => "Authorization: Bearer " . $cfg['secret_key'] . "\r\nContent-Type: application/x-www-form-urlencoded\r\n",
    'content' => http_build_query($params),
    'timeout' => 20,
    'ignore_errors' => true,
]]);
$res = @file_get_contents('https://api.stripe.com/v1/checkout/sessions', false, $ctx);
$session = is_string($res) ? json_decode($res, true) : null;
if (!is_array($session) || empty($session['url'])) {
    $err = is_array($session) ? ($session['error']['message'] ?? 'unknown') : 'no response';
    error_log('checkout: Stripe error: ' . $err);   // ★キーは記録に出さない
    fail(502, '決済ページを開けませんでした。少し時間をおいてもう一度お試しください。');
}

header('Location: ' . $session['url'], true, 303);
