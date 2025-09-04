# Task Management System (Laravel + Inertia.js + Vue 3 + Docker)

A full-stack demo project featuring Laravel (Controllers, Services, Requests, Resources, Observers), Inertia.js, Vue 3 (Composition API), Pinia, and Tailwind CSS.  
It includes a Docker environment (Nginx + PHP + PostgreSQL) for local development.

## Features

### Tasks
- CRUD for tasks: `title`, `description`, `status` (`pending|in_progress|completed`), `priority` (`low|medium|high`), `due_date`, `category_id`.
- Filtering and search: status, priority, category, date range, free text.
- Sorting: `created_at`, `due_date`, `priority`, `status`, `title`.
- Soft delete & restore support.

### Categories
- CRUD for categories with max 2 levels (parent → child).
- Statistics page with task counts by status and overall totals.
- Optimized queries with `withCount()` and optional caching.
- Cache invalidation via Observers on task/category changes.

### UX
- Unified **TaskForm** and **CategoryForm** (create & edit).
- Inline field errors with FormRequest validation.
- Loading/disabled state on form submit.
- Toast notifications for success actions.
- Tailwind-based layout with navigation highlighting.
- Additional UI component: `StatCard.vue` for displaying compact statistics.

## Architecture
- Controller (HTTP layer) → Service (domain logic) → Model (Eloquent + scopes).
- `TaskQueryService` centralizes filtering, sorting, and pagination.
- `CategoryStatsCache` handles category statistics caching with TTL and invalidation.
- Pinia stores for filters (`useTaskFiltersStore`) and statistics (`useCategoryStatsStore`).
- Clean commit history following Conventional Commits.

## Tech Stack
- **Backend:** Laravel 10+, PHP 8.2, PostgreSQL  
- **Frontend:** Inertia.js, Vue 3 (Composition API), Pinia, Tailwind CSS, Ziggy  
- **Infrastructure:** Docker (nginx, php-fpm, postgres)  
- **Cache:** File (dev) / Redis (recommended in production)  
- **Tooling:** Vite, Laravel Pint, Pest (tests planned)  

## Getting Started

### With Docker
```bash
docker compose up -d --build
```

- App → http://localhost:8080  
- Nginx config → `docker/nginx/default.conf`  
- PHP Dockerfile → `docker/php/Dockerfile`  
- PostgreSQL init script → `docker/postgres/init.sql`  

### Without Docker
```bash
composer install
cp .env.example .env
php artisan key:generate

npm install
npm run dev

php artisan migrate
php artisan serve
```

## Routing
```php
// Tasks
Route::resource('tasks', TaskController::class)->whereNumber('task');
Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');

// Categories
Route::get('categories/statistics', [CategoryController::class, 'statistics'])->name('categories.statistics');
Route::resource('categories', CategoryController::class)->whereNumber('category');
Route::post('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
```

## State Management

### Task Filters (`useTaskFiltersStore`)
- Persists filters (status, priority, category, date range, search, sorting).
- Saves to `localStorage` to survive page reloads.

### Category Statistics (`useCategoryStatsStore`)
- Stores statistics & totals.
- Can fetch JSON via `/categories/statistics`.
- Integrated into `Statistics.vue` with Refresh button.

## Caching & Observers
- `CategoryService::getStatistics()` uses `CategoryStatsCache::remember()`.
- Observers (`TaskObserver`, `CategoryObserver`) call `CategoryStatsCache::forget()` on create/update/delete/restore.
- Default TTL: 5 minutes.
- Works with `CACHE_DRIVER=file` (default for dev). Use `CACHE_DRIVER=redis` in production for better performance.

## Not Implemented Yet

### Laravel Echo + WebSockets
Planned for realtime updates. Suggested approach: broadcast `TaskCreated|TaskUpdated|TaskDeleted|TaskRestored` events, then refresh stats via `useCategoryStatsStore().fetch()` on the frontend.

### Tests (Pest)
Recommended coverage:
- **Feature:** Task/Category controllers (CRUD, filters, restore).  
- **Unit:** `TaskQueryService` (filters/sorting), `CategoryService` (rules/statistics).  
- **Request:** `TaskRequest` & `CategoryRequest`.  
- **Observer:** cache invalidation.  

## Commit Convention
- `feat:` new feature  
- `fix:` bug fix  
- `refactor:` code change without feature/bug impact  
- `chore:` tooling/deps/config  
- `docs:` documentation  

## Project Structure
```
docker/
  nginx/default.conf
  php/Dockerfile
  postgres/init.sql
src/
  app/
    Http/
      Controllers/ (TaskController, CategoryController)
      Requests/ (TaskRequest, CategoryRequest)
      Resources/ (TaskResource, CategoryResource)
      Middleware/HandleInertiaRequests.php
    Models/ (Task, Category)
    Services/ (TaskService, TaskQueryService, CategoryService)
    Observers/ (TaskObserver, CategoryObserver)
    Support/CategoryStatsCache.php
    Providers/EventServiceProvider.php
  database/migrations/
  resources/js/
    app.js
    Layouts/AppLayout.vue
    Components/ (Toasts.vue, StatCard.vue)
    Pages/Tasks/ (Index.vue, TaskForm.vue, Show.vue)
    Pages/Categories/ (Index.vue, CategoryForm.vue, Show.vue, Statistics.vue)
    stores/ (useTaskFiltersStore.js, useCategoryStatsStore.js)
  routes/web.php
```

## License
MIT — free to use for learning or as a project starter.

---

⚠️ Missing parts: Pest tests and Laravel Echo/WebSockets were not implemented yet due to time constraints. These would be the next steps to make the project production-ready.
