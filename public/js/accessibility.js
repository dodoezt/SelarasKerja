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