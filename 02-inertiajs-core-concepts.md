# Inertia.js Core Concepts

## Course Overview

This module dives deep into the fundamental concepts of Inertia.js, exploring
how it bridges the gap between server-side and client-side development. You'll
learn about server-side setup, client-side configuration, the Link component,
and data sharing mechanisms.

---

## Server-Side Setup with Laravel Adapter

### Understanding the Inertia.js Middleware

**What is the Middleware?** The Inertia.js middleware (`HandleInertiaRequests`)
intercepts all requests and determines whether to return a full page response or
an Inertia.js response. It also handles shared data and asset versioning.

**Middleware Responsibilities:**

- Detect Inertia.js requests
- Prepare shared data
- Handle asset versioning
- Manage CSRF tokens
- Process response types

### Installing and Configuring the Middleware

**1. Publish the Middleware:**

```bash
php artisan inertia:middleware
```

**2. Register in Kernel:**

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\HandleInertiaRequests::class,
    ],
];
```

**3. Customize Middleware (Optional):**

```php
// app/Http/Middleware/HandleInertiaRequests.php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'app' => [
                'name' => config('app.name'),
                'environment' => config('app.env'),
            ],
        ]);
    }
}
```

### Understanding Asset Versioning

**Why Asset Versioning?** Asset versioning ensures that users receive the latest
CSS and JavaScript files when you deploy updates, preventing caching issues.

**Versioning Strategies:**

```php
// app/Http/Middleware/HandleInertiaRequests.php

// Option 1: File-based versioning
public function version(Request $request): ?string
{
    return filemtime(public_path('build/manifest.json'));
}

// Option 2: Git-based versioning
public function version(Request $request): ?string
{
    return trim(exec('git log --pretty="%h" -n1 HEAD'));
}

// Option 3: Environment-based versioning
public function version(Request $request): ?string
{
    return config('app.version', '1.0.0');
}

// Option 4: Manifest-based versioning
public function version(Request $request): ?string
{
    $manifestPath = public_path('build/manifest.json');
    
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        return md5(json_encode($manifest));
    }
    
    return null;
}
```

### Shared Data Configuration

**Global Shared Data:** Data that's available on every page of your application.

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        // Authentication data
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'permissions' => $request->user()->permissions,
            ] : null,
            'check' => fn () => $request->user() !== null,
        ],
        
        // Flash messages
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
            'warning' => fn () => $request->session()->get('warning'),
            'info' => fn () => $request->session()->get('info'),
        ],
        
        // Application settings
        'app' => [
            'name' => config('app.name'),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'url' => config('app.url'),
        ],
        
        // CSRF token
        'csrf_token' => fn () => csrf_token(),
        
        // Locale information
        'locale' => [
            'current' => app()->getLocale(),
            'fallback' => config('app.fallback_locale'),
        ],
    ]);
}
```

**Conditional Shared Data:** Data that's only shared under certain conditions.

```php
public function share(Request $request): array
{
    $shared = parent::share($request);
    
    // Only share admin data for admin users
    if ($request->user() && $request->user()->isAdmin()) {
        $shared['admin'] = [
            'stats' => [
                'total_users' => User::count(),
                'total_orders' => Order::count(),
                'revenue' => Order::sum('total'),
            ],
            'notifications' => Notification::unread()->get(),
        ];
    }
    
    // Share user preferences only when authenticated
    if ($request->user()) {
        $shared['user_preferences'] = [
            'theme' => $request->user()->preferences->theme ?? 'light',
            'language' => $request->user()->preferences->language ?? 'en',
            'notifications' => $request->user()->preferences->notifications ?? true,
        ];
    }
    
    return $shared;
}
```

---

## Client-Side Setup with Vue.js

### Understanding the Client-Side Architecture

**How Inertia.js Works on the Client:**

1. Intercepts navigation events (clicks, form submissions)
2. Sends AJAX requests to the server
3. Receives page data and updates the DOM
4. Manages browser history and state
5. Handles loading states and errors

### Setting Up the Vue.js Application

**1. Main Entry Point:**

```typescript
// resources/js/app.ts
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

// Import global styles
import "../css/app.css";

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue); // For Ziggy route helpers

        // Global error handler
        app.config.errorHandler = (error, instance, info) => {
            console.error("Vue Error:", error, info);
        };

        app.mount(el);
    },
});
```

**2. Page Resolution Strategy:**

```typescript
// Custom page resolver for nested pages
resolve: ((name) => {
    const pages = import.meta.glob("./Pages/**/*.vue");
    const page = pages[`./Pages/${name}.vue`];

    if (page) {
        return page();
    }

    // Handle nested pages (e.g., Admin/Users/Index)
    const nestedPage = pages[`./Pages/${name.replace(/\./g, "/")}.vue`];
    if (nestedPage) {
        return nestedPage();
    }

    throw new Error(`Page ${name} not found.`);
});
```

