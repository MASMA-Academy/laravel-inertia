# 🗂️ Laravel Inertia State Management

## Overview

Inertia.js provides a sophisticated state management system that bridges
server-side and client-side state, eliminating the need for complex client-side
state management libraries in many cases.

## State Management Architecture

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

## 1. Server-Side State Management

### Page Props (Primary State)

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

### Shared Data (Global State)

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

### Flash Messages (Temporary State)

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

## 2. Client-Side State Management

### Page Props Access

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

### Local Component State

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
                        <option value="">All Statuses</option>
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

## 3. Advanced State Management Patterns

### Form State Management

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

## 4. State Management Best Practices

### Performance Optimization

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

### State Validation

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

## 5. State Management Flow Diagram

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

## 6. State Management Checklist

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
  - [ ] Smooth transitions between states

## Summary

Inertia.js state management provides a powerful bridge between Laravel's
server-side capabilities and Vue.js's reactive frontend. By understanding the
different types of state (page props, shared data, flash messages, and local
state), developers can build applications that are both performant and
maintainable.

The key is to leverage server-side state for data that needs to be consistent
across the application, while using client-side state for UI interactions and
temporary data. This approach eliminates the need for complex client-side state
management libraries while maintaining the benefits of modern SPA development.
