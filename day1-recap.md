# Day 1 Recap: Modern SPA Architecture & Inertia.js Fundamentals

## Course Overview

Day 1 introduced you to modern Single Page Application (SPA) architecture and
the Inertia.js pattern, providing a solid foundation for building full-stack
applications with Laravel 12 and Vue.js.

---

## 🏗️ Module 1: Modern SPA Architecture

### Key Concepts Covered

**1. Traditional Approaches vs Modern Solutions**

- **Server-Side Rendering (SSR)**: Full page reloads, server-generated HTML
- **Traditional SPAs**: JavaScript-driven, API-based, client-side routing
- **Inertia.js Pattern**: Best of both worlds - server-side routing with SPA
  experience

**2. Client-Side Rendering Challenges**

- **SEO Limitations**: Search engines struggle with JavaScript-rendered content
- **Initial Load Performance**: Bundle size and loading delays
- **State Management Complexity**: Data synchronization across components
- **Authentication Security**: Client-side route protection challenges

**3. Inertia.js Solution**

- **No API Required**: Use existing Laravel backend directly
- **Server-Side Routing**: Leverage Laravel's powerful routing system
- **Partial Updates**: Navigate without full page reloads
- **Built-in Authentication**: Laravel's authentication system works seamlessly

---

## 🔄 Architecture Comparison: SSR vs CSR vs Inertia.js

### Server-Side Rendering (SSR)

**How it Works:**

- Server generates complete HTML for each request
- Full page reload on every navigation
- HTML is sent to browser with all content rendered

**Characteristics:**

```php
// Traditional Laravel Blade SSR
Route::get('/users', function () {
    $users = User::all();
    return view('users.index', compact('users'));
});

// resources/views/users/index.blade.php
@extends('layouts.app')
@section('content')
    @foreach($users as $user)
        <div class="user-card">
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email }}</p>
        </div>
    @endforeach
@endsection
```

**Pros:**

- ✅ Excellent SEO (search engines see full content)
- ✅ Fast initial page load
- ✅ Works without JavaScript
- ✅ Simple server-side logic

**Cons:**

- ❌ Full page reloads (poor UX)
- ❌ No client-side state persistence
- ❌ Higher server load
- ❌ Slower navigation between pages

### Client-Side Rendering (CSR)

**How it Works:**

- Single HTML page loaded initially
- JavaScript handles all routing and content rendering
- API calls fetch data from backend
- No page reloads, only content updates

**Characteristics:**

```javascript
// Traditional Vue.js SPA with API
import { createRouter, createWebHistory } from "vue-router";
import UsersList from "./components/UsersList.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/users", component: UsersList },
    ],
});

// API call in component
async function fetchUsers() {
    const response = await fetch("/api/users");
    const users = await response.json();
    // Update component state
}
```

**Pros:**

- ✅ Fast navigation between pages
- ✅ Rich user experience
- ✅ Client-side state management
- ✅ Offline capabilities possible

**Cons:**

- ❌ Poor SEO (search engines see empty HTML)
- ❌ Slower initial load (JavaScript bundle)
- ❌ Complex state management
- ❌ Requires separate API development

### Inertia.js Hybrid Approach

**How it Works:**

- Combines server-side routing with client-side navigation
- Server prepares data and sends to client
- Client renders content without full page reloads
- No API needed - direct server communication

**Characteristics:**

```php
// Laravel Controller (Server-side)
public function index()
{
    $users = User::paginate(10);
    
    return Inertia::render('Users/Index', [
        'users' => $users
    ]);
}
```

```vue
<!-- Vue.js Component (Client-side) -->
<template>
    <div>
        <h1>Users</h1>
        <div v-for="user in users.data" :key="user.id">
            <h3>{{ user.name }}</h3>
            <p>{{ user.email }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { users } = usePage().props
</script>
```

**Pros:**

