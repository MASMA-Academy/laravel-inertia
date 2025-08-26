# Form Handling & Validation

## Course Overview

This module covers building form components, implementing client-side validation
strategies, and integrating with Laravel server-side validation in Inertia.js
applications.

---

## Building Form Components

### Understanding Form Architecture

**Form Component Patterns:**

- Controlled vs uncontrolled components
- Form state management
- Validation integration
- Error handling

### Basic Form Components

**1. Input Component:**

```vue
<!-- resources/js/Components/FormInput.vue -->
<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="[
        'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        hasError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
      ]"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
    />
    
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
    
    <p v-if="helpText" class="mt-2 text-sm text-gray-500">
      {{ helpText }}
    </p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  id: string
  label: string
  type?: string
  modelValue: string
  error?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  helpText?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  required: false,
  disabled: false
})

defineEmits<{
  'update:modelValue': [value: string]
}>()

const hasError = computed(() => !!props.error)
</script>
```

**2. Select Component:**

```vue
<!-- resources/js/Components/FormSelect.vue -->
<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <select
      :id="id"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :class="[
        'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        hasError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
      ]"
      :required="required"
      :disabled="disabled"
    >
      <option v-if="placeholder" value="">{{ placeholder }}</option>
      <option 
        v-for="option in options" 
        :key="option.value" 
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup lang="ts">
interface Option {
  value: string | number
  label: string
}

interface Props {
  id: string
  label: string
  modelValue: string | number
  options: Option[]
  error?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false
})

defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const hasError = computed(() => !!props.error)
</script>
```

**3. Textarea Component:**

```vue
<!-- resources/js/Components/FormTextarea.vue -->
<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <textarea
      :id="id"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :rows="rows"
      :class="[
        'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
        hasError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''
      ]"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
    />
    
    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
    
    <p v-if="helpText" class="mt-2 text-sm text-gray-500">
      {{ helpText }}
    </p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  id: string
  label: string
  modelValue: string
  error?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  helpText?: string
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  rows: 3
})

defineEmits<{
  'update:modelValue': [value: string]
}>()

const hasError = computed(() => !!props.error)
</script>
```

### Using Form Components

**1. Basic Form:**

```vue
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <FormInput
      id="name"
      label="Full Name"
      v-model="form.name"
      :error="form.errors.name"
      required
      placeholder="Enter your full name"
    />
    
    <FormInput
      id="email"
      label="Email Address"
      type="email"
      v-model="form.email"
      :error="form.errors.email"
      required
      placeholder="Enter your email address"
    />
    
    <FormSelect
      id="role"
      label="Role"
      v-model="form.role"
      :options="roleOptions"
      :error="form.errors.role"
      required
      placeholder="Select a role"
    />
    
    <FormTextarea
      id="bio"
      label="Biography"
      v-model="form.bio"
      :error="form.errors.bio"
      :help-text="'Tell us a bit about yourself'"
      rows="4"
    />
    
    <div class="flex justify-end space-x-3">
      <button
        type="button"
        @click="$emit('cancel')"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
      >
        Cancel
      </button>
      
      <button
        type="submit"
        :disabled="form.processing"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50"
      >
        {{ form.processing ? 'Saving...' : 'Save User' }}
      </button>
    </div>
  </form>
</template>`

<script setup lang="ts">
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import FormTextarea from '@/Components/FormTextarea.vue'
import { useForm } from '@inertiajs/vue3'

interface UserFormData {
  name: string
  email: string
  role: string
  bio: string
}

interface Props {
  user?: Partial<UserFormData>
  mode: 'create' | 'edit'
}

const props = withDefaults(defineProps<Props>(), {
  user: () => ({ name: '', email: '', role: '', bio: '' }),
  mode: 'create'
})

defineEmits<{
  cancel: []
  saved: [user: UserFormData]
}>()

const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  role: props.user.role || '',
  bio: props.user.bio || '',
})

const roleOptions = [
  { value: 'user', label: 'User' },
  { value: 'admin', label: 'Administrator' },
  { value: 'moderator', label: 'Moderator' },
]

const handleSubmit = () => {
  const url = props.mode === 'create' ? '/users' : `/users/${props.user.id}`
  const method = props.mode === 'create' ? 'post' : 'put'
  
  form[method](url, {
    onSuccess: () => {
      emit('saved', form.data())
    },
  })
}
</script>
```

---

## Client-side Validation Strategies

### Schema-based Validation

**1. Zod Integration:**

```typescript
// resources/js/composables/useZodValidation.ts
import { z } from "zod";
import { computed, ref } from "vue";

