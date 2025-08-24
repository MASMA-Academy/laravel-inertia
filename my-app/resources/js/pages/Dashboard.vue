<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardItem from '../components/DashboardItem.vue';
import AddItemForm from '../components/AddItemForm.vue';
import { Plus, Grid3X3, List, Search } from 'lucide-vue-next';

interface DashboardItemType {
    id: number;
    title: string;
    description?: string;
    type: 'note' | 'task' | 'link' | 'reminder';
    color: 'blue' | 'green' | 'red' | 'yellow' | 'purple' | 'orange';
    is_pinned: boolean;
    position: number;
}

const props = defineProps<{
    items?: DashboardItemType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const showAddForm = ref(false);
const viewMode = ref<'grid' | 'list'>('grid');
const searchQuery = ref('');

const itemAdded = () => {
    showAddForm.value = false;
};

const itemUpdated = () => {
    // Handle item update
};

const itemDeleted = () => {
    // Handle item deletion
};

const filteredItems = computed(() => {
    if (!props.items) return [];
    
    if (!searchQuery.value) return props.items;
    
    return props.items.filter(item => 
        item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (item.description && item.description.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        item.type.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const pinnedItems = computed(() => 
    filteredItems.value.filter(item => item.is_pinned)
);

const regularItems = computed(() => 
    filteredItems.value.filter(item => !item.is_pinned)
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Dashboard</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Manage your personal dashboard items and stay organized
                    </p>
                </div>
                
                <button 
                    @click="showAddForm = !showAddForm" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors font-medium w-full sm:w-auto"
                >
                    <Plus class="w-4 h-4 mr-2 inline" />
                    {{ showAddForm ? 'Cancel' : 'Add Item' }}
                </button>
            </div>

            <!-- Search and View Controls -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search items..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
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

            <!-- Add Item Form -->
            <AddItemForm 
                v-if="showAddForm" 
                @close="showAddForm = false"
                @item-added="itemAdded"
            />

            <!-- Pinned Items Section -->
            <div v-if="pinnedItems.length > 0" class="space-y-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pinned Items</h2>
                    <div class="h-px flex-1 bg-gray-300 dark:bg-gray-600"></div>
                </div>
                
                <div :class="[
                    'gap-4',
                    viewMode === 'grid' 
                        ? 'grid auto-rows-min md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' 
                        : 'space-y-3'
                ]">
                    <DashboardItem 
                        v-for="item in pinnedItems" 
                        :key="item.id" 
                        :item="item"
                        @item-updated="itemUpdated"
                        @item-deleted="itemDeleted"
                        :class="viewMode === 'list' ? 'max-w-none' : ''"
                    />
                </div>
            </div>

            <!-- Regular Items Section -->
            <div v-if="regularItems.length > 0" class="space-y-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">All Items</h2>
                    <div class="h-px flex-1 bg-gray-300 dark:bg-gray-600"></div>
                </div>
                
                <div :class="[
                    'gap-4',
                    viewMode === 'grid' 
                        ? 'grid auto-rows-min md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' 
                        : 'space-y-3'
                ]">
                    <DashboardItem 
                        v-for="item in regularItems" 
                        :key="item.id" 
                        :item="item"
                        @item-updated="itemUpdated"
                        @item-deleted="itemDeleted"
                        :class="viewMode === 'list' ? 'max-w-none' : ''"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!items || items.length === 0" class="text-center py-16">
                <div class="mx-auto max-w-md">
                    <div class="mx-auto h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-4">
                        <Plus class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">No items yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Get started by adding your first dashboard item. You can create notes, tasks, links, and reminders.
                    </p>
                    <button 
                        @click="showAddForm = true"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        <Plus class="w-4 h-4 mr-2 inline" />
                        Add Your First Item
                    </button>
                </div>
            </div>

            <!-- Search Empty State -->
            <div v-else-if="filteredItems.length === 0 && searchQuery" class="text-center py-16">
                <div class="mx-auto max-w-md">
                    <Search class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" />
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">No results found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Try adjusting your search terms or browse all items.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>