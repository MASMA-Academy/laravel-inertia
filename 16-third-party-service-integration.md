# Third-Party Service Integration with Inertia.js

## Overview

Third-party service integration is essential for modern web applications. This guide covers how to integrate external services like authentication providers, file storage, payment gateways, and APIs with Laravel 12 and Inertia.js, leveraging Inertia's seamless data flow to create smooth user experiences.

## Authentication Service Integration

### OAuth Integration with Socialite

```php
<?php
// app/Http/Controllers/Auth/SocialAuthController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider, Request $request)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'avatar' => $socialUser->getAvatar(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            } else {
                // Update existing user with provider info
                $user->update([
                    'avatar' => $socialUser->getAvatar(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            Auth::login($user);

            return redirect()->intended(route('dashboard'));
            
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }
}
```

### Vue Component for Social Login

```vue
<!-- resources/js/pages/Auth/Login.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Social Login Buttons -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300" />
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button
                        @click="loginWithProvider('google')"
                        :disabled="isLoading"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="ml-2">Google</span>
                    </button>

                    <button
                        @click="loginWithProvider('github')"
                        :disabled="isLoading"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <span class="ml-2">GitHub</span>
                    </button>
                </div>
            </div>

            <!-- Regular Login Form -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <!-- Form fields here -->
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const isLoading = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const loginWithProvider = (provider: string) => {
    isLoading.value = true;
    window.location.href = route(`auth.${provider}`);
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
```

## File Storage Integration

### Cloud Storage with Laravel

```php
<?php
// app/Http/Controllers/FileController.php
namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FileController extends Controller
{
    public function index()
    {
        $files = auth()->user()->files()->latest()->paginate(20);
        
        return Inertia::render('Files/Index', [
            'files' => $files,
            'uploadUrl' => route('files.upload'),
            'maxFileSize' => config('filesystems.max_file_size'),
            'allowedTypes' => config('filesystems.allowed_types'),
        ]);
    }

    public function upload(FileUploadRequest $request)
    {
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Upload to cloud storage
        $path = $file->storeAs('uploads', $filename, 's3');
        
        // Save file record
        $fileRecord = auth()->user()->files()->create([
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'url' => Storage::disk('s3')->url($path),
        ]);

        return response()->json([
            'success' => true,
            'file' => $fileRecord,
        ]);
    }

    public function delete($id)
    {
        $file = auth()->user()->files()->findOrFail($id);
        
        // Delete from storage
        Storage::disk('s3')->delete($file->path);
        
        // Delete record
        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully.');
    }
}
```

### Vue File Upload Component

```vue
<!-- resources/js/components/FileUpload.vue -->
<template>
    <div class="file-upload">
        <div
            @drop="handleDrop"
            @dragover.prevent
            @dragenter.prevent
            :class="[
                'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
                isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                :multiple="multiple"
                :accept="acceptedTypes"
                @change="handleFileSelect"
                class="hidden"
            />
            
            <div v-if="!isUploading">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="mt-4">
                    <button
                        @click="$refs.fileInput.click()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Select Files
                    </button>
                    <p class="mt-2 text-sm text-gray-600">
                        or drag and drop files here
                    </p>
                </div>
            </div>
            
            <div v-else class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <span class="ml-2 text-sm text-gray-600">Uploading...</span>
            </div>
        </div>

        <!-- Upload Progress -->
        <div v-if="uploadProgress.length > 0" class="mt-4 space-y-2">
            <div
                v-for="progress in uploadProgress"
                :key="progress.id"
                class="bg-white rounded-lg shadow p-4"
            >
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">{{ progress.name }}</span>
                    <span class="text-sm text-gray-500">{{ progress.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: progress.percentage + '%' }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="errors.length > 0" class="mt-4">
            <div
                v-for="error in errors"
                :key="error"
                class="text-sm text-red-600 bg-red-50 p-2 rounded"
            >
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Props {
    multiple?: boolean;
    acceptedTypes?: string;
    maxFileSize?: number;
    uploadUrl: string;
}

const props = withDefaults(defineProps<Props>(), {
    multiple: true,
    acceptedTypes: '*/*',
    maxFileSize: 10 * 1024 * 1024, // 10MB
});

const emit = defineEmits<{
    uploaded: [files: any[]];
    error: [error: string];
}>();

const fileInput = ref<HTMLInputElement>();
const isDragging = ref(false);
const isUploading = ref(false);
const uploadProgress = ref<any[]>([]);
const errors = ref<string[]>([]);

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer?.files || []);
    uploadFiles(files);
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    uploadFiles(files);
};

const uploadFiles = async (files: File[]) => {
    errors.value = [];
    isUploading.value = true;
    
    const validFiles = files.filter(file => {
        if (file.size > props.maxFileSize) {
            errors.value.push(`${file.name} is too large. Maximum size is ${props.maxFileSize / 1024 / 1024}MB.`);
            return false;
        }
        return true;
    });

    if (validFiles.length === 0) {
        isUploading.value = false;
        return;
    }

    const uploadPromises = validFiles.map(file => uploadFile(file));
    
    try {
        const results = await Promise.all(uploadPromises);
        emit('uploaded', results);
    } catch (error) {
        emit('error', 'Upload failed. Please try again.');
    } finally {
        isUploading.value = false;
        uploadProgress.value = [];
    }
};

const uploadFile = (file: File): Promise<any> => {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);

        const progressId = Date.now() + Math.random();
        uploadProgress.value.push({
            id: progressId,
            name: file.name,
            percentage: 0,
        });

        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                const percentage = Math.round((event.loaded / event.total) * 100);
                const progress = uploadProgress.value.find(p => p.id === progressId);
                if (progress) {
                    progress.percentage = percentage;
                }
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                resolve(response.file);
            } else {
                reject(new Error('Upload failed'));
            }
        });

        xhr.addEventListener('error', () => {
            reject(new Error('Upload failed'));
        });

        xhr.open('POST', props.uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
        xhr.send(formData);
    });
};
</script>
```

