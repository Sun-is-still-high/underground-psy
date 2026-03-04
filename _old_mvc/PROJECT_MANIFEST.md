# Манифест проекта psy-practice

## Описание проекта
**psy-practice** — некоммерческая платформа для связи клиентов с начинающими психологами. Построена на Next.js 16 с React 19, TypeScript, PostgreSQL и NextAuth.

---

## АКТОРЫ (роли пользователей)

### 1. CLIENT (Клиент)
**Возможности:**
- Регистрация с анкетой безопасности (скрининг психического здоровья)
- Просмотр и поиск психологов
- Фильтрация по методам терапии и наличию супервизии
- Бронирование сеансов терапии
- Просмотр своих забронированных сессий
- Оставление отзывов после сессий
- Подача жалоб

**Связанные модели:**
- `User` (role: CLIENT)
- `ClientProfile` — анкета клиента с данными о состоянии здоровья
- `BookingSession` — забронированные сеансы (как clientId)
- `Review` — отзывы (как authorId или targetId)
- `Complaint` — жалобы (как authorId)

---

### 2. PSYCHOLOGIST (Психолог)
**Возможности:**
- Регистрация с указанием образования и квалификации
- Создание и управление слотами доступности (регулярные или разовые)
- Просмотр дашборда с расписанием
- Управление бронированиями клиентов
- Отслеживание истории сеансов
- Получение рейтингов и отзывов

**Связанные модели:**
- `User` (role: PSYCHOLOGIST)
- `PsychologistProfile` — профиль с образованием, методами, опытом
- `AvailableSlot` — слоты доступности психолога
- `BookingSession` — сеансы с клиентами (как psychologistId)
- `Review` — получаемые отзывы (как targetId)
- `Complaint` — жалобы на психолога (как targetId)

---

### 3. ADMIN (Администратор)
**Возможности:**
- Доступ ко всем защищённым эндпоинтам
- Управление базой знаний (статьи, FAQ)
- Модерация жалоб
- Блокировка/разблокировка пользователей

**Связанные модели:**
- `User` (role: ADMIN)
- `KnowledgeArticle` — управление статьями
- `Complaint` — разрешение жалоб (isResolved, resolution)

---

## МОДЕЛИ ДАННЫХ (Prisma Schema)

### Основные модели

#### 1. User
**Назначение:** Базовая модель пользователя для всех ролей

**Поля:**
- `id` (String, UUID) — уникальный идентификатор
- `email` (String, unique) — email для входа
- `emailVerified` (DateTime?) — дата верификации email
- `passwordHash` (String) — хеш пароля (bcrypt)
- `name` (String?) — имя пользователя
- `role` (UserRole) — роль: CLIENT | PSYCHOLOGIST | ADMIN
- `image` (String?) — URL аватара
- `isBlocked` (Boolean) — флаг блокировки
- `blockedReason` (String?) — причина блокировки
- `createdAt` / `updatedAt` — временные метки

**Связи:**
- `psychologistProfile` → PsychologistProfile (1:1)
- `clientProfile` → ClientProfile (1:1)
- `accounts` → Account[] (NextAuth)
- `sessions` → Session[] (NextAuth)
- `clientSessions` → BookingSession[] (сеансы как клиент)
- `psychologistSessions` → BookingSession[] (сеансы как психолог)
- `reviewsGiven` → Review[] (оставленные отзывы)
- `reviewsReceived` → Review[] (полученные отзывы)
- `complaintsCreated` → Complaint[] (созданные жалобы)
- `complaintsAgainst` → Complaint[] (жалобы против пользователя)

---

#### 2. PsychologistProfile
**Назначение:** Профиль психолога с квалификацией и методами работы

**Поля:**
- `id` (String, UUID)
- `userId` (String, unique) → User
- `education` (String) — образование
- `educationStatus` (EducationStatus) — COMPLETED | IN_PROGRESS | RETRAINING
- `diplomaUrl` (String?) — ссылка на диплом
- `sessionCount` (Int) — количество проведённых сеансов
- `hasSupervision` (Boolean) — наличие супервизии
- `hasIntervision` (Boolean) — наличие интервизии
- `methods` (String[]) — методы терапии (CBT, гештальт, психоанализ и т.д.)
- `worksWith` (String[]) — с кем работает (темы, проблемы)
- `doesNotWorkWith` (String[]) — с чем не работает
- `bio` (String?) — описание
- `ethicsConfirmed` (Boolean) — подтверждение этического кодекса
- `createdAt` / `updatedAt`

