# Pinia State Management with Laravel 12, Vue & Inertia.js

## Overview

Pinia is the official state management library for Vue.js, providing a modern, type-safe, and developer-friendly approach to managing application state. When combined with Laravel 12, Vue, and Inertia.js, Pinia offers powerful state management capabilities that complement Inertia's server-side data flow while providing client-side state persistence and reactivity.

## Why Pinia with Inertia.js?

### Benefits of Using Pinia with Inertia

1. **Client-side State Persistence**: Maintain state across page navigations
2. **Reactive Data Management**: Automatic UI updates when state changes
3. **Type Safety**: Full TypeScript support for better development experience
4. **DevTools Integration**: Excellent debugging capabilities
5. **Modular Architecture**: Organize state by feature or domain
6. **Composition API**: Modern Vue 3 patterns with better tree-shaking

### When to Use Pinia vs Inertia Props

```
Inertia Props (Server-side):
├── Initial page data
├── User authentication state
├── Server-rendered content
└── SEO-critical data

Pinia Stores (Client-side):
├── UI state (modals, forms, filters)
├── Cached API responses
├── User preferences
├── Shopping cart data
└── Real-time data
```

## Pinia Setup and Configuration

### Installation and Basic Setup

```bash
npm install pinia
```

```typescript
// resources/js/app.ts
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';

const app = createApp({});

// Create Pinia instance
const pinia = createPinia();

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
        return pages[`./pages/${name}.vue`] as DefineComponent;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia) // Add Pinia to the app
            .mount(el);
    },
});
```

### TypeScript Configuration

```typescript
// resources/js/types/pinia.ts
import 'pinia';

declare module 'pinia' {
    export interface PiniaCustomProperties {
        // Custom properties for all stores
        $inertia: any;
    }
}

// Store state types
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: string;
}

export interface Notification {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    message: string;
    duration?: number;
    read: boolean;
}
```

## Core Store Patterns

### Authentication Store

```typescript
// resources/js/stores/auth.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import type { User } from '@/types/pinia';

export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref<User | null>(null);
    const isAuthenticated = ref(false);
    const isLoading = ref(false);

    // Getters
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isUser = computed(() => user.value?.role === 'user');
    const userInitials = computed(() => {
        if (!user.value?.name) return '';
        return user.value.name
            .split(' ')
            .map(name => name.charAt(0))
            .join('')
            .toUpperCase();
    });

    // Actions
    const setUser = (userData: User | null) => {
        user.value = userData;
        isAuthenticated.value = !!userData;
    };

    const login = async (credentials: { email: string; password: string }) => {
        isLoading.value = true;
        try {
            await router.post('/login', credentials, {
                onSuccess: () => {
                    // User will be set via Inertia props
                },
                onError: (errors) => {
                    throw new Error(Object.values(errors)[0] as string);
                },
            });
        } finally {
            isLoading.value = false;
        }
    };

    const logout = async () => {
        isLoading.value = true;
        try {
            await router.post('/logout', {}, {
                onSuccess: () => {
                    setUser(null);
                },
            });
        } finally {
            isLoading.value = false;
        }
    };

    const updateProfile = async (data: Partial<User>) => {
        isLoading.value = true;
        try {
            await router.put('/profile', data, {
                onSuccess: (page) => {
                    if (page.props.auth?.user) {
                        setUser(page.props.auth.user);
                    }
                },
            });
        } finally {
            isLoading.value = false;
        }
    };

    return {
        // State
        user,
        isAuthenticated,
        isLoading,
        
        // Getters
        isAdmin,
        isUser,
        userInitials,
        
        // Actions
        setUser,
        login,
        logout,
        updateProfile,
    };
});
```

### UI State Store

