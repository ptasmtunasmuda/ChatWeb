<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation - DIHAPUS TOTAL untuk semua role -->
    <!-- Semua user (admin dan user) tidak memiliki navigation bar -->

    <!-- Main content -->
    <main class="flex-1 relative">
      <router-view />
    </main>

    <!-- Global notifications -->
    <div class="fixed right-4 z-50 space-y-3 max-w-sm" :class="authStore.isAdmin ? 'top-20' : 'top-4'">
      <transition-group
        name="notification"
        tag="div"
        class="space-y-3"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notification"
          :class="{
            'success': notification.type === 'success',
            'error': notification.type === 'error',
            'info': notification.type === 'info',
            'warning': notification.type === 'warning'
          }"
        >
          <div class="flex items-start">
            <!-- Icon -->
            <div class="flex-shrink-0 mr-3">
              <svg v-if="notification.type === 'success'" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else-if="notification.type === 'error'" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else-if="notification.type === 'warning'" class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold" :class="{
                'text-green-800': notification.type === 'success',
                'text-red-800': notification.type === 'error',
                'text-yellow-800': notification.type === 'warning',
                'text-blue-800': notification.type === 'info'
              }">
                {{ notification.title }}
              </p>
              <p class="text-sm mt-1" :class="{
                'text-green-600': notification.type === 'success',
                'text-red-600': notification.type === 'error',
                'text-yellow-600': notification.type === 'warning',
                'text-blue-600': notification.type === 'info'
              }">
                {{ notification.message }}
              </p>
            </div>

            <!-- Close button -->
            <button
              @click="removeNotification(notification.id)"
              class="flex-shrink-0 ml-3 p-1 rounded-lg hover:bg-black/5 transition-colors duration-200"
            >
              <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Loading Overlay -->
    <div v-if="authStore.loading" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="card flex flex-col items-center space-y-4">
        <div class="spinner"></div>
        <p class="text-gray-600 font-medium">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useNotificationStore } from '../stores/notifications';

const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const showUserMenu = ref(false);
const showMobileMenu = ref(false);

const notifications = computed(() => notificationStore.notifications);

const logout = async () => {
  try {
    await authStore.logout();
    showUserMenu.value = false;
    showMobileMenu.value = false;
    router.push('/login');
  } catch (error) {
    notificationStore.error('Logout Failed', 'Failed to logout. Please try again.');
  }
};

const removeNotification = (id) => {
  notificationStore.removeNotification(id);
};

// Close menus when clicking outside
const handleClickOutside = (event) => {
  const userMenuElement = event.target.closest('.relative');
  const mobileMenuButton = event.target.closest('[data-mobile-menu]');

  if (!userMenuElement) {
    showUserMenu.value = false;
  }

  if (!mobileMenuButton && authStore.isAdmin) {
    showMobileMenu.value = false;
  }
};

// Close mobile menu when route changes
const handleRouteChange = () => {
  if (authStore.isAdmin) {
    showMobileMenu.value = false;
  }
  showUserMenu.value = false;
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);

  // Listen for route changes
  router.afterEach(handleRouteChange);

  // Initialize auth if token exists
  if (authStore.token && !authStore.user) {
    authStore.fetchUser();
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Navigation Styles */
.nav-link {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.nav-link:hover {
  color: #3b82f6;
  background-color: #f8fafc;
}

.nav-link-active {
  color: #3b82f6;
  background-color: #eff6ff;
}

.dropdown-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  color: #374151;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background-color: #f9fafb;
  color: #3b82f6;
}

.mobile-nav-link {
  display: block;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.mobile-nav-link:hover {
  color: #3b82f6;
  background-color: #f8fafc;
}

/* Notification Styles */
.notification {
  background: white;
  border-radius: 0.5rem;
  padding: 1rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border-left: 4px solid;
}

.notification.success {
  border-left-color: #10b981;
}

.notification.error {
  border-left-color: #ef4444;
}

.notification.warning {
  border-left-color: #f59e0b;
}

.notification.info {
  border-left-color: #3b82f6;
}

/* Notification Animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Spinner */
.spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid #e5e7eb;
  border-radius: 50%;
  border-top-color: #3b82f6;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
