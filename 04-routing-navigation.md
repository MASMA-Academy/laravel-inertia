# Routing & Navigation

## Course Overview

This module covers routing and navigation in Inertia.js applications, including
Laravel and Inertia routing integration, named routes, route parameters,
navigation events, and page transition techniques.

---

## Laravel and Inertia Routing Integration

### Understanding the Routing System

**How Inertia.js Routing Works:** Inertia.js uses Laravel's server-side routing
system. When a user navigates, Inertia.js intercepts the request, sends it to
Laravel, and then updates the page content without a full reload.

**Key Concepts:**

- Server-side routing with Laravel
- Client-side navigation with Inertia.js
- Route parameters and query strings
- Named routes and route helpers

### Basic Route Definition

**1. Web Routes:**

```php
// routes/web.php
<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Basic routes
Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});

// Controller-based routes
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
```

**2. Route Groups:**

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {
        Route::get('/users', [UserController::class, 'adminIndex'])->name('users.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
    
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    });
});
```

**3. Resource Routes:**

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('posts', PostController::class);
    Route::resource('categories', CategoryController::class);
});

// Custom resource routes
Route::resource('users', UserController::class)->except(['destroy']);
Route::resource('posts', PostController::class)->only(['index', 'show']);
Route::resource('categories', CategoryController::class)->names([
    'index' => 'categories.list',
    'show' => 'categories.view',
]);
```

---

## Named Routes and Route Parameters

### Using Named Routes

**1. Server-Side Route Names:**

```php
// routes/web.php
Route::get('/users/{user}/profile', [UserController::class, 'profile'])
    ->name('users.profile')
    ->where('user', '[0-9]+');

Route::get('/posts/{post}/comments', [PostController::class, 'comments'])
    ->name('posts.comments')
    ->where('post', '[0-9]+');
```

**2. Client-Side Route Usage:**

```vue
<template>
    <div>
        <Link :href="route('users.profile', user.id)">
            View Profile
        </Link>
        
        <Link :href="route('posts.comments', post.id)">
            View Comments
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const { user, post } = usePage().props
</script>
```

### Route Parameters and Query Strings

**1. Route Parameters:**

```php
// routes/web.php
Route::get('/users/{user}/orders/{order}', [OrderController::class, 'show'])
    ->name('orders.show');
```

```vue
<template>
    <div>
        <Link :href="route('orders.show', [user.id, order.id])">
            View Order Details
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const { user, order } = usePage().props
</script>
```

**2. Query Parameters:**

```vue
<template>
    <div>
        <Link 
            :href="route('users.index')"
            :data="{ 
                search: searchQuery, 
                page: currentPage,
                sort: sortBy,
                filter: activeFilter 
            }"
        >
            Search Users
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const searchQuery = ref('')
const currentPage = ref(1)
const sortBy = ref('name')
const activeFilter = ref('active')
</script>
```

**3. Dynamic Route Building:**

```vue
<template>
    <div>
        <Link 
            :href="buildUrl('/users', { 
                search: searchQuery, 
                page: currentPage 
            })"
        >
            Search
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const searchQuery = ref('')
const currentPage = ref(1)

const buildUrl = (baseUrl: string, params: Record<string, any>) => {
    const url = new URL(baseUrl, window.location.origin)
    Object.entries(params).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            url.searchParams.append(key, String(value))
        }
    })
    return url.pathname + url.search
}
</script>
```

---

## Handling Navigation Events

### Understanding Navigation Events

**Navigation Event Types:**

- `navigate`: Navigation starts
- `finish`: Navigation completes
- `error`: Navigation fails
- `progress`: Navigation progress updates

### Using Navigation Events

**1. Global Navigation Events:**

```typescript
// resources/js/app.ts
import { createApp, h } from "vue";
import { createInertiaApp, router } from "@inertiajs/vue3";

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Global navigation events
        router.on("navigate", () => {
            console.log("Navigation started");
            // Show loading indicator
        });

        router.on("finish", () => {
            console.log("Navigation completed");
            // Hide loading indicator
        });

        router.on("error", (error) => {
            console.error("Navigation failed:", error);
            // Show error message
        });

        app.use(plugin);
        app.mount(el);
    },
});
```

**2. Component-Level Navigation Events:**

```vue
<template>
    <div>
        <div v-if="isNavigating" class="loading-overlay">
            Loading...
        </div>
        
        <Link 
            href="/users"
            @click="handleNavigationStart"
        >
            Users
        </Link>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const isNavigating = ref(false)

const handleNavigationStart = () => {
    isNavigating.value = true
}

const handleNavigationFinish = () => {
    isNavigating.value = false
}

onMounted(() => {
    router.on('navigate', handleNavigationStart)
    router.on('finish', handleNavigationFinish)
})

onUnmounted(() => {
    router.off('navigate', handleNavigationStart)
    router.off('finish', handleNavigationFinish)
})
</script>
```

**3. Navigation Progress:**

```vue
<template>
    <div>
        <div v-if="navigationProgress > 0" class="progress-bar">
            <div 
                class="progress-fill"
                :style="{ width: `${navigationProgress}%` }"
            ></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const navigationProgress = ref(0)

const handleProgress = (progress: number) => {
    navigationProgress.value = progress
}

onMounted(() => {
    router.on('progress', handleProgress)
})

onUnmounted(() => {
    router.off('progress', handleProgress)
})
</script>

<style scoped>
.progress-bar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: #e5e7eb;
    z-index: 9999;
}

.progress-fill {
    height: 100%;
    background: #3b82f6;
    transition: width 0.3s ease;
}
</style>
```

