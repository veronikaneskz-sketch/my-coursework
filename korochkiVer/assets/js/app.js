document.addEventListener('DOMContentLoaded', function () {
  var burger = document.querySelector('[data-burger]');
  var nav = document.querySelector('[data-nav]');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      nav.classList.toggle('open');
    });
  }

  document.querySelectorAll('.card, .feature, .gallery-card, .form-card, .admin-ticket, .table-wrap, .showcase-panel').forEach(function (el) {
    el.classList.add('reveal');
  });

  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('show');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(function (el) { observer.observe(el); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('show'); });
  }

  var slider = document.querySelector('[data-slider]');
  if (slider) {
    var slides = slider.querySelectorAll('img');
    var current = 0;
    function show(index) {
      slides.forEach(function (img, i) {
        img.classList.toggle('active', i === index);
      });
    }
    function next() {
      current = (current + 1) % slides.length;
      show(current);
    }
    function prev() {
      current = (current - 1 + slides.length) % slides.length;
      show(current);
    }
    show(current);
    var timer = setInterval(next, 3000);
    var nextBtn = document.querySelector('[data-next]');
    var prevBtn = document.querySelector('[data-prev]');
    if (nextBtn) nextBtn.addEventListener('click', function () { clearInterval(timer); next(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { clearInterval(timer); prev(); });
  }
});