- ✅ Good SEO (server-side data preparation)
- ✅ Fast navigation (no full page reloads)
- ✅ No API development required
- ✅ Leverages existing Laravel backend
- ✅ Built-in authentication and authorization
- ✅ Server-side validation and security

**Cons:**

- ❌ Requires JavaScript to work
- ❌ More complex than traditional SSR
- ❌ Learning curve for team members

---

### Detailed Comparison Table

| Aspect               | Traditional SSR               | Traditional CSR                    | Inertia.js                    |
| -------------------- | ----------------------------- | ---------------------------------- | ----------------------------- |
| **Initial Load**     | Fast (HTML ready)             | Slow (JS bundle)                   | Fast (HTML + JS)              |
| **Navigation**       | Slow (full reload)            | Fast (no reload)                   | Fast (partial update)         |
| **SEO**              | Excellent                     | Poor                               | Good                          |
| **Development**      | Simple                        | Complex                            | Moderate                      |
| **API Required**     | No                            | Yes                                | No                            |
| **Authentication**   | Built-in                      | Custom                             | Built-in                      |
| **State Management** | Session-based                 | Client-side                        | Hybrid                        |
| **Performance**      | Good initial, poor navigation | Poor initial, excellent navigation | Good initial, good navigation |
| **Learning Curve**   | Low                           | High                               | Medium                        |
| **Team Adoption**    | Easy                          | Challenging                        | Moderate                      |

### When to Use Each Approach

**Choose Traditional SSR when:**

- Building content-heavy websites (blogs, news sites)
- SEO is the top priority
- Team has limited JavaScript experience
- Simple applications with minimal interactivity

**Choose Traditional CSR when:**

- Building highly interactive applications (dashboards, admin panels)
- SEO is not a concern
- Team has strong JavaScript skills
- Need offline capabilities

**Choose Inertia.js when:**

- Building full-stack applications with Laravel
- Want good SEO without sacrificing UX
- Team has both PHP and JavaScript skills
- Need rapid development with existing Laravel knowledge
- Building applications that require both server-side and client-side features

### Real-World Examples

**Traditional SSR:**

- WordPress websites
- E-commerce product pages
- Documentation sites
- News and blog platforms

**Traditional CSR:**

- Single-page applications
- Admin dashboards
- Real-time applications
- Mobile web apps

**Inertia.js:**

- Laravel-based web applications
- Business management systems
- Content management systems
- E-commerce platforms
- SaaS applications

### State Management Architecture

Inertia.js provides a sophisticated state management system that bridges
server-side and client-side state, eliminating the need for complex client-side
state management libraries in many cases.

```
┌─────────────────────────────────────────────────────────────────┐
│                    State Management Flow                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌──────────────┐    ┌─────────────────┐   │
│  │   Laravel   │    │   Inertia    │    │     Vue.js      │   │
│  │  Backend    │◄──►│   Middleware │◄──►│   Frontend      │   │
│  │             │    │              │    │                 │   │
│  └─────────────┘    └──────────────┘    └─────────────────┘   │
│         │                     │                     │          │
│         ▼                     ▼                     ▼          │
│  ┌─────────────┐    ┌──────────────┐    ┌─────────────────┐   │
│  │   Models    │    │ Shared Data  │    │  Page Props     │   │
│  │  Database   │    │ Flash Msgs   │    │  Local State    │   │
│  │  Sessions   │    │  Versions    │    │  Form State     │   │
│  └─────────────┘    └──────────────┘    └─────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 1. Server-Side State Management

#### **Page Props (Primary State)**

Data passed from Laravel controllers to Vue.js components via
`Inertia::render()`.

```php
// app/Http/Controllers/UserController.php
public function index()
{
    $users = User::with('profile')
        ->where('active', true)
        ->paginate(15);
    
    $filters = request()->only(['search', 'role', 'status']);
    
    return Inertia::render('Users/Index', [
        'users' => $users,
        'filters' => $filters,
        'roles' => Role::all(),
        'stats' => [
            'total' => User::count(),
            'active' => User::where('active', true)->count(),
            'inactive' => User::where('active', false)->count(),
        ]
    ]);
}
```

#### **Shared Data (Global State)**

Data available on every page across the entire application.

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'permissions' => $request->user()->permissions->pluck('name'),
            ] : null,
        ],
        'app' => [
            'name' => config('app.name'),
            'version' => config('app.version'),
            'environment' => config('app.env'),
        ],
        'notifications' => [
            'unread_count' => $request->user()?->unreadNotifications()->count() ?? 0,
        ],
        'settings' => [
            'theme' => $request->cookie('theme', 'light'),
            'locale' => app()->getLocale(),
        ],
    ]);
}
```

