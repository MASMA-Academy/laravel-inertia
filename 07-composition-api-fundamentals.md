# Composition API Fundamentals

## Course Overview

This module covers the Vue.js Composition API fundamentals, including reactive
state management, refs and reactive objects, lifecycle hooks, and creating
composable functions.

---

## Understanding Reactive State

### Reactive State Basics

**What is Reactive State?** Reactive state in Vue.js automatically updates the
UI when data changes. The Composition API provides `ref()` and `reactive()` to
create reactive state.

**Key Concepts:**

- `ref()` for primitive values
- `reactive()` for objects
- Automatic dependency tracking
- Deep reactivity

### Using `ref()` vs `reactive()`

**1. `ref()` for Primitive Values:**

```vue
<template>
  <div>
    <h1>Counter: {{ count }}</h1>
    <button @click="increment">Increment</button>
    <button @click="decrement">Decrement</button>
    
    <div class="mt-4">
      <input v-model="name" placeholder="Enter your name" />
      <p>Hello, {{ name || 'Guest' }}!</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

// ref() for primitive values
const count = ref(0)
const name = ref('')

const increment = () => {
  count.value++
}

const decrement = () => {
  count.value--
}
</script>
```

**2. `reactive()` for Objects:**

```vue
<template>
  <div>
    <h1>User Profile</h1>
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input 
          v-model="user.name" 
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input 
          v-model="user.email" 
          type="email"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Age</label>
        <input 
          v-model.number="user.age" 
          type="number"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
    </div>
    
    <div class="mt-6">
      <button 
        @click="saveUser"
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
      >
        Save User
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

// reactive() for objects
const user = reactive({
  name: '',
  email: '',
  age: 0
})

const saveUser = () => {
  console.log('Saving user:', user)
  // API call here
}
</script>
```

**3. When to Use Each:**

```typescript
// Use ref() for:
const count = ref(0); // Numbers
const name = ref(""); // Strings
const isVisible = ref(false); // Booleans
const items = ref([]); // Arrays (but reactive() is often better)

// Use reactive() for:
const user = reactive({ // Objects
    name: "",
    email: "",
    profile: {
        avatar: "",
        bio: "",
    },
});

const form = reactive({ // Form data
    username: "",
    password: "",
    confirmPassword: "",
});
```

---

## Working with Refs and Reactive Objects

### Template Refs

**Accessing DOM Elements:**

```vue
<template>
  <div>
    <input 
      ref="emailInput"
      v-model="email"
      type="email"
      placeholder="Enter email"
      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
    />
    
    <button 
      @click="focusEmail"
      class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
    >
      Focus Email Input
    </button>
    
    <div ref="statusContainer" class="mt-4 p-4 bg-gray-100 rounded-md">
      <p>Status: {{ status }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue'

const email = ref('')
const status = ref('Ready')

// Template refs
const emailInput = ref<HTMLInputElement>()
const statusContainer = ref<HTMLDivElement>()

const focusEmail = async () => {
  // Wait for DOM update
  await nextTick()
  
  if (emailInput.value) {
    emailInput.value.focus()
    status.value = 'Email input focused'
  }
}

// Access ref value in lifecycle
onMounted(() => {
  if (statusContainer.value) {
    console.log('Status container mounted:', statusContainer.value)
  }
})
</script>
```

### Reactive Objects Deep Dive

**1. Nested Object Reactivity:**

```vue
<template>
  <div>
    <h2>User Profile</h2>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">First Name</label>
        <input 
          v-model="user.profile.firstName" 
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Last Name</label>
        <input 
          v-model="user.profile.lastName" 
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Address</label>
        <input 
          v-model="user.profile.address.street" 
          type="text"
          placeholder="Street"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
        <input 
          v-model="user.profile.address.city" 
          type="text"
          placeholder="City"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
    </div>
    
    <div class="mt-6">
      <button 
        @click="updateUser"
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
      >
        Update User
      </button>
    </div>
    
    <div class="mt-4 p-4 bg-gray-100 rounded-md">
      <pre>{{ JSON.stringify(user, null, 2) }}</pre>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

const user = reactive({
  id: 1,
  profile: {
    firstName: 'John',
    lastName: 'Doe',
    address: {
      street: '123 Main St',
      city: 'Anytown',
      country: 'USA'
    }
  }
})

const updateUser = () => {
  // All nested properties are reactive
  user.profile.firstName = 'Jane'
  user.profile.address.city = 'New City'
  
  console.log('User updated:', user)
}
</script>
```

