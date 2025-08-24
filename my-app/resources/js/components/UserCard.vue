<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
    Edit3, 
    Trash2, 
    Shield, 
    User, 
    Mail,
    Calendar,
    MoreVertical,
    CheckCircle,
    XCircle,
    Crown
} from 'lucide-vue-next';

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
    user: User;
}>();

const emit = defineEmits<{
    'user-updated': [];
    'user-deleted': [];
}>();

const isEditing = ref(false);

const roleColors = {
    admin: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
    moderator: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800',
    user: 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800/50 dark:text-gray-400 dark:border-gray-700',
};

const roleIcons = {
    admin: Crown,
    moderator: Shield,
    user: User,
};

const editForm = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role || 'user',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({});
const toggleForm = useForm({});

const handleUpdate = () => {
    if (!editForm.name.trim() || !editForm.email.trim()) return;
    
    editForm.put(`/users/${props.user.id}`, {
        onSuccess: () => {
            isEditing.value = false;
            emit('user-updated');
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

const handleDelete = () => {
    if (!confirm(`Are you sure you want to delete ${props.user.name}?`)) return;
    
    deleteForm.delete(`/users/${props.user.id}`, {
        onSuccess: () => {
            emit('user-deleted');
            router.reload();
        },
        onError: (errors) => {
            console.error('Failed to delete user:', errors);
            alert('Failed to delete user. Please try again.');
        }
    });
};

const toggleStatus = () => {
    toggleForm.patch(`/users/${props.user.id}/toggle-status`, {
        onSuccess: () => {
            emit('user-updated');
            router.reload();
        },
        onError: (errors) => {
            console.error('Failed to toggle user status:', errors);
            alert('Failed to toggle user status. Please try again.');
        }
    });
};

const cancelEdit = () => {
    isEditing.value = false;
    editForm.name = props.user.name;
    editForm.email = props.user.email;
    editForm.role = props.user.role || 'user';
    editForm.password = '';
    editForm.password_confirmation = '';
};

const RoleIcon = roleIcons[props.user.role as keyof typeof roleIcons] || User;
</script>

<template>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-lg overflow-hidden">
        <!-- Status Indicator -->
        <div :class="[
            'absolute top-0 left-0 right-0 h-1',
            user.is_verified ? 'bg-green-500' : 'bg-yellow-500'
        ]"></div>

        <!-- Content -->
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <img 
                        :src="user.avatar" 
                        :alt="user.name"
                        class="w-12 h-12 rounded-full border-2 border-gray-200 dark:border-gray-600"
                    />
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            <Mail class="w-3 h-3" />
                            {{ user.email }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-1">
                    <button
                        @click="isEditing = true"
                        class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                        title="Edit user"
                    >
                        <Edit3 class="w-4 h-4" />
                    </button>
                    <button
                        @click="handleDelete"
                        :disabled="deleteForm.processing"
                        class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50"
                        title="Delete user"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span :class="[
                        'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium border',
                        roleColors[user.role as keyof typeof roleColors] || roleColors.user
                    ]">
                        <RoleIcon class="w-3 h-3" />
                        {{ user.role || 'user' }}
                    </span>
                    
                    <div class="flex items-center gap-1">
                        <component 
                            :is="user.is_verified ? CheckCircle : XCircle"
                            :class="[
                                'w-4 h-4',
                                user.is_verified ? 'text-green-500' : 'text-yellow-500'
                            ]"
                        />
                        <span :class="[
                            'text-xs font-medium',
                            user.is_verified ? 'text-green-700 dark:text-green-400' : 'text-yellow-700 dark:text-yellow-400'
                        ]">
                            {{ user.is_verified ? 'Verified' : 'Pending' }}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <Calendar class="w-3 h-3" />
                    Joined {{ user.created_at }}
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    @click="toggleStatus"
                    :disabled="toggleForm.processing"
                    :class="[
                        'w-full px-3 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50',
                        user.is_verified 
                            ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/30'
                            : 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30'
                    ]"
                >
                    {{ toggleForm.processing ? 'Processing...' : (user.is_verified ? 'Deactivate' : 'Activate') }}
                </button>
            </div>
        </div>

        <!-- Edit Form Overlay -->
        <div v-if="isEditing" class="absolute inset-0 bg-white dark:bg-gray-800 rounded-xl p-4 overflow-y-auto z-10">
            <form @submit.prevent="handleUpdate" class="space-y-4 h-full">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit User</h4>
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <XCircle class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input
                        v-model="editForm.name"
                        type="text"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        v-model="editForm.email"
                        type="email"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select
                        v-model="editForm.role"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="user">User</option>
                        <option value="moderator">Moderator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password (optional)</label>
                    <input
                        v-model="editForm.password"
                        type="password"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input
                        v-model="editForm.password_confirmation"
                        type="password"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                </div>
                
                <div class="flex space-x-2 pt-2">
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="bg-blue-500 text-white px-3 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-colors text-sm flex-1"
                    >
                        {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm flex-1"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