#### **Flash Messages (Temporary State)**

One-time messages that persist across page navigation.

```php
// app/Http/Controllers/UserController.php
public function store(CreateUserRequest $request)
{
    $user = User::create($request->validated());
    
    return redirect()->route('users.index')
        ->with('success', "User {$user->name} created successfully!");
}

public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());
    
    return redirect()->route('users.show', $user)
        ->with('info', 'User profile updated successfully!');
}
```

### 2. Client-Side State Management

#### **Page Props Access**

Accessing server-side data in Vue.js components.

```vue
<!-- resources/js/Pages/Users/Index.vue -->
<template>
    <div class="users-page">
        <!-- Access shared data -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Welcome, {{ $page.props.auth.user.name }}!
                </h1>
                <p class="mt-2 text-gray-600">
                    Managing {{ $page.props.stats.total }} users
                </p>
            </div>
        </header>

        <!-- Access page-specific data -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <UserFilters :filters="filters" :roles="roles" />
                <UserList :users="users" />
                <UserPagination :pagination="users" />
            </div>
        </main>

        <!-- Flash messages -->
        <FlashMessages />
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import UserFilters from '@/Components/Users/UserFilters.vue'
import UserList from '@/Components/Users/UserList.vue'
import UserPagination from '@/Components/Users/UserPagination.vue'
import FlashMessages from '@/Components/FlashMessages.vue'

const { users, filters, roles, stats } = usePage().props
</script>
```

#### **Local Component State**

Component-specific state that doesn't need to be shared.

```vue
<!-- resources/js/Components/Users/UserFilters.vue -->
<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <form @submit.prevent="applyFilters" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Search
                    </label>
                    <input
                        v-model="localFilters.search"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        placeholder="Search users..."
                    />
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Role
                    </label>
                    <select
                        v-model="localFilters.role"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    >
                        <option value="">All Roles</option>
                        <option
                            v-for="role in roles"
                            :key="role.id"
                            :value="role.id"
                        >
                            {{ role.name }}
                        </option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Status
                    </label>
                    <select
                        v-model="localFilters.status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between">
                <button
                    type="button"
                    @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Reset
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700"
                >
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'

interface Filters {
    search: string
    role: string
    status: string
}

interface Props {
    filters: Filters
    roles: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

// Local state for form inputs
const localFilters = reactive<Filters>({
    search: props.filters.search || '',
    role: props.filters.role || '',
    status: props.filters.status || ''
})

// Watch for prop changes and update local state
watch(() => props.filters, (newFilters) => {
    localFilters.search = newFilters.search || ''
    localFilters.role = newFilters.role || ''
    localFilters.status = newFilters.status || ''
}, { deep: true })

// Apply filters
const applyFilters = () => {
    router.get('/users', localFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Reset filters
const resetFilters = () => {
    localFilters.search = ''
    localFilters.role = ''
    localFilters.status = ''
    applyFilters()
}
</script>
```

### 3. Advanced State Management Patterns

#### **Form State Management**

Using Inertia.js form helpers for complex form handling.

