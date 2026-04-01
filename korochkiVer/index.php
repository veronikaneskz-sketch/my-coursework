<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Корочки.есть — электронная запись на программы дополнительного обучения';
$metaDescription = 'Сервис записи на программы дополнительного обучения: оформление заявки, личный кабинет, статусы обучения и сопровождение слушателей.';
$metaKeywords = 'запись на обучение, дополнительное образование, программы обучения, личный кабинет слушателя, заявка на курс';
$ogImage = 'assets/img/image15.jpg';
include __DIR__ . '/includes/header.php';
$courseList = courses();
$courseImages = ['image12.webp', 'image15.jpg', 'image16.jpg', 'image17.jpeg', 'image18.webp'];
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "Корочки.есть",
  "url": "https://example.local/korochki-est",
  "description": "Электронный сервис записи на программы дополнительного профессионального образования.",
  "image": "assets/img/image15.jpg",
  "sameAs": ["https://vk.com/", "https://t.me/"],
  "potentialAction": {
    "@type": "RegisterAction",
    "target": "https://example.local/korochki-est/register.php"
  }
}
</script>

<section class="portal-hero section-deep">
  <div class="container portal-hero-grid hero-single">
    <div class="hero-lead reveal show hero-lead-wide">
      <div class="eyebrow">образовательный портал</div>
      <h1>Подача заявки, статус обучения и личный кабинет — в одном цифровом окне</h1>
      <p>Сервис помогает быстро выбрать программу, отправить заявку на обучение и видеть весь маршрут слушателя без бумажных анкет и повторного заполнения форм.</p>
      <div class="hero-actions">
        <?php if (!is_logged_in()): ?>
          <a class="btn btn-primary" href="register.php">Подать заявку онлайн</a>
          <a class="btn btn-secondary" href="login.php">Войти в кабинет</a>
        <?php else: ?>
          <a class="btn btn-primary" href="create_application.php">Оформить заявку</a>
          <a class="btn btn-secondary" href="applications.php">Открыть кабинет</a>
        <?php endif; ?>
      </div>
      <div class="hero-summary-grid">
        <article class="metric-card">
          <span>формат</span>
          <strong>Онлайн-заявка, личный кабинет и история действий в одном интерфейсе</strong>
        </article>
        <article class="metric-card">
          <span>обработка</span>
          <strong>Проверка заявки, смена статусов и сопровождение без лишних шагов</strong>
        </article>
      </div>
      <div class="quick-points">
        <div><strong>01</strong><span>Безопасный ввод данных и серверная проверка полей</span></div>
        <div><strong>02</strong><span>Удобный просмотр заявок и статусов в личном кабинете</span></div>
        <div><strong>03</strong><span>Администратор управляет очередью заявок из одной панели</span></div>
      </div>
    </div>
  </div>
</section>

<section class="signal-strip">
  <div class="container signal-grid">
    <article class="signal-item reveal"><span>личный кабинет</span><strong>Все заявки в одном списке</strong></article>
    <article class="signal-item reveal"><span>обработка</span><strong>Изменение статусов без переходов между страницами</strong></article>
    <article class="signal-item reveal"><span>адаптивность</span><strong>Интерфейс корректно работает на телефоне и на ноутбуке</strong></article>
  </div>
</section>

<section class="section">
  <div class="container section-head portal-head">
    <div>
      <div class="kicker">доступные направления</div>
      <h2>Программы, которые можно оформить через портал</h2>
    </div>
    <p>После входа в кабинет пользователь выбирает программу, дату старта и способ оплаты. Запись сразу передаётся в административную панель для дальнейшей обработки.</p>
  </div>
  <div class="container">
    <div class="program-wall">
      <?php foreach ($courseList as $index => $course): ?>
        <article class="program-tile reveal" itemscope itemtype="https://schema.org/Course">
          <div class="program-media">
            <img src="assets/img/image01.webp" alt="Иллюстрация программы обучения">
          </div>
          <div class="program-body">
            <div class="program-top">
              <span class="chip chip-soft">Программа ДПО</span>
              <strong><?= number_format($course['price'], 0, ',', ' ') ?> ₽</strong>
            </div>
            <h3 itemprop="name"><?= e($course['title']) ?></h3>
            <p itemprop="description"><?= e($course['description']) ?></p>
            <a class="text-link" href="<?= is_logged_in() ? 'create_application.php' : 'register.php' ?>">Перейти к оформлению</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<section class="section media-carousel-section">
  <div class="container section-head portal-head reveal">
    <div>
      <div class="kicker">фотокарусель</div>
      <h2>Пространство, в котором проходят занятия и сопровождение слушателей</h2>
    </div>
    <p>Фотокарусель показывает несколько учебных помещений и помогает визуально поддержать описание программ и процесса записи.</p>
  </div>
  <div class="container photo-carousel-shell reveal micro-sway">
    <div class="photo-carousel" itemscope itemtype="https://schema.org/ImageGallery">
      <div class="slides photo-slides" data-slider>
        <img src="assets/img/image10.webp" alt="Светлая учебная аудитория" itemprop="image">
        <img src="assets/img/image11.jpg" alt="Компьютерный класс для занятий" itemprop="image">
        <img src="assets/img/image12.webp" alt="Помещение для практических занятий" itemprop="image">
        <img src="assets/img/image13.webp" alt="Учебное пространство образовательного центра" itemprop="image">
      </div>
      <div class="photo-carousel-bar">
        <button class="slider-btn prev" type="button" data-prev>&larr;</button>
        <div class="photo-carousel-copy">
          <span>фото центра</span>
          <strong>Учебные аудитории и рабочие пространства</strong>
        </div>
        <button class="slider-btn next" type="button" data-next>&rarr;</button>
      </div>
    </div>
  </div>
</section>




<?php include __DIR__ . '/includes/footer.php'; ?>