export function useZodValidation<T>(schema: z.ZodSchema<T>) {
    const errors = ref<Record<string, string>>({});
    const touched = ref<Record<string, boolean>>({});
    const dirty = ref<Record<string, boolean>>({});

    const validateField = (field: string, value: any) => {
        try {
            // Create a partial schema for the specific field
            const fieldSchema = z.object({ [field]: schema.shape[field] });
            fieldSchema.parse({ [field]: value });

            // Clear error if validation passes
            delete errors.value[field];
            return true;
        } catch (error) {
            if (error instanceof z.ZodError) {
                const fieldError = error.errors.find((e) =>
                    e.path[0] === field
                );
                if (fieldError) {
                    errors.value[field] = fieldError.message;
                }
            }
            return false;
        }
    };

    const validateForm = (data: Partial<T>) => {
        try {
            schema.parse(data);
            errors.value = {};
            return true;
        } catch (error) {
            if (error instanceof z.ZodError) {
                errors.value = {};
                error.errors.forEach((err) => {
                    const field = err.path[0] as string;
                    errors.value[field] = err.message;
                });
            }
            return false;
        }
    };

    const markFieldTouched = (field: string) => {
        touched.value[field] = true;
    };

    const markFieldDirty = (field: string) => {
        dirty.value[field] = true;
    };

    const hasErrors = computed(() => Object.keys(errors.value).length > 0);
    const isFormValid = computed(() => !hasErrors.value);

    return {
        errors,
        touched,
        dirty,
        validateField,
        validateForm,
        markFieldTouched,
        markFieldDirty,
        hasErrors,
        isFormValid,
    };
}
```

**2. Using Zod Validation:**

```vue
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <FormInput
      id="username"
      label="Username"
      v-model="form.username"
      :error="validation.errors.username"
      @blur="validation.markFieldTouched('username')"
      @input="validation.markFieldDirty('username')"
      required
    />
    
    <FormInput
      id="email"
      label="Email"
      type="email"
      v-model="form.email"
      :error="validation.errors.email"
      @blur="validation.markFieldTouched('email')"
      @input="validation.markFieldDirty('email')"
      required
    />
    
    <FormInput
      id="password"
      label="Password"
      type="password"
      v-model="form.password"
      :error="validation.errors.password"
      @blur="validation.markFieldTouched('password')"
      @input="validation.markFieldDirty('password')"
      required
    />
    
    <FormInput
      id="confirmPassword"
      label="Confirm Password"
      type="password"
      v-model="form.confirmPassword"
      :error="validation.errors.confirmPassword"
      @blur="validation.markFieldTouched('confirmPassword')"
      @input="validation.markFieldDirty('confirmPassword')"
      required
    />
    
    <button
      type="submit"
      :disabled="!validation.isFormValid"
      class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
    >
      Create Account
    </button>
  </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { z } from 'zod'
import FormInput from '@/Components/FormInput.vue'
import { useZodValidation } from '@/composables/useZodValidation'

const form = reactive({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
})

// Define validation schema
const userSchema = z.object({
  username: z.string()
    .min(3, 'Username must be at least 3 characters')
    .max(20, 'Username must be no more than 20 characters')
    .regex(/^[a-zA-Z0-9_]+$/, 'Username can only contain letters, numbers, and underscores'),
  
  email: z.string()
    .email('Please enter a valid email address'),
  
  password: z.string()
    .min(8, 'Password must be at least 8 characters')
    .regex(/[A-Z]/, 'Password must contain at least one uppercase letter')
    .regex(/[a-z]/, 'Password must contain at least one lowercase letter')
    .regex(/\d/, 'Password must contain at least one number'),
  
  confirmPassword: z.string()
}).refine((data) => data.password === data.confirmPassword, {
  message: "Passwords don't match",
  path: ["confirmPassword"],
})

const validation = useZodValidation(userSchema)

const handleSubmit = () => {
  if (validation.validateForm(form)) {
    console.log('Form is valid:', form)
    // Submit form
  } else {
    console.log('Form has errors:', validation.errors.value)
  }
}
</script>
```

### Real-time Validation

**1. Debounced Validation:**

```typescript
// resources/js/composables/useDebouncedValidation.ts
import { ref, watch } from "vue";
import { debounce } from "lodash-es";

export function useDebouncedValidation<T>(
    data: T,
    validator: (data: T) => Record<string, string>,
    delay: number = 300,
) {
    const errors = ref<Record<string, string>>({});
    const isValidating = ref(false);

    const validate = debounce(async () => {
        isValidating.value = true;

        try {
            const validationErrors = await validator(data);
            errors.value = validationErrors;
        } catch (error) {
            console.error("Validation error:", error);
        } finally {
            isValidating.value = false;
        }
    }, delay);

    // Watch for changes in the data
    watch(data, () => {
        validate();
    }, { deep: true });

    return {
        errors,
        isValidating,
        validate: () => validate.flush(),
    };
}
```

**2. Using Debounced Validation:**

```vue
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <FormInput
      id="username"
      label="Username"
      v-model="form.username"
      :error="validation.errors.username"
      :class="{ 'border-yellow-500': validation.isValidating }"
    />
    
    <div v-if="validation.isValidating" class="text-sm text-yellow-600">
      Validating...
    </div>
    
    <button
      type="submit"
      :disabled="Object.keys(validation.errors).length > 0"
      class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
    >
      Submit
    </button>
  </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import FormInput from '@/Components/FormInput.vue'
