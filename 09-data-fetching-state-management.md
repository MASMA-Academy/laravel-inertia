# Data Fetching & State Management

## Course Overview

This module covers data fetching strategies, state management patterns, and
building scalable data architectures in Inertia.js applications.

---

## Data Fetching Strategies

### Server-side Data Preparation

**Controller Data Preparation:**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'stats' => [
                'total_users' => User::count(),
                'total_posts' => Post::count(),
            ],
            'recent_posts' => Post::with('author')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'author' => $post->author->name,
                        'created_at' => $post->created_at->diffForHumans(),
                    ];
                }),
        ];
        
        return Inertia::render('Dashboard/Index', $data);
    }
}
```

**Using Data in Vue Components:**

```vue
<template>
  <div class="dashboard">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900">Total Users</h3>
        <p class="text-3xl font-bold text-indigo-600">{{ stats.total_users }}</p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900">Total Posts</h3>
        <p class="text-3xl font-bold text-green-600">{{ stats.total_posts }}</p>
      </div>
    </div>
    
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent Posts</h3>
      </div>
      
      <div class="divide-y divide-gray-200">
        <div 
          v-for="post in recentPosts" 
          :key="post.id"
          class="px-6 py-4 hover:bg-gray-50"
        >
          <h4 class="text-sm font-medium text-gray-900">{{ post.title }}</h4>
          <p class="text-sm text-gray-500">
            By {{ post.author }} • {{ post.created_at }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

const { stats, recentPosts } = usePage().props
</script>
```

### Client-side Data Fetching

**API Integration Composable:**

```typescript
// resources/js/composables/useApi.ts
import { computed, ref } from "vue";

interface ApiState<T> {
    data: T | null;
    loading: boolean;
    error: string | null;
}

export function useApi<T>() {
    const state = ref<ApiState<T>>({
        data: null,
        loading: false,
        error: null,
    });

    const fetchData = async (url: string) => {
        state.value.loading = true;
        state.value.error = null;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            state.value.data = result.data;
            return result;
        } catch (error) {
            state.value.error = error instanceof Error
                ? error.message
                : "An error occurred";
            throw error;
        } finally {
            state.value.loading = false;
        }
    };

    return {
        state: computed(() => state.value),
        fetchData,
    };
}
```

---

## State Management Patterns

### Local Component State

**Simple State Management:**

```vue
<template>
  <div class="user-management">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
      <button @click="showCreateModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-md">
        Add User
      </button>
    </div>
    
    <!-- Search and Filters -->
    <div class="mb-6">
      <input
        v-model="filters.search"
        type="text"
        placeholder="Search users..."
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
      />
      
      <select v-model="filters.role" class="mt-2 rounded-md border-gray-300 shadow-sm">
        <option value="">All Roles</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Role
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in filteredUsers" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <img :src="user.avatar" :alt="user.name" class="h-10 w-10 rounded-full" />
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="roleClasses[user.role]">
                {{ user.role }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                Edit
              </button>
              <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'

interface User {
  id: number
  name: string
  email: string
  role: string
  avatar: string
}

// Local state
const users = ref<User[]>([])
const showCreateModal = ref(false)

// Filters
const filters = ref({
  search: '',
  role: '',
})

// Computed properties
const filteredUsers = computed(() => {
  let filtered = users.value
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search)
    )
  }
  
  if (filters.value.role) {
    filtered = filtered.filter(user => user.role === filters.value.role)
  }
  
  return filtered
})

// CSS classes
const roleClasses = {
  user: 'bg-green-100 text-green-800',
  admin: 'bg-red-100 text-red-800',
}

// Methods
const loadUsers = async () => {
  try {
    const response = await fetch('/api/users')
    const data = await response.json()
    users.value = data.users
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const editUser = (user: User) => {
  // Handle edit
}

const deleteUser = async (user: User) => {
  if (confirm(`Are you sure you want to delete ${user.name}?`)) {
    try {
      await fetch(`/api/users/${user.id}`, { method: 'DELETE' })
      await loadUsers()
    } catch (error) {
      console.error('Failed to delete user:', error)
    }
  }
}

onMounted(() => {
  loadUsers()
})
</script>
```

---

## Key Concepts Summary

1. **Data Fetching**: Server-side preparation and client-side API integration
2. **State Management**: Local component state vs global Pinia stores
3. **Data Flow**: Unidirectional data flow with reactive updates
4. **Performance**: Computed properties and efficient filtering
5. **Error Handling**: Loading states and error management

---

## Next Steps

After completing this module, you should:

1. Implement efficient data fetching strategies
2. Use Pinia for global state management
3. Build scalable data architectures
4. Handle loading and error states
5. Be ready for the next module: Advanced Component Patterns
