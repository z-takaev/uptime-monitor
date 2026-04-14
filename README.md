# Uptime Monitor API

REST API сервис мониторинга доступности сайтов. Система периодически проверяет доступность указанных URL, ведёт историю проверок, считает uptime и отправляет уведомления в Telegram при падении или восстановлении сайта.

## Стек

- **PHP 8.3** + **Laravel 13**
- **PostgreSQL** — основная база данных
- **Redis** — очереди и кэш
- **Docker** + **docker-compose**
- **Pest** — тесты (покрытие 92%)

## Архитектура

- **Repository pattern** — слой работы с БД
- **Service layer** — бизнес-логика отдельно от контроллеров
- **DTO** — типизированная передача данных между слоями
- **API Resources** — трансформация ответов
- **Form Requests** — валидация
- **Events / Listeners** — уведомления

## Возможности

- Регистрация и авторизация через Laravel Sanctum (token-based)
- CRUD для мониторов с настройкой интервала проверки (1/5/10/30 минут)
- Фоновая проверка доступности через Laravel Queue + Redis
- История всех проверок с пагинацией
- Статистика uptime за 24ч / 7д / 30д
- Среднее время отклика
- Информация о последнем инциденте
- Уведомления в Telegram при падении и восстановлении сайта
- Swagger документация

## Запуск через Docker

### 1. Клонировать репозиторий

```bash
git clone https://github.com/z-takaev/uptime-monitor.git
cd uptime-monitor
```

### 2. Настроить окружение

```bash
cp .env.example .env
```

Заполни в `.env`:

```env
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
```

### 3. Запустить контейнеры

```bash
make up
```

### 4. Накатить миграции

```bash
make migrate
```

### 5. Открыть в браузере

- Swagger UI: `http://localhost:8000/api/documentation`

## Команды

```bash
make up        # запустить контейнеры
make down      # остановить контейнеры
make migrate   # накатить миграции
make test      # запустить тесты
make bash      # войти в контейнер
make logs      # логи в реальном времени
```

## API Endpoints

### Аутентификация

| Метод | Endpoint                | Описание    |
| ----- | ----------------------- | ----------- |
| POST  | `/api/v1/auth/register` | Регистрация |
| POST  | `/api/v1/auth/login`    | Вход        |
| POST  | `/api/v1/auth/logout`   | Выход       |

### Мониторы

| Метод  | Endpoint                        | Описание         |
| ------ | ------------------------------- | ---------------- |
| GET    | `/api/v1/monitors`              | Список мониторов |
| POST   | `/api/v1/monitors`              | Создать монитор  |
| GET    | `/api/v1/monitors/{id}`         | Получить монитор |
| PUT    | `/api/v1/monitors/{id}`         | Обновить монитор |
| DELETE | `/api/v1/monitors/{id}`         | Удалить монитор  |
| GET    | `/api/v1/monitors/{id}/history` | История проверок |
| GET    | `/api/v1/monitors/{id}/stats`   | Статистика       |

## Примеры запросов

### Регистрация

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

Ответ:

```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2026-04-14T10:00:00+00:00"
    },
    "token": "1|abc123..."
}
```

### Создать монитор

```bash
curl -X POST http://localhost:8000/api/v1/monitors \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abc123..." \
  -d '{
    "name": "Google",
    "url": "https://google.com",
    "interval": 5
  }'
```

Ответ:

```json
{
    "data": {
        "id": 1,
        "name": "Google",
        "url": "https://google.com",
        "interval": 5,
        "is_active": true,
        "created_at": "2026-04-14T10:00:00+00:00"
    }
}
```

### Статистика монитора

```bash
curl -X GET http://localhost:8000/api/v1/monitors/1/stats \
  -H "Authorization: Bearer 1|abc123..."
```

Ответ:

```json
{
    "data": {
        "uptime": {
            "24h": "99.50",
            "7d": "98.20",
            "30d": "97.80"
        },
        "avg_response_time": {
            "24h": "245.50",
            "7d": "312.00",
            "30d": "298.75"
        },
        "last_incident": {
            "downed_at": "2026-04-13T08:30:00+00:00",
            "restored_at": "2026-04-13T08:45:00+00:00"
        }
    }
}
```

## Тесты

```bash
make test
```
