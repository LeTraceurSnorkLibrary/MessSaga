# Раздельные таблицы для сообщений разных мессенджеров

## Архитектура

Сообщения из разных мессенджеров хранятся в отдельных таблицах для обеспечения:
- **Чистоты данных** - специфичные поля без nullable значений
- **Производительности** - меньшие таблицы, более эффективные индексы
- **Гибкости** - можно менять схему для одного мессенджера без влияния на другие

## Структура таблиц

### Общие таблицы
- `conversations` - общая таблица для всех переписок
- `messenger_accounts` - аккаунты мессенджеров пользователей

### Таблицы сообщений
- `telegram_messages` - сообщения Telegram
- `whatsapp_messages` - сообщения WhatsApp  
- `viber_messages` - сообщения Viber

## Модели

### Базовый класс
- `Message` (абстрактный) - базовый класс с общими полями, шифрованием и методами
  - Все модели мессенджеров наследуются от него
  - Содержит общие поля: `conversation_id`, `external_id`, `sender_name`, `sent_at`, `text`, `raw`
  - Автоматическое шифрование поля `text` через трейт `HasEncryptedAttributes`

### Модели сообщений (наследуются от Message)
- `TelegramMessage extends Message` - модель для Telegram сообщений
- `WhatsAppMessage extends Message` - модель для WhatsApp сообщений
- `ViberMessage extends Message` - модель для Viber сообщений

### Модель Conversation
Использует методы для работы с сообщениями:
- `telegramMessages()` - отношение HasMany для Telegram
- `whatsappMessages()` - отношение HasMany для WhatsApp
- `viberMessages()` - отношение HasMany для Viber
- `messagesQuery()` - универсальный метод, возвращающий query builder в зависимости от типа мессенджера
- `messages()` - универсальный метод, возвращающий коллекцию сообщений

## Специфичные поля

### Telegram (`telegram_messages`)
- `sticker_id`, `sticker_set_name` - стикеры
- `voice_duration`, `voice_file_id` - голосовые сообщения
- `video_file_id`, `video_duration` - видео
- `photo_file_id`, `photo_sizes` - фото
- `service_action`, `service_actor` - сервисные сообщения
- `forwarded_from_*` - пересылка сообщений
- `edited_at` - редактирование
- `reactions` - реакции

### WhatsApp (`whatsapp_messages`)
- `status`, `status_updated_at` - статусы сообщений
- `is_forwarded`, `forwarded_from_name` - пересылка
- `voice_note_*` - голосовые заметки
- `media_*` - медиа файлы
- `latitude`, `longitude` - локация
- `contact_data` - контакты
- `reactions` - реакции
- `mentions` - упоминания
- `quoted_message_id` - цитаты
- `labels` - ярлыки/пометки

### Viber (`viber_messages`)
- `media_url`, `media_thumbnail_url` - медиа файлы
- `video_duration` - видео
- `latitude`, `longitude` - локация
- `contact_data` - контакты
- `sticker_id` - стикеры
- `urls` - URL в сообщениях

## Использование

### Получение сообщений

```php
// Универсальный способ (рекомендуется)
$messages = $conversation->messagesQuery()
    ->where('message_type', 'text')
    ->orderBy('sent_at')
    ->get();

// Или для получения коллекции
$messages = $conversation->messages();

// Специфичный способ для конкретного мессенджера
$telegramMessages = $conversation->telegramMessages()->get();
```

### Создание сообщений

```php
// В ImportService автоматически определяется модель по типу мессенджера
TelegramMessage::create([...]);
WhatsAppMessage::create([...]);
ViberMessage::create([...]);
```

## Миграция данных

Миграция `migrate_messages_to_separate_tables` переносит данные из старой таблицы `messages` в новые таблицы в зависимости от типа мессенджера переписки.

После успешной миграции старую таблицу `messages` можно удалить (но она остается для возможности отката).

## Поиск по всем мессенджерам

Для кросс-мессенджерного поиска можно использовать UNION запросы:

```php
$results = TelegramMessage::where('text', 'like', "%{$query}%")
    ->union(
        WhatsAppMessage::where('text', 'like', "%{$query}%")
    )
    ->union(
        ViberMessage::where('text', 'like', "%{$query}%")
    )
    ->get();
```

Или использовать отдельный поисковый индекс (Elasticsearch, Meilisearch и т.д.).