**Связи:**
- `user` → User (1:1)
- `availableSlots` → AvailableSlot[] (слоты доступности)

---

#### 3. ClientProfile
**Назначение:** Анкета клиента для скрининга безопасности

**Поля:**
- `id` (String, UUID)
- `userId` (String, unique) → User
- `isAdult` (Boolean) — совершеннолетний?
- `hasMentalDisorders` (Boolean?) — психические расстройства?
- `takesPsychMedication` (Boolean?) — принимает психотропные препараты?
- `hasSuicidalThoughts` (Boolean?) — суицидальные мысли?
- `hasAddictions` (Boolean?) — зависимости?
- `warningShown` (Boolean) — показано ли предупреждение?
- `createdAt` / `updatedAt`

**Связи:**
- `user` → User (1:1)

---

#### 4. BookingSession
**Назначение:** Сеансы терапии между клиентом и психологом

**Поля:**
- `id` (String, UUID)
- `clientId` (String) → User
- `psychologistId` (String) → User
- `scheduledAt` (DateTime) — запланированное время
- `duration` (Int) — длительность в минутах
- `status` (SessionStatus) — PENDING | CONFIRMED | COMPLETED | CANCELLED
- `meetingLink` (String?) — ссылка на видеозвонок
- `clientChecklist` (Json?) — чеклист клиента
- `notes` (String?) — заметки
- `createdAt` / `updatedAt`

**Связи:**
- `client` → User (N:1)
- `psychologist` → User (N:1)
- `review` → Review? (1:1, опциональный отзыв)

---

#### 5. AvailableSlot
**Назначение:** Слоты доступности психолога для бронирования

**Поля:**
- `id` (String, UUID)
- `psychologistId` (String) → PsychologistProfile
- `type` (SlotType) — RECURRING | ONE_TIME
- `dayOfWeek` (Int?) — день недели (0-6) для RECURRING
- `specificDate` (DateTime?) — конкретная дата для ONE_TIME
- `startTime` (String) — время начала (HH:mm)
- `endTime` (String) — время окончания (HH:mm)
- `duration` (Int) — длительность сеанса в минутах
- `isActive` (Boolean) — активен ли слот
- `createdAt` / `updatedAt`

**Связи:**
- `psychologist` → PsychologistProfile (N:1)

**Типы слотов:**
- **RECURRING** — еженедельные слоты (например, каждый понедельник 10:00-11:00)
- **ONE_TIME** — разовые слоты на конкретную дату

---

#### 6. Review
**Назначение:** Отзывы клиентов о психологах после сеанса

**Поля:**
- `id` (String, UUID)
- `sessionId` (String, unique) → BookingSession
- `authorId` (String) → User
- `targetId` (String) → User
- `rating` (Int) — оценка от 1 до 5
- `text` (String?) — текст отзыва
- `createdAt`

**Связи:**
- `session` → BookingSession (1:1)
- `author` → User (клиент)
- `target` → User (психолог)

---

#### 7. Complaint
**Назначение:** Жалобы пользователей для модерации

**Поля:**
- `id` (String, UUID)
- `authorId` (String) → User
- `targetId` (String) → User
- `description` (String) — описание проблемы
- `isResolved` (Boolean) — решена ли жалоба
- `resolution` (String?) — решение администратора
- `createdAt` / `updatedAt`

**Связи:**
- `author` → User (создатель жалобы)
- `target` → User (на кого жалоба)

---

#### 8. KnowledgeArticle
**Назначение:** База знаний, FAQ, информационные статьи

**Поля:**
- `id` (String, UUID)
- `slug` (String, unique) — URL-slug
- `title` (String) — заголовок
- `content` (String) — содержимое (Markdown)
- `category` (String?) — категория
- `order` (Int) — порядок сортировки
- `isPublished` (Boolean) — опубликована ли статья
- `createdAt` / `updatedAt`

---

### Вспомогательные модели NextAuth

#### 9. Account
**Назначение:** OAuth-провайдеры для NextAuth (Google, GitHub и т.д.)

**Поля:**
- `userId`, `type`, `provider`, `providerAccountId`
- `refresh_token`, `access_token`, `expires_at`
- `token_type`, `scope`, `id_token`, `session_state`

---

#### 10. Session
**Назначение:** Серверные сессии для NextAuth (альтернатива JWT)

**Поля:**
- `sessionToken` (String, unique)
- `userId` (String) → User
- `expires` (DateTime)

---

#### 11. VerificationToken
**Назначение:** Токены для верификации email

