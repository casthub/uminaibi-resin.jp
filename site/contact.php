<?php
// お問い合わせ・体験教室予約フォームの送信先（contact.html から呼ばれる）。2026-10-26
//   ・お店（info@uminaibi-resin.jp）に内容をメールで送る
//   ・お客様には「受け付けました」の自動返信を送る（★入力内容は載せない＝他人宛の迷惑メールの踏み台にさせない）
//   ・迷惑な送信よけ：見えない入力欄（おとり）、他サイトからの送信を断る、同じ回線からの連続送信を断る
// ★PHP 7.4 以上で動く書き方にしてある（heteml）。

declare(strict_types=1);
mb_language('Japanese');
mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

const SHOP_TO   = 'info@uminaibi-resin.jp';
const SHOP_FROM = 'info@uminaibi-resin.jp';
const SHOP_NAME = 'うみないび';
const ALLOWED_HOSTS = ['uminaibi-resin.jp', 'www.uminaibi-resin.jp'];
const LIMIT_COUNT = 5;      // 同じ回線から
const LIMIT_SECONDS = 600;  // 10分に5回まで

function reply(int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

/** 1行の項目：改行や制御文字を消す（メールの見出しを書き換えられないように） */
function one_line(string $s, int $max): string {
    $s = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $s) ?? '';
    return mb_substr(trim($s), 0, $max);
}

/** 複数行の項目：改行は残し、ほかの制御文字だけ消す */
function multi_line(string $s, int $max): string {
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    $s = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]+/u', '', $s) ?? '';
    return mb_substr(trim($s), 0, $max);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    reply(405, ['ok' => false, 'message' => '送信方法が正しくありません。']);
}

// 他のサイトから送らせない
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$originHost = $origin !== '' ? (string) parse_url($origin, PHP_URL_HOST) : '';
if (!in_array($originHost, ALLOWED_HOSTS, true)) {
    reply(403, ['ok' => false, 'message' => 'このページからは送信できません。']);
}

// おとり欄（人には見えない）。入っていたら機械の送信とみなし、成功したふりをして何もしない
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    reply(200, ['ok' => true]);
}

// 連続送信よけ（回線ごとに数える。IP そのものは保存しない）
$ipKey = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|uminaibi-contact');
$limitFile = sys_get_temp_dir() . '/uminaibi_contact_' . substr($ipKey, 0, 32);
$now = time();
$hits = [];
if (is_file($limitFile)) {
    $hits = array_filter(
        array_map('intval', explode(',', (string) @file_get_contents($limitFile))),
        function (int $t) use ($now): bool { return $t > $now - LIMIT_SECONDS; }
    );
}
if (count($hits) >= LIMIT_COUNT) {
    reply(429, ['ok' => false, 'message' => '短い時間に何度も送信されています。しばらくしてからお試しいただくか、お電話・LINE でご連絡ください。']);
}

$subjects = [
    'workshop' => '体験教室のご予約について',
    'order'    => 'オーダーメイドのご相談',
    'other'    => 'その他お問い合わせ',
];

$name    = one_line((string) ($_POST['name'] ?? ''), 60);
$email   = one_line((string) ($_POST['email'] ?? ''), 200);
$tel     = one_line((string) ($_POST['tel'] ?? ''), 30);
$subjKey = (string) ($_POST['subject'] ?? '');
$date    = one_line((string) ($_POST['reserve_date'] ?? ''), 100);
$count   = one_line((string) ($_POST['reserve_count'] ?? ''), 3);
$message = multi_line((string) ($_POST['message'] ?? ''), 4000);

$errors = [];
if ($name === '') $errors[] = 'お名前を入力してください。';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'メールアドレスを正しく入力してください。';
if ($tel !== '' && !preg_match('/^[0-9０-９+\-－ー() ]{6,30}$/u', $tel)) $errors[] = '電話番号を正しく入力してください。';
if (!isset($subjects[$subjKey])) $errors[] = 'ご用件を選んでください。';
if ($subjKey === 'workshop') {
    if ($date === '') $errors[] = '第一希望日時を入力してください。';
    if (!preg_match('/^[0-9]{1,2}$/', $count) || (int) $count < 1 || (int) $count > 10) $errors[] = '参加人数を1〜10人で入力してください。';
}
if ($message === '') $errors[] = 'お問い合わせ内容を入力してください。';
if ($errors) {
    reply(422, ['ok' => false, 'message' => implode("\n", $errors)]);
}

$hits[] = $now;
@file_put_contents($limitFile, implode(',', $hits), LOCK_EX);

$subjectLabel = $subjects[$subjKey];
$sentAt = (new DateTime('now', new DateTimeZone('Asia/Tokyo')))->format('Y年n月j日 H:i');

// --- お店あてのメール ---
$body  = "ホームページのお問い合わせフォームから送信がありました。\n";
$body .= "このメールに返信すると、お客様（{$email}）に届きます。\n\n";
$body .= "■ ご用件：{$subjectLabel}\n";
$body .= "■ お名前：{$name}\n";
$body .= "■ メール：{$email}\n";
$body .= "■ 電話番号：" . ($tel !== '' ? $tel : '（未入力）') . "\n";
if ($subjKey === 'workshop') {
    $body .= "■ 第一希望日時：{$date}\n";
    $body .= "■ 参加人数：{$count}名\n";
}
$body .= "\n■ お問い合わせ内容\n{$message}\n\n";
$body .= "――――――――――\n送信日時：{$sentAt}\n";

$fromHeader = 'From: ' . mb_encode_mimeheader(SHOP_NAME . ' ホームページ') . ' <' . SHOP_FROM . '>';
$headers = $fromHeader . "\r\nReply-To: " . $email;
$shopSubject = "【HPお問い合わせ】{$subjectLabel}（{$name} 様）";
$sentShop = mb_send_mail(SHOP_TO, $shopSubject, $body, $headers, '-f' . SHOP_FROM);

if (!$sentShop) {
    reply(500, ['ok' => false, 'message' => '送信できませんでした。お手数ですが、お電話・LINE・メール（' . SHOP_TO . '）でご連絡ください。']);
}

// --- お客様への自動返信（★入力内容は載せない） ---
$auto  = "{$name} 様\n\n";
$auto .= "うみないびへのお問い合わせ（{$subjectLabel}）を受け付けました。\n";
$auto .= "内容を確認のうえ、通常2営業日以内に担当者よりご連絡いたします。\n";
if ($subjKey === 'workshop') {
    $auto .= "\n※ 体験教室のご予約は、こちらからの確認のご連絡をもって確定となります。\n";
}
$auto .= "\nこのメールは自動でお送りしています。ご質問は、このメールにそのまま返信してお送りいただけます。\n";
$auto .= "お心当たりのない場合は、お手数ですがこのメールを破棄してください。\n\n";
$auto .= "――――――――――\nうみないび（沖縄県名護市のレジンアート専門店）\nhttps://uminaibi-resin.jp/\n";
$autoHeaders = 'From: ' . mb_encode_mimeheader(SHOP_NAME) . ' <' . SHOP_FROM . '>';
@mb_send_mail($email, '【うみないび】お問い合わせを受け付けました', $auto, $autoHeaders, '-f' . SHOP_FROM);

reply(200, ['ok' => true]);
