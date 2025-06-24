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
          <div class="relative">
            <!-- Avatar Display -->
            <div 
              v-if="authStore.user?.avatar" 
              class="w-24 h-24 rounded-full overflow-hidden shadow-blue border-4 border-white"
            >
              <img 
                :src="authStore.user.avatar" 
                :alt="authStore.user?.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div 
              v-else
              class="w-24 h-24 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-blue"
            >
              {{ authStore.user?.name?.charAt(0).toUpperCase() }}
            </div>
            
            <!-- Upload Button Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300 cursor-pointer group" @click="triggerFileInput">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            
            <!-- Hidden File Input -->
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              @change="handleAvatarUpload"
              class="hidden"
            />
            
            <!-- Loading Spinner -->
            <div 
              v-if="avatarLoading" 
              class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center"
            >
              <div class="spinner-white"></div>
            </div>
          </div>
          
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-secondary-900">{{ authStore.user?.name }}</h2>
            <p class="text-secondary-600">{{ authStore.user?.email }}</p>
            <p class="text-sm text-secondary-500 mt-1">
              Member since {{ formatDate(authStore.user?.created_at) }}
            </p>
            
            <!-- Avatar Actions -->
            <div class="flex space-x-3 mt-3">
              <button 
                @click="triggerFileInput"
                :disabled="avatarLoading"
                class="text-sm bg-primary-100 hover:bg-primary-200 text-primary-700 px-3 py-1 rounded-lg transition-colors duration-200 flex items-center space-x-1"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span>{{ avatarLoading ? 'Uploading...' : 'Upload Photo' }}</span>
              </button>
              
              <button 
                v-if="authStore.user?.avatar"
                @click="removeAvatar"
                :disabled="avatarLoading"
                class="text-sm bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-lg transition-colors duration-200 flex items-center space-x-1"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Remove</span>
              </button>
            </div>
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
import axios from 'axios';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const passwordLoading = ref(false);
const avatarLoading = ref(false);
const errors = ref({});
const passwordErrors = ref({});
const fileInput = ref(null);

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
    const response = await axios.put('/api/auth/profile', form);
    
    // Update user in auth store
    authStore.updateUser(response.data);
    
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
    await axios.put('/api/auth/password', passwordForm);
    
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

const triggerFileInput = () => {
  if (avatarLoading.value) return;
  fileInput.value?.click();
};

const handleAvatarUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    notificationStore.error('Error', 'Please select a valid image file');
    return;
  }

  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    notificationStore.error('Error', 'File size must be less than 5MB');
    return;
  }

  avatarLoading.value = true;

  try {
    const formData = new FormData();
    formData.append('avatar', file);

    // Make API call to upload avatar
    const response = await axios.post('/api/profile/avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    // Update user avatar in store
    if (response.data.user) {
      authStore.updateUser(response.data.user);
      notificationStore.success('Success', 'Profile photo updated successfully');
    }
  } catch (error) {
    console.error('Avatar upload error:', error);
    notificationStore.error('Error', error.response?.data?.message || 'Failed to upload profile photo');
  } finally {
    avatarLoading.value = false;
    // Clear file input
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  }
};

const removeAvatar = async () => {
  if (avatarLoading.value) return;

  if (!confirm('Are you sure you want to remove your profile photo?')) {
    return;
  }

  avatarLoading.value = true;

  try {
    // Make API call to remove avatar
    const response = await axios.delete('/api/profile/avatar');

    // Update user avatar in store
    if (response.data.user) {
      authStore.updateUser(response.data.user);
      notificationStore.success('Success', 'Profile photo removed successfully');
    }
  } catch (error) {
    console.error('Avatar removal error:', error);
    notificationStore.error('Error', error.response?.data?.message || 'Failed to remove profile photo');
  } finally {
    avatarLoading.value = false;
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

.spinner-white {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid transparent;
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
