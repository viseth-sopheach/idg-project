# IDG POS — Frontend (Vue 3 SPA)

A standalone **Vue 3** single-page application that consumes the IDG POS Laravel API, providing the storefront/admin UI for authentication, dashboard analytics, customer management, product creation, order processing, and sales reporting.

![Vue](https://img.shields.io/badge/Vue.js-3.5-4FC08D?logo=vue.js&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?logo=vite&logoColor=white)
![Pinia](https://img.shields.io/badge/Pinia-4.x-FFD859?logo=pinia&logoColor=black)
![Vue Router](https://img.shields.io/badge/Vue_Router-5.x-4FC08D?logo=vuedotjs&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-06B6D4?logo=tailwindcss&logoColor=white)
![Axios](https://img.shields.io/badge/Axios-1.18-5A29E4?logo=axios&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES2022-F7DF1E?logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/license-MIT-green)

> Branch: `front` — plain JavaScript (no TypeScript), Composition API with `<script setup>` throughout.

---

## 1. Tech Stack

| Category | Technology | Version | Notes |
|---|---|---|---|
| Core framework | **Vue.js** | `^3.5.39` | Composition API, `<script setup>` SFCs |
| Build tool | **Vite** | `^8.1.1` | Dev server + production bundler |
| Vue/Vite integration | `@vitejs/plugin-vue` | `^6.0.7` | Single-File Component compilation |
| Routing | **Vue Router** | `^5.2.0` | Client-side routing, nested layouts |
| State management | **Pinia** | `^4.0.2` | Global store (`useAuthStore`) |
| HTTP client | **Axios** | `^1.18.1` | API requests + request interceptors |
| Styling | **Tailwind CSS** | `^4.3.3` | Utility-first CSS via `@tailwindcss/vite` |
| Language | **JavaScript (ES Modules)** | — | No TypeScript in this branch |
| Package manager | **npm** | — | `package-lock.json` present (lockfile v3) |
| Module type | `"type": "module"` | — | Native ESM project |

**Notable absence:** no UI component library (e.g., Vuetify/PrimeVue) — all UI is hand-built with Tailwind utility classes and a small set of reusable "Base" components.

---

## 2. Architecture & Component Workflow

```
┌───────────────────────────────────────────────────────────────────────┐
│                              Vue 3 SPA                                  │
│                                                                         │
│  index.html ──▶ src/main.js                                            │
│                    │  createApp(App)                                   │
│                    │  .use(createPinia())                              │
│                    │  .use(router)                                     │
│                    ▼                                                   │
│                 src/App.vue  (<router-view />)                         │
│                    │                                                   │
│                    ▼                                                   │
│         src/router/index.js  (route table + layouts)                   │
│           ├─ AuthLayout   → LoginView                                  │
│           └─ MainLayout   → Dashboard / Customers / Products /         │
│                              Orders / Reports / Settings                │
│                    │                                                   │
│                    ▼                                                   │
│              View components (src/views/**)                           │
│                    │  calls composables for data + logic               │
│                    ▼                                                   │
│         Composables (src/composables/**)                               │
│           useAuthStore, useCustomers, useOrders, useDashboard,         │
│           useReports, useSettings, usePagination                       │
│                    │                                                   │
│                    ▼                                                   │
│              src/services/api.js  (Axios instance)                     │
│           - baseURL: http://localhost:8000/api                         │
│           - request interceptor injects Bearer token                  │
│             from localStorage("auth_token")                            │
│                    │                                                   │
│                    ▼                                                   │
│              Laravel REST API  (external, separate repo/branch)        │
└───────────────────────────────────────────────────────────────────────┘
```

**Data flow, step by step:**
1. **Bootstrap** — `src/main.js` creates the Vue app, installs **Pinia** (state) and **Vue Router** (navigation), then mounts to `#app` in `index.html`.
2. **Routing** — `src/router/index.js` defines two route groups wrapped in layouts:
   - `AuthLayout` → `/` (Login page, unauthenticated)
   - `MainLayout` (sidebar + content area) → `/dashboard`, `/customers`, `/products/create`, `/orders`, `/orders/create`, `/reports`, `/settings`
   - Routes are **lazy-loaded** (`() => import(...)`) for code-splitting
   - Unmatched paths (`/:pathMatch(.*)*`) redirect to `/dashboard`
3. **State management** — `src/stores/auth.js` (Pinia store) holds `user` / `isAuthenticated` state and exposes `login()`, `fetchUser()`, `logout()` actions that call the API and persist the token to `localStorage`.
4. **API layer** — `src/services/api.js` centralizes the Axios instance: JSON headers, and a **request interceptor** that reads `auth_token` from `localStorage` and attaches it as `Authorization: Bearer <token>` on every outgoing request.
5. **Composables (business logic layer)** — Each domain (`customers`, `orders`, `dashboard`, `reports`, `settings`) has a dedicated composable in `src/composables/` that:
   - Owns local reactive state (`ref`/`computed`) for lists, filters, pagination `meta`, loading/error flags
   - Wraps CRUD calls to the API (`fetchX`, `createX`, `updateX`, `deleteX`)
   - Implements debounced search (`setTimeout`-based) and page navigation guards (bounds-checking `goToPage`)
6. **View components** (`src/views/**`) consume composables via destructuring and bind them to templates — keeping views declarative and composables reusable/testable independently of markup.
7. **Reusable UI components** (`src/components/common/**`, `src/components/orders/**`) — `BaseInput`, `BaseSelect`, `BaseButton`, `BasePagination`, `OrderStatusSelect` — accept `v-model` props/emit `update:modelValue`, keeping form/table markup DRY across views.
8. **Layouts** (`src/layouts/**`) — `MainLayout` renders the persistent `Sidebar` + a `<router-view />` outlet; `AuthLayout` centers unauthenticated content.

---

## 3. Key Features & UI Modules

### 🔑 Authentication (`views/auth/LoginView.vue`)
- Email/password login form using `BaseInput` + `BaseButton`
- Delegates to `useAuthStore().login()`, redirects to `/customers` on success, alerts on failure

### 📊 Dashboard (`views/dashboard/DashboardView.vue`)
- Stat cards: total revenue, today's sales, total orders, products, customers
- **Recent Orders** table with inline, editable order-status dropdown (`OrderStatusSelect`)
- **Low Stock** panel highlighting products at/under the configured threshold
- Quick-action buttons to jump to New Customer / New Product / New Order

### 👥 Customers (`views/customers/CustomersList.vue`)
- Server-paginated, searchable customer table (debounced search input)
- Create/Edit via modal dialog with client-side + server-error handling
- Delete with confirmation prompt
- Status badges (active/inactive)

### 📦 Products (`views/products/CreateProduct.vue`)
- Sectioned form: Basic Information + Pricing Information
- Live-computed **Selling Price** preview based on discount type (`none` / `fixed` / `percentage`)
- Submits directly to the Products API endpoint

### 🧾 Orders
- **Orders List** (`views/orders/OrdersList.vue`) — searchable/filterable (by status) paginated table with inline status updates
- **Create Order** (`views/orders/CreateOrder.vue`) — two-pane POS-style UI:
  - Left: searchable/paginated product catalog with "Add" buttons
  - Right: live cart with editable quantities, running Sub Total / Discount / Delivery / Total Amount / Total Paid / Payment Due, and an order note field
  - Client-side guards (customer required, cart non-empty) before submit, with 401/422 error mapping

### 📈 Reports (`views/reports/ReportsView.vue`)
- Date-range filter (`From` / `To`)
- Summary cards: Total Orders, Total Revenue, Total Paid
- **Revenue by Date** table and **Top Products** table (parallel-fetched via `Promise.all`)

### ⚙️ Settings (`views/settings/SettingsView.vue`)
- **Profile** panel — update display name (requires current password confirmation)
- **Change Password** panel — current/new/confirm password with client-side match validation

### 🧩 Shared Component Library
| Component | Purpose |
|---|---|
| `BaseInput.vue` | Labeled text/number/date/password input with prefix & disabled states |
| `BaseSelect.vue` | Labeled `<select>` with placeholder + options prop |
| `BaseButton.vue` | Primary/secondary/danger button variants |
| `BasePagination.vue` | Numbered pagination with prev/next and windowed page numbers |
| `OrderStatusSelect.vue` | Inline status dropdown with confirm dialog + optimistic revert on error |

---

## 4. Local Development Setup

### Prerequisites
- Node.js `>= 20.19` (required by Vite 8 / Vue Router 5 engines)
- npm (lockfile is `package-lock.json`, v3)
- A running instance of the **IDG POS Laravel API** (see backend repo/branch) — default expected at `http://localhost:8000/api`

### 1. Clone the `front` branch
```bash
git clone --branch front --single-branch https://github.com/viseth-sopheach/idg-project.git idg-project-front
cd idg-project-front
```

### 2. Install dependencies
```bash
npm install
```

### 3. Configure the API base URL
The Axios instance is currently defined directly in `src/services/api.js`:
```js
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})
```
If your backend runs elsewhere, update `baseURL` accordingly. To make this environment-driven, add a `.env` file at the project root:
```env
VITE_API_BASE_URL=http://localhost:8000/api
```
and reference it in `src/services/api.js` via `import.meta.env.VITE_API_BASE_URL`. *(Vite only exposes variables prefixed with `VITE_` to client code.)*

### 4. Run the development server
```bash
npm run dev
```
This starts Vite's dev server (default `http://localhost:5173`) with hot module replacement.

### 5. Build for production
```bash
npm run build
```
Outputs static assets to `dist/`.

### 6. Preview the production build locally
```bash
npm run preview
```

### Authentication note
On login, the app stores the Sanctum token in `localStorage` under the key `auth_token`. Ensure the backend's CORS configuration allows the frontend's dev origin (`http://localhost:5173`) and that Sanctum's stateful domains/API guard are configured for token-based (not cookie-based) auth if `baseURL` points cross-origin.