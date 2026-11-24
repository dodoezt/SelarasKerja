// Pengatur ukuran teks Normal / Besar. Disimpan di localStorage.
(function () {
  var root = document.documentElement;
  var buttons = document.querySelectorAll('[data-text-size]');

  function apply(size) {
    root.setAttribute('data-text', size);
    try { localStorage.setItem('sk-text', size); } catch (e) {}
    buttons.forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.dataset.textSize === size));
    });
  }

  var saved = 'normal';
  try { saved = localStorage.getItem('sk-text') || 'normal'; } catch (e) {}
  apply(saved);

  buttons.forEach(function (b) {
    b.addEventListener('click', function () { apply(b.dataset.textSize); });
  });
})();

// Menu mobile: tombol tiga garis membuka panel navigasi.
(function () {
  var head = document.querySelector('.site-head');
  var btn = document.querySelector('.nav-toggle');
  if (!head || !btn) return;

  var mobileNav = window.matchMedia('(max-width: 900px)');

  function set(open) {
    head.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
  }

  function syncButton() {
    btn.hidden = !mobileNav.matches;
    if (btn.hidden) set(false);
  }

  btn.addEventListener('click', function () {
    set(!head.classList.contains('is-open'));
  });

  document.documentElement.classList.add('has-js');
  syncButton();
  if (mobileNav.addEventListener) {
    mobileNav.addEventListener('change', syncButton);
  } else {
    window.addEventListener('resize', syncButton);
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') set(false);
  });

  document.addEventListener('click', function (e) {
    if (head.classList.contains('is-open') && !head.contains(e.target)) set(false);
  });

})();
