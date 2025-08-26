# Deployment & Performance Optimization

## Overview

Deployment and performance optimization are critical for production-ready Laravel SPA applications. This guide covers SPA performance best practices, lazy loading strategies, production builds with Vite, server configuration, and monitoring techniques to ensure optimal user experience.

## SPA Performance Best Practices

### Core Web Vitals Optimization

Core Web Vitals are key metrics that measure user experience on web pages:

- **Largest Contentful Paint (LCP)**: Loading performance
- **First Input Delay (FID)**: Interactivity
- **Cumulative Layout Shift (CLS)**: Visual stability

```typescript
// resources/js/utils/performance.ts
export const measureCoreWebVitals = () => {
    // LCP measurement
    new PerformanceObserver((entryList) => {
        const entries = entryList.getEntries();
        const lastEntry = entries[entries.length - 1];
        console.log('LCP:', lastEntry.startTime);
    }).observe({ entryTypes: ['largest-contentful-paint'] });

    // FID measurement
    new PerformanceObserver((entryList) => {
        const entries = entryList.getEntries();
        entries.forEach((entry) => {
            console.log('FID:', entry.processingStart - entry.startTime);
        });
    }).observe({ entryTypes: ['first-input'] });

    // CLS measurement
    let clsValue = 0;
    new PerformanceObserver((entryList) => {
        for (const entry of entryList.getEntries()) {
            if (!entry.hadRecentInput) {
                clsValue += entry.value;
            }
        }
        console.log('CLS:', clsValue);
    }).observe({ entryTypes: ['layout-shift'] });
};
```

### Critical Resource Optimization

```vue
<!-- resources/js/layouts/AppLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Critical CSS inline -->
        <style>
            .critical-styles {
                /* Inline critical CSS for above-the-fold content */
                .header { height: 64px; background: #1f2937; }
                .nav { display: flex; align-items: center; }
            }
        </style>
        
        <!-- Preload critical resources -->
        <link rel="preload" href="/fonts/Inter-Regular.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/images/hero-bg.jpg" as="image">
        
        <!-- DNS prefetch for external resources -->
        <link rel="dns-prefetch" href="//fonts.googleapis.com">
        <link rel="dns-prefetch" href="//cdn.example.com">
        
        <header class="header">
            <nav class="nav">
                <slot name="navigation" />
            </nav>
        </header>
        
        <main class="main">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';

onMounted(() => {
    // Load non-critical CSS after page load
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/css/non-critical.css';
    document.head.appendChild(link);
});
</script>
```

### Image Optimization Strategy

```vue
<!-- resources/js/components/OptimizedImage.vue -->
<template>
    <picture>
        <source
            v-if="webpSupported"
            :srcset="webpSrcset"
            type="image/webp"
        />
        <source
            :srcset="fallbackSrcset"
            :type="fallbackType"
        />
        <img
            :src="fallbackSrc"
            :alt="alt"
            :width="width"
            :height="height"
            :loading="loading"
            :class="className"
            @load="onLoad"
            @error="onError"
        />
    </picture>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Props {
    src: string;
    alt: string;
    width?: number;
    height?: number;
    loading?: 'lazy' | 'eager';
    className?: string;
    sizes?: string;
}

const props = withDefaults(defineProps<Props>(), {
    loading: 'lazy',
    sizes: '100vw',
});

const webpSupported = ref(false);

const webpSrcset = computed(() => {
    if (!webpSupported.value) return '';
    return generateSrcset(props.src, 'webp');
});

const fallbackSrcset = computed(() => {
    return generateSrcset(props.src, 'jpg');
});

const fallbackSrc = computed(() => {
    return `${props.src}?w=${props.width}&h=${props.height}&f=jpg&q=80`;
});

const fallbackType = computed(() => {
    const extension = props.src.split('.').pop();
    return `image/${extension}`;
});

const generateSrcset = (src: string, format: string) => {
    const sizes = [320, 640, 768, 1024, 1280, 1920];
    return sizes
        .map(size => `${src}?w=${size}&f=${format}&q=80 ${size}w`)
        .join(', ');
};

const onLoad = () => {
    // Image loaded successfully
};

const onError = () => {
    // Handle image load error
    console.error('Failed to load image:', props.src);
};

onMounted(() => {
    // Check WebP support
    const webp = new Image();
    webp.onload = webp.onerror = () => {
        webpSupported.value = webp.height === 2;
    };
    webp.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
});
</script>
```

