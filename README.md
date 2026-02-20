# MessSaga

Веб-приложение для импорта и просмотра переписок из мессенджеров (Telegram, WhatsApp, Viber). Laravel + Inertia + Vue.

---

## Требования

- **PHP** 8.2+
- **Composer**
- **Node.js** 18+ и **npm**
- **MySQL** 8+ (или MariaDB) — сервер должен быть запущен, база создана до `make setup` / `make migrate`

---

## Запуск с нуля (только что склонировали репозиторий)

### 1. Одна команда — полная установка

```bash
make setup
```

Эта команда по очереди:

1. Копирует `.env.example` в `.env`, если файла `.env` ещё нет.
2. Генерирует **APP_KEY** в `.env`.
3. Для SQLite создаёт файл `database/database.sqlite` (для MySQL базу нужно создать вручную заранее).
4. Ставит PHP-зависимости (`composer install`).
5. Ставит Node-зависимости (`npm install --legacy-peer-deps`).
6. Запускает миграции (`php artisan migrate`).
7. Собирает фронтенд (`npm run build`).

После этого проект готов к запуску.

### 2. Запуск приложения

**Вариант А — всё в одном терминале (удобно для разработки):**

```bash
make run
```

Поднимаются: Laravel-сервер, воркер очередей, лог и Vite. Откройте в браузере адрес, который выведет скрипт (обычно `http://127.0.0.1:8000`).

**Вариант Б — в разных терминалах:**

```bash
# Терминал 1 — сервер
make serve

# Терминал 2 — очередь (обработка импорта чатов)
make queue

# Терминал 3 — фронтенд с hot reload
make dev
```

Сервер по умолчанию на порту **8000**. Другой порт: `PORT=8080 make serve`.

---

## Почему появляется ошибка про APP_KEY?

В репозитории нет файла `.env` (он в `.gitignore`). В нём хранятся секреты и настройки окружения.

В `.env.example` лежит шаблон, где **APP_KEY** пустой. Laravel требует непустой APP_KEY для шифрования сессий, cookies и т.п. Если ключа нет, приложение выдаёт ошибку вроде:

```
No application encryption key has been specified.
```

**Что сделать:** один раз сгенерировать ключ:

```bash
cp .env.example .env
php artisan key:generate
```

Либо просто выполнить `make setup` — он сам создаёт `.env` и запускает `key:generate`.

---

## База данных

### По умолчанию — MySQL

В `.env` заданы параметры MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=messsaga
DB_USERNAME=root
DB_PASSWORD=
```

Перед первым запуском:

1. Запустите сервер MySQL (или MariaDB).
2. Создайте базу (один раз):

   ```sql
   CREATE DATABASE messsaga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. В `.env` укажите правильные `DB_USERNAME` и `DB_PASSWORD`, если отличаются от `root` и пустого пароля.
4. Выполните `make setup` или `make migrate`.

### SQLite (опционально)

Чтобы использовать SQLite вместо MySQL:

1. В `.env` задайте:

   ```env
   DB_CONNECTION=sqlite
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=messsaga
   # DB_USERNAME=root
   # DB_PASSWORD=
   ```

2. Создайте файл БД и выполните миграции:

   ```bash
   make db-create
   make migrate
   ```

---

## Полезные команды (Makefile)

| Команда          | Описание                                                                 |
|------------------|--------------------------------------------------------------------------|
| `make setup`     | Полная установка с нуля (.env, ключ, БД, зависимости, миграции, сборка). |
| `make run`       | Запуск всего: сервер + очередь + Vite + логи.                            |
| `make serve`     | Только Laravel-сервер.                                                   |
| `make queue`     | Воркер очередей (импорт чатов).                                          |
| `make dev`       | Vite в режиме разработки (hot reload).                                   |
| `make build`     | Сборка фронтенда для production.                                         |
| `make db-create` | Создать `database/database.sqlite` при использовании SQLite.             |
| `make migrate`   | Выполнить миграции.                                                      |
| `make fresh`     | Сбросить БД и заново выполнить миграции.                                 |
| `make test`      | Запуск тестов.                                                           |
| `make logs`      | Просмотр логов в реальном времени.                                       |
| `make clear`     | Очистка кэша приложения.                                                 |
| `make help`      | Список всех целей.                                                       |

---

## Если что-то пошло не так

- **«No application encryption key»** — выполните `php artisan key:generate` или заново `make setup`.
- **Ошибки при миграции / «could not find driver»** — для MySQL нужен PHP-модуль `pdo_mysql`. Для SQLite — `pdo_sqlite`.
- **«SQLSTATE[HY000] [1049] Unknown database»** — создайте базу MySQL: `CREATE DATABASE messsaga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- **Очередь не обрабатывает импорт** — должен быть запущен воркер: `make queue` или `make run`.
- **После pull не работают стили/скрипты** — пересоберите фронтенд: `npm install --legacy-peer-deps && make build`.

---

## Стек

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js, Vite, Tailwind CSS
- **БД по умолчанию:** MySQL (опционально SQLite)
- **Очереди:** database driver (таблица `jobs`)