```vue
<!-- resources/js/Pages/Users/Create.vue -->
<template>
    <div class="max-w-2xl mx-auto py-6">
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Name
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    :class="{ 'border-red-500': form.errors.name }"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    :class="{ 'border-red-500': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Role
                </label>
                <select
                    v-model="form.role_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    :class="{ 'border-red-500': form.errors.role_id }"
                >
                    <option value="">Select Role</option>
                    <option
                        v-for="role in roles"
                        :key="role.id"
                        :value="role.id"
                    >
                        {{ role.name }}
                    </option>
                </select>
                <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-600">
                    {{ form.errors.role_id }}
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <Link
                    :href="route('users.index')"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                >
                    <span v-if="form.processing">Creating...</span>
                    <span v-else>Create User</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

interface Props {
    roles: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

const form = useForm({
    name: '',
    email: '',
    role_id: ''
})

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            // Form automatically handles success redirect
        },
        onError: (errors) => {
            // Form automatically handles error display
        }
    })
}
</script>
```

### 4. State Management Best Practices

#### **Performance Optimization**

```php
// Eager loading relationships to avoid N+1 queries
public function index()
{
    $users = User::with(['profile', 'role', 'permissions'])
        ->when(request('search'), function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })
        ->when(request('role'), function ($query, $role) {
            $query->whereHas('role', function ($q) use ($role) {
                $q->where('id', $role);
            });
        })
        ->paginate(15)
        ->withQueryString();
    
    return Inertia::render('Users/Index', compact('users'));
}
```

#### **State Validation**

```php
// Form request validation
class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'User name is required.',
            'email.unique' => 'This email is already registered.',
            'role_id.exists' => 'Selected role is invalid.',
        ];
    }
}
```

### 5. State Management Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    State Management Flow                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. User Action (Click, Form Submit, Navigation)              │
│                              │                                 │
│                              ▼                                 │
│  2. Inertia Request to Laravel                                │
│                              │                                 │
│                              ▼                                 │
│  3. Laravel Controller Processes Request                       │
│     ├─ Database Queries                                       │
│     ├─ Business Logic                                         │
│     ├─ Validation                                             │
│     └─ Data Preparation                                       │
│                              │                                 │
│                              ▼                                 │
│  4. Inertia Response with Data                                │
│     ├─ Page Props (Primary Data)                              │
│     ├─ Shared Data (Global State)                             │
│     ├─ Flash Messages (Temporary)                             │
│     └─ Errors (If Any)                                        │
│                              │                                 │
│                              ▼                                 │
│  5. Vue.js Component Receives Data                            │
│     ├─ Updates Component State                                │
│     ├─ Renders New Content                                    │
│     ├─ Shows Success/Error Messages                           │
│     └─ Updates URL (If Navigation)                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 6. State Management Checklist

- [ ] **Server-Side State**
  - [ ] Page props properly structured and optimized
  - [ ] Shared data includes essential global information
  - [ ] Flash messages for user feedback
  - [ ] Proper error handling and validation

- [ ] **Client-Side State**
  - [ ] Local component state for UI interactions
  - [ ] Form state management with Inertia.js helpers
  - [ ] Reactive updates based on prop changes
  - [ ] Proper cleanup and memory management

- [ ] **Performance**
  - [ ] Eager loading relationships to avoid N+1 queries
  - [ ] Pagination for large datasets
  - [ ] Debounced search inputs
  - [ ] Optimistic updates where appropriate

- [ ] **User Experience**
  - [ ] Loading states during requests
  - [ ] Success and error feedback
  - [ ] Form validation in real-time

### Installation & Setup

**Prerequisites:**

```bash
composer global require laravel/installer
```

**Project Creation:**

```bash
# Create new Laravel project with Breeze already included
laravel new my-inertia-app --breeze --vue

# Navigate to project directory
cd my-inertia-app

# Run migrations and build assets
php artisan migrate
npm install
npm run build

# Start development servers
php artisan serve
npm run dev
```

