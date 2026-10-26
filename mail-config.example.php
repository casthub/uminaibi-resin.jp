<?php
// heteml の送信サーバーのログイン情報（このファイルをコピーして mail-config.php を作る）。
// ★mail-config.php は git に入れない。.htaccess で外から読めないようにしてある。
return [
    'host'     => 'smtp.hetemail.jp',
    'port'     => 465,            // SSL。つながらなければ 587（STARTTLS）
    'user'     => 'info@uminaibi-resin.jp',
    'password' => '',
];