**2. Array Reactivity:**

```vue
<template>
  <div>
    <h2>Todo List</h2>
    
    <div class="space-y-4">
      <div class="flex space-x-2">
        <input 
          v-model="newTodo" 
          @keyup.enter="addTodo"
          type="text"
          placeholder="Add new todo"
          class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
        <button 
          @click="addTodo"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
        >
          Add
        </button>
      </div>
      
      <ul class="space-y-2">
        <li 
          v-for="(todo, index) in todos" 
          :key="todo.id"
          class="flex items-center justify-between p-3 bg-white border rounded-md"
        >
          <div class="flex items-center space-x-3">
            <input 
              v-model="todo.completed" 
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <span :class="{ 'line-through text-gray-500': todo.completed }">
              {{ todo.text }}
            </span>
          </div>
          
          <button 
            @click="removeTodo(index)"
            class="text-red-600 hover:text-red-800"
          >
            Delete
          </button>
        </li>
      </ul>
    </div>
    
    <div class="mt-4 p-4 bg-gray-100 rounded-md">
      <p>Total todos: {{ todos.length }}</p>
      <p>Completed: {{ completedCount }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'

const newTodo = ref('')

const todos = reactive([
  { id: 1, text: 'Learn Vue.js', completed: false },
  { id: 2, text: 'Build an app', completed: false },
  { id: 3, text: 'Deploy to production', completed: false }
])

const completedCount = computed(() => 
  todos.filter(todo => todo.completed).length
)

const addTodo = () => {
  if (newTodo.value.trim()) {
    todos.push({
      id: Date.now(),
      text: newTodo.value.trim(),
      completed: false
    })
    newTodo.value = ''
  }
}

const removeTodo = (index: number) => {
  todos.splice(index, 1)
}
</script>
```

---

## Lifecycle Hooks in the Composition API

### Understanding Lifecycle Hooks

**Lifecycle Hook Types:**

- `onMounted` - Component is mounted to DOM
- `onBeforeMount` - Before component is mounted
- `onUpdated` - After component updates
- `onBeforeUpdate` - Before component updates
- `onUnmounted` - Component is unmounted
- `onErrorCaptured` - Error handling

### Using Lifecycle Hooks

**1. Basic Lifecycle Usage:**

```vue
<template>
  <div>
    <h2>Lifecycle Demo</h2>
    <p>Component has been mounted for: {{ mountedTime }} seconds</p>
    <p>Update count: {{ updateCount }}</p>
    
    <button 
      @click="triggerUpdate"
      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
    >
      Trigger Update
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeMount, onUpdated, onBeforeUpdate, onUnmounted } from 'vue'

const mountedTime = ref(0)
const updateCount = ref(0)
let intervalId: number

onBeforeMount(() => {
  console.log('Component is about to be mounted')
})

onMounted(() => {
  console.log('Component is mounted to DOM')
  
  // Start timer
  intervalId = setInterval(() => {
    mountedTime.value++
  }, 1000)
})

onBeforeUpdate(() => {
  console.log('Component is about to update')
})

onUpdated(() => {
  console.log('Component has updated')
  updateCount.value++
})

onUnmounted(() => {
  console.log('Component is unmounted')
  
  // Clean up timer
  if (intervalId) {
    clearInterval(intervalId)
  }
})

const triggerUpdate = () => {
  // This will trigger onBeforeUpdate and onUpdated
  mountedTime.value = mountedTime.value
}
</script>
```

**2. Inertia.js Integration:**

