# Testing & Authentication

## Overview

Testing and authentication are critical aspects of modern web applications. This guide covers comprehensive testing strategies for Vue components, Laravel Sanctum integration with Inertia.js, building secure authentication flows, and protecting routes on both client and server sides.

## Vue Component Testing Strategies

### Testing Setup with Vitest

```typescript
// vitest.config.ts
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['./tests/setup.ts'],
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
```

### Test Setup File

```typescript
// tests/setup.ts
import { config } from '@vue/test-utils';
import { createPinia } from 'pinia';
import { vi } from 'vitest';

// Mock Inertia.js
vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            auth: {
                user: {
                    id: 1,
                    name: 'Test User',
                    email: 'test@example.com',
                },
            },
        },
    }),
    router: {
        visit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
    Link: {
        name: 'Link',
        template: '<a><slot /></a>',
        props: ['href', 'method', 'data', 'headers'],
    },
}));

// Global test configuration
config.global.plugins = [createPinia()];
```

### Basic Component Testing

```typescript
// tests/components/UserCard.test.ts
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import UserCard from '@/components/UserCard.vue';

describe('UserCard', () => {
    const mockUser = {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        avatar: 'https://example.com/avatar.jpg',
        status: 'active',
    };

    it('renders user information correctly', () => {
        const wrapper = mount(UserCard, {
            props: { user: mockUser },
        });

        expect(wrapper.text()).toContain('John Doe');
        expect(wrapper.text()).toContain('john@example.com');
        expect(wrapper.find('img').attributes('src')).toBe(mockUser.avatar);
    });

    it('displays correct status badge', () => {
        const wrapper = mount(UserCard, {
            props: { user: mockUser },
        });

        const statusBadge = wrapper.find('[data-testid="status-badge"]');
        expect(statusBadge.classes()).toContain('bg-green-100');
        expect(statusBadge.text()).toBe('active');
    });

    it('emits edit event when edit button is clicked', async () => {
        const wrapper = mount(UserCard, {
            props: { user: mockUser },
        });

        await wrapper.find('[data-testid="edit-button"]').trigger('click');
        
        expect(wrapper.emitted('edit')).toBeTruthy();
        expect(wrapper.emitted('edit')[0]).toEqual([mockUser]);
    });
});
```

### Form Component Testing

```typescript
// tests/components/UserForm.test.ts
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import UserForm from '@/components/UserForm.vue';

describe('UserForm', () => {
    it('renders form fields correctly', () => {
        const wrapper = mount(UserForm);

        expect(wrapper.find('input[name="name"]').exists()).toBe(true);
        expect(wrapper.find('input[name="email"]').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('validates required fields', async () => {
        const wrapper = mount(UserForm);

        await wrapper.find('form').trigger('submit');

        expect(wrapper.find('.error-message').exists()).toBe(true);
        expect(wrapper.text()).toContain('Name is required');
    });

    it('submits form with correct data', async () => {
        const wrapper = mount(UserForm);
        const mockSubmit = vi.fn();
        
        wrapper.vm.submit = mockSubmit;

        await wrapper.find('input[name="name"]').setValue('John Doe');
        await wrapper.find('input[name="email"]').setValue('john@example.com');
        await wrapper.find('form').trigger('submit');

        expect(mockSubmit).toHaveBeenCalledWith({
            name: 'John Doe',
            email: 'john@example.com',
        });
    });
});
```

### Testing with Inertia Forms

```typescript
// tests/components/InertiaForm.test.ts
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { useForm } from '@inertiajs/vue3';
import InertiaForm from '@/components/InertiaForm.vue';

// Mock useForm
vi.mock('@inertiajs/vue3', async () => {
    const actual = await vi.importActual('@inertiajs/vue3');
    return {
        ...actual,
        useForm: vi.fn(() => ({
            data: { name: '', email: '' },
            errors: {},
            processing: false,
            post: vi.fn(),
            put: vi.fn(),
            delete: vi.fn(),
        })),
    };
});

describe('InertiaForm', () => {
    it('handles form submission correctly', async () => {
        const mockPost = vi.fn();
        const mockUseForm = vi.mocked(useForm);
        
        mockUseForm.mockReturnValue({
            data: { name: 'John Doe', email: 'john@example.com' },
            errors: {},
            processing: false,
            post: mockPost,
            put: vi.fn(),
            delete: vi.fn(),
        });

        const wrapper = mount(InertiaForm);

        await wrapper.find('form').trigger('submit');

        expect(mockPost).toHaveBeenCalledWith('/users', expect.any(Object));
    });

    it('displays validation errors', () => {
        const mockUseForm = vi.mocked(useForm);
        
        mockUseForm.mockReturnValue({
            data: { name: '', email: '' },
            errors: { name: 'Name is required' },
            processing: false,
            post: vi.fn(),
            put: vi.fn(),
            delete: vi.fn(),
        });

        const wrapper = mount(InertiaForm);

        expect(wrapper.text()).toContain('Name is required');
    });
});
```

## Laravel Sanctum with Inertia

### Sanctum Configuration

