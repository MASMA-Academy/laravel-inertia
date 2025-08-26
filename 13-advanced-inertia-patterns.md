# Advanced Inertia Patterns

## Overview

Advanced Inertia patterns extend beyond basic page navigation to include sophisticated state management, modal handling, partial reloads, and server-side rendering. These patterns enable building complex, performant applications that feel native while maintaining the benefits of server-side rendering.

## Tailwind CSS Integration

### Deep Integration with Inertia

Tailwind CSS works seamlessly with Inertia.js, providing utility-first styling that complements the component-based architecture.

```vue
<!-- resources/js/pages/Dashboard.vue -->
<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="border-4 border-dashed border-gray-200 rounded-lg h-96">
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                                Welcome to Dashboard
                            </h1>
                            <p class="text-gray-600 mb-6">
                                Manage your application from here
                            </p>
                            <Link
                                href="/users"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                            >
                                View Users
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
```

### Responsive Design Patterns

```vue
<!-- resources/js/components/UserCard.vue -->
<template>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <img
                        class="h-10 w-10 rounded-full"
                        :src="user.avatar"
                        :alt="user.name"
                    />
                </div>
                <div class="ml-4 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ user.name }}
                    </p>
                    <p class="text-sm text-gray-500 truncate">
                        {{ user.email }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                            'bg-green-100 text-green-800': user.status === 'active',
                            'bg-red-100 text-red-800': user.status === 'inactive',
                            'bg-yellow-100 text-yellow-800': user.status === 'pending'
                        }"
                    >
                        {{ user.status }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface User {
    id: number;
    name: string;
    email: string;
    avatar: string;
    status: 'active' | 'inactive' | 'pending';
}

defineProps<{
    user: User;
}>();
</script>
```

### Dark Mode Integration

```vue
<!-- resources/js/layouts/AppLayout.vue -->
<template>
    <div class="min-h-screen" :class="{ 'dark': isDark }">
        <div class="bg-white dark:bg-gray-900 transition-colors duration-200">
            <!-- Navigation -->
            <nav class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                                My App
                            </h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button
                                @click="toggleDarkMode"
                                class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                            >
                                <svg v-if="!isDark" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                                <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const isDark = ref(false);

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('darkMode', isDark.value.toString());
};

onMounted(() => {
    const saved = localStorage.getItem('darkMode');
    if (saved) {
        isDark.value = saved === 'true';
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
});
</script>
```

## Modal and Dialog Management

### Inertia Modal Pattern

```vue
<!-- resources/js/components/Modal.vue -->
<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click="closeOnBackdrop"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    :class="{ 'opacity-0': !show, 'opacity-100': show }"
                ></div>

                <!-- Modal panel -->
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    :class="{
                        'opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95': !show,
                        'opacity-100 translate-y-0 sm:scale-100': show
                    }"
                    @click.stop
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    {{ title }}
                                </h3>
                                <slot />
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <slot name="actions">
                            <button
                                type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="$emit('confirm')"
                            >
                                Confirm
                            </button>
                            <button
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="$emit('cancel')"
                            >
                                Cancel
                            </button>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
interface Props {
    show: boolean;
    title: string;
    closeOnBackdrop?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    closeOnBackdrop: true,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
    close: [];
}>();

const closeOnBackdrop = () => {
    if (props.closeOnBackdrop) {
        emit('close');
    }
};
</script>
```

### Modal with Inertia Forms

```vue
<!-- resources/js/pages/Users/Index.vue -->
<template>
    <AppLayout title="Users">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Users</h2>
                            <button
                                @click="showCreateModal = true"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Add User
                            </button>
                        </div>

                        <!-- Users List -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <UserCard
                                v-for="user in users"
                                :key="user.id"
                                :user="user"
                                @edit="editUser"
                                @delete="deleteUser"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <Modal
            :show="showCreateModal"
            title="Create New User"
            @close="showCreateModal = false"
            @confirm="createUser"
        >
            <form @submit.prevent="createUser">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import UserCard from '@/components/UserCard.vue';
import Modal from '@/components/Modal.vue';

interface User {
    id: number;
    name: string;
    email: string;
    avatar: string;
    status: string;
}

interface Props {
    users: User[];
}

const props = defineProps<Props>();

const showCreateModal = ref(false);

const form = useForm({
    name: '',
    email: '',
});

const createUser = () => {
    form.post('/users', {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const editUser = (user: User) => {
    // Navigate to edit page or show edit modal
    router.visit(`/users/${user.id}/edit`);
};

const deleteUser = (user: User) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${user.id}`);
    }
};
</script>
```

## Partial Reloads and Page Updates

### Selective Data Updates

```typescript
// resources/js/composables/useInertia.ts
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