**3. TypeScript Configuration:**

```typescript
// resources/js/types/inertia.d.ts
declare module "@inertiajs/vue3" {
    interface PageProps {
        auth: {
            user: {
                id: number;
                name: string;
                email: string;
                role: string;
                permissions: string[];
            } | null;
            check: () => boolean;
        };
        flash: {
            success?: string;
            error?: string;
            warning?: string;
            info?: string;
        };
        app: {
            name: string;
            environment: string;
            debug: boolean;
            url: string;
        };
        [key: string]: any;
    }
}
```

### Global Component Registration

**1. Auto-import Components:**

```typescript
// resources/js/app.ts
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Auto-import global components
        const components = import.meta.glob("./Components/**/*.vue");
        Object.entries(components).forEach(([path, component]) => {
            const componentName = path.split("/").pop()?.replace(".vue", "");
            if (componentName) {
                app.component(componentName, component);
            }
        });

        app.use(plugin);
        app.mount(el);
    },
});
```

**2. Manual Component Registration:**

```typescript
// resources/js/app.ts
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

// Import global components
import AppLayout from "./Layouts/AppLayout.vue";
import Button from "./Components/Button.vue";
import Modal from "./Components/Modal.vue";
import FormInput from "./Components/FormInput.vue";

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Register global components
        app.component("AppLayout", AppLayout);
        app.component("Button", Button);
        app.component("Modal", Modal);
        app.component("FormInput", FormInput);

        app.use(plugin);
        app.mount(el);
    },
});
```

---

## The Inertia.js Link Component

### Understanding the Link Component

**What is the Link Component?** The `Link` component is Inertia.js's equivalent
to HTML anchor tags (`<a>`) and Vue Router's `<router-link>`. It handles
navigation between pages without full page reloads.

**Key Features:**

- Client-side navigation
- Preserves scroll position
- Handles loading states
- Manages browser history
- Supports query parameters

### Basic Link Usage

**1. Simple Navigation:**

```vue
<template>
    <div>
        <Link href="/dashboard">Dashboard</Link>
        <Link href="/users">Users</Link>
        <Link href="/settings">Settings</Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>
```

**2. Link with Query Parameters:**

```vue
<template>
    <div>
        <Link 
            href="/users" 
            :data="{ 
                search: searchQuery, 
                page: currentPage,
                sort: sortBy 
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
</script>
```

**3. Link with Method:**

```vue
<template>
    <div>
        <Link 
            href="/users/1" 
            method="delete" 
            as="button"
            @click="confirmDelete"
        >
            Delete User
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this user?')) {
        // Link will handle the deletion
    }
}
</script>
```

### Advanced Link Features

**1. Preserving State:**

```vue
<template>
    <div>
        <Link 
            href="/users" 
            preserve-state
            preserve-scroll
        >
            Refresh Users
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>
```

**2. Link with Loading States:**

```vue
<template>
    <div>
        <Link 
            href="/users" 
            :class="{ 'opacity-50': $page.component === 'Users' }"
        >
            <span v-if="$page.component === 'Users'">Loading...</span>
            <span v-else>Users</span>
        </Link>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const $page = usePage()
</script>
```

**3. Conditional Link Rendering:**

```vue
<template>
    <div>
        <Link 
            v-if="canEditUsers"
            href="/users/create" 
            class="btn btn-primary"
        >
            Create User
        </Link>
        
        <span v-else class="text-gray-400">
            Create User (No Permission)
        </span>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const $page = usePage()

const canEditUsers = computed(() => {
    return $page.props.auth.user?.permissions?.includes('users.edit')
})
</script>
```

### Link Styling and Accessibility

**1. Styling with Tailwind CSS:**

```vue
<template>
    <Link 
        href="/dashboard"
        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
    >
        Dashboard
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>
```

**2. Accessibility Features:**

```vue
<template>
    <Link 
        href="/users"
        aria-label="View all users"
        role="button"
        tabindex="0"
        @keydown.enter="$event.target.click()"
        @keydown.space.prevent="$event.target.click()"
    >
        Users
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>
```

---

## Sharing Data Between Server and Client

### Understanding Data Flow

**Data Flow in Inertia.js:**

1. **Server-side**: Controllers prepare data using `Inertia::render()`
2. **Middleware**: Shared data is merged with page-specific data
3. **Client-side**: Vue components receive data as props
4. **Reactivity**: Data changes trigger component updates

### Server-Side Data Preparation

**1. Basic Page Data:**

```php
// app/Http/Controllers/UserController.php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => request()->only(['search', 'role', 'status']),
            'stats' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
            ],
        ]);
    }
}
```

**2. Data Transformation:**

