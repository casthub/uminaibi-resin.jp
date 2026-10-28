// Tailwind の設定（2026-10-28 CDN をやめて CSS ファイルにした）。
// 前は各ページの <script> に同じ設定を書いていた。いまはここだけ。
// 作り直し：bin/build-css.sh（site/css/tailwind.css ができる）
// ★2026-10-28 リビルド：白い地と蒼い海の色だけの、シンプルなアートサイトの色に
module.exports = {
  content: ['site/**/*.html', 'site/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        ocean: {
          deep: '#0a2a42',   // 見出し・濃い帯（白文字との差 14:1）
          DEFAULT: '#0b5b91', // リンク・ボタン（白文字との差 7:1）
          light: '#3aa6cf',  // 飾りの線だけ（文字には使わない）
        },
        emerald: {
          DEFAULT: '#0b636b', // 小見出しの英字（白地との差 6.6:1）
          bright: '#1fb7a3',
          light: '#6fd8c7',
        },
        sand: {
          DEFAULT: '#f6f9fa', // ページの地（ほぼ白）
          dark: '#ebf2f4',    // 区切りの帯
        },
        coral: '#a9412a', // 「人気」などの印だけ（白文字との差 5.7:1）
        ink: '#15283a',   // 本文
        line: '#d6e1e6',  // 細い線
      },
      fontFamily: {
        serifJp: ['"Zen Old Mincho"', '"Noto Serif JP"', 'serif'],
        sansJp: ['"Zen Kaku Gothic New"', '"Noto Sans JP"', 'sans-serif'],
      },
      // ★2026-10-27 読みやすさ：小さい文字を 12→13px、本文を 14→15px に。行間は日本語向けに広めに（WCAG 1.4.12 にも余裕）
      //   スマホの本文は style.css で 16px にしている
      fontSize: {
        xs: ['0.8125rem', { lineHeight: '1.7' }],
        sm: ['0.9375rem', { lineHeight: '1.9' }],
        base: ['1rem', { lineHeight: '1.9' }],
      },
    }
  }
}
