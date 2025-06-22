<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold gradient-text font-display">
          Profile Settings
        </h1>
        <p class="text-secondary-600 mt-2">
          Manage your account settings and preferences
        </p>
      </div>

      <!-- Profile Card -->
      <div class="card mb-8">
        <div class="flex items-center space-x-6">
          <div class="w-24 h-24 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-blue">
            {{ authStore.user?.name?.charAt(0).toUpperCase() }}
          </div>
          <div>
            <h2 class="text-2xl font-bold text-secondary-900">{{ authStore.user?.name }}</h2>
            <p class="text-secondary-600">{{ authStore.user?.email }}</p>
            <p class="text-sm text-secondary-500 mt-1">
              Member since {{ formatDate(authStore.user?.created_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Settings Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Account Information -->
        <div class="lg:col-span-2">
          <div class="card">
            <h3 class="text-lg font-semibold text-secondary-900 mb-6">Account Information</h3>
            
            <form @submit.prevent="updateProfile" class="space-y-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-secondary-700 mb-2">
                  Full Name
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-secondary-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300"
                  :class="{ 'border-error-300 focus:ring-error-500': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-error-600">{{ errors.name[0] }}</p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-secondary-700 mb-2">
                  Email Address
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 border border-secondary-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300"
                  :class="{ 'border-error-300 focus:ring-error-500': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-error-600">{{ errors.email[0] }}</p>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="loading"
                  class="btn-primary"
                >
                  <span v-if="loading" class="flex items-center">
                    <div class="spinner mr-2"></div>
                    Updating...
                  </span>
                  <span v-else>Update Profile</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Change Password -->
          <div class="card mt-8">
            <h3 class="text-lg font-semibold text-secondary-900 mb-6">Change Password</h3>
            
            <form @submit.prevent="updatePassword" class="space-y-6">
              <!-- Current Password -->
              <div>
                <label for="current_password" class="block text-sm font-medium text-secondary-700 mb-2">
                  Current Password
                </label>
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="w-full px-4 py-3 border border-secondary-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300"
                  :class="{ 'border-error-300 focus:ring-error-500': passwordErrors.current_password }"
                />
                <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-error-600">{{ passwordErrors.current_password[0] }}</p>
              </div>

              <!-- New Password -->
              <div>
                <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">
                  New Password
                </label>
                <input
                  id="password"
                  v-model="passwordForm.password"
                  type="password"
                  required
                  class="w-full px-4 py-3 border border-secondary-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300"
                  :class="{ 'border-error-300 focus:ring-error-500': passwordErrors.password }"
                />
                <p v-if="passwordErrors.password" class="mt-1 text-sm text-error-600">{{ passwordErrors.password[0] }}</p>
              </div>

              <!-- Confirm Password -->
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-secondary-700 mb-2">
                  Confirm New Password
                </label>
                <input
                  id="password_confirmation"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  class="w-full px-4 py-3 border border-secondary-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300"
                  :class="{ 'border-error-300 focus:ring-error-500': passwordErrors.password_confirmation }"
                />
                <p v-if="passwordErrors.password_confirmation" class="mt-1 text-sm text-error-600">{{ passwordErrors.password_confirmation[0] }}</p>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="passwordLoading"
                  class="btn-primary"
                >
                  <span v-if="passwordLoading" class="flex items-center">
                    <div class="spinner mr-2"></div>
                    Updating...
                  </span>
                  <span v-else>Update Password</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="space-y-6">
          <div class="card">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Quick Stats</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-secondary-600">Messages Sent</span>
                <span class="font-semibold text-secondary-900">0</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-secondary-600">Chat Rooms</span>
                <span class="font-semibold text-secondary-900">0</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-secondary-600">Last Active</span>
                <span class="font-semibold text-secondary-900">Now</span>
              </div>
            </div>
          </div>

          <div class="card">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Account Actions</h3>
            <div class="space-y-3">
              <button class="w-full text-left p-3 rounded-xl hover:bg-secondary-50 transition-colors duration-200">
                <div class="flex items-center space-x-3">
                  <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <span class="text-sm font-medium text-secondary-700">Export Data</span>
                </div>
              </button>
              
              <button class="w-full text-left p-3 rounded-xl hover:bg-error-50 transition-colors duration-200 text-error-600">
                <div class="flex items-center space-x-3">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  <span class="text-sm font-medium">Delete Account</span>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useNotificationStore } from '../stores/notifications';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const passwordLoading = ref(false);
const errors = ref({});
const passwordErrors = ref({});

const form = reactive({
  name: '',
  email: ''
});

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const updateProfile = async () => {
  loading.value = true;
  errors.value = {};

  try {
    // This would be implemented with actual API call
    notificationStore.success('Success', 'Profile updated successfully');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    notificationStore.error('Error', error.response?.data?.message || 'Failed to update profile');
  } finally {
    loading.value = false;
  }
};

const updatePassword = async () => {
  passwordLoading.value = true;
  passwordErrors.value = {};

  try {
    // This would be implemented with actual API call
    notificationStore.success('Success', 'Password updated successfully');
    
    // Clear form
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (error) {
    if (error.response?.status === 422) {
      passwordErrors.value = error.response.data.errors || {};
    }
    notificationStore.error('Error', error.response?.data?.message || 'Failed to update password');
  } finally {
    passwordLoading.value = false;
  }
};

onMounted(() => {
  if (authStore.user) {
    form.name = authStore.user.name;
    form.email = authStore.user.email;
  }
});
</script>

<style scoped>
.gradient-text {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  color: white;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.25);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover:not(:disabled) {
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.3);
  transform: scale(1.05);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
