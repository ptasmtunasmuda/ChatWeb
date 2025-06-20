<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full animate-bounce-in">
      <div class="p-6 border-b border-secondary-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold gradient-text font-display">Edit User</h3>
          <button @click="$emit('close')" class="p-2 hover:bg-secondary-100 rounded-lg">
            <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      <div class="p-6">
        <form @submit.prevent="updateUser" class="space-y-4">
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
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :class="{ 'border-error-300 focus:ring-error-500': errors.email }"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-error-600">{{ errors.email[0] }}</p>
          </div>

          <!-- Role -->
          <div>
            <label for="role" class="block text-sm font-medium text-secondary-700 mb-2">
              Role
            </label>
            <select
              id="role"
              v-model="form.role"
              required
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :class="{ 'border-error-300 focus:ring-error-500': errors.role }"
            >
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
            <p v-if="errors.role" class="mt-1 text-sm text-error-600">{{ errors.role[0] }}</p>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded"
              />
              <span class="ml-2 text-sm text-secondary-700">Active Account</span>
            </label>
          </div>

          <!-- Password (Optional) -->
          <div>
            <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">
              New Password (Optional)
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :class="{ 'border-error-300 focus:ring-error-500': errors.password }"
              placeholder="Leave blank to keep current password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-error-600">{{ errors.password[0] }}</p>
          </div>

          <!-- Password Confirmation -->
          <div v-if="form.password">
            <label for="password_confirmation" class="block text-sm font-medium text-secondary-700 mb-2">
              Confirm New Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="btn-secondary"
              :disabled="loading"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="loading"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Updating...
              </span>
              <span v-else>Update User</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';

const props = defineProps(['user']);
const emit = defineEmits(['close', 'updated']);

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const errors = ref({});

const form = reactive({
  name: '',
  email: '',
  role: 'user',
  is_active: true,
  password: '',
  password_confirmation: ''
});

// Initialize form with user data
watch(() => props.user, (user) => {
  if (user) {
    form.name = user.name || '';
    form.email = user.email || '';
    form.role = user.role || 'user';
    form.is_active = user.is_active !== undefined ? user.is_active : true;
    form.password = '';
    form.password_confirmation = '';
  }
}, { immediate: true });

const updateUser = async () => {
  if (!props.user) return;

  loading.value = true;
  errors.value = {};

  try {
    const updateData = {
      name: form.name,
      email: form.email,
      role: form.role,
      is_active: form.is_active
    };

    // Only include password if it's provided
    if (form.password) {
      updateData.password = form.password;
      updateData.password_confirmation = form.password_confirmation;
    }

    const result = await adminStore.updateUser(props.user.id, updateData);

    if (result.success) {
      notificationStore.success('Success', 'User updated successfully');
      emit('updated', result.user);
    } else {
      if (result.errors) {
        errors.value = result.errors;
      } else {
        notificationStore.error('Error', result.message);
      }
    }
  } catch (error) {
    console.error('Error updating user:', error);
    notificationStore.error('Error', 'Failed to update user');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.gradient-text {
  background: linear-gradient(135deg, #a855f7, #d946ef);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn-secondary {
  background: white;
  color: #475569;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #a855f7, #d946ef);
  color: white;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 14px 0 rgba(168, 85, 247, 0.25);
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  box-shadow: 0 10px 25px -3px rgba(168, 85, 247, 0.3);
  transform: scale(1.05);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.animate-bounce-in {
  animation: bounceIn 0.3s ease-out;
}

@keyframes bounceIn {
  0% { transform: scale(0.9); opacity: 0; }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); opacity: 1; }
}
</style>
