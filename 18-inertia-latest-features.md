# Inertia.js Latest Features & Best Practices

## Overview

Inertia.js continues to evolve with powerful new features that enhance developer experience and application performance. This guide covers the latest features, improvements, and best practices for building modern SPAs with Laravel 12, Vue.js, and Inertia.js.

## Enhanced TypeScript Support

### Improved Type Definitions

```typescript
// resources/js/types/inertia.ts
import { Page } from '@inertiajs/vue3';

// Define your page props interface
interface PageProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            avatar?: string;
            role: string;
        } | null;
    };
    flash: {
        message?: string;
        error?: string;
        success?: string;
    };
    errors: Record<string, string>;
}

// Extend the global Page interface
declare module '@inertiajs/vue3' {
    interface PageProps extends PageProps {}
}

// Usage in components
export default defineComponent({
    setup() {
        const page = usePage<PageProps>();
        
        // Now you have full type safety
        const user = computed(() => page.props.auth.user);
        const flashMessage = computed(() => page.props.flash.message);
        
        return { user, flashMessage };
    },
});
```

### Auto-generated Types from Laravel

```php
<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::paginate(15);
        
        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
            'can' => [
                'createUser' => $request->user()->can('create', User::class),
            ],
        ]);
    }
}
```

```typescript
// Auto-generated types (concept)
interface UsersIndexProps {
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            created_at: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters: {
        search?: string;
        role?: string;
    };
    can: {
        createUser: boolean;
    };
}
```

## Enhanced Form Handling

### Improved Form Validation

```vue
<!-- resources/js/pages/Users/Create.vue -->
<template>
    <AppLayout title="Create User">
        <div class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Name
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-500': form.errors.name }"
                    />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        :class="{ 'border-red-500': form.errors.email }"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <Link
                        :href="route('users.index')"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            // Handle success
        },
        onError: (errors) => {
            // Handle validation errors
            console.log('Validation errors:', errors);
        },
        onFinish: () => {
            // Always called after request completes
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>
```

### Advanced Form Features

```typescript
// resources/js/composables/useAdvancedForm.ts
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

export const useAdvancedForm = (initialData: Record<string, any> = {}) => {
    const form = useForm(initialData);
    const isDirty = ref(false);
    const hasUnsavedChanges = ref(false);

    // Watch for changes
    watch(
        () => form.data(),
        (newData, oldData) => {
            isDirty.value = JSON.stringify(newData) !== JSON.stringify(initialData);
            hasUnsavedChanges.value = isDirty.value;
        },
        { deep: true }
    );

    // Auto-save functionality
    const autoSave = ref(false);
    const autoSaveInterval = ref<NodeJS.Timeout | null>(null);

    const startAutoSave = (interval: number = 30000) => {
        autoSave.value = true;
        autoSaveInterval.value = setInterval(() => {
            if (isDirty.value) {
                form.post(route('auto-save'), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        hasUnsavedChanges.value = false;
                    },
                });
            }
        }, interval);
    };

    const stopAutoSave = () => {
        autoSave.value = false;
        if (autoSaveInterval.value) {
            clearInterval(autoSaveInterval.value);
            autoSaveInterval.value = null;
        }
    };

    // Form validation helpers
    const validateField = (field: string) => {
        return form.errors[field] ? 'border-red-500' : 'border-gray-300';
    };

    const getFieldError = (field: string) => {
        return form.errors[field] || '';
    };

    const clearFieldError = (field: string) => {
        if (form.errors[field]) {
            delete form.errors[field];
        }
    };

    return {
        form,
        isDirty,
        hasUnsavedChanges,
        autoSave,
        startAutoSave,
        stopAutoSave,
        validateField,
        getFieldError,
        clearFieldError,
    };
};
```

## Enhanced Navigation & Routing

### Improved Link Component

```vue
<!-- resources/js/components/EnhancedLink.vue -->
<template>
    <Link
        :href="href"
        :method="method"
        :data="data"
        :headers="headers"
        :replace="replace"
        :preserve-state="preserveState"
        :preserve-scroll="preserveScroll"
        :only="only"
        :except="except"
        :on-before="onBefore"
        :on-start="onStart"
        :on-progress="onProgress"
        :on-finish="onFinish"
        :on-cancel="onCancel"
        :on-success="onSuccess"
        :on-error="onError"
        :class="linkClasses"
        v-bind="$attrs"
    >
        <slot />
        
        <!-- Loading indicator -->
        <svg
            v-if="isLoading"
            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>
    </Link>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

interface Props {
    href: string;
    method?: string;
    data?: Record<string, any>;
    headers?: Record<string, string>;
    replace?: boolean;
    preserveState?: boolean;
    preserveScroll?: boolean;
    only?: string[];
    except?: string[];
    variant?: 'primary' | 'secondary' | 'danger';
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    method: 'get',
    replace: false,
    preserveState: false,
    preserveScroll: false,
    variant: 'primary',
    size: 'md',
});

const isLoading = ref(false);

const linkClasses = computed(() => {
    const baseClasses = 'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    const variantClasses = {
        primary: 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        secondary: 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    };
    
    const sizeClasses = {
        sm: 'px-3 py-2 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
    };
    
    return `${baseClasses} ${variantClasses[props.variant]} ${sizeClasses[props.size]}`;
});

// Enhanced event handlers
const onStart = () => {
    isLoading.value = true;
};

const onFinish = () => {
    isLoading.value = false;
};
</script>
```