```typescript
// resources/js/stores/ui.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useUIStore = defineStore('ui', () => {
    // State
    const sidebarOpen = ref(false);
    const theme = ref<'light' | 'dark'>('light');
    const modals = ref<Record<string, boolean>>({});
    const loading = ref<Record<string, boolean>>({});
    const notifications = ref<any[]>([]);

    // Getters
    const isModalOpen = computed(() => (modalName: string) => modals.value[modalName] || false);
    const isLoading = computed(() => (key: string) => loading.value[key] || false);
    const unreadNotifications = computed(() => 
        notifications.value.filter(n => !n.read)
    );

    // Actions
    const toggleSidebar = () => {
        sidebarOpen.value = !sidebarOpen.value;
        localStorage.setItem('sidebarOpen', sidebarOpen.value.toString());
    };

    const setTheme = (newTheme: 'light' | 'dark') => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        document.documentElement.classList.toggle('dark', newTheme === 'dark');
    };

    const openModal = (modalName: string) => {
        modals.value[modalName] = true;
    };

    const closeModal = (modalName: string) => {
        modals.value[modalName] = false;
    };

    const setLoading = (key: string, value: boolean) => {
        loading.value[key] = value;
    };

    const addNotification = (notification: any) => {
        const id = Date.now().toString();
        notifications.value.push({
            id,
            read: false,
            ...notification,
        });

        // Auto-remove after duration
        if (notification.duration) {
            setTimeout(() => {
                removeNotification(id);
            }, notification.duration);
        }
    };

    const removeNotification = (id: string) => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    };

    const markNotificationAsRead = (id: string) => {
        const notification = notifications.value.find(n => n.id === id);
        if (notification) {
            notification.read = true;
        }
    };

    // Initialize from localStorage
    const initialize = () => {
        const savedSidebar = localStorage.getItem('sidebarOpen');
        if (savedSidebar) {
            sidebarOpen.value = savedSidebar === 'true';
        }

        const savedTheme = localStorage.getItem('theme') as 'light' | 'dark';
        if (savedTheme) {
            setTheme(savedTheme);
        }
    };

    return {
        // State
        sidebarOpen,
        theme,
        modals,
        loading,
        notifications,
        
        // Getters
        isModalOpen,
        isLoading,
        unreadNotifications,
        
        // Actions
        toggleSidebar,
        setTheme,
        openModal,
        closeModal,
        setLoading,
        addNotification,
        removeNotification,
        markNotificationAsRead,
        initialize,
    };
});
```

### Data Store with Caching

```typescript
// resources/js/stores/data.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface CacheItem<T> {
    data: T;
    timestamp: number;
    expiresAt: number;
}

export const useDataStore = defineStore('data', () => {
    // State
    const cache = ref<Record<string, CacheItem<any>>>({});
    const loading = ref<Record<string, boolean>>({});

    // Getters
    const isCached = computed(() => (key: string) => {
        const item = cache.value[key];
        return item && Date.now() < item.expiresAt;
    });

    const getCachedData = computed(() => (key: string) => {
        const item = cache.value[key];
        return item && Date.now() < item.expiresAt ? item.data : null;
    });

    // Actions
    const setCache = <T>(key: string, data: T, ttl: number = 300000) => { // 5 minutes default
        cache.value[key] = {
            data,
            timestamp: Date.now(),
            expiresAt: Date.now() + ttl,
        };
    };

    const clearCache = (key?: string) => {
        if (key) {
            delete cache.value[key];
        } else {
            cache.value = {};
        }
    };

    const fetchData = async <T>(
        key: string,
        url: string,
        options: any = {},
        ttl: number = 300000
    ): Promise<T> => {
        // Return cached data if available
        if (isCached.value(key)) {
            return getCachedData.value(key);
        }

        // Set loading state
        loading.value[key] = true;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    ...options.headers,
                },
                ...options,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Cache the data
            setCache(key, data, ttl);
            
            return data;
        } finally {
            loading.value[key] = false;
        }
    };

    const invalidateCache = (pattern: string) => {
        Object.keys(cache.value).forEach(key => {
            if (key.includes(pattern)) {
                delete cache.value[key];
            }
        });
    };

    return {
        // State
        cache,
        loading,
        
        // Getters
        isCached,
        getCachedData,
        
        // Actions
        setCache,
        clearCache,
        fetchData,
        invalidateCache,
    };
});
```

