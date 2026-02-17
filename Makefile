#!/usr/bin/make
# Makefile readme (ru): <http://linux.yaroslavl.ru/docs/prog/gnu_make_3-79_russian_manual.html>
# Makefile readme (en): <https://www.gnu.org/software/make/manual/html_node/index.html#SEC_Contents>

.PHONY: serve queue dev build install setup db-create migrate fresh test tinker logs clear help

# Порт сервера (можно задать: make serve PORT=8080)
PORT ?= 8000

# --- Первый запуск (после клонирования) ---

setup: ## Полная установка с нуля: .env, APP_KEY, БД, зависимости, миграции, сборка
	@echo "→ Копируем .env из .env.example (если нет .env)..."
	@test -f .env || cp .env.example .env
	@echo "→ Генерируем APP_KEY..."
	@php artisan key:generate
	@$(MAKE) db-create
	@echo "→ Устанавливаем PHP-зависимости..."
	@composer install
	@echo "→ Устанавливаем Node-зависимости..."
	@npm install --legacy-peer-deps
	@echo "→ Выполняем миграции..."
	@php artisan migrate
	@echo "→ Собираем фронтенд..."
	@$(MAKE) build
	@echo ""
	@echo "Готово. Запуск: make run  (или make serve + make queue + make dev в отдельных терминалах)"

db-create: ## Создать файл БД для SQLite (если используется sqlite)
	@if grep -q '^DB_CONNECTION=sqlite' .env 2>/dev/null || true; then \
		touch database/database.sqlite 2>/dev/null || true; \
		echo "→ Файл БД SQLite: database/database.sqlite"; \
	fi

# --- Ежедневная разработка ---

serve: ## Запуск Laravel-сервера
	php artisan serve --port=$(PORT)

queue: ## Обработчик очередей (для импорта чатов)
	php artisan queue:work

dev: ## Фронтенд в dev-режиме (Vite + hot reload)
	npm run dev

build: ## Сборка фронтенда для production
	npm run build

run: ## Всё в одном: сервер + очередь + логи + Vite (одна команда, один терминал)
	composer run dev

install: ## Только зависимости и сборка (без .env/миграций; после make setup не нужен)
	composer install
	@test -f .env || (cp .env.example .env && php artisan key:generate)
	npm install --legacy-peer-deps
	$(MAKE) build

migrate: ## Выполнить миграции
	php artisan migrate

fresh: ## Сброс БД и повторный прогон миграций
	php artisan migrate:fresh

test: ## Тесты
	php artisan test

tinker: ## Интерактивная консоль Laravel
	php artisan tinker

logs: ## Просмотр логов в реальном времени
	tail -f storage/logs/laravel.log

clear: ## Очистка кэша приложения, конфига, роутов, вьюх
	php artisan optimize:clear

help: ## Список целей
	@echo "MessSaga — доступные команды:"
	@echo ""
	@echo "  Первый запуск (после git clone):"
	@echo "    make setup   — полная установка (.env, APP_KEY, БД, миграции, сборка)"
	@echo "    make run     — запустить приложение (сервер + очередь + Vite + логи)"
	@echo ""
	@echo "  Разработка:"
	@echo "    make serve   — Laravel (порт по умолчанию 8000; PORT=8080 make serve)"
	@echo "    make queue   — воркер очередей (обработка импорта чатов)"
	@echo "    make dev     — Vite dev-сервер (hot reload)"
	@echo "    make build   — сборка фронтенда для production"
	@echo "    make run     — всё в одном терминале"
	@echo ""
	@echo "  БД и прочее:"
	@echo "    make db-create — создать database/database.sqlite для SQLite"
	@echo "    make migrate   — выполнить миграции"
	@echo "    make fresh     — сброс БД и миграции заново"
	@echo "    make test      — запуск тестов"
	@echo "    make tinker    — интерактивная консоль Laravel"
	@echo "    make logs      — хвост лога (tail -f)"
	@echo "    make clear    — очистка кэша приложения"
	@echo "    make help     — этот список"