## Lazy Loading Components and Routes

### Route-Based Lazy Loading

```typescript
// resources/js/router.ts
import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: () => import('@pages/Dashboard.vue'),
            meta: { preload: true }, // Preload critical routes
        },
        {
            path: '/users',
            component: () => import('@pages/Users/Index.vue'),
            meta: { preload: false },
        },
        {
            path: '/settings',
            component: () => import('@pages/Settings.vue'),
            meta: { preload: false },
        },
        {
            path: '/admin',
            component: () => import('@pages/Admin/Dashboard.vue'),
            meta: { 
                preload: false,
                requiresAuth: true,
                role: 'admin'
            },
        },
    ],
});

// Preload critical routes
router.beforeEach((to, from, next) => {
    if (to.meta.preload && !to.matched.length) {
        // Preload the route component
        import(`@pages/${to.name}.vue`);
    }
    next();
});

export default router;
```

### Component Lazy Loading

```vue
<!-- resources/js/pages/Dashboard.vue -->
<template>
    <div class="dashboard">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Critical components loaded immediately -->
            <StatsCard
                v-for="stat in criticalStats"
                :key="stat.id"
                :stat="stat"
            />
        </div>
        
        <!-- Lazy load heavy components -->
        <Suspense>
            <template #default>
                <HeavyChart :data="chartData" />
            </template>
            <template #fallback>
                <div class="flex items-center justify-center h-64">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
            </template>
        </Suspense>
        
        <!-- Conditional lazy loading -->
        <div v-if="showAdvancedFeatures">
            <Suspense>
                <template #default>
                    <AdvancedFeatures />
                </template>
                <template #fallback>
                    <div class="text-center py-8">
                        <p class="text-gray-500">Loading advanced features...</p>
                    </div>
                </template>
            </Suspense>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, defineAsyncComponent, onMounted } from 'vue';
import StatsCard from '@/components/StatsCard.vue';

// Lazy load heavy components
const HeavyChart = defineAsyncComponent(() => 
    import('@/components/HeavyChart.vue')
);

const AdvancedFeatures = defineAsyncComponent(() => 
    import('@/components/AdvancedFeatures.vue')
);

const showAdvancedFeatures = ref(false);
const chartData = ref([]);
const criticalStats = ref([]);

onMounted(async () => {
    // Load critical data first
    criticalStats.value = await fetchCriticalStats();
    
    // Load chart data
    chartData.value = await fetchChartData();
    
    // Load advanced features after a delay
    setTimeout(() => {
        showAdvancedFeatures.value = true;
    }, 2000);
});

const fetchCriticalStats = async () => {
    // Fetch critical stats
    return [];
};

const fetchChartData = async () => {
    // Fetch chart data
    return [];
};
</script>
```

### Intersection Observer for Lazy Loading

```typescript
// resources/js/composables/useIntersectionObserver.ts
import { ref, onMounted, onUnmounted } from 'vue';

export const useIntersectionObserver = (
    target: Ref<Element | null>,
    options: IntersectionObserverInit = {}
) => {
    const isIntersecting = ref(false);
    const observer = ref<IntersectionObserver | null>(null);

    const defaultOptions: IntersectionObserverInit = {
        threshold: 0.1,
        rootMargin: '50px',
        ...options,
    };

    onMounted(() => {
        if (target.value) {
            observer.value = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        isIntersecting.value = entry.isIntersecting;
                    });
                },
                defaultOptions
            );
            observer.value.observe(target.value);
        }
    });

    onUnmounted(() => {
        if (observer.value) {
            observer.value.disconnect();
        }
    });

    return { isIntersecting };
};
```