```vue
<template>
  <div>
    <h2>User Dashboard</h2>
    
    <div v-if="isLoading" class="text-center py-8">
      <div class="spinner"></div>
      <p>Loading user data...</p>
    </div>
    
    <div v-else class="space-y-4">
      <div class="p-4 bg-white rounded-lg shadow">
        <h3 class="text-lg font-medium">{{ user.name }}</h3>
        <p class="text-gray-600">{{ user.email }}</p>
      </div>
      
      <div class="p-4 bg-white rounded-lg shadow">
        <h4 class="font-medium mb-2">Recent Activity</h4>
        <ul class="space-y-2">
          <li v-for="activity in recentActivity" :key="activity.id" class="text-sm text-gray-600">
            {{ activity.description }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const isLoading = ref(true)
const user = ref({})
const recentActivity = ref([])

onMounted(async () => {
  try {
    // Fetch user data
    const userResponse = await fetch('/api/user')
    user.value = await userResponse.json()
    
    // Fetch recent activity
    const activityResponse = await fetch('/api/user/activity')
    recentActivity.value = await activityResponse.json()
    
    isLoading.value = false
  } catch (error) {
    console.error('Failed to fetch user data:', error)
    isLoading.value = false
  }
})

// Listen for Inertia.js navigation events
onMounted(() => {
  router.on('navigate', () => {
    console.log('Navigation started')
  })
  
  router.on('finish', () => {
    console.log('Navigation completed')
  })
})

onUnmounted(() => {
  // Clean up event listeners
  router.off('navigate')
  router.off('finish')
})
</script>
```

---

## Creating Composable Functions

### Understanding Composables

**What are Composables?** Composables are functions that encapsulate and reuse
stateful logic. They follow the Composition API pattern and can be shared across
components.

**Benefits:**

- Reusable logic
- Better organization
- Easier testing
- Composition over inheritance

### Basic Composable Examples

**1. Counter Composable:**

```typescript
// resources/js/composables/useCounter.ts
import { computed, ref } from "vue";

export function useCounter(initialValue = 0) {
    const count = ref(initialValue);

    const increment = () => count.value++;
    const decrement = () => count.value--;
    const reset = () => count.value = initialValue;
    const double = computed(() => count.value * 2);

    return {
        count,
        increment,
        decrement,
        reset,
        double,
    };
}
```

**2. Using the Counter Composable:**

```vue
<template>
  <div>
    <h2>Counter: {{ count }}</h2>
    <p>Double: {{ double }}</p>
    
    <div class="space-x-2">
      <button 
        @click="increment"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
      >
        Increment
      </button>
      
      <button 
        @click="decrement"
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
      >
        Decrement
      </button>
      
      <button 
        @click="reset"
        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
      >
        Reset
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCounter } from '@/composables/useCounter'

const { count, increment, decrement, reset, double } = useCounter(10)
</script>
```

### Advanced Composables

**1. Form Validation Composable:**

