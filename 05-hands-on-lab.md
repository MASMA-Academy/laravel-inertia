# Hands-on Lab: Building Your First Inertia App

## Course Overview

This hands-on lab will guide you through building a complete Inertia.js
application from scratch.

---

## Lab Objectives

By the end of this lab, you will have:

1. Set up a complete Laravel 12 + Inertia.js project
2. Implemented user authentication with Laravel Sanctum
3. Created a user management system with CRUD operations
4. Built responsive layouts and components
5. Implemented form validation and error handling

---

## Lab Setup

### Step 1: Create New Laravel Project with Starter Kit

````bash
# Create new Laravel project with Breeze already included
laravel new user-management-app --breeze --vue

# Navigate to project directory
cd user-management-app

# Install additional dependencies
composer require laravel/sanctum

# Run database migrations
php artisan migrate

# Install and build frontend dependencies
npm install
npm run build

### Step 2: Verify Inertia.js Configuration

**Note:** The Laravel starter kit with `--breeze --vue` flags automatically provides:
- Inertia.js middleware and configuration
- Authentication scaffolding (login, register, password reset)
- Basic layout components and pages
- Vue.js setup with Inertia.js
- Tailwind CSS configuration
- Vite build configuration
- User model and migrations

**Verify middleware registration in `app/Http/Kernel.php`:**

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\HandleInertiaRequests::class,
    ],
];
````

**Verify Vite configuration `vite.config.ts`:**

The Vite configuration is automatically set up with the Laravel starter kit.

**Verify Vue app entry point `resources/js/app.ts`:**

The Vue app entry point is automatically created with the Laravel starter kit.

```typescript
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

---

## Database Setup

### Step 3: Configure Database

**Update `.env` file:**

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

**Create SQLite database:**

```bash
touch database/database.sqlite
php artisan migrate
```

**Create user seeder:**

```bash
php artisan make:seeder UserSeeder
```

**Update `database/seeders/UserSeeder.php`:**

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::factory(10)->create();
    }
}
```

**Run seeder:**

```bash
php artisan db:seed --class=UserSeeder
```

---

## Backend Implementation

### Step 4: Create Controllers

**Create User Controller:**

```bash
php artisan make:controller UserController --resource
```

**Update `app/Http/Controllers/UserController.php`:**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }
}
```

**Create Auth Controller:**

```bash
php artisan make:controller AuthController
```

**Update `app/Http/Controllers/AuthController.php`:**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
}
```

### Step 5: Create Routes

**Update `routes/web.php`:**

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
});
```

---

## Frontend Implementation

### Step 6: Create Layout Components

**Create `resources/js/Layouts/AppLayout.vue`:**

```vue
<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">
                            User Management
                        </h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <Link href="/dashboard">Dashboard</Link>
                        <Link href="/users">Users</Link>
                        <button @click="logout">Logout</button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'

const logout = () => {
    router.post('/logout')
}
</script>
```

### Step 7: Create Page Components

**Create `resources/js/Pages/Home.vue`:**

```vue
<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <h1 class="text-2xl font-bold mb-4">Welcome to User Management</h1>
            <p class="text-gray-600 mb-6">
                A modern application built with Laravel 12, Inertia.js, and Vue.js.
            </p>
            
            <div class="flex space-x-4">
                <Link
                    href="/login"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                >
                    Login
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>
```

**Create `resources/js/Pages/Auth/Login.vue`:**

```vue
<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to your account
                </h2>
            </div>
            
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="rounded-md shadow-sm -space-y-px">
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Email address"
                    />
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Password"
                    />
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post('/login')
}
</script>
```

---

## Testing the Application

### Step 8: Run the Application

**Start the development server:**

```bash
php artisan serve
```

**In another terminal, start Vite:**

```bash
npm run dev
```

**Visit the application:**

- Open your browser and go to `http://localhost:8000`
- Use the credentials from the seeder:
  - Email: `admin@example.com`
  - Password: `password`

---

## Lab Deliverables

By the end of this lab, you should have:

1. ✅ Complete Laravel 12 + Inertia.js setup
2. ✅ User authentication system
3. ✅ Basic user management
4. ✅ Responsive layout and components
5. ✅ Working application

---

## Next Steps

After completing this lab:

1. Add more user management features
2. Implement user roles and permissions
3. Add testing with PHPUnit
4. Deploy to production

---

## Troubleshooting

**Common Issues:**

- Vite not working: Ensure you're running `npm run dev`
- Database errors: Check your `.env` file
- Component not found: Verify file paths match exactly

**Getting Help:**

- Check browser console for JavaScript errors
- Check Laravel logs in `storage/logs/laravel.log`
- Verify all dependencies are installed correctly