```vue
<!-- resources/js/components/LazyImage.vue -->
<template>
    <div ref="container" class="lazy-image-container">
        <img
            v-if="isIntersecting"
            :src="src"
            :alt="alt"
            :class="className"
            @load="onLoad"
        />
        <div
            v-else
            class="placeholder"
            :style="{ width, height }"
        >
            <div class="animate-pulse bg-gray-200 w-full h-full rounded"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useIntersectionObserver } from '@/composables/useIntersectionObserver';

interface Props {
    src: string;
    alt: string;
    width?: string;
    height?: string;
    className?: string;
}

const props = defineProps<Props>();

const container = ref<Element | null>(null);
const { isIntersecting } = useIntersectionObserver(container);

const onLoad = () => {
    // Image loaded successfully
};
</script>
```

## Building for Production with Vite

### Production Build Configuration

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
    const isProduction = mode === 'production';
    
    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.ts'],
                refresh: !isProduction,
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
            },
        },
        build: {
            minify: isProduction ? 'terser' : false,
            sourcemap: !isProduction,
            rollupOptions: {
                output: {
                    manualChunks: {
                        vendor: ['vue', 'vue-router'],
                        ui: ['@headlessui/vue', '@heroicons/vue'],
                        utils: ['lodash', 'axios', 'date-fns'],
                    },
                    assetFileNames: (assetInfo) => {
                        const info = assetInfo.name.split('.');
                        const ext = info[info.length - 1];
                        if (/\.(css)$/.test(assetInfo.name)) {
                            return `css/[name]-[hash][extname]`;
                        }
                        if (/\.(png|jpe?g|svg|gif|tiff|bmp|ico)$/i.test(assetInfo.name)) {
                            return `images/[name]-[hash][extname]`;
                        }
                        return `assets/[name]-[hash][extname]`;
                    },
                },
            },
            terserOptions: {
                compress: {
                    drop_console: isProduction,
                    drop_debugger: isProduction,
                },
            },
        },
        optimizeDeps: {
            include: ['vue', 'vue-router', '@inertiajs/vue3'],
        },
    };
});
```

### Environment-Specific Builds

```bash
#!/bin/bash
# build.sh

# Set environment
ENV=${1:-production}

echo "Building for $ENV environment..."

# Install dependencies
npm ci

# Build assets
if [ "$ENV" = "production" ]; then
    npm run build
else
    npm run build:dev
fi

