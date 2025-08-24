<script setup lang="ts">
import { reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, X } from 'lucide-vue-next';

const emit = defineEmits<{
    'close': [];
    'item-added': [];
}>();

const form = reactive({
    title: '',
    description: '',
    type: 'note' as const,
    color: 'blue' as const,
});

const { post, processing } = useForm(form);

const handleSubmit = () => {
    post(route('dashboard.items.store'), {
        onSuccess: () => {
            // Reset form
            form.title = '';
            form.description = '';
            form.type = 'note';
            form.color = 'blue';
            
            emit('item-added');
        }
    });
};
</script>

<template>
    <Card class="border-dashed border-2 border-muted-foreground/25 hover:border-muted-foreground/50 transition-colors">
        <CardHeader class="pb-4">
            <div class="flex items-center justify-between">
                <CardTitle class="text-lg flex items-center gap-2">
                    <Plus class="w-5 h-5" />
                    Add New Item
                </CardTitle>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="$emit('close')"
                    class="h-8 w-8 p-0"
                >
                    <X class="w-4 h-4" />
                </Button>
            </div>
        </CardHeader>
        
        <CardContent>
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Title</label>
                    <Input
                        v-model="form.title"
                        placeholder="Enter item title"
                        required
                    />
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium">Description</label>
                    <Textarea
                        v-model="form.description"
                        placeholder="Add a description (optional)"
                        rows="3"
                    />
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Type</label>
                        <Select v-model="form.type">
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
                        <Select v-model="form.color">
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
                
                <div class="flex space-x-2 pt-2">
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="flex-1"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        {{ processing ? 'Adding...' : 'Add Item' }}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        @click="$emit('close')"
                        class="flex-1"
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>