---

## Page Transition Techniques

### Understanding Page Transitions

**What are Page Transitions?** Page transitions provide visual feedback when
navigating between pages, improving the user experience and making the
application feel more responsive.

**Transition Types:**

- Fade transitions
- Slide transitions
- Scale transitions
- Custom transitions

### Implementing Page Transitions

**1. Basic Fade Transition:**

```vue
<!-- resources/js/Pages/Users/Index.vue -->
<template>
    <Transition name="fade" mode="out-in">
        <div class="users-page">
            <h1>Users</h1>
            <!-- Page content -->
        </div>
    </Transition>
</template>

<script setup lang="ts">
// Component logic
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
```

**2. Slide Transition:**

```vue
<!-- resources/js/Pages/Users/Show.vue -->
<template>
    <Transition name="slide" mode="out-in">
        <div class="user-detail-page">
            <h1>{{ user.name }}</h1>
            <!-- User details -->
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { user } = usePage().props
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from {
    transform: translateX(100%);
}

.slide-leave-to {
    transform: translateX(-100%);
}
</style>
```

**3. Advanced Transition with Loading States:**

```vue
<!-- resources/js/Pages/Users/Index.vue -->
<template>
    <Transition name="page" mode="out-in">
        <div v-if="!isLoading" class="users-page">
            <h1>Users</h1>
            <div class="users-list">
                <div v-for="user in users" :key="user.id" class="user-card">
                    <h3>{{ user.name }}</h3>
                    <p>{{ user.email }}</p>
                </div>
            </div>
        </div>
        <div v-else class="loading-page">
            <div class="spinner"></div>
            <p>Loading users...</p>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const isLoading = ref(true)
const { users } = usePage().props

onMounted(() => {
    // Simulate loading delay for smooth transition
    setTimeout(() => {
        isLoading.value = false
    }, 300)
})
</script>

<style scoped>
.page-enter-active,
.page-leave-active {
    transition: all 0.4s ease;
}

.page-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.page-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.loading-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e5e7eb;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
```

### Transition with Route Guards

**1. Route Guard Implementation:**

```vue
<template>
    <Transition name="fade" mode="out-in">
        <div v-if="canAccess" class="protected-page">
            <h1>Admin Dashboard</h1>
            <!-- Admin content -->
        </div>
        <div v-else class="access-denied">
            <h1>Access Denied</h1>
            <p>You don't have permission to view this page.</p>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const { auth } = usePage().props

const canAccess = computed(() => {
    return auth.user?.role === 'admin'
})
</script>
```

---

## Advanced Navigation Patterns

### Programmatic Navigation

**1. Using the Router:**

```vue
<template>
    <div>
        <button @click="navigateToUsers">Go to Users</button>
        <button @click="navigateToUserProfile">View Profile</button>
        <button @click="goBack">Go Back</button>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const { user } = usePage().props

const navigateToUsers = () => {
    router.get('/users')
}

const navigateToUserProfile = () => {
    router.get(`/users/${user.id}/profile`)
}

const goBack = () => {
    router.visit(window.history.back())
}
</script>
```

**2. Navigation with Data:**

```vue
<template>
    <div>
        <button @click="searchUsers">Search Users</button>
        <button @click="filterUsers">Filter Users</button>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const searchQuery = ref('')
const filterStatus = ref('active')

const searchUsers = () => {
    router.get('/users', { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const filterUsers = () => {
    router.get('/users', { status: filterStatus.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>
```

### Navigation State Management

**1. Preserving State:**

```vue
<template>
    <div>
        <input 
            v-model="searchQuery" 
            placeholder="Search users..."
            @input="handleSearch"
        />
        
        <div class="filters">
            <select v-model="selectedRole" @change="handleFilter">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'

const page = usePage()
const { filters } = page.props

const searchQuery = ref(filters.search || '')
const selectedRole = ref(filters.role || '')

const handleSearch = debounce(() => {
    router.get('/users', { 
        search: searchQuery.value,
        role: selectedRole.value 
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}, 300)

const handleFilter = () => {
    router.get('/users', { 
        search: searchQuery.value,
        role: selectedRole.value 
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

// Sync with URL changes
watch(() => page.url, (url) => {
    const params = new URLSearchParams(url.split('?')[1])
    searchQuery.value = params.get('search') || ''
    selectedRole.value = params.get('role') || ''
})
</script>
```

---

## Key Concepts Summary

1. **Routing Integration**: Laravel handles server-side routing, Inertia.js
   manages client-side navigation
2. **Named Routes**: Use route names for maintainable navigation
3. **Route Parameters**: Pass dynamic data through URLs
4. **Navigation Events**: Listen for navigation lifecycle events
5. **Page Transitions**: Provide visual feedback during navigation
6. **State Preservation**: Maintain user input and scroll position

---

## Next Steps

After completing this module, you should:

1. Understand how Laravel and Inertia.js routing work together
2. Use named routes and route parameters effectively
3. Handle navigation events and implement loading states
4. Create smooth page transitions
5. Implement advanced navigation patterns
6. Be ready for the next module: Hands-on Lab

---

## Additional Resources

- [Laravel Routing Documentation](https://laravel.com/docs/routing)
- [Inertia.js Navigation](https://inertiajs.com/navigation)
- [Vue.js Transitions](https://vuejs.org/guide/built-ins/transition.html)
- [Laravel Route Model Binding](https://laravel.com/docs/routing#route-model-binding)
