# Inertia.js Cheatsheet

## Overview

This comprehensive cheatsheet covers all essential Inertia.js patterns, methods, and best practices based on the official documentation at [https://inertiajs.com/](https://inertiajs.com/). Perfect for quick reference during development.

## Installation & Setup

### Explanation
The installation process sets up Inertia.js to work seamlessly between your Laravel backend and Vue.js frontend. This creates the bridge that allows server-side data to flow to client-side components without traditional API endpoints.

### When to Use
- **New Laravel + Vue projects**: Start here when building a new SPA with Laravel and Vue
- **Converting existing apps**: When migrating from traditional Laravel apps to SPAs
- **Setting up development environment**: Initial project configuration

### Laravel Backend Setup

```bash
# Install Inertia server-side adapter
composer require inertiajs/inertia-laravel

# Publish middleware
php artisan inertia:middleware

# Add to Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\HandleInertiaRequests::class,
    ],
];
```
```

### Vue.js Frontend Setup

### Explanation
The frontend setup configures Vue.js to work with Inertia.js, enabling automatic page component resolution and seamless navigation between pages without full page reloads.

### When to Use
- **Vue 3 projects**: When using Vue 3 with Inertia.js
- **Page-based routing**: When you want automatic page component loading
- **SPA functionality**: When building single-page applications

```bash
# Install Inertia client-side adapter
npm install @inertiajs/vue3

# Install Vue 3 (if not already installed)
npm install vue@next
```

```typescript
// resources/js/app.ts
import { createApp } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
        return pages[`./pages/${name}.vue`] as DefineComponent;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

## Core Concepts

### Explanation
Core concepts form the foundation of Inertia.js, enabling programmatic navigation, form handling, and data flow between server and client without traditional API calls.

### When to Use
- **Programmatic navigation**: When you need to navigate based on user actions or conditions
- **Form submissions**: When handling user input and data submission
- **Dynamic content loading**: When loading data without full page refreshes

### Making Visits

### Explanation
Visits are the primary way to navigate between pages in Inertia.js. They allow you to make HTTP requests to your Laravel routes and automatically update the page with new data.

### When to Use
- **Navigation**: When moving between different pages in your app
- **Data submission**: When sending form data to the server
- **Dynamic content**: When loading new data without page refresh
- **Search/filtering**: When updating page content based on user input

```typescript
import { router } from '@inertiajs/vue3';

// Basic visit
router.visit('/users');

// Visit with data
router.visit('/users', {
    data: { search: 'john' },
    method: 'get',
});

// Visit with options
router.visit('/users', {
    method: 'post',
    data: { name: 'John' },
    preserveState: true,
    preserveScroll: true,
    replace: false,
    only: ['users'],
    headers: {
        'X-Custom-Header': 'value',
    },
});
```

### Link Component

### Explanation
The Link component provides declarative navigation in your Vue templates. It renders as an anchor tag but handles navigation through Inertia.js, preventing full page reloads.

### When to Use
- **Navigation links**: When creating clickable links in your UI
- **Form actions**: When you need links that act like form submissions
- **CRUD operations**: When creating, updating, or deleting resources
- **Search/filter links**: When linking to filtered or searched content

```vue
<template>
    <!-- Basic link -->
    <Link href="/users">Users</Link>
    
    <!-- Link with method -->
    <Link href="/users/1" method="delete">Delete User</Link>
    
    <!-- Link with data -->
    <Link href="/users" :data="{ search: 'john' }">Search Users</Link>
    
    <!-- Link with options -->
    <Link 
        href="/users" 
        method="post"
        :data="{ name: 'John' }"
        preserve-state
        preserve-scroll
        replace
        :only="['users']"
    >
        Create User
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
```

### Forms

### Explanation
Inertia.js forms provide a reactive way to handle form data, validation errors, and submission states. They automatically handle CSRF tokens and provide built-in error handling.

### When to Use
- **User input forms**: When collecting user data
- **Data submission**: When sending data to the server
- **Validation handling**: When you need server-side validation feedback
- **File uploads**: When handling file uploads
- **Complex forms**: When you need form state management

```vue
<template>
    <form @submit.prevent="submit">
        <input v-model="form.name" type="text" />
        <input v-model="form.email" type="email" />
        <button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Save' }}
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
});

const submit = () => {
    form.post('/users', {
        onSuccess: () => {
            // Handle success
        },
        onError: (errors) => {
            // Handle errors
        },
        onFinish: () => {
            // Always called
        },
    });
};
</script>
```

## Router Methods

### Explanation
Router methods provide programmatic control over navigation and HTTP requests. They offer shorthand methods for common HTTP operations and page management.

### When to Use
- **Programmatic navigation**: When you need to navigate based on JavaScript logic
- **API-like operations**: When you want to make HTTP requests without traditional APIs
- **Dynamic content loading**: When loading data based on user interactions
- **Form submissions**: When handling form data programmatically

### Visit Methods

### Explanation
Visit methods are shorthand functions for common HTTP operations. They provide a cleaner API compared to the generic `router.visit()` method.

### When to Use
- **RESTful operations**: When performing CRUD operations
- **Simple navigation**: When you don't need complex options
- **Quick data fetching**: When loading data with minimal configuration
- **Form submissions**: When submitting form data with specific HTTP methods

```typescript
import { router } from '@inertiajs/vue3';

// GET request
router.get('/users');

// POST request
router.post('/users', { name: 'John' });

// PUT request
router.put('/users/1', { name: 'John Updated' });

// PATCH request
router.patch('/users/1', { name: 'John Patched' });

// DELETE request
router.delete('/users/1');

// Reload current page
router.reload();

// Reload with only specific props
router.reload({ only: ['users'] });
```

### Visit Options

### Explanation
Visit options allow you to fine-tune the behavior of Inertia.js visits. They control state preservation, scroll behavior, data updates, and event handling.

### When to Use
- **State preservation**: When you want to maintain form data or UI state
- **Scroll management**: When you want to control scroll position after navigation
- **Partial updates**: When you only want to update specific parts of the page
- **Custom headers**: When you need to send additional HTTP headers
- **Event handling**: When you need to respond to navigation events
- **History management**: When you want to control browser history behavior

```typescript
router.visit('/users', {
    // HTTP method
    method: 'get' | 'post' | 'put' | 'patch' | 'delete',
    
    // Data to send
    data: { key: 'value' },
    
    // Preserve current page state
    preserveState: true,
    
    // Preserve scroll position
    preserveScroll: true,
    
    // Replace current history entry
    replace: true,
    
    // Only update specific props
    only: ['users', 'pagination'],
    
    // Except specific props
    except: ['flash'],
    
    // Custom headers
    headers: { 'X-Custom': 'value' },
    
    // Event callbacks
    onBefore: () => {},
    onStart: () => {},
    onProgress: (progress) => {},
    onFinish: () => {},
    onCancel: () => {},
    onSuccess: (page) => {},
    onError: (errors) => {},
});
```

## Form Methods

### Explanation
Form methods provide a reactive way to manage form state, handle submissions, and manage validation errors. They offer a clean API for form handling without manual state management.

### When to Use
- **Form state management**: When you need to track form data and validation state
- **Data submission**: When sending form data to the server
- **Validation handling**: When you need to display server-side validation errors
- **File uploads**: When handling file uploads with progress tracking
- **Complex forms**: When you need advanced form functionality

### Form Creation

### Explanation
Form creation sets up a reactive form object that tracks data, errors, and submission state. It provides methods for submitting data and managing form lifecycle.

### When to Use
- **New forms**: When creating forms for user input
- **Edit forms**: When editing existing data
- **Complex forms**: When you need form state management
- **File upload forms**: When handling file uploads

```typescript
import { useForm } from '@inertiajs/vue3';

// Basic form
const form = useForm({
    name: '',
    email: '',
});

// Form with initial data
const form = useForm({
    name: user.name,
    email: user.email,
});

// Form with options
const form = useForm({
    name: '',
    email: '',
}, {
    resetOnSuccess: false,
    preserveScroll: true,
});
```

### Form Methods

### Explanation
Form methods provide actions for submitting data, managing form state, and handling errors. They offer both HTTP method-specific submissions and utility methods for form management.

### When to Use
- **Data submission**: When sending form data to specific endpoints
- **Form state management**: When you need to reset or modify form data
- **Error handling**: When you need to manage validation errors
- **Data transformation**: When you need to modify data before submission

```typescript
const form = useForm({ name: '', email: '' });

// Submit methods
form.get('/users');
form.post('/users');
form.put('/users/1');
form.patch('/users/1');
form.delete('/users/1');

// Form utilities
form.reset(); // Reset to initial values
form.clearErrors(); // Clear all errors
form.setError('name', 'Name is required'); // Set specific error
form.setData('name', 'John'); // Set specific field
form.transform(data => ({ ...data, processed: true })); // Transform data
```

### Form Properties

### Explanation
Form properties provide reactive access to form state, including data, errors, submission status, and progress information. They automatically update when the form state changes.

### When to Use
- **UI state management**: When you need to show loading states or progress
- **Error display**: When you need to show validation errors to users
- **Form validation**: When you need to check form state before submission
- **Progress tracking**: When you need to show upload progress

```typescript
const form = useForm({ name: '', email: '' });

// Form state
form.data; // Current form data
form.errors; // Validation errors
form.processing; // Is form submitting
form.progress; // Upload progress (0-100)
form.wasSuccessful; // Was last submission successful
form.recentlySuccessful; // Was recently successful (with timeout)
```

## Page Object

### Explanation
The page object provides access to current page information, including URL, component name, props, and other metadata. It's the primary way to access server-side data in your Vue components.

### When to Use
- **Accessing server data**: When you need to use data passed from Laravel controllers
- **Page metadata**: When you need information about the current page
- **Asset management**: When you need to check asset versions
- **State management**: When you need to access remembered state

### Accessing Page Data

### Explanation
Page data access allows you to retrieve information about the current page and its props. This is how you access data that was passed from your Laravel controllers.

### When to Use
- **Displaying data**: When you need to show data from the server
- **Conditional rendering**: When you need to check page state
- **Navigation**: When you need page URL information
- **Asset management**: When you need to check asset versions

```typescript
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// Page properties
page.url; // Current URL
page.component; // Current component name
page.props; // Page props
page.version; // Asset version
page.remember; // Remembered state
```

### Page Props

### Explanation
Page props are the data passed from Laravel controllers to Vue components. They provide the bridge between server-side data and client-side rendering.

### When to Use
- **Data display**: When you need to show data from the server
- **Form initialization**: When you need to populate forms with existing data
- **Conditional rendering**: When you need to check data availability
- **State synchronization**: When you need to keep client state in sync with server state

```typescript
// In Laravel controller
return Inertia::render('Users/Index', [
    'users' => $users,
    'filters' => $filters,
]);

// In Vue component
const page = usePage();
const users = computed(() => page.props.users);
const filters = computed(() => page.props.filters);
```

## Shared Data

### Explanation
Shared data allows you to make certain data available across all pages in your Inertia.js application. This is useful for data that needs to be accessible globally, such as user authentication state or flash messages.

### When to Use
- **Global state**: When you need data available on every page
- **User authentication**: When you need user information across the app
- **Flash messages**: When you need to show success/error messages
- **App configuration**: When you need global app settings
- **Navigation state**: When you need shared navigation data

### Server-Side Sharing

### Explanation
Server-side sharing configures which data is automatically included with every Inertia.js response. This data is available in all Vue components without needing to pass it explicitly.

### When to Use
- **Authentication data**: When you need user info on every page
- **Flash messages**: When you need to show session messages
- **App settings**: When you need global configuration
- **Navigation data**: When you need shared navigation state

```php
<?php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ] : null,
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ]);
}
```

### Client-Side Access

### Explanation
Client-side access allows you to retrieve shared data in your Vue components. This data is automatically available on every page without needing to pass it explicitly.

### When to Use
- **User authentication**: When you need to check user login status
- **Flash messages**: When you need to display success/error messages
- **Global settings**: When you need to access app configuration
- **Navigation state**: When you need shared navigation data

```typescript
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// Access shared data
const user = computed(() => page.props.auth.user);
const flashMessage = computed(() => page.props.flash.message);
```

## Remembering State

### Explanation
State remembering allows you to preserve data across page visits. This is useful for maintaining form data, filters, or other state that should persist during navigation.

### When to Use
- **Form data preservation**: When you want to keep form data when navigating away and back
- **Filter persistence**: When you want to maintain search/filter state
- **UI state preservation**: When you want to maintain UI state like expanded panels
- **Multi-step forms**: When you need to preserve data across form steps

### Server-Side

### Explanation
Server-side state remembering allows you to specify which data should be preserved across requests. This data is automatically restored when the user returns to the page.

### When to Use
- **Form data**: When you want to preserve form input across navigation
- **List state**: When you want to preserve list filters or pagination
- **User preferences**: When you want to remember user settings

```php
<?php
// Remember data across requests
return Inertia::render('Users/Index', [
    'users' => $users,
])->with('remember', 'users');
```

### Client-Side

### Explanation
Client-side state remembering allows you to preserve component state and form data across page visits. This is useful for maintaining user input and UI state.

### When to Use
- **Form data**: When you want to preserve user input across navigation
- **Component state**: When you want to preserve UI state like expanded panels
- **User preferences**: When you want to remember user settings
- **Multi-step processes**: When you need to preserve data across steps

```typescript
// Remember form data
const form = useForm({
    name: '',
    email: '',
}, {
    remember: 'user-form', // Remember key
});

// Remember component state
const state = ref({ expanded: false });
router.remember('user-list-state', state);
```

## Asset Versioning

### Explanation
Asset versioning helps ensure users always have the latest version of your JavaScript and CSS assets. When assets are updated, Inertia.js can automatically reload the page to fetch the new assets.

### When to Use
- **Asset updates**: When you deploy new JavaScript/CSS files
- **Cache busting**: When you need to ensure users get fresh assets
- **Deployment management**: When you want to force asset updates
- **Development workflow**: When you want to ensure latest changes are loaded

### Server-Side

### Explanation
Server-side asset versioning allows you to specify how asset versions are determined. This version is sent with every Inertia.js response and used to detect when assets have changed.

### When to Use
- **Custom versioning**: When you need custom asset version logic
- **Build integration**: When you want to integrate with your build process
- **Environment-specific versions**: When you need different versions per environment

```php
<?php
// app/Http/Middleware/HandleInertiaRequests.php
public function version(Request $request): ?string
{
    return parent::version($request);
}
```

### Client-Side

### Explanation
Client-side asset versioning allows you to detect when assets have been updated and automatically reload the page to fetch the new assets.

### When to Use
- **Automatic updates**: When you want to automatically reload on asset changes
- **Deployment notifications**: When you want to notify users of updates
- **Cache management**: When you need to ensure fresh assets are loaded
- **Development workflow**: When you want to ensure latest changes are loaded

```typescript
// Check for asset updates
import { router } from '@inertiajs/vue3';

router.on('navigate', (event) => {
    if (event.detail.page.version !== window.assetVersion) {
        // Assets have been updated, reload page
        window.location.reload();
    }
});
```

## Event Handling

### Explanation
Event handling allows you to respond to Inertia.js navigation events. This is useful for showing loading states, handling errors, or performing side effects during navigation.

### When to Use
- **Loading states**: When you need to show loading indicators
- **Error handling**: When you need to handle navigation errors
- **Analytics**: When you need to track navigation events
- **Side effects**: When you need to perform actions during navigation
- **Debugging**: When you need to log navigation events

### Router Events

### Explanation
Router events provide hooks into the navigation lifecycle. You can listen for these events globally or on specific visits to respond to navigation state changes.

### When to Use
- **Global navigation tracking**: When you need to track all navigation events
- **Loading indicators**: When you need to show/hide loading states
- **Error handling**: When you need to handle navigation errors globally
- **Analytics**: When you need to track user navigation patterns

```typescript
import { router } from '@inertiajs/vue3';

// Global events
router.on('navigate', (event) => {
    console.log('Navigation started');
});

router.on('success', (event) => {
    console.log('Navigation successful');
});

router.on('error', (event) => {
    console.log('Navigation error');
});

router.on('finish', (event) => {
    console.log('Navigation finished');
});

// Visit-specific events
router.visit('/users', {
    onStart: () => console.log('Visit started'),
    onSuccess: () => console.log('Visit successful'),
    onError: () => console.log('Visit error'),
    onFinish: () => console.log('Visit finished'),
});
```

### Form Events

### Explanation
Form events provide hooks into the form submission lifecycle. They allow you to respond to form state changes and handle success/error scenarios.

### When to Use
- **Form validation**: When you need to handle validation errors
- **Loading states**: When you need to show form submission progress
- **Success handling**: When you need to perform actions on successful submission
- **Error handling**: When you need to handle submission errors
- **Progress tracking**: When you need to show upload progress

```typescript
const form = useForm({ name: '', email: '' });

form.post('/users', {
    onBefore: () => console.log('Before submit'),
    onStart: () => console.log('Submit started'),
    onProgress: (progress) => console.log('Progress:', progress),
    onSuccess: (page) => console.log('Submit successful'),
    onError: (errors) => console.log('Submit error'),
    onFinish: () => console.log('Submit finished'),
});
```

## Error Handling

### Explanation
Error handling allows you to gracefully handle navigation and form submission errors. This is crucial for providing a good user experience when things go wrong.

### When to Use
- **Network errors**: When you need to handle connection issues
- **Validation errors**: When you need to display form validation errors
- **Authentication errors**: When you need to handle login/session issues
- **Server errors**: When you need to handle 500-level errors
- **User feedback**: When you need to inform users about errors

### Global Error Handling

### Explanation
Global error handling allows you to catch and handle errors that occur during any Inertia.js navigation. This is useful for handling common error scenarios across your application.

### When to Use
- **Common errors**: When you need to handle errors that occur throughout your app
- **Authentication issues**: When you need to handle session expiration
- **Network issues**: When you need to handle connection problems
- **User notifications**: When you need to show error messages to users

```typescript
import { router } from '@inertiajs/vue3';

router.on('error', (event) => {
    if (event.detail.response.status === 419) {
        // CSRF token mismatch
        alert('Session expired. Please refresh the page.');
    }
});
```

### Form Error Handling

### Explanation
Form error handling allows you to display validation errors and handle form submission failures. This provides immediate feedback to users when their input is invalid.

### When to Use
- **Validation feedback**: When you need to show validation errors to users
- **Form submission errors**: When you need to handle submission failures
- **User experience**: When you need to provide clear error feedback
- **Data integrity**: When you need to ensure valid data submission

```vue
<template>
    <form @submit.prevent="submit">
        <input 
            v-model="form.name" 
            type="text"
            :class="{ 'border-red-500': form.errors.name }"
        />
        <div v-if="form.errors.name" class="text-red-500">
            {{ form.errors.name }}
        </div>
        
        <button type="submit" :disabled="form.processing">
            Submit
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
});

const submit = () => {
    form.post('/users', {
        onError: (errors) => {
            console.log('Validation errors:', errors);
        },
    });
};
</script>
```

## Progress Indicators

### Explanation
Progress indicators allow you to show users the status of ongoing requests. This is especially useful for file uploads and long-running operations.

### When to Use
- **File uploads**: When you need to show upload progress
- **Long operations**: When you need to show progress for time-consuming tasks
- **User feedback**: When you need to keep users informed about request status
- **Loading states**: When you need to show that something is happening

### Global Progress

### Explanation
Global progress tracking allows you to monitor the progress of all Inertia.js requests. This is useful for showing application-wide loading indicators.

### When to Use
- **Global loading indicators**: When you need to show loading state across the entire app
- **Progress bars**: When you need to show overall request progress
- **User feedback**: When you need to inform users about ongoing operations
- **Analytics**: When you need to track request performance

```typescript
import { router } from '@inertiajs/vue3';

router.on('progress', (event) => {
    const progress = event.detail.progress;
    console.log(`Progress: ${progress}%`);
});
```

### Form Progress

### Explanation
Form progress tracking allows you to show the progress of form submissions, especially useful for file uploads. The progress property provides a percentage value from 0 to 100.

### When to Use
- **File uploads**: When you need to show upload progress
- **Long form submissions**: When you need to show submission progress
- **User feedback**: When you need to keep users informed about form processing
- **Loading states**: When you need to show that form is being processed

```vue
<template>
    <form @submit.prevent="submit">
        <input v-model="form.name" type="text" />
        <button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Save' }}
        </button>
        
        <!-- Progress bar -->
        <div v-if="form.processing" class="progress-bar">
            <div 
                class="progress-fill" 
                :style="{ width: form.progress + '%' }"
            ></div>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/users');
};
</script>
```

## File Uploads

### Explanation
File uploads in Inertia.js allow you to handle file submissions through forms. The `forceFormData` option is required to properly handle file uploads.

### When to Use
- **Profile pictures**: When users need to upload profile images
- **Document uploads**: When users need to upload documents
- **Media uploads**: When users need to upload images, videos, or audio
- **Bulk uploads**: When users need to upload multiple files

### Basic File Upload

### Explanation
Basic file uploads allow users to select and upload a single file. The file is automatically included in the form data when submitted.

### When to Use
- **Single file uploads**: When users need to upload one file at a time
- **Profile images**: When users need to upload profile pictures
- **Document uploads**: When users need to upload documents
- **Simple uploads**: When you need basic file upload functionality

```vue
<template>
    <form @submit.prevent="submit">
        <input 
            type="file" 
            @input="form.photo = $event.target.files[0]"
        />
        <button type="submit" :disabled="form.processing">
            Upload
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    photo: null,
});

const submit = () => {
    form.post('/upload', {
        forceFormData: true, // Required for file uploads
    });
};
</script>
```

### Multiple File Upload

### Explanation
Multiple file uploads allow users to select and upload multiple files at once. The files are stored in an array and sent together in the form submission.

### When to Use
- **Bulk uploads**: When users need to upload multiple files at once
- **Gallery uploads**: When users need to upload multiple images
- **Document collections**: When users need to upload multiple documents
- **Batch processing**: When you need to process multiple files together

```vue
<template>
    <form @submit.prevent="submit">
        <input 
            type="file" 
            multiple
            @input="form.photos = $event.target.files"
        />
        <button type="submit" :disabled="form.processing">
            Upload
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    photos: [],
});

const submit = () => {
    form.post('/upload', {
        forceFormData: true,
    });
};
</script>
```

## Partial Reloads

### Explanation
Partial reloads allow you to update only specific parts of your page without reloading everything. This is useful for updating dynamic content while preserving other page state.

### When to Use
- **Dynamic content**: When you need to update specific parts of the page
- **Real-time updates**: When you need to refresh data without full page reload
- **Performance optimization**: When you want to minimize data transfer
- **User experience**: When you want to preserve user's current state

### Reloading Specific Props

### Explanation
Reloading specific props allows you to update only certain data on the page while preserving other data and state. This is more efficient than full page reloads.

### When to Use
- **Data updates**: When you need to refresh specific data
- **List updates**: When you need to update lists or tables
- **Filter updates**: When you need to update filtered content
- **Performance**: When you want to minimize unnecessary data loading

```typescript
import { router } from '@inertiajs/vue3';

// Reload only specific props
router.reload({ only: ['users'] });

// Reload except specific props
router.reload({ except: ['flash'] });

// Reload with data
router.reload({ 
    only: ['users'],
    data: { search: 'john' }
});
```

### Conditional Reloads

### Explanation
Conditional reloads allow you to choose what to reload based on specific conditions or user actions. This gives you fine-grained control over what gets updated.

### When to Use
- **User actions**: When you want to reload based on user interactions
- **Data dependencies**: When you need to reload related data
- **Performance optimization**: When you want to reload only what's necessary
- **User experience**: When you want to give users control over what gets refreshed

```vue
<template>
    <div>
        <button @click="refreshUsers">Refresh Users</button>
        <button @click="refreshAll">Refresh All</button>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const refreshUsers = () => {
    router.reload({ only: ['users'] });
};

const refreshAll = () => {
    router.reload();
};
</script>
```

## Common Patterns

### Explanation
Common patterns are reusable solutions for typical Inertia.js scenarios. These patterns help you build consistent and maintainable applications.

### When to Use
- **Consistent UI**: When you want to maintain consistent user interface patterns
- **Code reusability**: When you want to avoid repeating common code
- **Best practices**: When you want to follow proven solutions
- **Team development**: When you want to establish common patterns across your team

### Modal Management

### Explanation
Modal management allows you to show/hide modal dialogs for forms or content. This pattern is useful for creating, editing, or viewing data without navigating away from the current page.

### When to Use
- **Form dialogs**: When you need to show forms in a modal
- **Data editing**: When you need to edit data without page navigation
- **Confirmation dialogs**: When you need to confirm user actions
- **Content display**: When you need to show additional content in a modal

```vue
<template>
    <div>
        <button @click="showModal = true">Open Modal</button>
        
        <div v-if="showModal" class="modal">
            <form @submit.prevent="submit">
                <input v-model="form.name" type="text" />
                <button type="submit">Save</button>
                <button type="button" @click="showModal = false">Cancel</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showModal = ref(false);
const form = useForm({ name: '' });

const submit = () => {
    form.post('/users', {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>
```

### Search with Debouncing

### Explanation
Search with debouncing allows you to implement real-time search functionality while preventing excessive API calls. The debounce function delays the search request until the user stops typing.

### When to Use
- **Real-time search**: When you need to search as users type
- **Performance optimization**: When you want to reduce API calls
- **User experience**: When you want to provide responsive search
- **Large datasets**: When you're searching through large amounts of data

```vue
<template>
    <div>
        <input 
            v-model="search" 
            type="text" 
            placeholder="Search users..."
        />
        <div v-if="users.length">
            <div v-for="user in users" :key="user.id">
                {{ user.name }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const search = ref('');
const users = ref([]);

const debouncedSearch = debounce((value) => {
    router.get('/users', { search: value }, {
        preserveState: true,
        preserveScroll: true,
        only: ['users'],
    });
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});
</script>
```

### Pagination

### Explanation
Pagination allows you to display large datasets across multiple pages. Inertia.js works seamlessly with Laravel's pagination to provide smooth navigation between pages.

### When to Use
- **Large datasets**: When you have too much data to display on one page
- **Performance**: When you want to load data in chunks
- **User experience**: When you want to provide easy navigation through data
- **Database optimization**: When you want to limit database queries

```vue
<template>
    <div>
        <div v-for="user in users.data" :key="user.id">
            {{ user.name }}
        </div>
        
        <div class="pagination">
            <Link 
                v-for="link in users.links" 
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                :class="{ 'active': link.active }"
            />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const users = computed(() => page.props.users);
</script>
```

## Best Practices

### Explanation
Best practices help you build more maintainable, performant, and reliable Inertia.js applications. Following these practices ensures consistent code quality and better user experience.

### When to Use
- **New projects**: When starting a new Inertia.js project
- **Team development**: When working with a team to maintain consistency
- **Code quality**: When you want to ensure high-quality code
- **Maintenance**: When you want to make your code easier to maintain

### 1. Use TypeScript

### Explanation
TypeScript provides type safety and better developer experience. It helps catch errors at compile time and provides better IDE support.

### When to Use
- **All projects**: TypeScript should be used in all Inertia.js projects
- **Team development**: When working with multiple developers
- **Large applications**: When building complex applications
- **Error prevention**: When you want to catch errors early

```typescript
// Define page props interface
interface PageProps {
    users: {
        data: User[];
        links: PaginationLink[];
    };
    filters: {
        search?: string;
    };
}

// Use typed page
const page = usePage<PageProps>();
```

### 2. Handle Loading States

### Explanation
Loading states provide feedback to users when operations are in progress. This improves user experience by clearly indicating when something is happening.

### When to Use
- **Form submissions**: When users submit forms
- **Navigation**: When navigating between pages
- **Data loading**: When loading data from the server
- **User feedback**: When you want to keep users informed

```vue
<template>
    <button @click="submit" :disabled="form.processing">
        {{ form.processing ? 'Saving...' : 'Save' }}
    </button>
</template>
```

### 3. Preserve State When Appropriate

### Explanation
State preservation allows you to maintain user's current state during navigation. This is especially useful for search, filters, and form data.

### When to Use
- **Search operations**: When users are searching or filtering
- **Form data**: When you want to preserve form input
- **Scroll position**: When you want to maintain scroll position
- **User experience**: When you want to provide seamless navigation

```typescript
// Preserve state for search/filter operations
router.get('/users', { search: 'john' }, {
    preserveState: true,
    preserveScroll: true,
});
```

### 4. Use Partial Reloads

### Explanation
Partial reloads allow you to update only specific parts of your page, improving performance and user experience by minimizing data transfer.

### When to Use
- **Data updates**: When you only need to update specific data
- **Performance**: When you want to minimize server requests
- **User experience**: When you want to preserve other page state
- **Real-time updates**: When you need to refresh specific content

```typescript
// Only reload what's necessary
router.reload({ only: ['users'] });
```

### 5. Handle Errors Gracefully

### Explanation
Error handling ensures that users receive clear feedback when something goes wrong. This improves user experience and helps users understand what needs to be fixed.

### When to Use
- **Form validation**: When displaying validation errors
- **Network errors**: When handling connection issues
- **Server errors**: When handling server-side errors
- **User feedback**: When you need to inform users about problems

```vue
<template>
    <div v-if="form.errors.name" class="error">
        {{ form.errors.name }}
    </div>
</template>
```

## Troubleshooting

### Explanation
Troubleshooting helps you identify and resolve common issues that arise when working with Inertia.js. Understanding these common problems and their solutions will save you time during development.

### When to Use
- **Development issues**: When you encounter problems during development
- **Debugging**: When you need to identify the cause of issues
- **Production problems**: When issues occur in production
- **Learning**: When you want to understand common pitfalls

### Common Issues

### Explanation
Common issues are problems that developers frequently encounter when working with Inertia.js. Knowing these issues and their solutions will help you resolve problems quickly.

### When to Use
- **Problem solving**: When you encounter these specific issues
- **Prevention**: When you want to avoid these common problems
- **Debugging**: When you need to identify the root cause
- **Learning**: When you want to understand potential issues

1. **CSRF Token Mismatch**
```typescript
// Ensure CSRF token is included
router.post('/users', data, {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
});
```

2. **File Upload Issues**
```typescript
// Use forceFormData for file uploads
form.post('/upload', {
    forceFormData: true,
});
```

3. **State Not Preserving**
```typescript
// Check preserveState option
router.visit('/users', {
    preserveState: true,
});
```

4. **Props Not Updating**
```typescript
// Use only/except to control updates
router.reload({ only: ['users'] });
```

This cheatsheet provides a comprehensive reference for all Inertia.js features and patterns, based on the official documentation at [https://inertiajs.com/](https://inertiajs.com/). Keep it handy for quick reference during development!
