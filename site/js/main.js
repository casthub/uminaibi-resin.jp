document.addEventListener('DOMContentLoaded', () => {
  // 1. スクロールに応じたヘッダーの背景色切り替え
  const header = document.getElementById('site-header');
  const logo = document.getElementById('header-logo');
  const navLinks = document.querySelectorAll('.nav-link');

  const onScroll = () => {
    if (!header || !logo) return;
    if (window.scrollY > 40) {
      header.classList.remove('bg-transparent', 'text-white');
      header.classList.add('bg-[#f7efdd]/90', 'backdrop-blur-md', 'shadow-md', 'text-[#16323f]');
      logo.classList.remove('text-white');
      logo.classList.add('text-[#0a3d52]');
      navLinks.forEach((link) => {
        link.classList.remove('text-white/90', 'hover:text-white');
        link.classList.add('text-[#16323f]/90', 'hover:text-[#0f6c8a]');
      });
    } else {
      header.classList.add('bg-transparent', 'text-white');
      header.classList.remove('bg-[#f7efdd]/90', 'backdrop-blur-md', 'shadow-md', 'text-[#16323f]');
      logo.classList.add('text-white');
      logo.classList.remove('text-[#0a3d52]');
      navLinks.forEach((link) => {
        link.classList.add('text-white/90', 'hover:text-white');
        link.classList.remove('text-[#16323f]/90', 'hover:text-[#0f6c8a]');
      });
    }
  };

  window.addEventListener('scroll', onScroll);
  onScroll(); // 初期読み込み時にも適用

  // 2. モバイルメニューのトグル
  const menuBtn = document.getElementById('menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  const bar1 = document.getElementById('bar1');
  const bar2 = document.getElementById('bar2');
  const bar3 = document.getElementById('bar3');

  let menuOpen = false;
  const toggleMenu = () => {
    menuOpen = !menuOpen;
    if (!mobileMenu || !menuBtn) return;
    
    mobileMenu.classList.toggle('translate-x-full', !menuOpen);
    menuBtn.setAttribute('aria-expanded', String(menuOpen));
    
    if (bar1 && bar2 && bar3) {
      if (menuOpen) {
        bar1.style.transform = 'translateY(6px) rotate(45deg)';
        bar2.style.opacity = '0';
        bar3.style.transform = 'translateY(-6px) rotate(-45deg)';
      } else {
        bar1.style.transform = 'none';
        bar2.style.opacity = '1';
        bar3.style.transform = 'none';
      }
    }
    document.body.style.overflow = menuOpen ? 'hidden' : '';
  };

  menuBtn?.addEventListener('click', toggleMenu);

  // 3. スクロールによる要素のふわっと表示アニメーション
  const revealEls = document.querySelectorAll('.reveal');
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
});
