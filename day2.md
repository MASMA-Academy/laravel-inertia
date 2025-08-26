# Day 2: Vue.js Components & State Management

## Course Overview

Day 2 focuses on building robust Vue.js components and implementing effective
state management patterns within the Laravel 12 + Inertia.js + Vue.js ecosystem.

---

## Morning Sessions

### 1. Vue.js Component Architecture

#### Single File Components (SFCs)

**Duration**: 90 minutes

**Core Concepts**:

- Understanding the `.vue` file structure
- Template, script, and style sections
- Vue 3 syntax and best practices
- Component lifecycle in Inertia.js context

**Laravel 12 + Vue Starter Kit Specifics**:

```vue
<!-- Example: resources/js/Pages/Dashboard.vue -->
<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dashboard
      </h2>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Component content -->
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

defineOptions({
  layout: AppLayout,
})
</script>
```

**Key Learning Points**:

- Using `<script setup>` syntax for cleaner component code
- TypeScript integration with Vue 3
- Inertia.js Head component for meta management
- Layout system integration

---

#### Component Communication Patterns

**Duration**: 90 minutes

**Patterns Covered**:

1. **Props Down, Events Up**
   - Parent-child communication
   - Event emission and handling
   - Prop validation and defaults

2. **Provide/Inject Pattern**
   - Deep component communication
   - Avoiding prop drilling
   - Context sharing across component trees

3. **Event Bus Alternatives**
   - Using Inertia.js events
   - Custom event handling
   - Global state considerations

**Laravel 12 Integration**:

```vue
<template>
  <div>
    <UserForm 
      :user="user" 
      @user-updated="handleUserUpdate"
      @validation-error="handleValidationError"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import UserForm from '@/Components/UserForm.vue'
import { router } from '@inertiajs/vue3'

const user = ref({
  name: '',
  email: ''
})

const handleUserUpdate = (updatedUser: any) => {
  router.post('/users', updatedUser, {
    onSuccess: () => {
      // Handle success
    },
    onError: (errors) => {
      // Handle validation errors
    }
  })
}
</script>
```

---

#### Slots and Templates

**Duration**: 60 minutes

**Advanced Component Patterns**:

- Default slots and named slots
- Scoped slots for data passing
- Dynamic slot content
- Slot fallbacks and conditional rendering

**Practical Examples**:

```vue
<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium">{{ title }}</h3>
        <slot name="actions" />
      </div>
      
      <div class="content">
        <slot />
      </div>
      
      <div v-if="$slots.footer" class="mt-4 pt-4 border-t">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
}

defineProps<Props>()
</script>
```

---

### 2. Composition API Fundamentals

#### Understanding Reactive State

**Duration**: 90 minutes

**Core Concepts**:

- `ref()` vs `reactive()` usage
- When to use each approach
- Deep reactivity considerations
- Performance implications

**Laravel 12 + Inertia.js Integration**:

```vue
<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

// Using Inertia.js useForm for reactive form handling
const form = useForm({
  name: '',
  email: '',
  role: 'user'
})

// Local reactive state
const isLoading = ref(false)
const searchQuery = ref('')

// Computed properties
const isFormValid = computed(() => {
  return form.name.length > 0 && form.email.includes('@')
})

// Reactive objects for complex state
const filters = reactive({
  status: 'active',
  dateRange: {
    start: null,
    end: null
  }
})
</script>
```

---

#### Working with Refs and Reactive Objects

**Duration**: 90 minutes

**Advanced Patterns**:

1. **Template Refs**
   - Accessing DOM elements
   - Component instance references
   - Dynamic ref handling

2. **Reactive Objects Deep Dive**
   - Nested object reactivity
   - Array reactivity considerations
   - Object destructuring pitfalls

**Practical Examples**:

```vue
<template>
  <div>
    <input 
      ref="emailInput"
      v-model="form.email"
      type="email"
      @focus="focusEmail"
    />
    
    <div ref="statusContainer" class="status">
      {{ statusMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue'

const emailInput = ref<HTMLInputElement>()
const statusContainer = ref<HTMLDivElement>()

const focusEmail = async () => {
  await nextTick()
  emailInput.value?.focus()
}

onMounted(() => {
  console.log(statusContainer.value)
})
</script>
```

---

#### Lifecycle Hooks in the Composition API

**Duration**: 60 minutes

**Hook Usage Patterns**:

