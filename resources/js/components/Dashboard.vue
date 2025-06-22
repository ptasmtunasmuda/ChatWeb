<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Welcome back, {{ authStore.user?.name }}!
            </h1>
            <p class="text-gray-600 mt-2">
              Here's what's happening in your chats today.
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <router-link to="/chat" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              Start Chatting
            </router-link>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Chats</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalChats }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Messages Today</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.messagesToday }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Active Groups</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.activeGroups }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Online Friends</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.onlineFriends }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Chats -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-bold text-gray-900">Recent Chats</h2>
              <router-link to="/chat" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                View All
              </router-link>
            </div>

            <div v-if="loading" class="space-y-4">
              <div v-for="i in 3" :key="i" class="animate-pulse">
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                  <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="recentChats.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No chats yet</h3>
              <p class="mt-1 text-sm text-gray-500">Start a conversation to see your chats here.</p>
              <div class="mt-6">
                <router-link to="/chat" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                  Start Your First Chat
                </router-link>
              </div>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="chat in recentChats"
                :key="chat.id"
                class="flex items-center space-x-4 p-4 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
                @click="$router.push(`/chat/${chat.id}`)"
              >
                <div class="relative">
                  <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ chat.name.charAt(0).toUpperCase() }}
                  </div>
                  <div v-if="chat.unread_count > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                    {{ chat.unread_count > 9 ? '9+' : chat.unread_count }}
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ chat.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatTime(chat.updated_at) }}</p>
                  </div>
                  <p class="text-sm text-gray-600 truncate">
                    {{ chat.latest_message?.content || 'No messages yet' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions & Activity -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button class="w-full flex items-center p-3 text-left rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900">Create Group</p>
                  <p class="text-sm text-gray-500">Start a new group chat</p>
                </div>
              </button>

              <button class="w-full flex items-center p-3 text-left rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900">Find Friends</p>
                  <p class="text-sm text-gray-500">Search for users to chat</p>
                </div>
              </button>

              <router-link to="/profile" class="w-full flex items-center p-3 text-left rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900">Edit Profile</p>
                  <p class="text-sm text-gray-500">Update your information</p>
                </div>
              </router-link>
            </div>
          </div>

          <!-- Online Users -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Online Now</h3>
            <div v-if="onlineUsers.length === 0" class="text-center py-6">
              <p class="text-sm text-gray-500">No users online</p>
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="user in onlineUsers"
                :key="user.id"
                class="flex items-center space-x-3"
              >
                <div class="relative">
                  <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                  <p class="text-xs text-green-600">Online</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useNotificationStore } from '../stores/notifications';
import axios from 'axios';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const loading = ref(true);
const recentChats = ref([]);
const onlineUsers = ref([]);

const stats = ref({
  totalChats: 0,
  messagesToday: 0,
  activeGroups: 0,
  onlineFriends: 0
});

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diffInHours = (now - date) / (1000 * 60 * 60);

  if (diffInHours < 1) {
    return 'Just now';
  } else if (diffInHours < 24) {
    return `${Math.floor(diffInHours)}h ago`;
  } else {
    return date.toLocaleDateString();
  }
};

const fetchDashboardData = async () => {
  try {
    loading.value = true;

    // Check if user is authenticated
    if (!authStore.user) {
      // Mock data for unauthenticated users
      recentChats.value = [];
      stats.value = {
        totalChats: 0,
        messagesToday: 0,
        activeGroups: 0,
        onlineFriends: 0
      };
      onlineUsers.value = [];
      return;
    }

    // Fetch recent chats for authenticated users
    try {
      const chatsResponse = await axios.get('/api/chat-rooms?per_page=5');
      recentChats.value = chatsResponse.data.data || [];
    } catch (chatError) {
      console.warn('Could not fetch chat rooms:', chatError);
      recentChats.value = [];
    }

    // Mock stats for now (you can implement real API endpoints later)
    stats.value = {
      totalChats: recentChats.value.length,
      messagesToday: Math.floor(Math.random() * 50),
      activeGroups: recentChats.value.filter(chat => chat.type === 'group').length,
      onlineFriends: Math.floor(Math.random() * 10)
    };

    // Mock online users
    onlineUsers.value = [
      { id: 1, name: 'John Doe' },
      { id: 2, name: 'Jane Smith' },
      { id: 3, name: 'Mike Johnson' }
    ];

  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    notificationStore.error('Error', 'Failed to load dashboard data');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
/* Custom animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>