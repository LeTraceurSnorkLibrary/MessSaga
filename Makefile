#!/usr/bin/make
# Makefile readme (ru): <http://linux.yaroslavl.ru/docs/prog/gnu_make_3-79_russian_manual.html>
# Makefile readme (en): <https://www.gnu.org/software/make/manual/html_node/index.html#SEC_Contents>

.PHONY: serve queue dev build install migrate fresh test tinker logs clear help

# Порт сервера (можно задать: make serve PORT=8080)
PORT ?= 8000

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

install: ## Установка зависимостей и первичная настройка
	composer install
	@test -f .env || (cp .env.example .env && php artisan key:generate)
	npm install --legacy-peer-deps
	$(MAKE) build

migrate: ## Миграции
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
	@echo "  make serve    — запуск Laravel (порт по умолчанию 8000; PORT=8080 make serve)"
	@echo "  make queue    — воркер очередей (обработка импорта)"
	@echo "  make dev      — Vite dev-сервер (hot reload)"
	@echo "  make build    — сборка фронтенда для production"
	@echo "  make run      — сервер + очередь + логи + Vite в одном терминале"
	@echo "  make install  — composer install, npm install, сборка"
	@echo "  make migrate  — выполнить миграции"
	@echo "  make fresh    — сброс БД и миграции заново"
	@echo "  make test     — запуск тестов"
	@echo "  make tinker   — интерактивная консоль Laravel"
	@echo "  make logs     — хвост лога (tail -f)"
	@echo "  make clear    — очистка кэша приложения"
	@echo "  make help     — этот список"
