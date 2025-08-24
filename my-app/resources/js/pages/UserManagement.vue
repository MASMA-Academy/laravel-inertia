<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import UserCard from '../components/UserCard.vue';
import AddUserForm from '../components/AddUserForm.vue';
import { Plus, Grid3X3, List, Search, Users } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    is_verified: boolean;
    avatar: string;
    role?: string;
}

const props = defineProps<{
    users?: User[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: '/users',
    },
];

const showAddForm = ref(false);
const viewMode = ref<'grid' | 'list'>('grid');
const searchQuery = ref('');
const roleFilter = ref<string>('all');

const userAdded = () => {
    showAddForm.value = false;
};

const userUpdated = () => {
    // Handle user update
};

const userDeleted = () => {
    // Handle user deletion
};

const filteredUsers = computed(() => {
    if (!props.users) return [];
    
    let filtered = props.users;
    
    // Filter by search query
    if (searchQuery.value) {
        filtered = filtered.filter(user => 
            user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (user.role && user.role.toLowerCase().includes(searchQuery.value.toLowerCase()))
        );
    }
    
    // Filter by role
    if (roleFilter.value !== 'all') {
        filtered = filtered.filter(user => user.role === roleFilter.value);
    }
    
    return filtered;
});

const verifiedUsers = computed(() => 
    filteredUsers.value.filter(user => user.is_verified)
);

const unverifiedUsers = computed(() => 
    filteredUsers.value.filter(user => !user.is_verified)
);

const totalUsers = computed(() => props.users?.length || 0);
const activeUsers = computed(() => props.users?.filter(user => user.is_verified).length || 0);
const pendingUsers = computed(() => props.users?.filter(user => !user.is_verified).length || 0);
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <Users class="w-8 h-8" />
                        User Management
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Manage user accounts, roles, and permissions
                    </p>
                </div>
                
                <button 
                    @click="showAddForm = !showAddForm" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors font-medium w-full sm:w-auto"
                >
                    <Plus class="w-4 h-4 mr-2 inline" />
                    {{ showAddForm ? 'Cancel' : 'Add User' }}
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ totalUsers }}</p>
                        </div>
                        <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                            <Users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ activeUsers }}</p>
                        </div>
                        <div class="h-12 w-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ pendingUsers }}</p>
                        </div>
                        <div class="h-12 w-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Controls -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-80">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search users..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        />
                    </div>
                    
                    <select
                        v-model="roleFilter"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="all">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="moderator">Moderator</option>
                        <option value="user">User</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <button
                        @click="viewMode = 'grid'"
                        :class="[
                            'px-3 py-2 rounded-lg border transition-colors',
                            viewMode === 'grid' 
                                ? 'bg-blue-100 border-blue-300 text-blue-700 dark:bg-blue-900/20 dark:border-blue-700 dark:text-blue-300' 
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'
                        ]"
                    >
                        <Grid3X3 class="w-4 h-4" />
                    </button>
                    <button
                        @click="viewMode = 'list'"
                        :class="[
                            'px-3 py-2 rounded-lg border transition-colors',
                            viewMode === 'list' 
                                ? 'bg-blue-100 border-blue-300 text-blue-700 dark:bg-blue-900/20 dark:border-blue-700 dark:text-blue-300' 
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'
                        ]"
                    >
                        <List class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Add User Form -->
            <AddUserForm 
                v-if="showAddForm" 
                @close="showAddForm = false"
                @user-added="userAdded"
            />

            <!-- Verified Users Section -->
            <div v-if="verifiedUsers.length > 0" class="space-y-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Active Users</h2>
                    <div class="h-px flex-1 bg-gray-300 dark:bg-gray-600"></div>
                </div>
                
                <div :class="[
                    'gap-4',
                    viewMode === 'grid' 
                        ? 'grid auto-rows-min md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' 
                        : 'space-y-3'
                ]">
                    <UserCard 
                        v-for="user in verifiedUsers" 
                        :key="user.id" 
                        :user="user"
                        @user-updated="userUpdated"
                        @user-deleted="userDeleted"
                        :class="viewMode === 'list' ? 'max-w-none' : ''"
                    />
                </div>
            </div>

            <!-- Unverified Users Section -->
            <div v-if="unverifiedUsers.length > 0" class="space-y-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pending Users</h2>
                    <div class="h-px flex-1 bg-gray-300 dark:bg-gray-600"></div>
                </div>
                
                <div :class="[
                    'gap-4',
                    viewMode === 'grid' 
                        ? 'grid auto-rows-min md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' 
                        : 'space-y-3'
                ]">
                    <UserCard 
                        v-for="user in unverifiedUsers" 
                        :key="user.id" 
                        :user="user"
                        @user-updated="userUpdated"
                        @user-deleted="userDeleted"
                        :class="viewMode === 'list' ? 'max-w-none' : ''"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!users || users.length === 0" class="text-center py-16">
                <div class="mx-auto max-w-md">
                    <div class="mx-auto h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-4">
                        <Users class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">No users yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Get started by adding your first user to the system.
                    </p>
                    <button 
                        @click="showAddForm = true"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        <Plus class="w-4 h-4 mr-2 inline" />
                        Add Your First User
                    </button>
                </div>
            </div>

            <!-- Search Empty State -->
            <div v-else-if="filteredUsers.length === 0 && (searchQuery || roleFilter !== 'all')" class="text-center py-16">
                <div class="mx-auto max-w-md">
                    <Search class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" />
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">No results found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Try adjusting your search terms or filters.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
