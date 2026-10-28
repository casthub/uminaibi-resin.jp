// Tailwind の設定（2026-10-28 CDN をやめて CSS ファイルにした）。
// 前は各ページの <script> に同じ設定を書いていた。いまはここだけ。
// 作り直し：bin/build-css.sh（site/css/tailwind.css ができる）
module.exports = {
  content: ['site/**/*.html', 'site/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        ocean: {
          deep: '#0b3a5e',
          DEFAULT: '#0f64a0',
          light: '#1fb0cc',
        },
        emerald: {
          DEFAULT: '#0a6e62', // ★文字・ボタン用に濃くした（白との差 6.1:1）。元の明るい色は bright
          bright: '#1fb7a3',
          light: '#6fd8c7',
        },
        sand: {
          DEFAULT: '#eef8fa',
          dark: '#dcf0f4',
        },
        coral: '#b8431f', // ★白文字との差 5.4:1（元 #ff8a65 は 2.3:1）
        ink: '#122f45',
      },
      fontFamily: {
        serifJp: ['"Zen Old Mincho"', '"Noto Serif JP"', 'serif'],
        sansJp: ['"Zen Kaku Gothic New"', '"Noto Sans JP"', 'sans-serif'],
      },
      // ★2026-10-27 読みやすさ：小さい文字を 12→13px、本文を 14→15px に。行間は日本語向けに広めに（WCAG 1.4.12 にも余裕）
      fontSize: {
        xs: ['0.8125rem', { lineHeight: '1.7' }],
        sm: ['0.9375rem', { lineHeight: '1.9' }],
        base: ['1rem', { lineHeight: '1.9' }],
      },
      boxShadow: {
        soft: '0 10px 40px -10px rgba(10, 61, 82, 0.15)',
      }
    }
  }
}
