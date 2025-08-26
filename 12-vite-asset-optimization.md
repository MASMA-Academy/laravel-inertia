# Vite & Asset Optimization

## Overview

Vite is a modern build tool that provides lightning-fast development experience and optimized production builds. In Laravel 12 with Vue starter kit, Vite serves as the primary asset bundler, replacing Laravel Mix and Webpack.

## Understanding Vite's Architecture

### Core Concepts

Vite operates on two distinct modes:

1. **Development Mode**: Uses native ES modules with esbuild for instant server start
2. **Production Mode**: Uses Rollup for optimized bundling

### Key Features

- **Hot Module Replacement (HMR)**: Instant updates without page refresh
- **Native ES Modules**: Leverages browser's native module system
- **Pre-bundling**: Optimizes dependencies using esbuild
- **Tree Shaking**: Eliminates unused code automatically
- **Code Splitting**: Automatic and manual code splitting strategies

### Vite vs Traditional Build Tools

```
Traditional (Webpack):
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Source Files  │───▶│   Webpack       │───▶│   Bundle        │
│   (ES6, Vue)    │    │   (Slow Start)  │    │   (Large)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘

Vite:
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Source Files  │───▶│   esbuild       │───▶│   Native ESM    │
│   (ES6, Vue)    │    │   (Instant)     │    │   (Fast)        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Laravel 12 Vite Configuration

### Default Configuration

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
```

### Advanced Configuration Options

```typescript
// vite.config.ts - Advanced setup
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',
                'resources/js/ssr.ts', // SSR entry point
            ],
            refresh: [
                'resources/views/**',
                'app/View/Components/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
            '@components': resolve(__dirname, 'resources/js/components'),
            '@pages': resolve(__dirname, 'resources/js/pages'),
            '@layouts': resolve(__dirname, 'resources/js/layouts'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', 'vue-router'],
                    utils: ['lodash', 'axios'],
                },
            },
        },
    },
});
```

## Hot Module Replacement (HMR)

### How HMR Works

HMR allows you to update modules in a running application without a full page reload, preserving application state.

```typescript
// HMR API usage in Vue components
if (import.meta.hot) {
    import.meta.hot.accept((newModule) => {
        // Handle module updates
        console.log('Module updated:', newModule);
    });
    
    import.meta.hot.dispose((data) => {
        // Cleanup before module replacement
        console.log('Module disposed:', data);
    });
}
```

### Vue HMR Integration

```vue
<!-- resources/js/components/Counter.vue -->
<template>
    <div>
        <h2>Count: {{ count }}</h2>
        <button @click="increment">Increment</button>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const count = ref(0);

const increment = () => {
    count.value++;
};

// HMR support
if (import.meta.hot) {
    import.meta.hot.accept();
}
</script>
```

### Laravel Blade Integration

```blade
{{-- resources/views/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body>
    @inertia
</body>
</html>
```

## Code Splitting Strategies

### Automatic Code Splitting

Vite automatically splits code based on dynamic imports:

```typescript
// resources/js/app.ts
import { createApp } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: (name) => {
        // Automatic code splitting for pages
        const pages = import.meta.glob('./pages/**/*.vue');
        return pages[`./pages/${name}.vue`]();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

### Manual Code Splitting

```typescript
// resources/js/composables/useAuth.ts
import { ref, computed } from 'vue';

// Lazy load authentication logic
export const useAuth = () => {
    const user = ref(null);
    const isAuthenticated = computed(() => !!user.value);
    
    return {
        user,
        isAuthenticated,
    };
};

// resources/js/pages/Login.vue
<script setup lang="ts">
import { defineAsyncComponent } from 'vue';

// Lazy load heavy components
const HeavyChart = defineAsyncComponent(() => 
    import('@components/HeavyChart.vue')
);

// Conditional loading
const loadAdminPanel = () => {
    return import('@components/AdminPanel.vue');
};
</script>
```

### Route-Based Code Splitting

```typescript
// resources/js/router.ts
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('@pages/Dashboard.vue'),
        },
        {
            path: '/users',
            component: () => import('@pages/Users/Index.vue'),
        },
        {
            path: '/settings',
            component: () => import('@pages/Settings.vue'),
        },
    ],
});
```

## Optimizing Asset Loading

### CSS Optimization

```typescript
// vite.config.ts - CSS optimization
export default defineConfig({
    css: {
        postcss: {
            plugins: [
                require('tailwindcss'),
                require('autoprefixer'),
            ],
        },
        preprocessorOptions: {
            scss: {
                additionalData: `@import "@/styles/variables.scss";`,
            },
        },
    },
    build: {
        cssCodeSplit: true, // Split CSS into separate files
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        return `css/[name]-[hash][extname]`;
                    }
                    return `assets/[name]-[hash][extname]`;
                },
            },
        },
    },
});
```

### Image Optimization

```typescript
// vite.config.ts - Image optimization
import { defineConfig } from 'vite';
import { ViteImageOptimize } from 'vite-plugin-imagemin';

