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

/**
 * メールを送る。★heteml の送信サーバー（smtp.hetemail.jp）に info@ でログインして送る（2026-10-26 オーナー指示）。
 *   ログイン情報は mail-config.php（git に入れない・.htaccess で外から読めない）。無いときは PHP の mb_send_mail で送る。
 */
function send_mail(string $to, string $subject, string $body, string $fromName, ?string $replyTo = null): bool {
    $cfgFile = __DIR__ . '/mail-config.php';
    $cfg = is_file($cfgFile) ? (require $cfgFile) : null;
    if (!is_array($cfg) || empty($cfg['password'])) {
        $headers = 'From: ' . mb_encode_mimeheader($fromName) . ' <' . SHOP_FROM . '>' . ($replyTo ? "\r\nReply-To: " . $replyTo : '');
        return mb_send_mail($to, $subject, $body, $headers, '-f' . SHOP_FROM);
    }
    return smtp_send($cfg, $to, $subject, $body, $fromName, $replyTo);
}

function smtp_send(array $cfg, string $to, string $subject, string $body, string $fromName, ?string $replyTo): bool {
    $host = $cfg['host'] ?? 'smtp.hetemail.jp';
    $port = (int) ($cfg['port'] ?? 465);
    $user = $cfg['user'] ?? SHOP_FROM;
    $remote = ($port === 465 ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) { error_log("smtp connect failed: $errstr"); return false; }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) { $data .= $line; if (isset($line[3]) && $line[3] === ' ') break; }
        return $data;
    };
    $cmd = function (string $c, array $ok) use ($fp, $read): bool {
        if ($c !== '') fwrite($fp, $c . "\r\n");
        $r = $read();
        if (!in_array((int) substr($r, 0, 3), $ok, true)) { error_log('smtp: ' . preg_replace('/AUTH.*/', 'AUTH ***', $c) . ' -> ' . trim($r)); return false; }
        return true;
    };
    $ok = $cmd('', [220]) && $cmd('EHLO uminaibi-resin.jp', [250]);
    if ($ok && $port !== 465) {
        $ok = $cmd('STARTTLS', [220]) && stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)
            && $cmd('EHLO uminaibi-resin.jp', [250]);
    }
    $ok = $ok && $cmd('AUTH LOGIN', [334]) && $cmd(base64_encode($user), [334]) && $cmd(base64_encode((string) $cfg['password']), [235])
        && $cmd('MAIL FROM:<' . SHOP_FROM . '>', [250]) && $cmd('RCPT TO:<' . $to . '>', [250, 251]) && $cmd('DATA', [354]);
    if ($ok) {
        $date = (new DateTime('now', new DateTimeZone('Asia/Tokyo')))->format('r');
        $msgId = '<' . bin2hex(random_bytes(12)) . '@uminaibi-resin.jp>';
        $h  = 'Date: ' . $date . "\r\n";
        $h .= 'From: ' . mb_encode_mimeheader($fromName, 'UTF-8', 'B') . ' <' . SHOP_FROM . ">\r\n";
        $h .= 'To: <' . $to . ">\r\n";
        if ($replyTo) $h .= 'Reply-To: <' . $replyTo . ">\r\n";
        $h .= 'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8', 'B') . "\r\n";
        $h .= 'Message-ID: ' . $msgId . "\r\n";
        $h .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n";
        $data = $h . "\r\n" . chunk_split(base64_encode(str_replace("\n", "\r\n", $body)), 76, "\r\n") . '.';
        $ok = $cmd($data, [250]);
    }
    fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
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
    reply(429, ['ok' => false, 'message' => '短い時間に何度も送信されています。しばらくしてからお試しいただくか、お電話（050-7116-0133）でご連絡ください。']);
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

$shopSubject = "【HPお問い合わせ】{$subjectLabel}（{$name} 様）";
$sentShop = send_mail(SHOP_TO, $shopSubject, $body, SHOP_NAME . ' ホームページ', $email);

if (!$sentShop) {
    reply(500, ['ok' => false, 'message' => '送信できませんでした。お手数ですが、お電話（050-7116-0133）・メール（' . SHOP_TO . '）でご連絡ください。']);
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
send_mail($email, '【うみないび】お問い合わせを受け付けました', $auto, SHOP_NAME);

reply(200, ['ok' => true]);
