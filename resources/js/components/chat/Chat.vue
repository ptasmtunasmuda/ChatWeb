<template>
  <div class="h-screen bg-gradient-to-br from-slate-50 via-white to-purple-50 flex">
    <!-- Chat Rooms Sidebar -->
    <div class="w-80 bg-white/80 backdrop-blur-sm border-r border-white/20 flex flex-col">
      <!-- Sidebar Header -->
      <div class="p-6 border-b border-white/20">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold gradient-text font-display">Chats</h2>
          <button
            @click="showCreateRoomModal = true"
            class="w-10 h-10 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl flex items-center justify-center text-white shadow-purple hover:shadow-purple-lg transition-all duration-300 hover:scale-110"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
          </button>
        </div>

        <!-- Search -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search chats..."
            class="w-full pl-10 pr-4 py-2 bg-white/50 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300"
          />
        </div>
      </div>

      <!-- Chat Rooms List -->
      <div class="flex-1 overflow-y-auto">
        <div v-if="chatStore.loading" class="p-4 space-y-4">
          <div v-for="i in 5" :key="i" class="animate-pulse">
            <div class="flex items-center space-x-3 p-3">
              <div class="w-12 h-12 bg-secondary-200 rounded-full"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-secondary-200 rounded w-3/4"></div>
                <div class="h-3 bg-secondary-200 rounded w-1/2"></div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="filteredChatRooms.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          <h3 class="text-sm font-medium text-secondary-900 mb-1">No chats found</h3>
          <p class="text-sm text-secondary-500">Start a new conversation</p>
        </div>

        <div v-else class="p-2">
          <div
            v-for="room in filteredChatRooms"
            :key="room.id"
            @click="selectChatRoom(room)"
            class="flex items-center space-x-3 p-3 rounded-xl cursor-pointer transition-all duration-300 hover:bg-white/50 group"
            :class="{ 'bg-primary-50 border border-primary-200': currentChatRoom?.id === room.id }"
          >
            <div class="relative">
              <div class="w-12 h-12 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-semibold shadow-sm group-hover:shadow-purple transition-all duration-300">
                {{ room.name.charAt(0).toUpperCase() }}
              </div>
              <div v-if="room.unread_count > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-error-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                {{ room.unread_count > 9 ? '9+' : room.unread_count }}
              </div>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-secondary-900 truncate">{{ room.name }}</p>
                <p class="text-xs text-secondary-500">{{ formatTime(room.updated_at) }}</p>
              </div>
              <p class="text-sm text-secondary-600 truncate">
                {{ room.latest_message?.content || 'No messages yet' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col">
      <!-- No Chat Selected -->
      <div v-if="!currentChatRoom" class="flex-1 flex items-center justify-center">
        <div class="text-center">
          <div class="w-24 h-24 bg-gradient-to-r from-primary-500 to-accent-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-purple-lg animate-float">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold gradient-text font-display mb-2">Welcome to ChatWeb</h3>
          <p class="text-secondary-600 mb-6">Select a chat to start messaging</p>
          <button
            @click="showCreateRoomModal = true"
            class="btn-primary"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Start New Chat
          </button>
        </div>
      </div>

      <!-- Chat Interface -->
      <div v-else class="flex-1 flex flex-col">
        <!-- Chat Header -->
        <div class="bg-white/80 backdrop-blur-sm border-b border-white/20 p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-semibold">
                {{ currentChatRoom.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-semibold text-secondary-900">{{ currentChatRoom.name }}</h3>
                <p class="text-sm text-secondary-600">
                  {{ currentChatParticipants.length }} participants
                  <span v-if="typingUsers.length > 0" class="text-primary-600">
                    • {{ typingUsers.map(u => u.name).join(', ') }} typing...
                  </span>
                </p>
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <button class="p-2 rounded-lg hover:bg-white/20 transition-colors duration-200">
                <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
              </button>
              <button class="p-2 rounded-lg hover:bg-white/20 transition-colors duration-200">
                <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
              </button>
              <button class="p-2 rounded-lg hover:bg-white/20 transition-colors duration-200">
                <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Messages Area -->
        <ChatMessages
          :messages="sortedMessages"
          :current-user="authStore.user"
          :loading="chatStore.messagesLoading"
          @edit-message="handleEditMessage"
          @delete-message="handleDeleteMessage"
          @load-more="loadMoreMessages"
        />

        <!-- Message Input -->
        <ChatInput
          @send-message="handleSendMessage"
          @typing="handleTyping"
          :disabled="!currentChatRoom"
        />
      </div>
    </div>

    <!-- Create Room Modal -->
    <CreateRoomModal
      v-if="showCreateRoomModal"
      @close="showCreateRoomModal = false"
      @created="handleRoomCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useChatStore } from '../../stores/chat';
import { useNotificationStore } from '../../stores/notifications';
import ChatMessages from './ChatMessages.vue';
import ChatInput from './ChatInput.vue';
import CreateRoomModal from './CreateRoomModal.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const chatStore = useChatStore();
const notificationStore = useNotificationStore();

const searchQuery = ref('');
const showCreateRoomModal = ref(false);
const typingTimeout = ref(null);

// Computed properties
const currentChatRoom = computed(() => chatStore.currentChatRoom);
const sortedMessages = computed(() => chatStore.sortedMessages);
const currentChatParticipants = computed(() => chatStore.currentChatParticipants);
const typingUsers = computed(() => chatStore.typingUsers);

const filteredChatRooms = computed(() => {
  if (!searchQuery.value) {
    return chatStore.sortedChatRooms;
  }

  return chatStore.sortedChatRooms.filter(room =>
    room.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Methods
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

const selectChatRoom = async (room) => {
  if (currentChatRoom.value?.id === room.id) return;

  // Clear current chat
  chatStore.clearCurrentChat();

  // Fetch chat room details and messages
  const roomResult = await chatStore.fetchChatRoom(room.id);
  if (roomResult.success) {
    const messagesResult = await chatStore.fetchMessages(room.id);
    if (!messagesResult.success) {
      notificationStore.error('Error', messagesResult.message);
    }

    // Update URL
    router.push(`/chat/${room.id}`);
  } else {
    notificationStore.error('Error', roomResult.message);
  }
};

const handleSendMessage = async (messageData) => {
  if (!currentChatRoom.value) return;

  const result = await chatStore.sendMessage(currentChatRoom.value.id, messageData);
  if (!result.success) {
    notificationStore.error('Error', result.message);
  }
};

const handleEditMessage = async (messageId, content) => {
  if (!currentChatRoom.value) return;

  const result = await chatStore.editMessage(currentChatRoom.value.id, messageId, content);
  if (!result.success) {
    notificationStore.error('Error', result.message);
  }
};

const handleDeleteMessage = async (messageId) => {
  if (!currentChatRoom.value) return;

  const result = await chatStore.deleteMessage(currentChatRoom.value.id, messageId);
  if (!result.success) {
    notificationStore.error('Error', result.message);
  }
};

const handleTyping = async (isTyping) => {
  if (!currentChatRoom.value) return;

  await chatStore.sendTypingStatus(currentChatRoom.value.id, isTyping);
};

const loadMoreMessages = async () => {
  if (!currentChatRoom.value) return;

  // Implementation for pagination will be added
  console.log('Load more messages');
};

const handleRoomCreated = (room) => {
  showCreateRoomModal.value = false;
  selectChatRoom(room);
  notificationStore.success('Success', 'Chat room created successfully!');
};

// Initialize chat
const initializeChat = async () => {
  // Fetch chat rooms
  const result = await chatStore.fetchChatRooms();
  if (!result.success) {
    notificationStore.error('Error', result.message);
    return;
  }

  // If there's a room ID in the route, select it
  const roomId = route.params.id;
  if (roomId) {
    const room = chatStore.chatRooms.find(r => r.id == roomId);
    if (room) {
      await selectChatRoom(room);
    } else {
      router.push('/chat');
    }
  }
};

// Setup real-time listeners
const setupRealTimeListeners = () => {
  if (!window.Echo) return;

  // Listen for new messages in current chat room
  if (currentChatRoom.value) {
    window.Echo.private(`chat-room.${currentChatRoom.value.id}`)
      .listen('.message.sent', (e) => {
        chatStore.addMessage(e.message);
      })
      .listen('.user.typing', (e) => {
        if (e.user.id !== authStore.user.id) {
          chatStore.updateTypingUsers(e.user, e.is_typing);

          // Clear typing indicator after 3 seconds
          if (e.is_typing) {
            setTimeout(() => {
              chatStore.updateTypingUsers(e.user, false);
            }, 3000);
          }
        }
      })
      .listen('.user.joined', (e) => {
        if (e.user.id !== authStore.user.id) {
          notificationStore.info('User Joined', `${e.user.name} joined the chat`);
        }
      })
      .listen('.user.left', (e) => {
        if (e.user.id !== authStore.user.id) {
          notificationStore.info('User Left', `${e.user.name} left the chat`);
        }
      });
  }
};

// Watch for chat room changes to setup listeners
watch(currentChatRoom, (newRoom, oldRoom) => {
  if (oldRoom && window.Echo) {
    window.Echo.leave(`chat-room.${oldRoom.id}`);
  }

  if (newRoom) {
    setupRealTimeListeners();
  }
});

onMounted(() => {
  initializeChat();
});

onUnmounted(() => {
  if (currentChatRoom.value && window.Echo) {
    window.Echo.leave(`chat-room.${currentChatRoom.value.id}`);
  }
  chatStore.clearCurrentChat();
});
</script>

<style scoped>
.gradient-text {
  background: linear-gradient(135deg, #a855f7, #d946ef);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn-primary {
  background: linear-gradient(135deg, #a855f7, #d946ef);
  color: white;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 4px 14px 0 rgba(168, 85, 247, 0.25);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover {
  box-shadow: 0 10px 25px -3px rgba(168, 85, 247, 0.3);
  transform: scale(1.05);
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}
</style>