### Advanced Navigation Patterns

```typescript
// resources/js/composables/useNavigation.ts
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

export const useNavigation = () => {
    const page = usePage();
    const isLoading = ref(false);
    const navigationHistory = ref<string[]>([]);

    // Enhanced visit with progress tracking
    const visit = async (
        url: string,
        options: any = {},
        onProgress?: (progress: number) => void
    ) => {
        isLoading.value = true;
        
        try {
            await router.visit(url, {
                ...options,
                onStart: () => {
                    isLoading.value = true;
                    options.onStart?.();
                },
                onProgress: (progress) => {
                    onProgress?.(progress);
                    options.onProgress?.(progress);
                },
                onFinish: () => {
                    isLoading.value = false;
                    navigationHistory.value.push(url);
                    options.onFinish?.();
                },
                onError: (errors) => {
                    isLoading.value = false;
                    options.onError?.(errors);
                },
            });
        } catch (error) {
            isLoading.value = false;
            throw error;
        }
    };

    // Smart navigation with caching
    const smartVisit = async (url: string, options: any = {}) => {
        const cacheKey = `navigation_${url}`;
        const cachedData = sessionStorage.getItem(cacheKey);
        
        if (cachedData && !options.force) {
            // Use cached data for faster navigation
            const data = JSON.parse(cachedData);
            return visit(url, {
                ...options,
                data,
                preserveState: true,
            });
        }
        
        return visit(url, {
            ...options,
            onSuccess: (page) => {
                // Cache the response
                sessionStorage.setItem(cacheKey, JSON.stringify(page.props));
                options.onSuccess?.(page);
            },
        });
    };

    // Breadcrumb navigation
    const breadcrumbs = computed(() => {
        const path = page.url;
        const segments = path.split('/').filter(Boolean);
        
        return segments.map((segment, index) => {
            const url = '/' + segments.slice(0, index + 1).join('/');
            return {
                name: segment.charAt(0).toUpperCase() + segment.slice(1),
                url,
                current: index === segments.length - 1,
            };
        });
    });

    // Back navigation with history
    const goBack = () => {
        if (navigationHistory.value.length > 1) {
            const previousUrl = navigationHistory.value[navigationHistory.value.length - 2];
            navigationHistory.value.pop();
            visit(previousUrl);
        } else {
            window.history.back();
        }
    };

    return {
        isLoading,
        navigationHistory,
        breadcrumbs,
        visit,
        smartVisit,
        goBack,
    };
};
```

## Enhanced Data Fetching

### Improved Partial Reloads

```typescript
// resources/js/composables/usePartialReload.ts
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export const usePartialReload = () => {
    const isReloading = ref(false);
    const lastReloadTime = ref<number>(0);

    const reload = async (
        only: string[] = [],
        options: any = {}
    ) => {
        const now = Date.now();
        
        // Prevent rapid successive reloads
        if (now - lastReloadTime.value < 1000) {
            return;
        }
        
        lastReloadTime.value = now;
        isReloading.value = true;

        try {
            await router.reload({
                only,
                preserveState: true,
                preserveScroll: true,
                ...options,
                onFinish: () => {
                    isReloading.value = false;
                    options.onFinish?.();
                },
            });
        } catch (error) {
            isReloading.value = false;
            throw error;
        }
    };

    const reloadWithDebounce = (
        only: string[] = [],
        delay: number = 300,
        options: any = {}
    ) => {
        return new Promise((resolve) => {
            setTimeout(() => {
                reload(only, options).then(resolve);
            }, delay);
        });
    };

    return {
        isReloading,
        reload,
        reloadWithDebounce,
    };
};
```

### Smart Data Caching