**Поля:**
- `identifier` (String) — email
- `token` (String, unique)
- `expires` (DateTime)

---

## ENUMS (Перечисления)

### UserRole
```prisma
enum UserRole {
  CLIENT        // Клиент
  PSYCHOLOGIST  // Психолог
  ADMIN         // Администратор
}
```

### SessionStatus
```prisma
enum SessionStatus {
  PENDING    // Ожидает подтверждения
  CONFIRMED  // Подтверждена
  COMPLETED  // Завершена
  CANCELLED  // Отменена
}
```

### EducationStatus
```prisma
enum EducationStatus {
  COMPLETED   // Завершено
  IN_PROGRESS // В процессе
  RETRAINING  // Переподготовка
}
```

### SlotType
```prisma
enum SlotType {
  RECURRING // Регулярный (еженедельный)
  ONE_TIME  // Разовый (на конкретную дату)
}
```

---

## API ЭНДПОИНТЫ

### Аутентификация
- `POST /api/auth/[...nextauth]` — обработчик NextAuth
- `POST /api/auth/register/client` — регистрация клиента
- `POST /api/auth/register/psychologist` — регистрация психолога

### Психологи
- `GET /api/psychologists` — список психологов (фильтры: method, supervision, search)
- `GET /api/psychologists/[id]` — детали психолога
- `GET /api/psychologists/[id]/slots` — доступные слоты психолога

### Сеансы
- `POST /api/sessions` — создать бронирование
- `GET /api/sessions` — сеансы пользователя (клиент или психолог)
- `GET /api/sessions/[id]` — детали сеанса
- `PATCH/DELETE /api/sessions/[id]` — обновить/отменить сеанс

### Слоты доступности
- `GET /api/slots` — слоты текущего психолога
- `POST /api/slots` — создать слот (RECURRING или ONE_TIME)
- `DELETE /api/slots?id=[id]` — деактивировать слот

---

## ТЕХНОЛОГИЧЕСКИЙ СТЕК

**Frontend:**
- React 19.2.3
- TypeScript
- Tailwind CSS v4
- Radix UI (через Shadcn/ui)
- React Hook Form + Zod

**Backend:**
- Next.js 16.1.1 (App Router)
- Node.js
- NextAuth v5 (beta)

**База данных:**
- PostgreSQL
- Prisma ORM

**UI-компоненты:**
- Shadcn/ui: Button, Card, Form, Input, Select, Calendar, Dialog, Badge, Avatar, Tabs, Sheet, Dropdown Menu
- Sonner (уведомления-тосты)

**Деплой:**
- Docker + docker-compose (готовые конфигурации)

---

## КЛЮЧЕВЫЕ БИЗНЕС-ПРОЦЕССЫ

### 1. Регистрация клиента
1. Клиент заполняет анкету безопасности (`ClientProfile`)
2. Проверка на высокий риск (суицидальные мысли, тяжёлые расстройства)
3. Показ предупреждения при необходимости
4. Создание аккаунта с ролью CLIENT

### 2. Регистрация психолога
1. Ввод образования, статуса (COMPLETED/IN_PROGRESS/RETRAINING)
2. Указание методов работы (CBT, гештальт, психоанализ и т.д.)
3. Указание наличия супервизии/интервизии
4. Создание аккаунта с ролью PSYCHOLOGIST + `PsychologistProfile`

### 3. Поиск психолога клиентом
1. Фильтрация по методам терапии
2. Фильтрация по супервизии/интервизии
3. Полнотекстовый поиск по имени и био
4. Просмотр профиля с рейтингом и отзывами

### 4. Бронирование сеанса
1. Клиент выбирает психолога
2. Просмотр доступных слотов (RECURRING или ONE_TIME)
3. Выбор времени и создание `BookingSession` со статусом PENDING
4. Психолог подтверждает → статус CONFIRMED
5. После сеанса → статус COMPLETED
6. Клиент оставляет отзыв (`Review`)

### 5. Управление расписанием психолога
1. Создание регулярных слотов (например, каждый вторник 14:00-15:00)
2. Создание разовых слотов на конкретные даты
3. Деактивация слотов при необходимости
4. Система предотвращает двойное бронирование

---

## ФУНКЦИИ БЕЗОПАСНОСТИ

- **Скрининг клиентов:** анкета выявляет высокорисковых клиентов
- **Предупреждения:** система показывает warnings для опасных случаев
- **База знаний:** статьи с дисклеймерами и информацией
- **Жалобы и модерация:** пользователи могут жаловаться друг на друга
- **Блокировка пользователей:** админы могут заблокировать пользователей с указанием причины