## Payment Gateway Integration

### Stripe Integration

```php
<?php
// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPayment(Request $request)
    {
        $amount = $request->amount * 100; // Convert to cents
        
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'metadata' => [
                'user_id' => Auth::id(),
                'description' => $request->description,
            ],
        ]);

        return Inertia::render('Payments/Create', [
            'clientSecret' => $paymentIntent->client_secret,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
        
        if ($paymentIntent->status === 'succeeded') {
            // Save payment record
            $payment = Payment::create([
                'user_id' => Auth::id(),
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => 'completed',
                'metadata' => $paymentIntent->metadata->toArray(),
            ]);

            return redirect()->route('payments.success', $payment);
        }

        return redirect()->route('payments.failed');
    }
}
```

### Vue Payment Component

```vue
<!-- resources/js/pages/Payments/Create.vue -->
<template>
    <div class="max-w-md mx-auto mt-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">Complete Payment</h2>
            
            <div class="mb-4">
                <p class="text-gray-600">Amount: ${{ amount }}</p>
                <p class="text-gray-600">Description: {{ description }}</p>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Card Information
                    </label>
                    <div id="card-element" class="p-3 border border-gray-300 rounded-md"></div>
                    <div id="card-errors" class="text-red-600 text-sm mt-2"></div>
                </div>

                <button
                    type="submit"
                    :disabled="isProcessing"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ isProcessing ? 'Processing...' : `Pay $${amount}` }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';

interface Props {
    clientSecret: string;
    amount: number;
    description: string;
}

const props = defineProps<Props>();

const stripe = ref<any>(null);
const elements = ref<any>(null);
const cardElement = ref<any>(null);
const isProcessing = ref(false);

const form = useForm({
    payment_intent_id: '',
});

onMounted(async () => {
    stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY);
    elements.value = stripe.value.elements();
    
    cardElement.value = elements.value.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#424770',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
        },
    });
    
    cardElement.value.mount('#card-element');
    
    cardElement.value.on('change', (event: any) => {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError!.textContent = event.error.message;
        } else {
            displayError!.textContent = '';
        }
    });
});

onUnmounted(() => {
    if (cardElement.value) {
        cardElement.value.destroy();
    }
});

const handleSubmit = async () => {
    isProcessing.value = true;
    
    const { error, paymentIntent } = await stripe.value.confirmCardPayment(
        props.clientSecret,
        {
            payment_method: {
                card: cardElement.value,
            },
        }
    );
    
    if (error) {
        console.error('Payment failed:', error);
        isProcessing.value = false;
    } else {
        form.payment_intent_id = paymentIntent.id;
        form.post(route('payments.confirm'));
    }
};
</script>
```

## API Integration Patterns

### External API Service

```php
<?php
// app/Services/ExternalApiService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExternalApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.external_api.url');
        $this->apiKey = config('services.external_api.key');
    }

    public function getData(string $endpoint, array $params = []): array
    {
        $cacheKey = 'api_data_' . md5($endpoint . serialize($params));
        
        return Cache::remember($cacheKey, 300, function () use ($endpoint, $params) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API request failed: ' . $response->body());
        });
    }

    public function postData(string $endpoint, array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post($this->baseUrl . $endpoint, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('API request failed: ' . $response->body());
    }
}
```