export default defineConfig({
    plugins: [
        ViteImageOptimize({
            gifsicle: { optimizationLevel: 7 },
            mozjpeg: { quality: 80 },
            pngquant: { quality: [0.65, 0.8] },
            svgo: {
                plugins: [
                    { name: 'removeViewBox', active: false },
                    { name: 'removeEmptyAttrs', active: false },
                ],
            },
        }),
    ],
});
```

### Font Loading Optimization

```css
/* resources/css/app.css */
@font-face {
    font-family: 'Inter';
    src: url('/fonts/Inter-Regular.woff2') format('woff2');
    font-display: swap; /* Optimize font loading */
    font-weight: 400;
    font-style: normal;
}

@font-face {
    font-family: 'Inter';
    src: url('/fonts/Inter-Bold.woff2') format('woff2');
    font-display: swap;
    font-weight: 700;
    font-style: normal;
}
```

## Performance Monitoring

### Bundle Analysis

```bash
# Install bundle analyzer
npm install --save-dev rollup-plugin-visualizer

# Add to vite.config.ts
import { visualizer } from 'rollup-plugin-visualizer';

export default defineConfig({
    plugins: [
        // ... other plugins
        visualizer({
            filename: 'dist/stats.html',
            open: true,
            gzipSize: true,
            brotliSize: true,
        }),
    ],
});
```

### Performance Metrics

```typescript
// resources/js/utils/performance.ts
export const measurePerformance = (name: string, fn: () => void) => {
    const start = performance.now();
    fn();
    const end = performance.now();
    console.log(`${name} took ${end - start} milliseconds`);
};

// Usage in components
import { measurePerformance } from '@/utils/performance';

export default {
    mounted() {
        measurePerformance('Component Mount', () => {
            // Component initialization
        });
    },
};
```

## When to Use Each Strategy

### Use Automatic Code Splitting When:
- Building standard SPAs with multiple pages
- You want minimal configuration
- Development speed is prioritized

### Use Manual Code Splitting When:
- You have specific performance requirements
- You need fine-grained control over bundle sizes
- You're building complex applications with heavy dependencies

### Use CSS Code Splitting When:
- You have large CSS files
- Different pages use different styles
- You want to optimize critical CSS loading

## Best Practices

1. **Keep vendor chunks separate**: Separate third-party libraries from application code
2. **Use dynamic imports for heavy components**: Load components only when needed
3. **Optimize images**: Use appropriate formats and compression
4. **Monitor bundle sizes**: Regularly check and optimize bundle sizes
5. **Use tree shaking**: Ensure unused code is eliminated
6. **Implement proper caching**: Configure appropriate cache headers

## Common Pitfalls

1. **Over-splitting**: Too many small chunks can hurt performance
2. **Ignoring CSS optimization**: CSS can significantly impact load times
3. **Not monitoring bundle sizes**: Bundle sizes can grow unexpectedly
4. **Poor image optimization**: Large images can severely impact performance
5. **Inefficient dynamic imports**: Loading too many small modules can be counterproductive

## Laravel 12 Specific Considerations

### Vite Plugin Features

```typescript
// Laravel Vite plugin specific options
laravel({
    input: ['resources/css/app.css', 'resources/js/app.ts'],
    refresh: [
        'resources/views/**',
        'app/View/Components/**',
        'routes/**',
    ],
    buildDirectory: 'build', // Custom build directory
    hotFile: 'hot', // Custom hot file name
}),
```

### Environment-Specific Builds

```typescript
// vite.config.ts - Environment specific configuration
export default defineConfig(({ mode }) => {
    const isProduction = mode === 'production';
    
    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.ts'],
                refresh: !isProduction,
            }),
            vue(),
        ],
        build: {
            minify: isProduction ? 'terser' : false,
            sourcemap: !isProduction,
        },
    };
});
```

This comprehensive guide covers all aspects of Vite and asset optimization in Laravel 12 with Vue starter kit, providing practical examples and best practices for building performant applications.