# Optimize images
if command -v imagemin &> /dev/null; then
    echo "Optimizing images..."
    imagemin resources/images/* --out-dir=public/images --plugin=imagemin-mozjpeg --plugin=imagemin-pngquant
fi

# Generate service worker
if [ "$ENV" = "production" ]; then
    echo "Generating service worker..."
    npm run generate-sw
fi

echo "Build completed for $ENV environment"
```

### Build Optimization Scripts

```json
{
  "scripts": {
    "build": "vite build",
    "build:dev": "vite build --mode development",
    "build:analyze": "vite build --mode analyze",
    "generate-sw": "workbox generateSW workbox-config.js",
    "preview": "vite preview",
    "optimize:images": "imagemin resources/images/* --out-dir=public/images",
    "bundle:analyze": "npx vite-bundle-analyzer dist"
  }
}
```

## Server Configuration Considerations

### Nginx Configuration

```nginx
# /etc/nginx/sites-available/laravel-spa
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/laravel-spa/public;
    index index.php;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration

```apache
# .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    
    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
```

### Laravel Production Configuration

```php
<?php
// config/app.php
return [
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'https://your-domain.com'),
    
    // ... other config
];

// config/cache.php
return [
    'default' => env('CACHE_DRIVER', 'redis'),
    
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],
];

// config/session.php
return [
    'driver' => env('SESSION_DRIVER', 'redis'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'lax',
];
```

## Monitoring SPA Performance

### Performance Monitoring Setup

```typescript
// resources/js/utils/performanceMonitor.ts
export class PerformanceMonitor {
    private static instance: PerformanceMonitor;
    private metrics: Map<string, number> = new Map();

    static getInstance(): PerformanceMonitor {
        if (!PerformanceMonitor.instance) {
            PerformanceMonitor.instance = new PerformanceMonitor();
        }
        return PerformanceMonitor.instance;
    }

    startTiming(name: string): void {
        this.metrics.set(name, performance.now());
    }

    endTiming(name: string): number {
        const startTime = this.metrics.get(name);
        if (!startTime) return 0;
        
        const duration = performance.now() - startTime;
        this.metrics.delete(name);
        
        // Send to analytics
        this.sendMetric(name, duration);
        
        return duration;
    }

    measurePageLoad(): void {
        window.addEventListener('load', () => {
            const navigation = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
            
            this.sendMetric('page_load_time', navigation.loadEventEnd - navigation.fetchStart);
            this.sendMetric('dom_content_loaded', navigation.domContentLoadedEventEnd - navigation.fetchStart);
            this.sendMetric('first_paint', this.getFirstPaint());
            this.sendMetric('largest_contentful_paint', this.getLCP());
        });
    }

    private getFirstPaint(): number {
        const paintEntries = performance.getEntriesByType('paint');
        const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
        return firstPaint ? firstPaint.startTime : 0;
    }

    private getLCP(): number {
        const lcpEntries = performance.getEntriesByType('largest-contentful-paint');
        return lcpEntries.length > 0 ? lcpEntries[lcpEntries.length - 1].startTime : 0;
    }

    private sendMetric(name: string, value: number): void {
        // Send to your analytics service
        if (typeof gtag !== 'undefined') {
            gtag('event', 'timing_complete', {
                name: name,
                value: Math.round(value),
            });
        }
        
        // Log to console in development
        if (process.env.NODE_ENV === 'development') {
            console.log(`Performance Metric: ${name} = ${value}ms`);
        }
    }
}

// Usage in components
export const usePerformanceMonitor = () => {
    const monitor = PerformanceMonitor.getInstance();
    
    return {
        startTiming: (name: string) => monitor.startTiming(name),
        endTiming: (name: string) => monitor.endTiming(name),
        measurePageLoad: () => monitor.measurePageLoad(),
    };
};
```

### Error Monitoring

```typescript
// resources/js/utils/errorMonitor.ts
export class ErrorMonitor {
    private static instance: ErrorMonitor;

    static getInstance(): ErrorMonitor {
        if (!ErrorMonitor.instance) {
            ErrorMonitor.instance = new ErrorMonitor();
        }
        return ErrorMonitor.instance;
    }

    init(): void {
        // Global error handler
        window.addEventListener('error', (event) => {
            this.reportError({
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
                type: 'javascript_error',
            });
        });

        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            this.reportError({
                message: event.reason?.message || 'Unhandled promise rejection',
                stack: event.reason?.stack,
                type: 'promise_rejection',
            });
        });

        // Vue error handler
        this.setupVueErrorHandler();
    }

    private setupVueErrorHandler(): void {
        // This would be set up in your main app file
        // app.config.errorHandler = (err, instance, info) => {
        //     this.reportError({
        //         message: err.message,
        //         stack: err.stack,
        //         component: instance?.$options.name,
        //         info: info,
        //         type: 'vue_error',
        //     });
        // };
    }

    private reportError(error: any): void {
        // Send to your error monitoring service
        if (typeof gtag !== 'undefined') {
            gtag('event', 'exception', {
                description: error.message,
                fatal: false,
            });
        }

        // Log to console in development
        if (process.env.NODE_ENV === 'development') {
            console.error('Error reported:', error);
        }
    }
}
```

### Real User Monitoring (RUM)

```typescript
// resources/js/utils/rum.ts
export class RealUserMonitoring {
    private static instance: RealUserMonitoring;

    static getInstance(): RealUserMonitoring {
        if (!RealUserMonitoring.instance) {
            RealUserMonitoring.instance = new RealUserMonitoring();
        }
        return RealUserMonitoring.instance;
    }

    init(): void {
        this.trackPageViews();
        this.trackUserInteractions();
        this.trackResourceTiming();
    }

    private trackPageViews(): void {
        // Track page views
        const trackPageView = () => {
            const data = {
                url: window.location.href,
                title: document.title,
                timestamp: Date.now(),
                userAgent: navigator.userAgent,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight,
                },
            };

            this.sendData('page_view', data);
        };

        // Initial page view
        trackPageView();

        // Track navigation changes (for SPAs)
        window.addEventListener('popstate', trackPageView);
    }

    private trackUserInteractions(): void {
        // Track clicks
        document.addEventListener('click', (event) => {
            const target = event.target as HTMLElement;
            if (target.tagName === 'A' || target.tagName === 'BUTTON') {
                this.sendData('user_interaction', {
                    type: 'click',
                    element: target.tagName,
                    text: target.textContent?.trim(),
                    href: target.getAttribute('href'),
                    timestamp: Date.now(),
                });
            }
        });

        // Track form submissions
        document.addEventListener('submit', (event) => {
            const form = event.target as HTMLFormElement;
            this.sendData('user_interaction', {
                type: 'form_submit',
                formId: form.id,
                formAction: form.action,
                timestamp: Date.now(),
            });
        });
    }

    private trackResourceTiming(): void {
        window.addEventListener('load', () => {
            const resources = performance.getEntriesByType('resource');
            resources.forEach((resource) => {
                this.sendData('resource_timing', {
                    name: resource.name,
                    duration: resource.duration,
                    size: resource.transferSize,
                    timestamp: Date.now(),
                });
            });
        });
    }

    private sendData(type: string, data: any): void {
        // Send to your analytics service
        if (typeof gtag !== 'undefined') {
            gtag('event', type, data);
        }

        // Log to console in development
        if (process.env.NODE_ENV === 'development') {
            console.log(`RUM Data (${type}):`, data);
        }
    }
}
```

## When to Use Each Strategy

### Use Lazy Loading When:
- You have large components that aren't immediately visible
- You want to improve initial page load times
- You're building applications with many routes
- You have heavy third-party libraries

### Use Performance Monitoring When:
- You need to track real user performance
- You want to identify performance bottlenecks
- You're optimizing for Core Web Vitals
- You need to monitor production performance

### Use Production Builds When:
- Deploying to production
- You want optimized asset sizes
- You need to enable caching strategies
- You're concerned about security

## Best Practices

1. **Measure before optimizing**: Always measure performance before making changes
2. **Use lazy loading strategically**: Don't over-lazy load; balance performance with user experience
3. **Implement proper caching**: Use appropriate cache headers for different asset types
4. **Monitor continuously**: Set up ongoing performance monitoring
5. **Test on real devices**: Don't rely only on desktop testing
6. **Optimize images**: Use appropriate formats and compression
7. **Minimize JavaScript**: Remove unused code and dependencies

## Common Pitfalls

1. **Over-optimization**: Don't optimize prematurely; measure first
2. **Ignoring mobile performance**: Mobile devices have different performance characteristics
3. **Not testing in production**: Development and production environments can behave differently
4. **Poor caching strategies**: Incorrect cache headers can hurt performance
5. **Large bundle sizes**: Not monitoring and controlling bundle sizes
6. **Blocking resources**: Loading critical resources synchronously

## Laravel 12 Specific Considerations

### Production Environment Setup

```bash
# .env.production
APP_NAME="My SPA App"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm

echo "Deployment completed successfully!"
```

This comprehensive guide covers deployment and performance optimization strategies that will help you build and deploy high-performance Laravel SPA applications with optimal user experience.
