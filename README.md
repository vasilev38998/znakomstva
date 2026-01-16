# Znakomstva — сервис знакомств нового поколения

Проект готов для запуска на shared-хостинге Beget: загрузите файлы, импортируйте БД, настройте `config/config.php`.

## Быстрый старт (Beget)
1. Загрузите весь репозиторий в корень домена.
2. Создайте БД MySQL и импортируйте `sql/schema.sql`.
3. Обновите `config/config.php` (DB_HOST/DB_NAME/DB_USER/DB_PASS).
4. Укажите `VAPID_PUBLIC_KEY` для push.
5. Откройте сайт — базовый UI уже работает.

## Авторизация
- Регистрация: `/register`
- Вход: `/login`
- Подтверждение email: `/verify?token=...` (в демо ссылка показывается после регистрации)

## PWA
- `pwa/manifest.json`
- `pwa/service-worker.js`
- Offline-экран доступен по `/offline`.

## Push-уведомления
Директории для логики:
- `app/services/PushService.php` — отправка с VAPID
- `app/services/EventBus.php` — триггеры (лайк/матч/сообщение и др.)
- `admin` — интерфейс центра уведомлений

API:
- `POST /api/push/subscribe`
- `POST /api/push/unsubscribe`

## Демо-данные
`sql/schema.sql` содержит базовые таблицы. Для тестовых данных добавьте пользователей вручную через phpMyAdmin или SQL.

## Структура
```
/index.php
/app
  /controllers
  /models
  /services
  /middleware
  /views
/config
/assets
/pwa
/storage
/sql
/admin
```

## Крон и webhook
- В будущем: cron для задач рассылок и очистки push-подписок.
- Webhook платежей — отдельный endpoint в `/app/controllers/PaymentController.php`.

## Админ-доступ
Пока используется заглушка `/admin/index.php`. Логика авторизации будет подключена через таблицу `users`.
