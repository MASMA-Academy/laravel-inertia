<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Plus, X, User, Mail, Lock, Shield } from 'lucide-vue-next';

const emit = defineEmits<{
    'close': [];
    'user-added': [];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user' as const,
});

const handleSubmit = () => {
    if (!form.name.trim() || !form.email.trim() || !form.password.trim()) return;
    
    if (form.password !== form.password_confirmation) {
        alert('Passwords do not match');
        return;
    }
    
    form.post('/users', {
        onSuccess: () => {
            form.reset();
            emit('user-added');
            emit('close');
            // Reload the current page to show the new user
            router.reload();
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            if (errors) {
                const errorMessages = Object.values(errors).flat().join('\n');
                alert(`Validation errors:\n${errorMessages}`);
            }
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <Plus class="w-6 h-6" />
                Add New User
            </h3>
            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <X class="w-5 h-5" />
            </button>
        </div>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <User class="w-4 h-4" />
                        Full Name
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Enter full name"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <Mail class="w-4 h-4" />
                        Email Address
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Enter email address"
                        required
                    />
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <Lock class="w-4 h-4" />
                        Password
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Enter password"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                        <Lock class="w-4 h-4" />
                        Confirm Password
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Confirm password"
                        required
                    />
                </div>
            </div>
            
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-1">
                    <Shield class="w-4 h-4" />
                    Role
                </label>
                <select
                    v-model="form.role"
                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="user">User</option>
                    <option value="moderator">Moderator</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 disabled:opacity-50 transition-colors font-medium flex-1 flex items-center justify-center gap-2"
                >
                    <Plus class="w-4 h-4" />
                    {{ form.processing ? 'Creating User...' : 'Create User' }}
                </button>
                <button
                    type="button"
                    @click="$emit('close')"
                    class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium flex-1"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>