---

## СТРУКТУРА ПРОЕКТА

```
w:\psy-practice/
├── src/
│   ├── app/                    # Next.js App Router
│   │   ├── (auth)/             # Группа маршрутов для аутентификации
│   │   │   ├── login/
│   │   │   └── register/
│   │   │       ├── client/
│   │   │       └── psychologist/
│   │   ├── (client)/           # Маршруты для клиентов
│   │   │   ├── psychologists/
│   │   │   │   ├── [id]/
│   │   │   │   └── [id]/book/
│   │   │   └── sessions/
│   │   ├── (psychologist)/     # Маршруты для психологов
│   │   │   └── dashboard/
│   │   │       └── schedule/
│   │   ├── api/                # API-эндпоинты
│   │   │   ├── auth/
│   │   │   ├── psychologists/
│   │   │   ├── sessions/
│   │   │   └── slots/
│   │   ├── knowledge-base/     # База знаний
│   │   │   └── [slug]/
│   │   ├── layout.tsx
│   │   ├── page.tsx            # Главная страница
│   │   └── globals.css
│   ├── components/
│   │   ├── ui/                 # UI-компоненты (Radix + Shadcn)
│   │   ├── psychologist/       # Компоненты психологов
│   │   ├── shared/             # Общие компоненты (header, footer)
│   │   └── providers/          # React-провайдеры
│   ├── lib/
│   │   ├── auth.ts             # Конфигурация NextAuth
│   │   ├── auth-utils.ts       # Хелперы (getSession, requireAuth, requireRole)
│   │   ├── prisma.ts           # Prisma-клиент (singleton)
│   │   ├── utils.ts            # Общие утилиты
│   │   └── validators.ts       # Zod-схемы валидации
│   └── types/
│       └── next-auth.d.ts      # TypeScript-декларации для NextAuth
├── prisma/
│   ├── schema.prisma           # Схема базы данных
│   └── migrations/             # Миграции
├── public/                     # Статические файлы
├── package.json
├── tsconfig.json
├── next.config.ts
├── docker-compose.yml
├── docker-compose.dev.yml
└── Dockerfile
```

---

## ОСНОВНЫЕ ФАЙЛЫ

| Файл | Назначение |
|------|-----------|
| `src/lib/auth.ts` | Конфигурация NextAuth (JWT, Credentials, callbacks) |
| `src/lib/auth-utils.ts` | Хелперы аутентификации: getSession, getCurrentUser, requireAuth, requireRole |
| `src/lib/validators.ts` | Zod-схемы для валидации форм (login, register, booking, reviews) |
| `src/lib/prisma.ts` | Singleton Prisma-клиента |
| `src/app/page.tsx` | Лендинг с hero, how-it-works, предупреждениями |
| `src/app/(auth)/login/page.tsx` | Форма входа |
| `src/app/(client)/psychologists/page.tsx` | Каталог психологов с фильтрами |
| `src/app/(client)/psychologists/[id]/page.tsx` | Профиль психолога |
| `src/app/(client)/psychologists/[id]/book/page.tsx` | Форма бронирования сеанса |
| `src/app/(client)/sessions/page.tsx` | Список сеансов клиента |
| `src/app/(psychologist)/dashboard/page.tsx` | Дашборд психолога |
| `src/app/(psychologist)/dashboard/schedule/page.tsx` | Управление расписанием |
| `src/app/api/auth/register/client/route.ts` | API регистрации клиента |
| `src/app/api/auth/register/psychologist/route.ts` | API регистрации психолога |
| `src/app/api/psychologists/route.ts` | API списка психологов с фильтрами |
| `src/app/api/sessions/route.ts` | API создания/получения сеансов |
| `src/app/api/slots/route.ts` | API управления слотами доступности |
| `prisma/schema.prisma` | Полная схема базы данных (12 моделей) |

---

## Итоговая диаграмма связей

```
User (CLIENT)
  ↓ 1:1
ClientProfile
  ↓ 1:N
BookingSession ← (M:N) → PsychologistProfile
                              ↑ 1:1
                          User (PSYCHOLOGIST)
                              ↓ 1:N
                          AvailableSlot

BookingSession → Review (1:1)

User → Review (author/target) — M:N
User → Complaint (author/target) — M:N
```

---

**Дата создания манифеста:** 2026-02-02
**Версия проекта:** Next.js 16.1.1, React 19.2.3
**Статус:** В разработке
