<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Edit3, 
    Trash2, 
    Pin, 
    FileText, 
    CheckSquare, 
    Link, 
    Bell,
    MoreVertical 
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface DashboardItem {
    id: number;
    title: string;
    description?: string;
    type: 'note' | 'task' | 'link' | 'reminder';
    color: 'blue' | 'green' | 'red' | 'yellow' | 'purple' | 'orange';
    is_pinned: boolean;
    position: number;
}

const props = defineProps<{
    item: DashboardItem;
}>();

const emit = defineEmits<{
    'item-updated': [];
    'item-deleted': [];
}>();

const isEditing = ref(false);
const updating = ref(false);

const colorClasses = {
    blue: 'border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-950/20',
    green: 'border-green-200 bg-green-50/50 dark:border-green-800 dark:bg-green-950/20',
    red: 'border-red-200 bg-red-50/50 dark:border-red-800 dark:bg-red-950/20',
    yellow: 'border-yellow-200 bg-yellow-50/50 dark:border-yellow-800 dark:bg-yellow-950/20',
    purple: 'border-purple-200 bg-purple-50/50 dark:border-purple-800 dark:bg-purple-950/20',
    orange: 'border-orange-200 bg-orange-50/50 dark:border-orange-800 dark:bg-orange-950/20',
};

const typeIcons = {
    note: FileText,
    task: CheckSquare,
    link: Link,
    reminder: Bell,
};

const editForm = reactive({
    title: props.item.title,
    description: props.item.description || '',
    type: props.item.type,
    color: props.item.color,
    is_pinned: props.item.is_pinned,
});

const { put } = useForm(editForm);

const handleUpdate = () => {
    updating.value = true;
    put(route('dashboard.items.update', props.item.id), {
        onSuccess: () => {
            isEditing.value = false;
            updating.value = false;
            emit('item-updated');
        },
        onError: () => {
            updating.value = false;
        }
    });
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this item?')) {
        useForm().delete(route('dashboard.items.destroy', props.item.id), {
            onSuccess: () => {
                emit('item-deleted');
            }
        });
    }
};

const cancelEdit = () => {
    isEditing.value = false;
    editForm.title = props.item.title;
    editForm.description = props.item.description || '';
    editForm.type = props.item.type;
    editForm.color = props.item.color;
    editForm.is_pinned = props.item.is_pinned;
};

const TypeIcon = typeIcons[props.item.type];
</script>

<template>
    <Card :class="`transition-all duration-200 hover:shadow-lg ${colorClasses[item.color]}`">
        <CardHeader class="pb-3">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-2">
                    <TypeIcon class="w-4 h-4 text-muted-foreground" />
                    <CardTitle class="text-base line-clamp-2">{{ item.title }}</CardTitle>
                </div>
                
                <div class="flex items-center gap-1">
                    <Badge v-if="item.is_pinned" variant="secondary" class="text-xs">
                        <Pin class="w-3 h-3 mr-1" />
                        Pinned
                    </Badge>
                    
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                <MoreVertical class="w-4 h-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="isEditing = true">
                                <Edit3 class="w-4 h-4 mr-2" />
                                Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="handleDelete" class="text-destructive">
                                <Trash2 class="w-4 h-4 mr-2" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </CardHeader>
        
        <CardContent class="pt-0">
            <p v-if="item.description" class="text-sm text-muted-foreground line-clamp-3 mb-3">
                {{ item.description }}
            </p>
            
            <div class="flex items-center justify-between">
                <Badge variant="outline" class="text-xs capitalize">
                    {{ item.type }}
                </Badge>
                
                <div class="flex items-center gap-1">
                    <div :class="`w-3 h-3 rounded-full bg-${item.color}-500`"></div>
                </div>
            </div>
        </CardContent>

        <!-- Edit Form Overlay -->
        <div v-if="isEditing" class="absolute inset-0 bg-background/95 backdrop-blur-sm rounded-lg border-2 border-primary/20 p-4 overflow-y-auto z-10">
            <form @submit.prevent="handleUpdate" class="space-y-4 h-full">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Title</label>
                    <Input
                        v-model="editForm.title"
                        placeholder="Item title"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium">Description</label>
                    <Textarea
                        v-model="editForm.description"
                        placeholder="Description (optional)"
                        rows="3"
                    />
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Type</label>
                        <Select v-model="editForm.type">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="note">Note</SelectItem>
                                <SelectItem value="task">Task</SelectItem>
                                <SelectItem value="link">Link</SelectItem>
                                <SelectItem value="reminder">Reminder</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Color</label>
                        <Select v-model="editForm.color">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="blue">Blue</SelectItem>
                                <SelectItem value="green">Green</SelectItem>
                                <SelectItem value="red">Red</SelectItem>
                                <SelectItem value="yellow">Yellow</SelectItem>
                                <SelectItem value="purple">Purple</SelectItem>
                                <SelectItem value="orange">Orange</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <Checkbox
                        v-model="editForm.is_pinned"
                        id="pinned"
                    />
                    <label for="pinned" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Pin to top
                    </label>
                </div>
                
                <div class="flex space-x-2 pt-2">
                    <Button
                        type="submit"
                        :disabled="updating"
                        class="flex-1"
                    >
                        {{ updating ? 'Saving...' : 'Save Changes' }}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancelEdit"
                        class="flex-1"
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </Card>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>