## Advanced Store Patterns

### Shopping Cart Store

```typescript
// resources/js/stores/cart.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

interface CartItem {
    id: number;
    name: string;
    price: number;
    quantity: number;
    image?: string;
    variant?: string;
}

export const useCartStore = defineStore('cart', () => {
    // State
    const items = ref<CartItem[]>([]);
    const isOpen = ref(false);

    // Getters
    const itemCount = computed(() => 
        items.value.reduce((total, item) => total + item.quantity, 0)
    );

    const totalPrice = computed(() => 
        items.value.reduce((total, item) => total + (item.price * item.quantity), 0)
    );

    const isEmpty = computed(() => items.value.length === 0);

    // Actions
    const addItem = (product: Omit<CartItem, 'quantity'>) => {
        const existingItem = items.value.find(item => 
            item.id === product.id && item.variant === product.variant
        );

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            items.value.push({ ...product, quantity: 1 });
        }

        saveToLocalStorage();
    };

    const removeItem = (id: number, variant?: string) => {
        items.value = items.value.filter(item => 
            !(item.id === id && item.variant === variant)
        );
        saveToLocalStorage();
    };

    const updateQuantity = (id: number, quantity: number, variant?: string) => {
        const item = items.value.find(item => 
            item.id === id && item.variant === variant
        );

        if (item) {
            if (quantity <= 0) {
                removeItem(id, variant);
            } else {
                item.quantity = quantity;
                saveToLocalStorage();
            }
        }
    };

    const clearCart = () => {
        items.value = [];
        saveToLocalStorage();
    };

    const openCart = () => {
        isOpen.value = true;
    };

    const closeCart = () => {
        isOpen.value = false;
    };

    const saveToLocalStorage = () => {
        localStorage.setItem('cart', JSON.stringify(items.value));
    };

    const loadFromLocalStorage = () => {
        const saved = localStorage.getItem('cart');
        if (saved) {
            try {
                items.value = JSON.parse(saved);
            } catch (error) {
                console.error('Failed to load cart from localStorage:', error);
                items.value = [];
            }
        }
    };

    // Initialize
    loadFromLocalStorage();

    return {
        // State
        items,
        isOpen,
        
        // Getters
        itemCount,
        totalPrice,
        isEmpty,
        
        // Actions
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        openCart,
        closeCart,
    };
});
```

### Form Store with Validation

```typescript
// resources/js/stores/forms.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface FormState {
    data: Record<string, any>;
    errors: Record<string, string>;
    processing: boolean;
    hasErrors: boolean;
}

export const useFormsStore = defineStore('forms', () => {
    // State
    const forms = ref<Record<string, FormState>>({});

    // Getters
    const getForm = computed(() => (name: string) => forms.value[name]);

    const hasFormErrors = computed(() => (name: string) => {
        const form = forms.value[name];
        return form ? Object.keys(form.errors).length > 0 : false;
    });

    // Actions
    const createForm = (name: string, initialData: Record<string, any> = {}) => {
        const form = useForm(initialData);
        
        forms.value[name] = {
            data: form.data,
            errors: form.errors,
            processing: form.processing,
            hasErrors: Object.keys(form.errors).length > 0,
        };

        // Watch for changes
        form.watch(() => {
            forms.value[name] = {
                data: form.data,
                errors: form.errors,
                processing: form.processing,
                hasErrors: Object.keys(form.errors).length > 0,
            };
        });

        return form;
    };

    const updateFormData = (name: string, data: Record<string, any>) => {
        const form = forms.value[name];
        if (form) {
            form.data = { ...form.data, ...data };
        }
    };

    const setFormErrors = (name: string, errors: Record<string, string>) => {
        const form = forms.value[name];
        if (form) {
            form.errors = errors;
            form.hasErrors = Object.keys(errors).length > 0;
        }
    };

    const clearFormErrors = (name: string) => {
        const form = forms.value[name];
        if (form) {
            form.errors = {};
            form.hasErrors = false;
        }
    };

    const resetForm = (name: string, data: Record<string, any> = {}) => {
        const form = forms.value[name];
        if (form) {
            form.data = data;
            form.errors = {};
            form.processing = false;
            form.hasErrors = false;
        }
    };

    const removeForm = (name: string) => {
        delete forms.value[name];
    };

    return {
        // State
        forms,
        
        // Getters
        getForm,
        hasFormErrors,
        
        // Actions
        createForm,
        updateFormData,
        setFormErrors,
        clearFormErrors,
        resetForm,
        removeForm,
    };
});
```