**What's Automatically Included:**

- Inertia.js middleware and configuration
- Authentication scaffolding (login, register, password reset)
- Basic layout components
- Vue.js setup with Inertia.js
- Tailwind CSS configuration
- Vite build configuration

---

## 🔧 Module 2: Inertia.js Core Concepts

### Server-Side Setup

**1. Middleware Configuration**

- `HandleInertiaRequests` middleware automatically registered
- Handles Inertia.js request detection
- Manages shared data and asset versioning

**2. Root Template**

```php
<!-- resources/views/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @inertia
    </div>
</body>
</html>
```

**3. Controller Integration**

```php
// app/Http/Controllers/UserController.php
use Inertia\Inertia;

public function index()
{
    $users = User::paginate(10);
    
    return Inertia::render('Users/Index', [
        'users' => $users
    ]);
}
```

### Client-Side Setup

**1. Vue App Entry Point**

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

**2. Page Resolution Strategy**

- Automatic page component discovery
- Nested page support (e.g., `Admin/Users/Index.vue`)
- TypeScript configuration for better development experience

### The Link Component

**1. Basic Navigation**

```vue
<template>
    <Link href="/users">Users</Link>
    <Link href="/users/create" method="post" :data="formData">
        Create User
    </Link>
</template>
```

**2. Advanced Features**

- Query parameters: `href="/users?search=john&role=admin"`
- Method specification: `method="post"`, `method="put"`, `method="delete"`
- State preservation: `preserve-state`, `preserve-scroll`
- Loading states and progress indicators

### Data Sharing Mechanisms

**1. Server to Client**

- **Page Props**: Data passed via `Inertia::render()`
- **Shared Data**: Global data available on all pages
- **Flash Messages**: Temporary success/error notifications

**2. Client to Server**

- **Form Submissions**: Using `useForm()` composable
- **Query Parameters**: URL-based data passing
- **Request Headers**: CSRF tokens, authentication headers

---

## 🎨 Module 3: Page & Layout Components

### Page Components

**1. Basic Structure**

```vue
<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold">Users</h1>
                <!-- Page content -->
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})
</script>
```

**2. Data Access**

```vue
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { users, filters } = usePage().props
</script>
```

### Layout Components

**1. Reusable Layouts**

```vue
<!-- resources/js/Layouts/AppLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <!-- Navigation content -->
        </nav>
        
        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>
```

**2. Layout Usage**

```vue
<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})
</script>
```

### Responsive Design Patterns

**1. Mobile-First Approach**

```vue
<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Responsive grid layout -->
    </div>
</template>
```

**2. Tailwind CSS Integration**

- Utility-first CSS framework
- Responsive breakpoints: `sm:`, `md:`, `lg:`, `xl:`
- Component-based design system

---

## 🛣️ Module 4: Routing & Navigation

### Laravel + Inertia.js Integration

**1. Route Definition**

```php
// routes/web.php
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
```

**2. Client-Side Navigation**

```vue
<template>
    <Link :href="route('users.show', user.id)">
        View User
    </Link>
</template>
```

### Navigation Events

**1. Global Event Handling**

```typescript
// resources/js/app.ts
import { router } from "@inertiajs/vue3";

router.on("navigate", () => {
    console.log("Navigation started");
    // Show loading indicator
});

router.on("finish", () => {
    console.log("Navigation completed");
    // Hide loading indicator
});
```

**2. Component-Level Events**

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3'