```php
// app/Http/Controllers/UserController.php
public function show(User $user)
{
    return Inertia::render('Users/Show', [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->name,
            'permissions' => $user->role->permissions->pluck('name'),
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
        ],
        'related_data' => [
            'orders' => $user->orders()->latest()->take(5)->get(),
            'activities' => $user->activities()->latest()->take(10)->get(),
        ],
    ]);
}
```

**3. Conditional Data:**

```php
// app/Http/Controllers/UserController.php
public function edit(User $user)
{
    $data = [
        'user' => $user->only(['id', 'name', 'email', 'role_id']),
        'roles' => Role::all(['id', 'name']),
    ];

    // Only include sensitive data for admins
    if (auth()->user()->isAdmin()) {
        $data['user']['permissions'] = $user->permissions;
        $data['audit_log'] = $user->auditLog()->latest()->take(20)->get();
    }

    return Inertia::render('Users/Edit', $data);
}
```

### Client-Side Data Access

**1. Accessing Page Props:**

```vue
<template>
    <div>
        <h1>Users ({{ users.total }})</h1>
        
        <div class="stats">
            <div>Total: {{ stats.total }}</div>
            <div>Active: {{ stats.active }}</div>
            <div>Inactive: {{ stats.inactive }}</div>
        </div>
        
        <div class="users-list">
            <div v-for="user in users.data" :key="user.id" class="user-item">
                <h3>{{ user.name }}</h3>
                <p>{{ user.email }}</p>
                <span>{{ user.role.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { users, stats } = usePage().props
</script>
```

**2. Reactive Data Updates:**

```vue
<template>
    <div>
        <div v-if="flash.success" class="alert alert-success">
            {{ flash.success }}
        </div>
        
        <div v-if="flash.error" class="alert alert-error">
            {{ flash.error }}
        </div>
        
        <UserForm @user-created="handleUserCreated" />
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
import UserForm from '@/Components/UserForm.vue'

const page = usePage()

// Watch for flash message changes
watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        // Auto-hide success message after 5 seconds
        setTimeout(() => {
            // Clear flash message (you'd need to implement this)
        }, 5000)
    }
}, { deep: true })

const handleUserCreated = () => {
    // User was created successfully
    // Flash message will be automatically displayed
}
</script>
```

### Advanced Data Patterns

**1. Computed Properties with Page Data:**

```vue
<template>
    <div>
        <div class="filters">
            <input 
                v-model="searchQuery" 
                placeholder="Search users..."
                @input="updateFilters"
            />
            
            <select v-model="selectedRole" @change="updateFilters">
                <option value="">All Roles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                </option>
            </select>
        </div>
        
        <div class="results">
            Showing {{ filteredUsers.length }} of {{ users.total }} users
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()
const { users, roles, filters } = page.props

const searchQuery = ref(filters.search || '')
const selectedRole = ref(filters.role || '')

const filteredUsers = computed(() => {
    let filtered = users.data
    
    if (searchQuery.value) {
        filtered = filtered.filter(user => 
            user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    }
    
    if (selectedRole.value) {
        filtered = filtered.filter(user => user.role_id == selectedRole.value)
    }
    
    return filtered
})

const updateFilters = () => {
    router.get('/users', {
        search: searchQuery.value,
        role: selectedRole.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>
```

**2. Shared Data with Composables:**

```typescript
// resources/js/composables/useAuth.ts
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);
    const isAuthenticated = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.role === "admin");

    const hasPermission = (permission: string) => {
        return user.value?.permissions?.includes(permission) ?? false;
    };

    const can = (action: string, resource: string) => {
        return hasPermission(`${resource}.${action}`);
    };

    return {
        user,
        isAuthenticated,
        isAdmin,
        hasPermission,
        can,
    };
}
```

---

## Key Concepts Summary

### 1. **Server-Side Setup**

- Middleware handles Inertia.js requests
- Asset versioning prevents caching issues
- Shared data available on all pages
- Conditional data based on user context

### 2. **Client-Side Setup**

- Vue.js app with Inertia.js plugin
- Page resolution strategy
- Global component registration
- TypeScript support

### 3. **Link Component**

- Client-side navigation without reloads
- Query parameters and methods
- State preservation options
- Loading states and accessibility

### 4. **Data Sharing**

- Server controllers prepare data
- Middleware merges shared data
- Vue components access via props
- Reactive updates and computed properties

---

## Next Steps

After completing this module, you should:

1. **Understand the middleware** and its role in Inertia.js
2. **Configure shared data** for global application state
3. **Set up the client-side** Vue.js application
4. **Use the Link component** for navigation
5. **Share data effectively** between server and client
6. **Be ready to move** to the next module: Page & Layout Components

---

## Additional Resources

- [Inertia.js Documentation](https://inertiajs.com/)
- [Laravel 12 Documentation](https://laravel.com/docs)
- [Vue.js 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Laravel Vite Plugin](https://laravel.com/docs/vite)
- [Inertia.js Middleware](https://inertiajs.com/server-side-setup)
