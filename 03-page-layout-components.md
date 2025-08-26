# Page & Layout Components

## Course Overview

This module covers the creation and management of page components and layouts in
Inertia.js applications.

---

## Creating Page Components

### Understanding Page Components

Page components in Inertia.js are Vue components that represent individual pages
or routes. They receive data from the server and render the page content.

### Basic Page Component Structure

**Simple Page Component:**

```vue
<!-- resources/js/Pages/Home.vue -->
<template>
    <div>
        <h1>Welcome to {{ app.name }}</h1>
        <p>This is the home page of your application.</p>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { app } = usePage().props
</script>
```

**Page with Data Props:**

```vue
<!-- resources/js/Pages/Users/Index.vue -->
<template>
    <div>
        <h1>Users ({{ users.total }})</h1>
        <div class="users-list">
            <div v-for="user in users.data" :key="user.id" class="user-card">
                <h3>{{ user.name }}</h3>
                <p>{{ user.email }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { users } = usePage().props
</script>
```

---

## Building Reusable Layouts

### Understanding Layout Components

Layout components provide the structural framework for your pages, including
headers, navigation, sidebars, and footers.

### Basic Layout Structure

**Simple Layout Component:**

```vue
<!-- resources/js/Layouts/AppLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">
                            {{ app.name }}
                        </h1>
                    </div>
                    
                    <nav class="flex space-x-4">
                        <Link href="/dashboard">Dashboard</Link>
                        <Link href="/users">Users</Link>
                    </nav>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'

const { app } = usePage().props
</script>
```

### Using Layouts with Pages

**Basic Layout Usage:**

```vue
<!-- resources/js/Pages/Dashboard.vue -->
<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Welcome to your dashboard!
                    </div>
                </div>
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

---

## Managing Persistent Layouts

### Understanding Persistent Layouts

Persistent layouts maintain their state and DOM elements when navigating between
pages, providing a smoother user experience.

### Implementing Persistent Layouts

**Using `defineOptions` (Vue 3.3+):**

```vue
<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})
</script>
```

---

## Implementing Responsive Design Patterns

### Responsive Design Principles

Responsive design ensures your application works well on all devices and screen
sizes.

### Responsive Layout Implementation

**Mobile-First CSS with Tailwind:**

```vue
<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile Navigation -->
        <div class="lg:hidden">
            <MobileNavigation />
        </div>
        
        <!-- Desktop Navigation -->
        <div class="hidden lg:block">
            <DesktopNavigation />
        </div>
        
        <!-- Main Content -->
        <main class="py-4 lg:py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>
```

**Responsive Grid Layouts:**

```vue
<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
        <!-- Content that adapts to screen size -->
    </div>
</template>
```

---

## Key Concepts Summary

1. **Page Components**: Single responsibility per page, receive server data
2. **Layout Components**: Provide structural framework and UI consistency
3. **Persistent Layouts**: Maintain state across page changes
4. **Responsive Design**: Mobile-first approach with flexible layouts

---

## Next Steps

After completing this module, you should:

1. Create page components with proper structure
2. Build reusable layouts with named slots
3. Implement persistent layouts for better performance
4. Design responsive interfaces for all devices
5. Be ready for the next module: Routing & Navigation
