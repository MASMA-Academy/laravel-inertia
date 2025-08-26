# Modern SPA Architecture

## Course Overview

This module covers the fundamental concepts of modern Single Page Application
(SPA) architecture, comparing traditional approaches with modern solutions, and
introducing the Inertia.js pattern for Laravel applications.

---

## Traditional SSR vs SPA Approaches

### Server-Side Rendering (SSR)

**What is SSR?** Server-Side Rendering generates the complete HTML page on the
server before sending it to the client. Each page request results in a full page
reload from the server.

**Traditional SSR Characteristics:**

- Full page reloads on navigation
- Server generates complete HTML
- SEO-friendly by default
- Slower perceived performance
- Higher server load
- Limited interactivity

**Laravel Blade Example:**

```php
// Traditional Laravel Blade approach
Route::get('/users', function () {
    $users = User::paginate(15);
    return view('users.index', compact('users'));
});
```

```blade
{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Users</h1>
    <div class="users-list">
        @foreach($users as $user)
            <div class="user-item">
                <h3>{{ $user->name }}</h3>
                <p>{{ $user->email }}</p>
            </div>
        @endforeach
    </div>
    
    {{ $users->links() }}
</div>
@endsection
```

**Pros of Traditional SSR:**

- SEO-friendly
- Fast initial page load
- Works without JavaScript
- Simple deployment
- Good for content-heavy sites

**Cons of Traditional SSR:**

- Full page reloads
- Poor user experience
- Higher bandwidth usage
- Limited interactivity
- Server round-trips for every action

---

### Single Page Applications (SPAs)

**What is a SPA?** Single Page Applications load a single HTML page and
dynamically update content as users interact with the app, without full page
reloads.

**Traditional SPA Characteristics:**

- Single HTML page
- JavaScript-driven navigation
- API-based data fetching
- Client-side routing
- Rich interactivity
- Fast navigation between views

**Traditional SPA Architecture:**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Laravel API   │◄──►│   Vue.js App    │◄──►│   User Browser  │
│   (Backend)     │    │   (Frontend)    │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**Traditional SPA Example:**

```javascript
// Vue Router setup
import { createRouter, createWebHistory } from "vue-router";
import UsersList from "./components/UsersList.vue";
import UserDetail from "./components/UserDetail.vue";

const routes = [
    { path: "/users", component: UsersList },
    { path: "/users/:id", component: UserDetail },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// API calls from components
async function fetchUsers() {
    const response = await fetch("/api/users");
    return response.json();
}
```

**Pros of Traditional SPAs:**

- Fast navigation
- Rich user experience
- Reduced server load
- Offline capabilities
- Better performance

**Cons of Traditional SPAs:**

- SEO challenges
- Initial load time
- JavaScript dependency
- Complex state management
- Development complexity

---

## Client-Side Rendering Challenges

### 1. SEO and Search Engine Optimization

**The Problem:** Search engines primarily crawl HTML content. Traditional SPAs
render content via JavaScript, which can be problematic for SEO.

**Challenges:**

- Search engines may not execute JavaScript
- Dynamic content not immediately visible
- Meta tags and structured data management
- Social media sharing issues

**Solutions:**

- Server-Side Rendering (SSR)
- Pre-rendering services
- Dynamic rendering
- Meta tag management

### 2. Initial Load Performance

**The Problem:** SPAs must download all JavaScript before rendering, causing
initial load delays.

**Challenges:**

- Bundle size management
- Code splitting complexity
- Critical rendering path
- Time to Interactive (TTI)

**Solutions:**

- Code splitting and lazy loading
- Critical CSS inlining
- Progressive enhancement
- Service worker caching

### 3. State Management Complexity

**The Problem:** Managing application state across multiple views and components
becomes complex.

**Challenges:**

- Data synchronization
- Cache invalidation
- Offline state management
- Error handling

**Solutions:**

- Centralized state management (Pinia/Vuex)
- Optimistic updates
- Error boundaries
- State persistence

### 4. Authentication and Security

**The Problem:** Managing authentication state and protecting routes on the
client side.

**Challenges:**

- Token management
- Route protection
- CSRF protection
- Session handling

**Solutions:**

- Laravel Sanctum integration
- Route guards
- Token refresh strategies
- Secure storage

---

## Introduction to the Inertia.js Pattern

### What is Inertia.js?

Inertia.js is a library that allows you to create modern SPAs using classic
server-side routing and controllers, without building an API. It bridges the gap
between traditional server-rendered applications and modern SPAs.

**Core Concept:** Instead of building an API and a separate SPA, Inertia.js
allows you to use your existing Laravel backend with modern frontend frameworks
like Vue.js, while maintaining the benefits of both approaches.

### How Inertia.js Works

**The Flow:**

1. User clicks a link or submits a form
2. Inertia.js intercepts the request
3. Request goes to Laravel backend
4. Laravel processes the request and returns data
5. Inertia.js updates the page without a full reload
6. Vue.js components re-render with new data

**Key Benefits:**

