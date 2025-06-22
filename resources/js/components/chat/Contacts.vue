<template>
  <div class="flex-1 overflow-y-auto">
    <!-- Search Results atau All Users -->
    <div v-if="usersStore.loading" class="p-4 space-y-4">
      <div v-for="i in 8" :key="i" class="animate-pulse">
        <div class="flex items-center space-x-3 p-3">
          <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="usersStore.filteredUsers.length === 0" class="p-6 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
      <h3 class="text-sm font-medium text-gray-900 mb-1">No contacts found</h3>
      <p class="text-sm text-gray-500">Try searching for users</p>
    </div>

    <div v-else>
      <!-- Online Users Section -->
      <div v-if="usersStore.onlineUsers.length > 0" class="mb-6">
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">
            Online ({{ usersStore.onlineUsers.length }})
          </h3>
        </div>
        <div>
          <button
            v-for="user in usersStore.onlineUsers"
            :key="`online-${user.id}`"
            @click="startChat(user)"
            class="w-full p-4 flex items-center hover:bg-gray-50 transition-colors group"
            :disabled="usersStore.loading"
          >
            <div class="relative">
              <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold" style="background: var(--primary-gradient)">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <!-- Online indicator -->
              <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            <div class="ml-3 flex-1 text-left">
              <p class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                {{ user.name }}
              </p>
              <p class="text-sm text-green-600 font-medium">Online now</p>
            </div>
            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
          </button>
        </div>
      </div>

      <!-- Offline Users Section -->
      <div v-if="usersStore.offlineUsers.length > 0">
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">
            Offline ({{ usersStore.offlineUsers.length }})
          </h3>
        </div>
        <div>
          <button
            v-for="user in usersStore.offlineUsers"
            :key="`offline-${user.id}`"
            @click="startChat(user)"
            class="w-full p-4 flex items-center hover:bg-gray-50 transition-colors group"
            :disabled="usersStore.loading"
          >
            <div class="relative">
              <div class="w-12 h-12 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center text-white font-semibold">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <!-- Offline indicator -->
              <div class="absolute bottom-0 right-0 w-4 h-4 bg-gray-400 rounded-full border-2 border-white"></div>
            </div>
            <div class="ml-3 flex-1 text-left">
              <p class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                {{ user.name }}
              </p>
              <p class="text-sm text-gray-500">
                {{ formatLastSeen(user.last_seen) }}
              </p>
            </div>
            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
          </button>
        </div>
      </div>

      <!-- All Users (when no online/offline distinction) -->
      <div v-if="!usersStore.onlineUsers.length && !usersStore.offlineUsers.length">
        <button
          v-for="user in usersStore.filteredUsers"
          :key="user.id"
          @click="startChat(user)"
          class="w-full p-4 flex items-center hover:bg-gray-50 transition-colors group"
          :disabled="usersStore.loading"
        >
          <div class="relative">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold" style="background: var(--primary-gradient)">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
          </div>
          <div class="ml-3 flex-1 text-left">
            <p class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
              {{ user.name }}
            </p>
            <p class="text-sm text-gray-500">{{ user.email }}</p>
          </div>
          <div class="opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useUsersStore } from '../../stores/users';
import { useChatStore } from '../../stores/chat';
import { useAuthStore } from '../../stores/auth';
import { useNotificationStore } from '../../stores/notifications';

const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  }
});

const usersStore = useUsersStore();
const chatStore = useChatStore();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const emit = defineEmits(['user-selected', 'chat-created']);

// Watch for search query changes
watch(() => props.searchQuery, async (newQuery) => {
  if (newQuery && newQuery.length >= 2) {
    // Only call API search for queries 2+ chars
    await usersStore.searchUsers(newQuery);
  } else {
    // For short queries, just update the search query for local filtering
    usersStore.searchQuery = newQuery;
  }
});

const formatLastSeen = (timestamp) => {
  if (!timestamp) return 'Long time ago';
  
  const date = new Date(timestamp);
  const now = new Date();
  const diffInMinutes = (now - date) / (1000 * 60);

  if (diffInMinutes < 1) {
    return 'Just now';
  } else if (diffInMinutes < 60) {
    return `${Math.floor(diffInMinutes)}m ago`;
  } else if (diffInMinutes < 1440) { // 24 hours
    return `${Math.floor(diffInMinutes / 60)}h ago`;
  } else {
    const days = Math.floor(diffInMinutes / 1440);
    return `${days}d ago`;
  }
};

const startChat = async (user) => {
  // Check if user is trying to chat with themselves
  if (user.id === authStore.user.id) {
    notificationStore.info('Info', 'You cannot start a chat with yourself');
    return;
  }

  try {
    // Try to create a private chat room
    const result = await usersStore.createPrivateChat(user.id);
    
    if (result.success) {
      // Refresh chat rooms list
      await chatStore.fetchChatRooms();
      
      // Emit event to parent component to select the new chat
      emit('chat-created', result.data);
      
      notificationStore.success('Success', `Chat started with ${user.name}`);
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    console.error('Error starting chat:', error);
    notificationStore.error('Error', 'Failed to start chat');
  }
};

onMounted(async () => {
  // Fetch all users when component mounts
  const result = await usersStore.fetchUsers();
  if (!result.success) {
    notificationStore.error('Error', result.message);
  }
});
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Smooth transitions */
.transition-colors {
  transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}

.transition-opacity {
  transition: opacity 0.2s ease-in-out;
}
</style>