import { useDebouncedValidation } from '@/composables/useDebouncedValidation'

const form = reactive({
  username: '',
})

const validateUsername = async (data: typeof form) => {
  const errors: Record<string, string> = {}
  
  // Simulate API call for username availability
  if (data.username.length > 0) {
    try {
      const response = await fetch(`/api/check-username?username=${data.username}`)
      const result = await response.json()
      
      if (!result.available) {
        errors.username = 'Username is already taken'
      }
    } catch (error) {
      console.error('Username validation error:', error)
    }
  }
  
  return errors
}

const validation = useDebouncedValidation(form, validateUsername, 500)

const handleSubmit = () => {
  if (Object.keys(validation.errors).length === 0) {
    console.log('Form submitted:', form)
  }
}
</script>
```

---

## Server-side Validation with Laravel

### Laravel Form Request Classes

**1. Creating Form Request:**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
            ],
            'role' => ['required', 'in:user,admin,moderator'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.unique' => 'This email address is already taken.',
            'role.in' => 'Please select a valid role.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'user name',
            'email' => 'email address',
        ];
    }
}
```

**2. Using Form Request in Controller:**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'bio' => $validated['bio'] ?? null,
            'password' => Hash::make('temporary-password'),
        ]);
        
        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }
}
```

### Inertia.js Error Handling

**1. Error Display Component:**

```vue
<!-- resources/js/Components/FormErrors.vue -->
<template>
  <div v-if="hasErrors" class="rounded-md bg-red-50 p-4">
    <div class="flex">
      <div class="flex-shrink-0">
        <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
      </div>
      <div class="ml-3">
        <h3 class="text-sm font-medium text-red-800">
          There were errors with your submission
        </h3>
        <div class="mt-2 text-sm text-red-700">
          <ul class="list-disc space-y-1 pl-5">
            <li v-for="(error, field) in errors" :key="field">
              {{ error }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

interface Props {
  errors: Record<string, string>
}

const props = defineProps<Props>()

const hasErrors = computed(() => Object.keys(props.errors).length > 0)
</script>
```

**2. Form with Error Handling:**

```vue
<template>
  <div>
    <FormErrors :errors="form.errors" />
    
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <FormInput
        id="name"
        label="Full Name"
        v-model="form.name"
        :error="form.errors.name"
        required
      />
      
      <FormInput
        id="email"
        label="Email Address"
        type="email"
        v-model="form.email"
        :error="form.errors.email"
        required
      />
      
      <FormSelect
        id="role"
        label="Role"
        v-model="form.role"
        :options="roleOptions"
        :error="form.errors.role"
        required
      />
      
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Cancel
        </button>
        
        <button
          type="submit"
          :disabled="form.processing"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ form.processing ? 'Saving...' : 'Save User' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/FormInput.vue'
import FormSelect from '@/Components/FormSelect.vue'
import FormErrors from '@/Components/FormErrors.vue'

interface UserFormData {
  name: string
  email: string
  role: string
}

const form = useForm({
  name: '',
  email: '',
  role: '',
})

const roleOptions = [
  { value: 'user', label: 'User' },
  { value: 'admin', label: 'Administrator' },
  { value: 'moderator', label: 'Moderator' },
]

const handleSubmit = () => {
  form.post('/users', {
    onSuccess: () => {
      // Form submitted successfully
      console.log('User created successfully')
    },
    onError: (errors) => {
      // Handle validation errors
      console.log('Validation errors:', errors)
    },
    onFinish: () => {
      // Always executed, regardless of success/failure
      console.log('Form submission finished')
    },
  })
}

defineEmits<{
  cancel: []
}>()
</script>
```

---

## Key Concepts Summary

1. **Form Components**: Build reusable input, select, and textarea components
2. **Client-side Validation**: Use Zod schemas and real-time validation
3. **Server-side Validation**: Laravel Form Request classes with Inertia.js
4. **Error Handling**: Display validation errors effectively
5. **Form State**: Manage form data and submission states

---

## Next Steps

After completing this module, you should:

1. Create reusable form components
2. Implement client-side validation with Zod
3. Handle server-side validation errors
4. Build forms with proper error handling
5. Be ready for the next module: Data Fetching & State Management

---

## Additional Resources

- [Vue.js 3 Form Handling](https://vuejs.org/guide/essentials/forms.html)
- [Zod Documentation](https://zod.dev/)
- [Laravel Form Request Validation](https://laravel.com/docs/validation#form-request-validation)
- [Inertia.js Forms](https://inertiajs.com/forms)
