document.addEventListener('DOMContentLoaded', () => {
  // 1. ヘッダー：少しスクロールしたら下に細い影（2026-10-28 リビルド。ヘッダーはいつも白い地）
  const header = document.getElementById('site-header');
  const onScroll = () => header?.classList.toggle('shadow-[0_1px_0_#d6e1e6]', window.scrollY > 8);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // 2. モバイルメニューのトグル
  //   ★閉じているときは inert（キーボードでも読み上げでも入れない）。Esc で閉じ、閉じたらボタンへ戻る（WCAG 2.1.2 / 2.4.3）
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  const bar1 = document.getElementById('bar1');
  const bar2 = document.getElementById('bar2');
  const bar3 = document.getElementById('bar3');

  let menuOpen = false;
  const setMenu = (open) => {
    if (!mobileMenu || !menuBtn) return;
    menuOpen = open;
    mobileMenu.classList.toggle('translate-x-full', !open);
    mobileMenu.toggleAttribute('inert', !open);
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
    if (bar1 && bar2 && bar3) {
      bar1.style.transform = open ? 'translateY(6px) rotate(45deg)' : 'none';
      bar2.style.opacity = open ? '0' : '1';
      bar3.style.transform = open ? 'translateY(-6px) rotate(-45deg)' : 'none';
    }
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) mobileMenu.querySelector('a')?.focus();
  };

  menuBtn?.addEventListener('click', () => setMenu(!menuOpen));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && menuOpen) { setMenu(false); menuBtn?.focus(); }
  });
  // 画面が広くなってメニューが要らなくなったら閉じる
  window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => { if (e.matches && menuOpen) setMenu(false); });

  // 3. スクロールによる要素のふわっと表示アニメーション
  //   ★「動きを減らす」設定の人には動かさない（CSS 側でも止めている）
  const revealEls = document.querySelectorAll('.reveal');
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  } else {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );
    revealEls.forEach((el) => io.observe(el));
    // キーボードで隠れている部分に移動したときは、すぐ見えるようにする
    document.addEventListener('focusin', (e) => {
      const r = e.target.closest?.('.reveal');
      if (r) r.classList.add('is-visible');
    });
  }
});

// ダイアログ（送信完了など）を開く／閉じる共通処理
//   開いたら中の最初のボタンへ、閉じたら元の場所へフォーカスを戻す。Tab は中で回り、Esc で閉じる（WCAG 2.1.2 / 2.4.3）
window.uminaibiDialog = (() => {
  let current = null;
  let returnTo = null;
  const focusables = (el) => [...el.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])')];
  const onKey = (e) => {
    if (!current) return;
    if (e.key === 'Escape') {
      e.preventDefault();
      const btn = current.querySelector('[data-dialog-close]');
      btn ? btn.click() : close(current);
    } else if (e.key === 'Tab') {
      const f = focusables(current);
      if (!f.length) return;
      const first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  };
  const open = (el, from) => {
    current = el;
    returnTo = from || document.activeElement;
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onKey);
    (focusables(el)[0] || el).focus();
  };
  const close = (el) => {
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKey);
    current = null;
    returnTo?.focus?.();
  };
  return { open, close };
})();
