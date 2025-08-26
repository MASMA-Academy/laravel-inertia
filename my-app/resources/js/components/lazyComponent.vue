<script setup lang="ts">
import { defineAsyncComponent, ref, onMounted } from 'vue';

interface Props {
    componentName: string;
    fallback?: string;
}

const props = withDefaults(defineProps<Props>(), {
    fallback: 'Loading...'
});

const isVisible = ref(false);
const componentRef = ref<HTMLElement>();

// Lazy load component when it becomes visible
const LazyComponent = defineAsyncComponent({
    loader: () => import(`./ui/${props.componentName}.vue`),
    loadingComponent: {
        template: `<div class="flex items-center justify-center p-4">${props.fallback}</div>`
    },
    errorComponent: {
        template: '<div class="text-red-500">Failed to load component</div>'
    },
    delay: 200,
    timeout: 3000
});

// Intersection Observer for lazy loading
onMounted(() => {
    if (componentRef.value) {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    isVisible.value = true;
                    observer.disconnect();
                }
            },
            { threshold: 0.1 }
        );
        observer.observe(componentRef.value);
    }
});
</script>

<template>
    <div ref="componentRef">
        <LazyComponent v-if="isVisible" />
        <div v-else class="flex items-center justify-center p-4">
            {{ fallback }}
        </div>
    </div>
</template>