- `onMounted` vs `onBeforeMount`
- `onUpdated` and `onBeforeUpdate`
- `onUnmounted` for cleanup
- `onErrorCaptured` for error handling

**Inertia.js Integration**:

```vue
<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

onMounted(() => {
  // Initialize component state
  // Set up event listeners
  // Load initial data
})

onUnmounted(() => {
  // Clean up event listeners
  // Cancel pending requests
  // Reset state
})

// Inertia.js navigation events
router.on('navigate', () => {
  // Handle navigation start
})

router.on('finish', () => {
  // Handle navigation complete
})
</script>
```

---

#### Creating Composable Functions

**Duration**: 90 minutes

**Composable Patterns**:

1. **State Management Composables**
   - Local state encapsulation
   - Shared state across components
   - State persistence strategies

2. **Utility Composables**
   - API calls and data fetching
   - Form validation helpers
   - UI state management

**Example Composable**:

```typescript
// resources/js/composables/useUser.ts
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";

export function useUser() {
    const users = ref([]);
    const isLoading = ref(false);

    const form = useForm({
        name: "",
        email: "",
        role: "user",
    });

    const fetchUsers = async () => {
        isLoading.value = true;
        try {
            const response = await fetch("/api/users");
            users.value = await response.json();
        } finally {
            isLoading.value = false;
        }
    };

    const createUser = () => {
        form.post("/users", {
            onSuccess: () => {
                form.reset();
                fetchUsers();
            },
        });
    };

    const userCount = computed(() => users.value.length);

    return {
        users,
        isLoading,
        form,
        fetchUsers,
        createUser,
        userCount,
    };
}
```

---

## Afternoon Sessions

### 3. Form Handling & Validation

#### Building Form Components

**Duration**: 90 minutes

**Form Architecture Patterns**:

1. **Controlled vs Uncontrolled Components**
   - Form state management approaches
   - Input binding strategies
   - Form submission handling

2. **Reusable Form Components**
   - Input components with validation
   - Form layout components
   - Error display components

**Laravel 12 + Inertia.js Form Handling**:

```vue
<!-- resources/js/Components/FormInput.vue -->
<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
    </label>
    
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="[
        'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        hasError ? 'border-red-500' : ''
      ]"
    />
    
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  id: string
  label: string
  type?: string
  modelValue: string
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text'
})

defineEmits<{
  'update:modelValue': [value: string]
}>()

const hasError = computed(() => !!props.error)
</script>
```

---

#### Client-side Validation Strategies

**Duration**: 90 minutes

**Validation Approaches**:

1. **Schema-based Validation**
   - Zod integration
   - Yup alternatives
   - Custom validation rules

2. **Real-time Validation**
   - Input change validation
   - Debounced validation
   - Field-level error display

3. **Form State Management**
   - Dirty state tracking
   - Pristine state management
   - Validation state persistence

**Advanced Validation Example**:

```typescript
// resources/js/composables/useValidation.ts
import { computed, ref } from "vue";
import { z } from "zod";

export function useValidation<T>(schema: z.ZodSchema<T>) {
    const errors = ref<Record<string, string>>({});
    const touched = ref<Record<string, boolean>>({});
    const dirty = ref<Record<string, boolean>>({});

    const validateField = (field: string, value: any) => {
        try {
            schema.pick({ [field]: true }).parse({ [field]: value });
            delete errors.value[field];
            return true;
        } catch (error) {
            if (error instanceof z.ZodError) {
                const fieldError = error.errors.find((e) =>
                    e.path[0] === field
                );
                if (fieldError) {
                    errors.value[field] = fieldError.message;
                }
            }
            return false;
        }
    };

    const validateForm = (data: Partial<T>) => {
        try {
            schema.parse(data);
            errors.value = {};
            return true;
        } catch (error) {
            if (error instanceof z.ZodError) {
                errors.value = {};
                error.errors.forEach((err) => {
                    errors.value[err.path[0] as string] = err.message;
                });
            }
            return false;
        }
    };

    const markFieldTouched = (field: string) => {
        touched.value[field] = true;
    };

    const markFieldDirty = (field: string) => {
        dirty.value[field] = true;
    };

    const hasErrors = computed(() => Object.keys(errors.value).length > 0);
    const isFormValid = computed(() => !hasErrors.value);

    return {
        errors,
        touched,
        dirty,
        validateField,
        validateForm,
        markFieldTouched,
        markFieldDirty,
        hasErrors,
        isFormValid,
    };
}
```

