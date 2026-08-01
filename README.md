# IDG POS System

A full-stack Point-of-Sale (POS) application built with a **Laravel 13 REST API** backend and a **Vue 3 SPA** frontend, covering product inventory, customer management, order processing, and sales reporting.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)
![Vue](https://img.shields.io/badge/Vue.js-3.5-4FC08D?logo=vue.js&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?logo=vite&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-06B6D4?logo=tailwindcss&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-Default_DB-003B57?logo=sqlite&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 1. Tech Stack

### Backend
| Technology | Version / Package | Purpose |
|---|---|---|
| PHP | ^8.3 | Core language |
| Laravel Framework | ^13.8 | API framework, routing, ORM, validation |
| Laravel Sanctum | ^4.3 | Token-based API authentication |
| Laravel Tinker | ^3.0 | REPL / debugging |
| Laravel Pail | ^1.2.5 (dev) | Real-time log tailing |
| Laravel Pint | ^1.27 (dev) | Code style / linting |
| PHPUnit | ^12.5 (dev) | Testing framework |
| Mockery | ^1.6 (dev) | Test mocking |
| Faker (fakerphp) | ^1.23 (dev) | Factory fake data |

### Frontend (SPA — `src/`)
| Technology | Version | Purpose |
|---|---|---|
| Vue.js | ^3.5 | Reactive UI framework (Composition API, `<script setup>`) |
| Vue Router | ^5.2 | Client-side routing / layouts |
| Pinia | ^4.0 | Global state management (auth store) |
| Axios | ^1.18 | HTTP client for the Laravel API |
| Tailwind CSS | ^4.3 | Utility-first styling |
| Vite | ^8.x | Dev server & build tool |
| @vitejs/plugin-vue | ^6.x | Vue SFC compilation |

### Backend Asset Pipeline (Laravel side — `resources/`)
| Technology | Purpose |
|---|---|
| Laravel Vite Plugin | Asset bundling inside the Laravel app |
| Tailwind CSS v4 (`@tailwindcss/vite`) | Styling for Blade views |
| Bunny Fonts (`Instrument Sans`) | Web font loading |

### Database
- **SQLite** (default, `database/database.sqlite`) — used in local/dev and testing (`:memory:`)
- Configurable via `config/database.php` for **MySQL / MariaDB / PostgreSQL / SQL Server**

### Package & Dependency Managers
- **Composer** — PHP dependencies (`composer.json` / `composer.lock`)
- **npm** — JS dependencies (two `package.json` files: one for the Laravel asset pipeline, one for the standalone Vue SPA)

---

## 2. Architecture & Tech Workflow

```
┌────────────────────┐      Axios (Bearer token)      ┌──────────────────────────┐
│   Vue 3 SPA (src/)  │ ───────────────────────────▶   │  routes/api.php          │
│  Pinia + Vue Router │ ◀───────────────────────────   │  (Laravel API routes)    │
└────────────────────┘        JSON responses           └────────────┬─────────────┘
                                                                     │
                                                          Form Request validation
                                                     (StoreProductRequest, StoreOrderRequest, ...)
                                                                     │
                                                                     ▼
                                                        ┌─────────────────────────┐
                                                        │   Api Controllers        │
                                                        │  (Product/Order/Customer │
                                                        │   /Auth/Report/Dashboard)│
                                                        └────────────┬─────────────┘
                                                                     │
                                                          delegates business logic to
                                                                     ▼
                                                        ┌─────────────────────────┐
                                                        │   Services                │
                                                        │  ProductService            │
                                                        │  OrderService (DB::transaction,│
                                                        │  row locking for stock)    │
                                                        │  CustomerService            │
                                                        │  ReportService              │
                                                        │  DashboardService           │
                                                        └────────────┬─────────────┘
                                                                     │
                                                                     ▼
                                                        ┌─────────────────────────┐
                                                        │  Eloquent Models          │
                                                        │  Product / Order /        │
                                                        │  OrderItem / Customer /   │
                                                        │  User                     │
                                                        └────────────┬─────────────┘
                                                                     │
                                                                     ▼
                                                        ┌─────────────────────────┐
                                                        │      Database             │
                                                        │  (SQLite / MySQL / PgSQL) │
                                                        └─────────────────────────┘
```

**Request lifecycle:**
1. The Vue SPA sends requests via `src/services/api.js` (Axios instance) with a bearer token attached from `localStorage`.
2. Laravel's `bootstrap/app.php` routes API traffic through `routes/api.php`, protected by the `auth:sanctum` middleware group (except `/login`).
3. **Form Request** classes (`app/Http/Requests/**`) validate and authorize incoming payloads before hitting controllers.
4. **API Controllers** (`app/Http/Controllers/Api/**`) stay thin — they call into **Service classes** (`app/Services/**`) for business logic.
5. Services encapsulate transactional logic — e.g. `OrderService::create()` wraps stock deduction in `DB::transaction()` with `lockForUpdate()` to prevent overselling, and throws a custom `InsufficientStockException` when stock is short.
6. **Eloquent Models** (`app/Models/**`) map to the database schema defined in `database/migrations/**`.
7. Responses are shaped through **API Resources** (`app/Http/Resources/**`) and returned in a consistent envelope via the `ApiResponse` trait (`success()` / `error()` helpers).
8. Centralized exception handling in `bootstrap/app.php` converts validation errors and thrown exceptions into structured JSON (`success`, `message`, `errors`).
9. Background/queueable work (if introduced) would flow through `config/queue.php` drivers (`database` by default) and the `jobs` / `job_batches` / `failed_jobs` tables already migrated.

---

## 3. Key Features & Modules

### 🔐 Authentication
- Token-based login/logout via **Laravel Sanctum** (`AuthController`)
- Current user profile (`/me`), profile update, and password change (with current-password confirmation)
- Frontend session persisted via Pinia `auth` store + `localStorage` token

### 📦 Product & Inventory Management
- Full CRUD for products (`ProductController`, `ProductService`)
- SKU uniqueness validation, cost price / selling price separation
- Discount engine supporting `none`, `percentage`, and `fixed` discount types with validation guards (percentage ≤ 100%, fixed ≤ price)
- Automatic **selling price calculation** (`ProductService::calculateSellingPrice`)
- Stock adjustment endpoint supporting `set`, `increment`, `decrement` operations with row-level locking
- Low-stock detection and threshold configurable via `config/pos.php` (`POS_LOW_STOCK_THRESHOLD`)

### 👥 Customer Management
- CRUD with search by name/code/phone
- Auto-generated unique customer codes (`CUS-00001`)
- Active/inactive status tracking

### 🧾 Order Processing
- Order creation with multiple line items, computed sub-total, discount, delivery fee, and total
- **Stock-safe checkout**: locks product rows during order creation, computes shortages, and throws `InsufficientStockException` (HTTP 422) if any item is out of stock
- Order status lifecycle: `pending → completed / cancelled`, with automatic **stock restock** when an order is cancelled
- Auto-generated unique order numbers (`ORD-YYYYMMDD-####`)

### 📊 Dashboard & Reporting
- `DashboardService`: total products/customers/orders, total revenue, today's sales, low-stock list, recent orders
- `ReportService`: sales summary grouped by date (excluding cancelled orders) and top-selling products by quantity/revenue within a date range

### 🖥️ Frontend SPA
- Vue Router-driven layouts (`AuthLayout`, `MainLayout` w/ sidebar)
- Pages: Login, Dashboard, Customers, Products (Create), Orders (List/Create), Reports, Settings
- Reusable base components: `BaseInput`, `BaseSelect`, `BaseButton`, `BasePagination`, `OrderStatusSelect`
- Composables encapsulate API calls and state (`useCustomers`, `useOrders`, `useDashboard`, `useReports`, `useSettings`, `usePagination`)

---

## 4. Local Development Setup

### Prerequisites
- PHP `>= 8.3` with common extensions (`ctype`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `session`)
- Composer `2.x`
- Node.js `>= 20.19` and npm
- SQLite (bundled with PHP `pdo_sqlite`) — or MySQL/PostgreSQL if you prefer

### 1. Clone the repository
```bash
git clone https://github.com/viseth-sopheach/idg-project.git
cd idg-project
```

### 2. Backend setup (Laravel API)
```bash
# Install PHP dependencies
composer install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Create the SQLite database file (default driver)
touch database/database.sqlite

# Run migrations and seed sample data
php artisan migrate --seed
```

> `composer.json` also exposes a one-shot setup script:
> ```bash
> composer run setup
> ```
> which installs dependencies, copies `.env`, generates the key, migrates the database, and builds frontend assets.

### 3. Configure environment variables
Edit `.env` as needed — key defaults:
```env
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
POS_LOW_STOCK_THRESHOLD=5
```

### 4. Install & build Laravel-side frontend assets
```bash
npm install
npm run dev     # for local development (Vite HMR)
# or
npm run build   # for production assets
```

### 5. Serve the Laravel API
```bash
php artisan serve
# API available at http://localhost:8000
```

Alternatively, run the full dev stack (server, queue listener, logs, Vite) in one command:
```bash
composer run dev
```

### 6. Frontend SPA setup (Vue 3 app in `src/`)
```bash
npm install
npm run dev       # starts Vite dev server (default: http://localhost:5173)
```
Update the API base URL in `src/services/api.js` if your backend isn't running on `http://localhost:8000/api`.

### 7. Run tests
```bash
composer run test
# or
php artisan test
```

### Default seeded credentials
```
email: admin@idg.com
password: password
```