### Vue API Integration Component

```vue
<!-- resources/js/components/ApiDataTable.vue -->
<template>
    <div class="api-data-table">
        <div class="mb-4 flex justify-between items-center">
            <h3 class="text-lg font-medium">External Data</h3>
            <button
                @click="refreshData"
                :disabled="isLoading"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
            >
                {{ isLoading ? 'Loading...' : 'Refresh' }}
            </button>
        </div>

        <div v-if="isLoading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-2 text-gray-600">Loading data...</p>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-red-800">{{ error }}</p>
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in data" :key="item.id">
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                        >
                            {{ getNestedValue(item, column.key) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Props {
    endpoint: string;
    columns: Array<{
        key: string;
        label: string;
    }>;
}

const props = defineProps<Props>();

const data = ref<any[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);

const form = useForm({
    endpoint: props.endpoint,
});

const fetchData = async () => {
    isLoading.value = true;
    error.value = null;
    
    try {
        const response = await fetch('/api/external-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.data()),
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch data');
        }
        
        const result = await response.json();
        data.value = result.data;
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An error occurred';
    } finally {
        isLoading.value = false;
    }
};

const refreshData = () => {
    fetchData();
};

const getNestedValue = (obj: any, path: string) => {
    return path.split('.').reduce((current, key) => current?.[key], obj);
};

onMounted(() => {
    fetchData();
});
</script>
```

## How Inertia Improves Integration

### 1. Seamless Data Flow

```php
<?php
// app/Http/Controllers/IntegrationController.php
public function showDashboard()
{
    // Fetch data from multiple services
    $userData = $this->userService->getProfile();
    $paymentData = $this->paymentService->getRecentPayments();
    $fileData = $this->fileService->getRecentFiles();
    
    return Inertia::render('Dashboard', [
        'user' => $userData,
        'payments' => $paymentData,
        'files' => $fileData,
        'integrations' => [
            'stripe' => config('services.stripe.key'),
            's3' => config('filesystems.disks.s3.bucket'),
        ],
    ]);
}
```

### 2. Real-time Updates

```vue
<!-- resources/js/pages/Dashboard.vue -->
<template>
    <div class="dashboard">
        <!-- User Profile from Auth Service -->
        <UserProfile :user="user" />
        
        <!-- Payment History from Stripe -->
        <PaymentHistory :payments="payments" />
        
        <!-- File Manager from S3 -->
        <FileManager :files="files" />
        
        <!-- Real-time notifications -->
        <NotificationCenter />
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// Access integrated data seamlessly
const user = computed(() => page.props.user);
const payments = computed(() => page.props.payments);
const files = computed(() => page.props.files);

onMounted(() => {
    // Set up real-time updates for integrated services
    setupRealTimeUpdates();
});
</script>
```

### 3. Error Handling

```vue
<!-- resources/js/composables/useIntegration.ts -->
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export const useIntegration = () => {
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    const handleIntegration = async (callback: () => Promise<any>) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const result = await callback();
            return result;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Integration failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    const retryIntegration = async (callback: () => Promise<any>, maxRetries = 3) => {
        for (let i = 0; i < maxRetries; i++) {
            try {
                return await handleIntegration(callback);
            } catch (err) {
                if (i === maxRetries - 1) throw err;
                await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
            }
        }
    };

    return {
        isLoading,
        error,
        handleIntegration,
        retryIntegration,
    };
};
```

## Best Practices

1. **Use Inertia's data sharing**: Leverage Inertia's ability to share data between server and client
2. **Implement proper error handling**: Handle integration failures gracefully
3. **Cache external API responses**: Reduce API calls and improve performance
4. **Use environment-specific configurations**: Different settings for development and production
5. **Implement retry mechanisms**: Handle temporary service failures
6. **Monitor integration health**: Track success rates and response times

## Common Pitfalls

1. **Not handling async operations properly**: Ensure proper loading states
2. **Exposing sensitive API keys**: Never expose secrets in client-side code
3. **Not implementing proper error boundaries**: Handle integration failures gracefully
4. **Ignoring rate limits**: Respect API rate limits and implement backoff strategies
5. **Not testing integrations**: Mock external services in tests

This comprehensive guide shows how Inertia.js significantly improves the integration process by providing seamless data flow, real-time updates, and better error handling for third-party services in Laravel 12 applications.