```typescript
// resources/js/composables/useSmartCache.ts
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface CacheItem<T> {
    data: T;
    timestamp: number;
    ttl: number;
}

export const useSmartCache = () => {
    const cache = ref<Record<string, CacheItem<any>>>({});
    const page = usePage();

    const set = <T>(key: string, data: T, ttl: number = 300000) => { // 5 minutes default
        cache.value[key] = {
            data,
            timestamp: Date.now(),
            ttl,
        };
    };

    const get = <T>(key: string): T | null => {
        const item = cache.value[key];
        
        if (!item) return null;
        
        if (Date.now() - item.timestamp > item.ttl) {
            delete cache.value[key];
            return null;
        }
        
        return item.data;
    };

    const invalidate = (pattern: string) => {
        Object.keys(cache.value).forEach(key => {
            if (key.includes(pattern)) {
                delete cache.value[key];
            }
        });
    };

    const clear = () => {
        cache.value = {};
    };

    // Auto-cache page props
    const cachePageProps = (key: string, ttl?: number) => {
        set(key, page.props, ttl);
    };

    // Get cached data with fallback
    const getCachedOrFetch = async <T>(
        key: string,
        fetchFn: () => Promise<T>,
        ttl?: number
    ): Promise<T> => {
        const cached = get<T>(key);
        if (cached) return cached;

        const data = await fetchFn();
        set(key, data, ttl);
        return data;
    };

    return {
        cache: computed(() => cache.value),
        set,
        get,
        invalidate,
        clear,
        cachePageProps,
        getCachedOrFetch,
    };
};
```

## Enhanced Error Handling

### Global Error Boundary

```vue
<!-- resources/js/components/ErrorBoundary.vue -->
<template>
    <div v-if="hasError" class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        Something went wrong
                    </h3>
                </div>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    {{ error?.message || 'An unexpected error occurred' }}
                </p>
            </div>
            
            <div class="flex space-x-3">
                <button
                    @click="retry"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                >
                    Try Again
                </button>
                <button
                    @click="goHome"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400"
                >
                    Go Home
                </button>
            </div>
            
            <details v-if="error?.stack" class="mt-4">
                <summary class="text-sm text-gray-500 cursor-pointer">Error Details</summary>
                <pre class="mt-2 text-xs text-gray-400 bg-gray-100 p-2 rounded overflow-auto">{{ error.stack }}</pre>
            </details>
        </div>
    </div>
    
    <slot v-else />
</template>

<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue';
import { router } from '@inertiajs/vue3';

const hasError = ref(false);
const error = ref<Error | null>(null);

onErrorCaptured((err: Error) => {
    hasError.value = true;
    error.value = err;
    
    // Log error to monitoring service
    console.error('Error caught by boundary:', err);
    
    return false; // Prevent error from propagating
});

const retry = () => {
    hasError.value = false;
    error.value = null;
    window.location.reload();
};

const goHome = () => {
    router.visit('/');
};
</script>
```

## Performance Enhancements

### Lazy Loading with Suspense

```vue
<!-- resources/js/pages/LazyPage.vue -->
<template>
    <AppLayout title="Lazy Loaded Page">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Suspense>
                    <template #default>
                        <LazyComponent />
                    </template>
                    <template #fallback>
                        <div class="flex items-center justify-center h-64">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                            <span class="ml-2 text-gray-600">Loading component...</span>
                        </div>
                    </template>
                </Suspense>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const LazyComponent = defineAsyncComponent(() => 
    import('@/components/HeavyComponent.vue')
);
</script>
```

### Optimized Bundle Splitting

```typescript
// resources/js/app.ts
import { createApp } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: (name) => {
        // Dynamic imports with better chunking
        const pages = import.meta.glob('./pages/**/*.vue', { 
            eager: false,
            import: 'default'
        });
        
        return pages[`./pages/${name}.vue`]();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

## Best Practices for Latest Features

### 1. Type Safety

```typescript
// Always define proper types
interface User {
    id: number;
    name: string;
    email: string;
}

// Use typed forms
const form = useForm<UserFormData>({
    name: '',
    email: '',
});

// Type your page props
const page = usePage<{
    users: User[];
    pagination: PaginationMeta;
}>();
```

### 2. Error Handling

```typescript
// Implement comprehensive error handling
const handleFormSubmit = () => {
    form.post(route('users.store'), {
        onSuccess: (page) => {
            // Handle success
        },
        onError: (errors) => {
            // Handle validation errors
            Object.keys(errors).forEach(field => {
                // Custom error handling per field
            });
        },
        onFinish: () => {
            // Always called
        },
    });
};
```

### 3. Performance Optimization

```typescript
// Use partial reloads efficiently
const refreshUserData = () => {
    router.reload({ only: ['users'] });
};

// Implement smart caching
const { getCachedOrFetch } = useSmartCache();
const userData = await getCachedOrFetch('users', fetchUsers, 300000);
```

## Migration Guide

### Upgrading to Latest Features

1. **Update Dependencies**
```bash
npm update @inertiajs/vue3
```

2. **Update Type Definitions**
```typescript
// Add new type definitions
declare module '@inertiajs/vue3' {
    interface PageProps {
        // Your page props
    }
}
```

3. **Migrate Forms**
```typescript
// Old way
const form = useForm(data);

// New way with enhanced features
const { form, isDirty, hasUnsavedChanges } = useAdvancedForm(data);
```

4. **Update Navigation**
```typescript
// Enhanced navigation with better error handling
const { visit, smartVisit } = useNavigation();
```

This comprehensive guide covers the latest Inertia.js features and best practices, helping you build more robust, performant, and maintainable applications with Laravel 12, Vue.js, and Inertia.js.
