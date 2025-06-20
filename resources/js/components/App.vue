<template>
  <div id="app" class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-purple-50">
    <!-- Navigation -->
    <nav v-if="authStore.isAuthenticated" class="glass border-b border-white/20 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <router-link
              to="/dashboard"
              class="flex items-center space-x-3 group"
            >
              <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl flex items-center justify-center shadow-purple group-hover:shadow-purple-lg transition-all duration-300 group-hover:scale-110">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
              </div>
              <span class="text-xl font-bold gradient-text font-display">
                ChatWeb
              </span>
            </router-link>
          </div>

          <!-- Navigation Links -->
          <div class="hidden md:flex items-center space-x-2">
            <router-link
              to="/dashboard"
              class="nav-link"
              :class="{ 'nav-link-active': $route.path === '/dashboard' }"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
              </svg>
              Dashboard
            </router-link>

            <router-link
              to="/chat"
              class="nav-link"
              :class="{ 'nav-link-active': $route.path.startsWith('/chat') }"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              Chat
            </router-link>

            <router-link
              v-if="authStore.isAdmin"
              to="/admin"
              class="nav-link"
              :class="{ 'nav-link-active': $route.path.startsWith('/admin') }"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              Admin
            </router-link>

            <!-- User Menu -->
            <div class="relative ml-4">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-2 p-2 rounded-xl hover:bg-white/20 transition-all duration-300 group"
              >
                <div class="w-8 h-8 bg-gradient-to-r from-primary-400 to-accent-400 rounded-lg flex items-center justify-center text-white font-semibold text-sm shadow-sm group-hover:shadow-purple transition-all duration-300">
                  {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                </div>
                <span class="hidden lg:block text-sm font-medium text-secondary-700">
                  {{ authStore.user?.name }}
                </span>
                <svg class="w-4 h-4 text-secondary-500 transition-transform duration-300" :class="{ 'rotate-180': showUserMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-if="showUserMenu"
                  class="absolute right-0 mt-2 w-56 glass rounded-2xl shadow-xl border border-white/20 py-2 z-50"
                >
                  <div class="px-4 py-3 border-b border-white/10">
                    <p class="text-sm font-medium text-secondary-800">{{ authStore.user?.name }}</p>
                    <p class="text-xs text-secondary-500">{{ authStore.user?.email }}</p>
                  </div>

                  <router-link
                    to="/profile"
                    class="dropdown-item"
                    @click="showUserMenu = false"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile
                  </router-link>

                  <router-link
                    to="/settings"
                    class="dropdown-item"
                    @click="showUserMenu = false"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                  </router-link>

                  <div class="border-t border-white/10 mt-2 pt-2">
                    <button
                      @click="logout"
                      class="dropdown-item text-error-600 hover:bg-error-50"
                    >
                      <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                      </svg>
                      Sign out
                    </button>
                  </div>
                </div>
              </transition>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="md:hidden flex items-center">
            <button
              @click="showMobileMenu = !showMobileMenu"
              class="p-2 rounded-xl hover:bg-white/20 transition-all duration-300"
            >
              <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div v-if="showMobileMenu" class="md:hidden glass border-t border-white/20">
          <div class="px-4 py-3 space-y-2">
            <router-link to="/dashboard" class="mobile-nav-link">Dashboard</router-link>
            <router-link to="/chat" class="mobile-nav-link">Chat</router-link>
            <router-link v-if="authStore.isAdmin" to="/admin" class="mobile-nav-link">Admin</router-link>
            <router-link to="/profile" class="mobile-nav-link">Profile</router-link>
            <button @click="logout" class="mobile-nav-link text-error-600 w-full text-left">Sign out</button>
          </div>
        </div>
      </transition>
    </nav>

    <!-- Main content -->
    <main class="flex-1 relative">
      <router-view />
    </main>

    <!-- Global notifications -->
    <div class="fixed top-20 right-4 z-50 space-y-3 max-w-sm">
      <transition-group
        name="notification"
        tag="div"
        class="space-y-3"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notification animate-slide-in-right"
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
              <svg v-if="notification.type === 'success'" class="w-5 h-5 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else-if="notification.type === 'error'" class="w-5 h-5 text-error-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else-if="notification.type === 'warning'" class="w-5 h-5 text-warning-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold" :class="{
                'text-success-800': notification.type === 'success',
                'text-error-800': notification.type === 'error',
                'text-warning-800': notification.type === 'warning',
                'text-primary-800': notification.type === 'info'
              }">
                {{ notification.title }}
              </p>
              <p class="text-sm mt-1" :class="{
                'text-success-600': notification.type === 'success',
                'text-error-600': notification.type === 'error',
                'text-warning-600': notification.type === 'warning',
                'text-primary-600': notification.type === 'info'
              }">
                {{ notification.message }}
              </p>
            </div>

            <!-- Close button -->
            <button
              @click="removeNotification(notification.id)"
              class="flex-shrink-0 ml-3 p-1 rounded-lg hover:bg-black/5 transition-colors duration-200"
            >
              <svg class="w-4 h-4 text-secondary-400 hover:text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Loading Overlay -->
    <div v-if="authStore.loading" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="glass rounded-2xl p-8 flex flex-col items-center space-y-4">
        <div class="spinner"></div>
        <p class="text-secondary-600 font-medium">Loading...</p>
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

  if (!mobileMenuButton) {
    showMobileMenu.value = false;
  }
};

// Close mobile menu when route changes
const handleRouteChange = () => {
  showMobileMenu.value = false;
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
  color: #475569;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
  transform: scale(1);
}

.nav-link:hover {
  color: #9333ea;
  background-color: rgba(255, 255, 255, 0.2);
  transform: scale(1.05);
}

.nav-link-active {
  color: #9333ea;
  background-color: rgba(255, 255, 255, 0.3);
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.dropdown-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  color: #334155;
  transition: all 0.2s ease;
  border-radius: 0.5rem;
  margin: 0 0.5rem;
}

.dropdown-item:hover {
  background-color: rgba(255, 255, 255, 0.2);
  color: #9333ea;
}

.mobile-nav-link {
  display: block;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #334155;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
}

.mobile-nav-link:hover {
  color: #9333ea;
  background-color: rgba(255, 255, 255, 0.2);
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

/* Custom Animations */
@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-slide-in-right {
  animation: slideInRight 0.3s ease-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .nav-link {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }
}
</style>
