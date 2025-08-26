# Vue.js Component Architecture

## Course Overview

This module covers building robust Vue.js components within the Laravel 12 +
Inertia.js + Vue.js ecosystem.

---

## Single File Components (SFCs)

### Understanding SFCs

Single File Components (SFCs) are Vue.js components that encapsulate template,
script, and styles in a single `.vue` file.

**SFC Structure:**

```vue
<template>
  <!-- HTML template -->
</template>

<script setup lang="ts">
  // Component logic
</script>

<style scoped>
  /* Component styles */
</style>
```

### SFC Best Practices

**Component Organization:**

```vue
<!-- resources/js/Components/UserCard.vue -->
<template>
  <div class="user-card bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center space-x-4">
      <img 
        :src="user.avatar" 
        :alt="user.name"
        class="h-12 w-12 rounded-full"
      />
      <div class="flex-1 min-w-0">
        <h3 class="text-lg font-medium text-gray-900 truncate">
          {{ user.name }}
        </h3>
        <p class="text-sm text-gray-500 truncate">
          {{ user.email }}
        </p>
      </div>
      <span 
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        :class="roleClasses[user.role]"
      >
        {{ user.role }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
interface User {
  id: number
  name: string
  email: string
  avatar: string
  role: 'admin' | 'user' | 'moderator'
}

interface Props {
  user: User
}

defineProps<Props>()

const roleClasses = {
  admin: 'bg-red-100 text-red-800',
  user: 'bg-green-100 text-green-800',
  moderator: 'bg-yellow-100 text-yellow-800',
}
</script>
```

---

## Component Communication Patterns

### Props Down, Events Up

**Parent to Child Communication (Props):**

```vue
<template>
  <div>
    <UserList 
      :users="users"
      :loading="isLoading"
      @user-selected="handleUserSelection"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import UserList from '@/Components/UserList.vue'

const users = ref([])
const isLoading = ref(false)

const handleUserSelection = (user: any) => {
  console.log('User selected:', user)
}
</script>
```

**Child to Parent Communication (Events):**

```vue
<template>
  <div class="user-list">
    <div 
      v-for="user in users" 
      :key="user.id"
      class="user-item p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
      @click="$emit('user-selected', user)"
    >
      <h3 class="font-medium">{{ user.name }}</h3>
      <p class="text-sm text-gray-600">{{ user.email }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface User {
  id: number
  name: string
  email: string
}

interface Props {
  users: User[]
}

defineProps<Props>()

defineEmits<{
  'user-selected': [user: User]
}>()
</script>
```

### Provide/Inject Pattern

**Providing Data from Parent:**

```vue
<!-- resources/js/Layouts/AppLayout.vue -->
<template>
  <div class="min-h-screen bg-gray-100">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { provide, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

// Provide authentication context
provide('auth', computed(() => page.props.auth))

// Provide application settings
provide('app', computed(() => page.props.app))
</script>
```

**Injecting Data in Child Components:**

```vue
<!-- resources/js/Components/UserMenu.vue -->
<template>
  <div class="relative">
    <button class="flex items-center space-x-2">
      <img 
        :src="auth.user?.avatar" 
        :alt="auth.user?.name"
        class="h-8 w-8 rounded-full"
      />
      <span>{{ auth.user?.name }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { inject } from 'vue'

// Inject provided values
const auth = inject('auth')
</script>
```

---

## Slots and Templates

### Default Slots

**Basic Slot Usage:**

```vue
<!-- resources/js/Components/Card.vue -->
<template>
  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <slot />
    </div>
  </div>
</template>
```

**Using Default Slots:**

```vue
<template>
  <Card>
    <h3 class="text-lg font-medium text-gray-900">User Information</h3>
    <p class="mt-1 text-sm text-gray-500">
      This is the default content that will be rendered in the slot.
    </p>
  </Card>
</template>
```

### Named Slots

**Component with Named Slots:**

```vue
<!-- resources/js/Components/DataTable.vue -->
<template>
  <div class="bg-white shadow overflow-hidden sm:rounded-md">
    <!-- Header Slot -->
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
      <slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          {{ title }}
        </h3>
      </slot>
    </div>
    
    <!-- Table Content -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <slot name="header-row" />
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <slot name="body-row" v-for="item in items" :key="item.id" :item="item" />
        </tbody>
      </table>
    </div>
    
    <!-- Footer Slot -->
    <div v-if="$slots.footer" class="px-4 py-4 sm:px-6 border-t border-gray-200">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
interface Column {
  key: string
  label: string
}

interface Props {
  title: string
  columns: Column[]
  items: any[]
}

defineProps<Props>()
</script>
```

**Using Named Slots:**

```vue
<template>
  <DataTable 
    title="Users"
    :columns="columns"
    :items="users"
  >
    <template #header>
      <div class="flex justify-between items-center">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Users</h3>
        <button class="btn btn-primary">Add User</button>
      </div>
    </template>
    
    <template #header-row>
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
          Name
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
          Email
        </th>
      </tr>
    </template>
    
    <template #body-row="{ item }">
      <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
          {{ item.name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          {{ item.email }}
        </td>
      </tr>
    </template>
  </DataTable>
</template>
```

---

## Key Concepts Summary

1. **Single File Components**: Encapsulate template, script, and styles in one
   file
2. **Component Communication**: Use props down, events up pattern
3. **Provide/Inject**: Share data across component trees
4. **Slots**: Flexible content projection with default, named, and scoped slots

---

## Next Steps

After completing this module, you should:

1. Create well-structured SFCs with proper organization
2. Implement effective component communication patterns
3. Use slots for flexible component composition
4. Build reusable UI components
5. Be ready for the next module: Composition API Fundamentals