export const useInertia = () => {
    const isLoading = ref(false);
    const errors = ref({});

    const reload = (only: string[] = []) => {
        isLoading.value = true;
        
        router.reload({
            only,
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                errors.value = errors;
                isLoading.value = false;
            },
        });
    };

    const visit = (url: string, options: any = {}) => {
        isLoading.value = true;
        
        router.visit(url, {
            ...options,
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                errors.value = errors;
                isLoading.value = false;
            },
        });
    };

    return {
        isLoading,
        errors,
        reload,
        visit,
    };
};
```

### Real-time Updates with Partial Reloads

```vue
<!-- resources/js/pages/Dashboard.vue -->
<template>
    <AppLayout title="Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Stats Cards -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Users
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.totalUsers }}
                                        </dd>
                                    </dl>
                                </div>
                                <button
                                    @click="refreshStats"
                                    class="ml-2 p-1 rounded-md text-gray-400 hover:text-gray-500"
                                    :disabled="isLoading"
                                >
                                    <svg class="h-4 w-4" :class="{ 'animate-spin': isLoading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow rounded-lg col-span-2">
                        <div class="p-5">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                            <div class="space-y-3">
                                <div
                                    v-for="activity in recentActivity"
                                    :key="activity.id"
                                    class="flex items-center space-x-3"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">
                                                {{ activity.user.name.charAt(0) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">
                                            {{ activity.description }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatTime(activity.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="refreshActivity"
                                class="mt-4 text-sm text-indigo-600 hover:text-indigo-500"
                                :disabled="isLoading"
                            >
                                {{ isLoading ? 'Refreshing...' : 'Refresh Activity' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useInertia } from '@/composables/useInertia';

interface Props {
    stats: {
        totalUsers: number;
        totalOrders: number;
        totalRevenue: number;
    };
    recentActivity: Array<{
        id: number;
        description: string;
        user: { name: string };
        created_at: string;
    }>;
}

const props = defineProps<Props>();

const { isLoading, reload } = useInertia();

const refreshStats = () => {
    reload(['stats']);
};

const refreshActivity = () => {
    reload(['recentActivity']);
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};
</script>
```

## Shared Data and Global State

### Global Shared Data

```php
<?php
// app/Http/Middleware/HandleInertiaRequests.php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar' => $request->user()->avatar,
                    'role' => $request->user()->role,
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
            'app' => [
                'name' => config('app.name'),
                'version' => config('app.version'),
                'environment' => app()->environment(),
            ],
            'notifications' => fn () => $request->user() 
                ? $request->user()->unreadNotifications()->limit(5)->get()
                : collect(),
        ]);
    }
}
```

### Client-side Global State

```typescript
// resources/js/composables/useGlobalState.ts
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useGlobalState = () => {
    const page = usePage();
    
    // Global state
    const sidebarOpen = ref(false);
    const theme = ref('light');
    const notifications = ref([]);
    
    // Computed properties
    const user = computed(() => page.props.auth.user);
    const isAuthenticated = computed(() => !!user.value);
    const flashMessage = computed(() => page.props.flash.message);
    const flashError = computed(() => page.props.flash.error);
    const flashSuccess = computed(() => page.props.flash.success);
    
    // Actions
    const toggleSidebar = () => {
        sidebarOpen.value = !sidebarOpen.value;
    };
    
    const setTheme = (newTheme: string) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
    };
    
    const addNotification = (notification: any) => {
        notifications.value.push({
            id: Date.now(),
            ...notification,
        });
    };
    
    const removeNotification = (id: number) => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    };
    
    // Watch for flash messages
    watch(flashMessage, (message) => {
        if (message) {
            addNotification({
                type: 'info',
                message,
            });
        }
    });
    
    watch(flashError, (error) => {
        if (error) {
            addNotification({
                type: 'error',
                message: error,
            });
        }
    });
    
    watch(flashSuccess, (success) => {
        if (success) {
            addNotification({
                type: 'success',
                message: success,
            });
        }
    });
    
    return {
        // State
        sidebarOpen,
        theme,
        notifications,
        
        // Computed
        user,
        isAuthenticated,
        flashMessage,
        flashError,
        flashSuccess,
        
        // Actions
        toggleSidebar,
        setTheme,
        addNotification,
        removeNotification,
    };
};
```

## Server-Side Rendering (SSR) Setup

### SSR Configuration

```typescript
// resources/js/ssr.ts
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, h, type DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => {
            const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
            return pages[`./pages/${name}.vue`] as DefineComponent;
        },
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin);
        },
    })
);
```

### Vite SSR Configuration

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
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
    ssr: {
        noExternal: ['@inertiajs/vue3'],
    },
});
```

### Laravel SSR Route

```php
<?php
// routes/web.php
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
});

// SSR route
Route::get('/ssr', function () {
    return Inertia::render('Dashboard');
})->middleware('ssr');
```

## When to Use Each Pattern

### Use Tailwind CSS Integration When:
- Building modern, responsive UIs
- You want utility-first styling
- You need consistent design systems
- You're building component libraries

### Use Modal Management When:
- You need overlay dialogs
- You want to avoid page navigation for simple actions
- You're building forms that don't require full page context
- You need confirmation dialogs

### Use Partial Reloads When:
- You want to update specific data without full page refresh
- You're building real-time features
- You need to optimize performance
- You're working with large datasets

### Use Shared Data When:
- You need global application state
- You want to share data across multiple pages
- You're building user-specific features
- You need to maintain state across navigation

### Use SSR When:
- SEO is important
- You need fast initial page loads
- You're building public-facing applications
- You want to improve Core Web Vitals

## Best Practices

1. **Keep modals focused**: Each modal should have a single purpose
2. **Use partial reloads wisely**: Don't overuse them for simple updates
3. **Optimize shared data**: Only share data that's actually needed globally
4. **Implement proper error handling**: Handle errors gracefully in all patterns
5. **Use TypeScript**: Leverage type safety for better development experience
6. **Test thoroughly**: Ensure all patterns work correctly across different scenarios

## Common Pitfalls

1. **Modal state management**: Forgetting to reset modal state when closing
2. **Memory leaks**: Not cleaning up event listeners and watchers
3. **Over-sharing data**: Sharing too much data globally can hurt performance
4. **SSR hydration mismatches**: Ensuring client and server render the same content
5. **Accessibility**: Not implementing proper ARIA attributes for modals and dialogs

This comprehensive guide covers advanced Inertia patterns that will help you build sophisticated, performant applications with Laravel 12 and Vue.js.