- No API needed
- Server-side routing
- Full Laravel ecosystem
- Modern frontend experience
- SEO-friendly
- Authentication built-in

### Inertia.js vs Traditional Approaches

| Aspect             | Traditional SSR   | Traditional SPA       | Inertia.js      |
| ------------------ | ----------------- | --------------------- | --------------- |
| **Routing**        | Server-side       | Client-side           | Server-side     |
| **Data Fetching**  | Server-rendered   | API calls             | Server-rendered |
| **Navigation**     | Full page reloads | JavaScript routing    | Partial updates |
| **SEO**            | Excellent         | Poor                  | Good            |
| **Development**    | Simple            | Complex               | Moderate        |
| **Performance**    | Slower navigation | Fast navigation       | Fast navigation |
| **Authentication** | Built-in          | Custom implementation | Built-in        |

---

## Setting Up a Laravel Project with Inertia.js

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- npm or yarn
- Laravel 12

### Prerequisites

**Install Laravel Installer (if not already installed):**

```bash
composer global require laravel/installer
```

## Installation Steps

**1. Create New Laravel Project with Starter Kit:**

```bash
# Create new Laravel project with Breeze already included
laravel new my-inertia-app --breeze --vue

# Navigate to project directory
cd my-inertia-app
```

**3. Install Inertia.js Client-Side Adapter:**

```bash
npm install @inertiajs/vue3
```

**4. Configure Inertia.js (Already included with Starter Kit):**

The Laravel starter kit with `--breeze --vue` flags automatically provides:

- Inertia.js middleware and configuration
- Authentication scaffolding (login, register, password reset)
- Basic layout components
- Vue.js setup with Inertia.js
- Tailwind CSS configuration
- Vite build configuration

**5. Verify Middleware Registration:**

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\HandleInertiaRequests::class,
    ],
];
```

**6. Root Template (Already included with Starter Kit):**

The root template `resources/views/app.blade.php` is automatically created with
the proper Inertia.js setup when using the Laravel starter kit.

```php
// resources/views/app.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.ts'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @inertia
    </div>
</body>
</html>
```

**7. Vite Configuration (Already included with Starter Kit):**

The Vite configuration is automatically set up when using the Laravel starter
kit with Vue.js.

```typescript
// vite.config.ts
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.ts"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
});
```

**8. Vue App Entry Point (Already included with Starter Kit):**

The Vue app entry point `resources/js/app.ts` is automatically created with the
proper Inertia.js setup when using the Laravel starter kit.

```typescript
// resources/js/app.ts
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

**9. First Page (Already included with Starter Kit):**

The welcome page and authentication pages are automatically created when using
the Laravel starter kit.

```vue
<!-- resources/js/Pages/Welcome.vue -->
<template>
    <div>
        <h1>Welcome to Laravel with Inertia.js!</h1>
        <p>This is a modern SPA built with Laravel, Inertia.js, and Vue.js</p>
    </div>
</template>

<script setup lang="ts">
// Component logic here
</script>
```

**10. Routes (Already included with Starter Kit):**

The basic routes including authentication are automatically set up when using
the Laravel starter kit.

```php
// routes/web.php
Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Authentication routes are automatically included
require __DIR__.'/auth.php';
```

**11. Final Setup Steps:**

```bash
# Run database migrations
php artisan migrate

# Install and build frontend dependencies
npm install
npm run build

# Start development servers
php artisan serve
npm run dev
```

### Project Structure

```
my-inertia-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
├── resources/
│   ├── js/
│   │   ├── Pages/          # Page components
│   │   ├── Components/     # Reusable components
│   │   ├── Layouts/        # Layout components
│   │   └── app.ts         # Main entry point
│   ├── css/
│   └── views/
├── routes/
└── vite.config.ts
```

---

## Key Concepts Summary

### 1. **Architecture Evolution**

- Traditional SSR → Traditional SPA → Inertia.js
- Each approach has trade-offs
- Inertia.js provides the best of both worlds

### 2. **Client-Side Challenges**

- SEO limitations
- Performance concerns
- State management complexity
- Security considerations

### 3. **Inertia.js Benefits**

- Server-side routing with SPA experience
- No API development required
- Full Laravel ecosystem access
- Modern frontend development

### 4. **Setup Process**

- Install server and client adapters
- Configure middleware
- Set up Vite and Vue
- Create root template
- Build page components

---

## Next Steps

After completing this module, you should:

1. **Understand the differences** between traditional SSR, SPAs, and Inertia.js
2. **Recognize the challenges** of client-side rendering
3. **Appreciate the benefits** of the Inertia.js approach
4. **Successfully set up** a Laravel project with Inertia.js
5. **Be ready to move** to the next module: Inertia.js Core Concepts

---

## Additional Resources

- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel 12 Documentation](https://laravel.com/docs)
- [Vue.js 3 Documentation](https://vuejs.org/)
- [Vite Documentation](https://vitejs.dev/)
- [Laravel Vite Plugin](https://laravel.com/docs/vite)
