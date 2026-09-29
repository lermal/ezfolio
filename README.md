# Портфолио — webcodewizard.ru

Личный сайт-портфолио с админ-панелью. Публичная часть: главная страница с разделами «Обо мне», «Резюме», «Услуги», «Проекты» и формой обратной связи. В админке редактируется всё содержимое, настраиваются SEO, цвета и почта, есть статистика посетителей.

Проект — форк [EzFolio](https://github.com/arifszn/ezfolio) (MIT). От оригинала он отличается переработанной темой, исправлениями безопасности и упрощённой системой тем. Подробности ниже.

## Стек

- **Backend:** Laravel 8 (PHP 7.3+ / 8.x), JWT-авторизация (`tymon/jwt-auth`), очереди на драйвере `database`.
- **Админка:** SPA на React 17, Redux, Ant Design 4, собирается через Laravel Mix.
- **Сайт:** Blade, Bootstrap, jQuery, AOS, Typed.js. Список проектов — отдельный React-виджет.
- **Интеграции:** Cloudflare Turnstile (капча формы), Telegram-бот (уведомления о новых сообщениях), Google Analytics.

## Архитектура

```
app/
  Http/Controllers/
    Admin/            SPA-оболочка админки и её API (Admin/Api/*)
    Frontend/         публичная страница и приём формы обратной связи
  Services/           бизнес-логика; интерфейсы в Services/Contracts,
                      привязка в AppServiceProvider
  Helpers/ThemeRegistry.php   единая точка доступа к темам
  Events/NewMessage + Listeners/NewMessageListener   уведомление в Telegram
config/
  themes.php          реестр тем
  services.php        ключи Turnstile и Telegram
resources/
  views/frontend/layouts/theme.blade.php   общий layout всех тем
  views/frontend/theme/custom.blade.php    текущая тема
  views/frontend/partials/                 виджет проектов, Turnstile
  js/client/admin/                         исходники админки
public/assets/common/js/contact-form.js    отправка формы (общая для тем)
```

Контроллеры тонкие: вся работа идёт в сервисах. Сервис возвращает массив `message`, `payload`, `status`.

### Темы

Сейчас есть одна тема — `custom`. Остальные темы из апстрима удалены.

Все темы перечислены в `config/themes.php`. Этот список используют валидация при сохранении, админка (она получает список с сервера, пересобирать JS не нужно), выбор шаблона на сайте и сидер. Если в базе сохранена неизвестная тема, сайт откатывается на тему `default`.

Общие части вынесены в `frontend.layouts.theme`: `<head>`, SEO-мета, Google Analytics, пользовательские скрипты из админки, CSS-переменные акцентного цвета (`--accent-color`, `--accent-color-rgb`), прелоадер, jQuery, валидация и AJAX-отправка формы, Turnstile.

Как добавить тему:

1. Добавить запись в `config/themes.php`:
   ```php
   'mytheme' => ['title' => 'My Theme', 'preview' => 'assets/common/img/templates/mytheme.png'],
   ```
2. Создать `resources/views/frontend/theme/mytheme.blade.php`:
   ```blade
   @extends('frontend.layouts.theme')

   @section('styles') ... @endsection
   @section('content')
       ...
       @include('frontend.partials.projects')
       <form id="contactForm"> ... @include('frontend.partials.turnstile') ... </form>
   @endsection
   @section('scripts') ... @endsection
   ```
3. Положить ассеты в `public/assets/themes/mytheme/` и картинку превью по указанному пути.

### Форма обратной связи

Отправка идёт через AJAX на `route('contact-me')`. Капча Turnstile проверяется на сервере и сбрасывается после каждой отправки. Ошибки валидации показываются пользователю.

Сообщение сохраняется в базу. Уведомление в Telegram отправляет слушатель `NewMessageListener` в очереди: 3 попытки с задержкой, неудачи пишутся в лог. Поэтому запрос формы не ждёт ответа Telegram и не падает, если тот недоступен.

### Безопасность

- Все ключи и токены читаются через `config()`, а не `env()`, поэтому работают после `php artisan config:cache`.
- Секреты (почта, Turnstile, Telegram) не встраиваются в HTML админки. Их отдаёт только API настроек, закрытое JWT.
- Очистка кешей (`POST /api/optimize`) доступна только авторизованному админу.
- Просмотр логов (`/admin/system-logs`) открывается по временной подписанной ссылке, которую выдаёт API. После перехода доступ держится в сессии 30 минут.
- Маршрут для произвольного запуска artisan-команд из апстрима удалён.

## Установка

Требования: PHP 7.3+ (проверялось на 8.x), Composer, Node.js, MySQL/MariaDB.

```sh
cp .env.example .env
composer install
php artisan key:generate
php artisan jwt:secret
```

Заполнить в `.env`:

- `APP_URL`, `DB_*` — адрес сайта и подключение к БД;
- `MAIL_*` — почта (можно поменять позже в админке);
- `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` — если пусто, капча не выводится;
- `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID` — если пусто, уведомления не отправляются;
- `QUEUE_CONNECTION=database`.

```sh
php artisan migrate --seed
php artisan storage:link
npm install
npm run prod        # или npm run watch при разработке
```

Сидер создаёт администратора `admin@webcodewizard.ru` со случайным паролем. Пароль выводится в консоль один раз.

### Очередь

Без воркера уведомления в Telegram копятся в таблице `jobs` и не уходят:

```sh
php artisan queue:work --tries=3
```

На сервере воркер лучше держать под supervisor или systemd. После сохранения настроек почты, Turnstile или Telegram в админке приложение само выполняет `config:clear` и `queue:restart`, чтобы воркер подхватил новые значения.

### Docker

В репозитории есть `docker-compose.yml` для Laravel Sail. Команды те же, только через `./vendor/bin/sail` (`sail artisan migrate --seed`, `sail npm run prod`). Если при миграции возникает ошибка `Connection refused`, выставить `DB_HOST=mysql`.

## Лицензия

MIT. Оригинальный проект EzFolio © 2022 Ariful Alam, текст лицензии в [LICENSE](LICENSE).