```typescript
// resources/js/composables/useFormValidation.ts
import { computed, ref } from "vue";

interface ValidationRule {
    required?: boolean;
    minLength?: number;
    maxLength?: number;
    pattern?: RegExp;
    custom?: (value: any) => boolean | string;
}

interface ValidationRules {
    [key: string]: ValidationRule;
}

export function useFormValidation(rules: ValidationRules) {
    const errors = ref<Record<string, string>>({});
    const touched = ref<Record<string, boolean>>({});
    const dirty = ref<Record<string, boolean>>({});

    const validateField = (field: string, value: any) => {
        const rule = rules[field];
        if (!rule) return true;

        // Required validation
        if (rule.required && (!value || value.toString().trim() === "")) {
            errors.value[field] = `${field} is required`;
            return false;
        }

        // Min length validation
        if (
            rule.minLength && value && value.toString().length < rule.minLength
        ) {
            errors.value[field] =
                `${field} must be at least ${rule.minLength} characters`;
            return false;
        }

        // Max length validation
        if (
            rule.maxLength && value && value.toString().length > rule.maxLength
        ) {
            errors.value[field] =
                `${field} must be no more than ${rule.maxLength} characters`;
            return false;
        }

        // Pattern validation
        if (rule.pattern && value && !rule.pattern.test(value.toString())) {
            errors.value[field] = `${field} format is invalid`;
            return false;
        }

        // Custom validation
        if (rule.custom) {
            const result = rule.custom(value);
            if (result !== true) {
                errors.value[field] = typeof result === "string"
                    ? result
                    : `${field} is invalid`;
                return false;
            }
        }

        // Clear error if validation passes
        delete errors.value[field];
        return true;
    };

    const validateForm = (data: Record<string, any>) => {
        let isValid = true;

        Object.keys(rules).forEach((field) => {
            if (!validateField(field, data[field])) {
                isValid = false;
            }
        });

        return isValid;
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

**2. Using the Form Validation Composable:**

```vue
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div>
      <label for="username" class="block text-sm font-medium text-gray-700">
        Username
      </label>
      <input
        id="username"
        v-model="form.username"
        @blur="markFieldTouched('username')"
        @input="markFieldDirty('username')"
        type="text"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        :class="{ 'border-red-500': errors.username && touched.username }"
      />
      <p v-if="errors.username && touched.username" class="mt-2 text-sm text-red-600">
        {{ errors.username }}
      </p>
    </div>
    
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700">
        Email
      </label>
      <input
        id="email"
        v-model="form.email"
        @blur="markFieldTouched('email')"
        @input="markFieldDirty('email')"
        type="email"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        :class="{ 'border-red-500': errors.email && touched.email }"
      />
      <p v-if="errors.email && touched.email" class="mt-2 text-sm text-red-600">
        {{ errors.email }}
      </p>
    </div>
    
    <div>
      <label for="password" class="block text-sm font-medium text-gray-700">
        Password
      </label>
      <input
        id="password"
        v-model="form.password"
        @blur="markFieldTouched('password')"
        @input="markFieldDirty('password')"
        type="password"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        :class="{ 'border-red-500': errors.password && touched.password }"
      />
      <p v-if="errors.password && touched.password" class="mt-2 text-sm text-red-600">
        {{ errors.password }}
      </p>
    </div>
    
    <button
      type="submit"
      :disabled="!isFormValid"
      class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
    >
      Submit
    </button>
  </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useFormValidation } from '@/composables/useFormValidation'

const form = reactive({
  username: '',
  email: '',
  password: ''
})

const validationRules = {
  username: {
    required: true,
    minLength: 3,
    maxLength: 20
  },
  email: {
    required: true,
    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  },
  password: {
    required: true,
    minLength: 8,
    custom: (value: string) => {
      if (!/[A-Z]/.test(value)) return 'Password must contain at least one uppercase letter'
      if (!/[a-z]/.test(value)) return 'Password must contain at least one lowercase letter'
      if (!/\d/.test(value)) return 'Password must contain at least one number'
      return true
    }
  }
}

const {
  errors,
  touched,
  dirty,
  validateField,
  validateForm,
  markFieldTouched,
  markFieldDirty,
  hasErrors,
  isFormValid
} = useFormValidation(validationRules)

const handleSubmit = () => {
  if (validateForm(form)) {
    console.log('Form is valid:', form)
    // Submit form
  } else {
    console.log('Form has errors:', errors.value)
  }
}
</script>
```

---

## Key Concepts Summary

1. **Reactive State**: Use `ref()` for primitives, `reactive()` for objects
2. **Template Refs**: Access DOM elements and component instances
3. **Lifecycle Hooks**: Manage component lifecycle with Composition API
4. **Composables**: Reusable stateful logic functions
5. **Deep Reactivity**: Automatic tracking of nested object changes

---

## Next Steps

After completing this module, you should:

1. Understand when to use `ref()` vs `reactive()`
2. Work with template refs and DOM elements
3. Use lifecycle hooks effectively
4. Create and use composable functions
5. Be ready for the next module: Form Handling & Validation

---

## Additional Resources

- [Vue.js 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Vue.js 3 Lifecycle Hooks](https://vuejs.org/api/composition-api-lifecycle.html)
- [Vue.js 3 Template Refs](https://vuejs.org/guide/essentials/template-refs.html)
- [Vue.js 3 Composables](https://vuejs.org/guide/reusability/composables.html)