---

#### Server-side Validation with Laravel

**Duration**: 60 minutes

**Laravel 12 Validation Integration**:

1. **Form Request Classes**
   - Custom validation rules
   - Authorization logic
   - Error message customization

2. **Inertia.js Error Handling**
   - Automatic error binding
   - Error display strategies
   - Error state management

**Laravel Form Request Example**:

```php
// app/Http/Requests/StoreUserRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:user,admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.unique' => 'This email address is already taken.',
        ];
    }
}
```

---

### 4. Data Fetching & State Management

#### Inertia.js Data Fetching Patterns

**Duration**: 90 minutes

**Data Fetching Strategies**:

1. **Page Data**
   - Server-side data injection
   - Props and shared data
   - Data transformation

2. **API Calls**
   - Inertia.js vs direct fetch
   - Error handling patterns
   - Loading state management

**Data Fetching Examples**:

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3'

// Using Inertia.js for navigation with data
const fetchUsers = () => {
  router.get('/users', {}, {
    only: ['users', 'pagination'],
    preserveState: true,
    preserveScroll: true
  })
}

// Direct API calls for non-navigation data
const updateUserStatus = async (userId: number, status: string) => {
  try {
    const response = await fetch(`/api/users/${userId}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ status })
    })
    
    if (response.ok) {
      await fetchUsers()
    }
  } catch (error) {
    console.error('Failed to update user status:', error)
  }
}
</script>
```

---

#### Using Pinia for Complex State

**Duration**: 90 minutes

**Pinia Integration with Laravel 12**:

1. **Store Setup**
   - Store configuration
   - TypeScript integration
   - DevTools setup

2. **Store Patterns**
   - Modular store design
   - Store composition
   - State persistence

**Pinia Store Example**:

```typescript
// resources/js/stores/userStore.ts
import { defineStore } from "pinia";
import { computed, ref } from "vue";

export const useUserStore = defineStore("user", () => {
    // State
    const currentUser = ref(null);
    const users = ref([]);
    const isLoading = ref(false);

    // Getters
    const isAuthenticated = computed(() => !!currentUser.value);
    const isAdmin = computed(() => currentUser.value?.role === "admin");
    const userCount = computed(() => users.value.length);

    // Actions
    const setCurrentUser = (user: any) => {
        currentUser.value = user;
    };

    const fetchUsers = async () => {
        isLoading.value = true;
        try {
            const response = await fetch("/api/users");
            users.value = await response.json();
        } finally {
            isLoading.value = false;
        }
    };

    const addUser = (user: any) => {
        users.value.push(user);
    };

    const removeUser = (userId: number) => {
        const index = users.value.findIndex((u) => u.id === userId);
        if (index > -1) {
            users.value.splice(index, 1);
        }
    };

    return {
        // State
        currentUser,
        users,
        isLoading,

        // Getters
        isAuthenticated,
        isAdmin,
        userCount,

        // Actions
        setCurrentUser,
        fetchUsers,
        addUser,
        removeUser,
    };
});
```

---

## Hands-on Lab: Building Dynamic Form Components

### Lab Objectives

- Create reusable form components with validation
- Implement complex form state management
- Build dynamic form generation system
- Integrate with Laravel backend validation

### Lab Requirements

1. **Form Component Library**
   - Input components (text, email, select, checkbox)
   - Form layout components
   - Validation error display
   - Loading states

2. **Dynamic Form Builder**
   - JSON schema-based form generation
   - Conditional field display
   - Field dependencies
   - Custom validation rules

3. **Form State Management**
   - Form data persistence
   - Dirty state tracking
   - Validation state management
   - Submission handling

### Lab Deliverables

- Complete form component library
- Dynamic form builder implementation
- Form validation system
- Working form submission flow

### Lab Time: 2 hours

---

## Summary & Next Steps

### Key Takeaways

- Modern Vue 3 Composition API patterns
- Effective component communication strategies
- Robust form handling and validation
- Scalable state management with Pinia
- Laravel 12 + Inertia.js integration best practices

### Preparation for Day 3

- Review Vite configuration and build processes
- Understand performance optimization concepts
- Familiarize with testing frameworks
- Prepare for deployment considerations

### Additional Resources

- [Vue 3 Composition API Guide](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Laravel 12 Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