## Integration with Inertia.js

### Syncing Inertia Props with Pinia

```typescript
// resources/js/composables/useInertiaSync.ts
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import { useUIStore } from '@/stores/ui';

export const useInertiaSync = () => {
    const page = usePage();
    const authStore = useAuthStore();
    const uiStore = useUIStore();

    // Sync auth data from Inertia props
    watch(
        () => page.props.auth,
        (authData) => {
            if (authData?.user) {
                authStore.setUser(authData.user);
            }
        },
        { immediate: true }
    );

    // Sync flash messages
    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.message) {
                uiStore.addNotification({
                    type: 'success',
                    title: 'Success',
                    message: flash.message,
                });
            }
            if (flash?.error) {
                uiStore.addNotification({
                    type: 'error',
                    title: 'Error',
                    message: flash.error,
                });
            }
        },
        { immediate: true }
    );

    // Initialize UI state
    uiStore.initialize();
};
```

### Using Stores in Components

```vue
<!-- resources/js/pages/Dashboard.vue -->
<template>
    <AppLayout title="Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- User Profile Section -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-lg font-medium text-indigo-600">
                                        {{ authStore.userInitials }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Welcome, {{ authStore.user?.name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ authStore.user?.email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <button
                        @click="uiStore.openModal('createProject')"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow"
                    >
                        <h3 class="text-lg font-medium text-gray-900">Create Project</h3>
                        <p class="text-sm text-gray-500">Start a new project</p>
                    </button>
                    
                    <button
                        @click="uiStore.openModal('uploadFile')"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow"
                    >
                        <h3 class="text-lg font-medium text-gray-900">Upload File</h3>
                        <p class="text-sm text-gray-500">Add new files</p>
                    </button>
                    
                    <button
                        @click="cartStore.openCart()"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow relative"
                    >
                        <h3 class="text-lg font-medium text-gray-900">Shopping Cart</h3>
                        <p class="text-sm text-gray-500">View your items</p>
                        <span
                            v-if="cartStore.itemCount > 0"
                            class="absolute top-2 right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                        >
                            {{ cartStore.itemCount }}
                        </span>
                    </button>
                </div>

                <!-- Notifications -->
                <div v-if="uiStore.notifications.length > 0" class="space-y-2">
                    <div
                        v-for="notification in uiStore.notifications"
                        :key="notification.id"
                        class="bg-white p-4 rounded-lg shadow"
                        :class="{
                            'border-l-4 border-green-400': notification.type === 'success',
                            'border-l-4 border-red-400': notification.type === 'error',
                            'border-l-4 border-yellow-400': notification.type === 'warning',
                            'border-l-4 border-blue-400': notification.type === 'info',
                        }"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">
                                    {{ notification.title }}
                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{ notification.message }}
                                </p>
                            </div>
                            <button
                                @click="uiStore.removeNotification(notification.id)"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <CreateProjectModal />
        <UploadFileModal />
        <ShoppingCartModal />
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CreateProjectModal from '@/components/modals/CreateProjectModal.vue';
import UploadFileModal from '@/components/modals/UploadFileModal.vue';
import ShoppingCartModal from '@/components/modals/ShoppingCartModal.vue';
import { useAuthStore } from '@/stores/auth';
import { useUIStore } from '@/stores/ui';
import { useCartStore } from '@/stores/cart';
import { useInertiaSync } from '@/composables/useInertiaSync';

const authStore = useAuthStore();
const uiStore = useUIStore();
const cartStore = useCartStore();

// Initialize Inertia sync
useInertiaSync();

onMounted(() => {
    // Load any additional data needed for the dashboard
});
</script>
```