router.on('navigate', () => {
    // Component-specific navigation handling
})
</script>
```

### Page Transitions

**1. Basic Transitions**

```vue
<template>
    <Transition name="fade" mode="out-in">
        <slot />
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
```

**2. Advanced Transitions**

- Slide transitions
- Loading states
- Progress indicators
- Error handling

---

## 🧪 Module 5: Hands-on Lab

### Project Setup

**1. Complete Application Structure**

```
user-management-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── UserController.php
│   │   └── AuthController.php
│   └── Models/
│       └── User.php
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Home.vue
│   │   │   ├── Users/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   └── Edit.vue
│   │   │   └── Auth/
│   │   │       └── Login.vue
│   │   ├── Layouts/
│   │   │   └── AppLayout.vue
│   │   └── Components/
│   │       ├── UserCard.vue
│   │       └── UserForm.vue
│   └── views/
│       └── app.blade.php
└── routes/
    └── web.php
```

**2. Key Features Implemented**

- User authentication system
- CRUD operations for users
- Responsive design with Tailwind CSS
- Form validation and error handling
- Navigation between pages

### Database & Models

**1. User Model**

```php
// app/Models/User.php
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```

**2. Database Seeding**

```php
// database/seeders/UserSeeder.php
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
]);
```

### Frontend Components

**1. User Management Interface**

- User listing with search and filters
- Create and edit user forms
- Responsive table design
- Action buttons (edit, delete)

**2. Authentication Forms**

- Login form with validation
- Error message display
- Loading states
- Form submission handling

---

## 🎯 Key Takeaways

### What You've Learned

1. **Modern SPA Architecture**: Understanding the evolution from traditional SSR
   to modern SPAs
2. **Inertia.js Pattern**: How to bridge server-side and client-side development
3. **Component Architecture**: Building reusable Vue.js components with proper
   structure
4. **Routing Integration**: Seamless Laravel + Inertia.js routing system
5. **Data Flow**: Server-to-client and client-to-server data management
6. **Responsive Design**: Mobile-first approach with Tailwind CSS

### Best Practices Established

1. **Project Structure**: Organized file and folder structure
2. **Component Design**: Reusable, maintainable component architecture
3. **State Management**: Proper data flow and state handling
4. **Error Handling**: User-friendly error messages and validation
5. **Performance**: Efficient navigation and data loading
6. **Accessibility**: Semantic HTML and proper ARIA attributes

### Development Workflow

1. **Setup**: Use Laravel starter kits for rapid development
2. **Development**: Build components incrementally
3. **Testing**: Test navigation and user interactions
4. **Refinement**: Iterate on design and functionality
5. **Deployment**: Prepare for production deployment

---

## 🚀 Next Steps

### Day 2 Preview

Day 2 will build upon these fundamentals and cover:

- **Vue.js Component Architecture**: Advanced component patterns
- **Composition API Fundamentals**: Modern Vue.js development
- **Form Handling & Validation**: Client and server-side validation
- **Data Fetching & State Management**: API integration and state management

### Practice Exercises

1. **Extend User Management**: Add user roles, permissions, and profile
   management
2. **Build Dashboard**: Create analytics and reporting components
3. **Add Search & Filters**: Implement advanced search functionality
4. **Create Admin Panel**: Build role-based access control
5. **Mobile Optimization**: Enhance mobile user experience

### Resources

- [Laravel 12.x Documentation](https://laravel.com/docs/12.x)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue.js 3 Documentation](https://vuejs.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)

---

## 📝 Assessment Checklist

By the end of Day 1, you should be able to:

- [ ] Explain the differences between traditional SSR, SPAs, and Inertia.js
- [ ] Set up a new Laravel 12 project with Inertia.js and Vue.js
- [ ] Create and use page components with proper layouts
- [ ] Implement navigation between pages using Inertia.js
- [ ] Build responsive user interfaces with Tailwind CSS
- [ ] Handle form submissions and data validation
- [ ] Create a basic CRUD application with authentication
- [ ] Understand the data flow between server and client
- [ ] Use the Inertia.js Link component for navigation
- [ ] Implement proper error handling and user feedback

---

**Congratulations on completing Day 1!** You now have a solid foundation in
modern SPA development with Laravel and Inertia.js. You're ready to tackle more
advanced concepts in Day 2.