```php
<?php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort()
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

### Authentication Controller

```php
<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
```

### Form Request Validation

```php
<?php
// app/Http/Requests/LoginRequest.php
namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
```

## Building Login and Registration Forms

### Login Component

```vue
<!-- resources/js/pages/Auth/Login.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <Link
                        :href="route('register')"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        create a new account
                    </Link>
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="Email address"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.password }"
                            placeholder="Password"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            v-model="form.remember"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <Link
                            :href="route('password.request')"
                            class="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Forgot your password?
                        </Link>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg
                                v-if="form.processing"
                                class="h-5 w-5 text-indigo-500 animate-spin"
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
                        </span>
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
```

### Registration Component

```vue
<!-- resources/js/pages/Auth/Register.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <Link
                        :href="route('login')"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        sign in to your existing account
                    </Link>
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            name="name"
                            type="text"
                            autocomplete="name"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Full name"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email Address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="Email address"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.password }"
                            placeholder="Password"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm Password
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Confirm password"
                        />
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Creating account...' : 'Create account' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
```

## Protecting Routes on Client and Server

### Server-Side Route Protection

```php
<?php
// routes/web.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    });
});
```

### Client-Side Route Protection

```typescript
// resources/js/composables/useAuth.ts
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useAuth = () => {
    const page = usePage();
    
    const user = computed(() => page.props.auth.user);
    const isAuthenticated = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.role === 'admin');
    
    const can = (permission: string) => {
        if (!user.value) return false;
        return user.value.permissions?.includes(permission) || false;
    };
    
    const hasRole = (role: string) => {
        if (!user.value) return false;
        return user.value.role === role;
    };
    
    return {
        user,
        isAuthenticated,
        isAdmin,
        can,
        hasRole,
    };
};
```

### Route Middleware Component

```vue
<!-- resources/js/components/RouteGuard.vue -->
<template>
    <div v-if="!isLoading">
        <slot v-if="hasAccess" />
        <div v-else class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Access Denied</h1>
                <p class="text-gray-600 mb-6">You don't have permission to access this page.</p>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Go to Dashboard
                </Link>
            </div>
        </div>
    </div>
    <div v-else class="min-h-screen flex items-center justify-center">
        <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-indigo-600"></div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAuth } from '@/composables/useAuth';

interface Props {
    requireAuth?: boolean;
    requireRole?: string;
    requirePermission?: string;
}

const props = withDefaults(defineProps<Props>(), {
    requireAuth: true,
});

const { user, isAuthenticated, hasRole, can } = useAuth();
const isLoading = ref(true);

const hasAccess = computed(() => {
    if (props.requireAuth && !isAuthenticated.value) {
        return false;
    }
    
    if (props.requireRole && !hasRole(props.requireRole)) {
        return false;
    }
    
    if (props.requirePermission && !can(props.requirePermission)) {
        return false;
    }
    
    return true;
});

onMounted(() => {
    // Simulate loading time for auth check
    setTimeout(() => {
        isLoading.value = false;
    }, 100);
});
</script>
```

### Usage in Pages

```vue
<!-- resources/js/pages/Admin/Dashboard.vue -->
<template>
    <RouteGuard require-role="admin">
        <AppLayout title="Admin Dashboard">
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <!-- Admin content -->
                </div>
            </div>
        </AppLayout>
    </RouteGuard>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import RouteGuard from '@/components/RouteGuard.vue';
</script>
```

## Testing Authentication Flows

### Feature Tests

```php
<?php
// tests/Feature/AuthTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_page()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
```

### Component Tests for Authentication

```typescript
// tests/components/LoginForm.test.ts
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import LoginForm from '@/components/LoginForm.vue';

describe('LoginForm', () => {
    it('renders login form correctly', () => {
        const wrapper = mount(LoginForm);

        expect(wrapper.find('input[name="email"]').exists()).toBe(true);
        expect(wrapper.find('input[name="password"]').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('validates email field', async () => {
        const wrapper = mount(LoginForm);

        await wrapper.find('input[name="email"]').setValue('invalid-email');
        await wrapper.find('form').trigger('submit');

        expect(wrapper.find('.error-message').exists()).toBe(true);
    });

    it('shows loading state during submission', async () => {
        const wrapper = mount(LoginForm);
        
        // Mock form processing state
        wrapper.vm.form.processing = true;
        await wrapper.vm.$nextTick();

        expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
        expect(wrapper.text()).toContain('Signing in...');
    });
});
```

## When to Use Each Strategy

### Use Component Testing When:
- Testing individual component behavior
- Validating form interactions
- Testing user interface logic
- Ensuring component props and events work correctly

### Use Feature Testing When:
- Testing complete user workflows
- Validating authentication flows
- Testing route protection
- Ensuring database interactions work correctly

### Use Route Protection When:
- Building multi-role applications
- Implementing permission-based access
- Protecting sensitive areas
- Ensuring proper user authorization

## Best Practices

1. **Test both success and failure cases**: Ensure your tests cover all scenarios
2. **Use factories for test data**: Create consistent, realistic test data
3. **Mock external dependencies**: Isolate your tests from external services
4. **Test accessibility**: Ensure your forms are accessible to all users
5. **Validate security**: Test for common security vulnerabilities
6. **Use descriptive test names**: Make it clear what each test is validating

## Common Pitfalls

1. **Not testing edge cases**: Always test boundary conditions and error states
2. **Hardcoding test data**: Use factories and fakers for dynamic test data
3. **Ignoring security testing**: Test for common vulnerabilities like CSRF, XSS
4. **Not testing accessibility**: Ensure your forms work with screen readers
5. **Over-mocking**: Don't mock everything; test real interactions where possible

This comprehensive guide covers testing and authentication strategies that will help you build secure, well-tested applications with Laravel 12, Inertia.js, and Vue.js.