## Testing Pinia Stores

### Store Testing Setup

```typescript
// tests/stores/auth.test.ts
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
    router: {
        post: vi.fn(),
        put: vi.fn(),
    },
}));

describe('Auth Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should initialize with no user', () => {
        const store = useAuthStore();
        
        expect(store.user).toBeNull();
        expect(store.isAuthenticated).toBe(false);
    });

    it('should set user correctly', () => {
        const store = useAuthStore();
        const userData = {
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            role: 'user',
        };

        store.setUser(userData);

        expect(store.user).toEqual(userData);
        expect(store.isAuthenticated).toBe(true);
        expect(store.isUser).toBe(true);
        expect(store.isAdmin).toBe(false);
    });

    it('should generate user initials correctly', () => {
        const store = useAuthStore();
        const userData = {
            id: 1,
            name: 'John Doe Smith',
            email: 'john@example.com',
            role: 'user',
        };

        store.setUser(userData);

        expect(store.userInitials).toBe('JDS');
    });

    it('should handle logout', async () => {
        const store = useAuthStore();
        store.setUser({
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            role: 'user',
        });

        await store.logout();

        expect(store.user).toBeNull();
        expect(store.isAuthenticated).toBe(false);
    });
});
```

## Best Practices

### 1. Store Organization

```
stores/
├── auth.ts          # Authentication state
├── ui.ts            # UI state (modals, theme, etc.)
├── data.ts          # Cached data and API responses
├── cart.ts          # Shopping cart
├── forms.ts         # Form state management
└── modules/         # Feature-specific stores
    ├── projects.ts
    ├── users.ts
    └── settings.ts
```

### 2. State Management Patterns

```typescript
// Good: Clear separation of concerns
const useUserStore = defineStore('user', () => {
    // State
    const users = ref<User[]>([]);
    const currentUser = ref<User | null>(null);
    
    // Getters
    const activeUsers = computed(() => 
        users.value.filter(user => user.status === 'active')
    );
    
    // Actions
    const fetchUsers = async () => {
        // Implementation
    };
    
    return { users, currentUser, activeUsers, fetchUsers };
});

// Avoid: Mixing unrelated state
const useBadStore = defineStore('bad', () => {
    const users = ref([]);
    const cartItems = ref([]);
    const theme = ref('light');
    // Don't mix unrelated state in one store
});
```

### 3. Error Handling

```typescript
// resources/js/stores/base.ts
export const useBaseStore = defineStore('base', () => {
    const errors = ref<Record<string, string>>({});
    const loading = ref<Record<string, boolean>>({});

    const setError = (key: string, error: string) => {
        errors.value[key] = error;
    };

    const clearError = (key: string) => {
        delete errors.value[key];
    };

    const setLoading = (key: string, value: boolean) => {
        loading.value[key] = value;
    };

    return { errors, loading, setError, clearError, setLoading };
});
```

## When to Use Pinia vs Inertia Props

### Use Pinia When:
- ✅ State needs to persist across page navigations
- ✅ Multiple components need to share state
- ✅ You need reactive state updates
- ✅ Building complex client-side interactions
- ✅ Caching API responses
- ✅ Managing UI state (modals, forms, etc.)

### Use Inertia Props When:
- ✅ Initial page data from server
- ✅ SEO-critical content
- ✅ User authentication state
- ✅ Server-rendered data
- ✅ Data that changes with page navigation

## Common Pitfalls

1. **Over-using Pinia**: Don't store everything in Pinia; use Inertia props for initial data
2. **Not syncing with Inertia**: Ensure Pinia state stays in sync with server state
3. **Memory leaks**: Clean up watchers and subscriptions
4. **Circular dependencies**: Avoid stores depending on each other
5. **Not handling errors**: Always implement proper error handling in stores

This comprehensive guide shows how Pinia enhances Laravel 12 + Vue + Inertia.js applications by providing powerful client-side state management while maintaining the benefits of Inertia's server-side data flow.
