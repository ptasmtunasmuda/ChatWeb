<template>
  <div class="h-screen bg-gray-50 flex overflow-hidden">
    <!-- Chat Rooms Sidebar -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
      <!-- Sidebar Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ authStore.user?.name?.charAt(0).toUpperCase() || 'A' }}
            </div>
            <div class="ml-3">
              <p class="font-semibold text-gray-900">{{ authStore.user?.name }}</p>
              <p class="text-sm text-gray-500">ChatWeb</p>
            </div>
          </div>
          <div class="relative">
            <button
              @click="dropdownOpen = !dropdownOpen"
              class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors"
            >
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z" />
              </svg>
            </button>
            <div
              v-if="dropdownOpen"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
            >
              <div class="py-1">
                <router-link
                  to="/profile"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="dropdownOpen = false"
                >
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Profile
                </router-link>

                <!-- Admin Menu (Only for Admin) -->
                <router-link
                  v-if="authStore.isAdmin"
                  to="/admin"
                  class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="dropdownOpen = false"
                >
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  Admin
                </router-link>

                <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Help & feedback
                </button>
                <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="handleLogout">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Sign Out
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Search -->
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="currentView === 'contacts' ? 'Search contacts...' : 'Search chats...'"
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            @input="handleSearch"
          />
          <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- Navigation -->
      <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex justify-between text-sm">
          <button
            @click="currentView = 'chats'"
            :class="[
              'flex flex-col items-center transition-colors',
              currentView === 'chats' ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600'
            ]"
          >
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            Chats
          </button>
          <button
            @click="currentView = 'calls'"
            :class="[
              'flex flex-col items-center transition-colors',
              currentView === 'calls' ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600'
            ]"
          >
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            Calls
          </button>
          <button
            @click="currentView = 'contacts'"
            :class="[
              'flex flex-col items-center transition-colors',
              currentView === 'contacts' ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600'
            ]"
          >
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Contacts
          </button>
          <button
            @click="currentView = 'notifications'"
            :class="[
              'flex flex-col items-center transition-colors',
              currentView === 'notifications' ? 'text-blue-600' : 'text-gray-500 hover:text-blue-600'
            ]"
          >
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.0001 9.7041V9C19.0001 5.13401 15.8661 2 12.0001 2C8.13407 2 5.00006 5.13401 5.00006 9V9.7041C5.00006 10.5491 4.74995 11.3752 4.28123 12.0783L3.13263 13.8012C2.08349 15.3749 2.88442 17.5139 4.70913 18.0116C9.48258 19.3134 14.5175 19.3134 19.291 18.0116C21.1157 17.5139 21.9166 15.3749 20.8675 13.8012L19.7189 12.0783C19.2502 11.3752 19.0001 10.5491 19.0001 9.7041Z" />
              <path opacity="0.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 19C8.15503 20.7478 9.92246 22 12 22C14.0775 22 15.845 20.7478 16.5 19" />
            </svg>
            Notification
          </button>
        </div>
      </div>

      <!-- Chat Rooms List -->
      <div class="flex-1 overflow-y-auto">
        <!-- Chats Tab -->
        <div v-if="currentView === 'chats'">
          <div v-if="chatStore.loading" class="p-4 space-y-4">
            <div v-for="i in 5" :key="i" class="animate-pulse">
              <div class="flex items-center space-x-3 p-3">
                <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                  <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="filteredChatRooms.length === 0" class="p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-900 mb-1">No chats found</h3>
            <p class="text-sm text-gray-500">Start a new conversation</p>
          </div>

          <div v-else>
            <button
              v-for="room in filteredChatRooms"
              :key="room.id"
              @click="selectChatRoom(room)"
              :class="[
                'w-full p-4 flex items-center hover:bg-gray-50 transition-colors',
                selectedChatRoom?.id === room.id ? 'bg-blue-50 border-r-2 border-blue-500' : ''
              ]"
            >
              <div class="relative">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ getChatAvatarInitial(room) }}
                </div>
                <!-- Online indicator for private chats -->
                <div
                  v-if="isOtherParticipantOnline(room)"
                  class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-white"
                ></div>
                <!-- Unread count badge -->
                <div
                  v-if="room.unread_count > 0"
                  class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"
                >
                  {{ room.unread_count > 9 ? '9+' : room.unread_count }}
                </div>
              </div>
              <div class="ml-3 flex-1 text-left">
                <p class="font-semibold text-gray-900">{{ getChatDisplayName(room) }}</p>
                <p class="text-sm text-gray-500 truncate">{{ room.latest_message?.content || 'No messages yet' }}</p>
              </div>
              <div class="text-xs text-gray-500">{{ formatTime(room.updated_at) }}</div>
            </button>
          </div>
        </div>

        <!-- Contacts Tab -->
        <Contacts
          v-else-if="currentView === 'contacts'"
          :search-query="searchQuery"
          @user-selected="handleUserSelected"
          @chat-created="handleChatCreated"
        />

        <!-- Calls Tab -->
        <div v-else-if="currentView === 'calls'" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
          </svg>
          <h3 class="text-sm font-medium text-gray-900 mb-1">Calls</h3>
          <p class="text-sm text-gray-500">Call history will appear here</p>
        </div>

        <!-- Notifications Tab -->
        <div v-else-if="currentView === 'notifications'" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM16 3h5v5h-5V3zM4 3h6v6H4V3z"></path>
          </svg>
          <h3 class="text-sm font-medium text-gray-900 mb-1">Notifications</h3>
          <p class="text-sm text-gray-500">Your notifications will appear here</p>
        </div>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col">
      <!-- Welcome Screen -->
      <div v-if="!currentChatRoom" class="flex-1 flex items-center justify-center bg-white">
        <button
          @click="isShowChatMenu = !isShowChatMenu"
          class="xl:hidden absolute top-4 left-4 p-2 rounded-lg hover:bg-gray-100"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="text-center">
          <div class="w-64 h-64 mb-8 mx-auto">
            <svg viewBox="0 0 400 400" class="w-full h-full text-gray-300">
              <circle cx="200" cy="200" r="180" fill="currentColor" opacity="0.1"/>
              <svg x="176" y="176" class="w-12 h-12" fill="currentColor" opacity="0.3" viewBox="0 0 24 24">
                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </svg>
          </div>
          <div class="flex items-center justify-center bg-gray-100 px-6 py-3 rounded-lg max-w-xs mx-auto">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span class="font-semibold text-gray-700">Click User To Chat</span>
          </div>
        </div>
      </div>

      <!-- Chat Interface -->
      <div v-else class="flex-1 flex flex-col h-full relative">
        <!-- Chat Header -->
        <div class="bg-white border-b border-gray-200 px-4 py-3 flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <button
                @click="isShowChatMenu = !isShowChatMenu"
                class="xl:hidden mr-3 p-2 rounded-lg hover:bg-gray-100"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div class="relative">
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ getChatAvatarInitial(selectedChatRoom) }}
                </div>
                <div
                  v-if="isOtherParticipantOnline(selectedChatRoom)"
                  class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 rounded-full border-2 border-white"
                ></div>
              </div>
              <div class="ml-3">
                <p class="font-semibold text-gray-900">{{ getChatDisplayName(selectedChatRoom) }}</p>
                <p class="text-sm" :class="isOtherParticipantOnline(selectedChatRoom) ? 'text-green-600' : 'text-gray-500'">
                  {{ getChatStatusText(selectedChatRoom) }}
                </p>
              </div>
            </div>
            <div class="flex space-x-3">
              <button class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </button>
              <button class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </button>
              <button class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>
          </div>


        </div>

        <!-- Messages Area - Scrollable -->
        <div class="flex-1 min-h-0">
          <ChatMessages
            :messages="sortedMessages"
            :current-user="authStore.user"
            :loading="chatStore.messagesLoading"
            :typing-users="typingUsers"
            @edit-message="handleEditMessage"
            @delete-message="handleDeleteMessage"
            @load-more="loadMoreMessages"
            @start-edit="handleStartEdit"
          />
        </div>

        <!-- Message Input - Fixed at bottom -->
        <div class="flex-shrink-0">
          <ChatInput
            @send-message="handleSendMessage"
            @typing="handleTyping"
            @edit-message="handleSaveEdit"
            @cancel-edit="handleCancelEdit"
            :disabled="!currentChatRoom"
            :editing-message="editingMessage"
          />
        </div>
      </div>
    </div>

    <!-- Create Room Modal -->
    <CreateRoomModal
      v-if="showCreateRoomModal"
      @close="showCreateRoomModal = false"
      @created="handleRoomCreated"
    />

    <!-- Mobile Overlay -->
    <div
      v-if="isShowChatMenu"
      @click="isShowChatMenu = false"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 xl:hidden"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useChatStore } from '../../stores/chat';
import { useUsersStore } from '../../stores/users';
import { useNotificationStore } from '../../stores/notifications';
import ChatMessages from './ChatMessages.vue';
import ChatInput from './ChatInput.vue';
import CreateRoomModal from './CreateRoomModal.vue';
import Contacts from './Contacts.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const chatStore = useChatStore();
const usersStore = useUsersStore();
const notificationStore = useNotificationStore();

const searchQuery = ref('');
const showCreateRoomModal = ref(false);
const isShowChatMenu = ref(false);
const dropdownOpen = ref(false);
const selectedChatRoom = ref(null);
const currentView = ref('chats'); // 'chats', 'calls', 'contacts', 'notifications'

// Computed properties
const currentChatRoom = computed(() => chatStore.currentChatRoom);
const sortedMessages = computed(() => chatStore.sortedMessages);
const currentChatParticipants = computed(() => chatStore.currentChatParticipants);
const typingUsers = computed(() => chatStore.typingUsers);

// Helper function to get display name for chat room
const getChatDisplayName = (room) => {
  if (!room) return '';

  // For private chats, show the other participant's name
  if (room.type === 'private' && room.participants) {
    const otherParticipant = room.participants.find(p => p.id !== authStore.user?.id);
    return otherParticipant ? otherParticipant.name : room.name;
  }

  // For group chats, use the room name
  return room.name;
};

// Helper function to get avatar initial for chat room
const getChatAvatarInitial = (room) => {
  const displayName = getChatDisplayName(room);
  return displayName.charAt(0).toUpperCase();
};

// Helper function to get other participant from private chat
const getOtherParticipant = (room) => {
  if (!room || room.type !== 'private' || !room.participants) return null;
  return room.participants.find(p => p.id !== authStore.user?.id);
};

// Helper function to check if other participant is online
const isOtherParticipantOnline = (room) => {
  const otherParticipant = getOtherParticipant(room);
  if (!otherParticipant) return false;

  return usersStore.onlineUsers.some(user => user.id === otherParticipant.id);
};

// Helper function to format last seen time
const formatLastSeen = (timestamp) => {
  if (!timestamp) return 'Long time ago';

  const date = new Date(timestamp);
  const now = new Date();
  const diffInMinutes = (now - date) / (1000 * 60);

  if (diffInMinutes < 1) {
    return 'Just now';
  } else if (diffInMinutes < 60) {
    return `${Math.floor(diffInMinutes)} minutes ago`;
  } else if (diffInMinutes < 1440) {
    return `${Math.floor(diffInMinutes / 60)} hours ago`;
  } else {
    const days = Math.floor(diffInMinutes / 1440);
    return `${days} day${days > 1 ? 's' : ''} ago`;
  }
};

// Helper function to get status text for chat header
const getChatStatusText = (room) => {
  if (!room || room.type !== 'private') return '';

  const otherParticipant = getOtherParticipant(room);
  if (!otherParticipant) return '';

  // Check if user is online
  if (isOtherParticipantOnline(room)) {
    return 'Online now';
  }

  // Show last seen time
  return `Last seen ${formatLastSeen(otherParticipant.last_seen)}`;
};

const filteredChatRooms = computed(() => {
  if (!searchQuery.value || currentView.value !== 'chats') {
    return chatStore.sortedChatRooms;
  }

  return chatStore.sortedChatRooms.filter(room => {
    const displayName = getChatDisplayName(room);
    return displayName.toLowerCase().includes(searchQuery.value.toLowerCase());
  });
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

  selectedChatRoom.value = room;

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
  if (!currentChatRoom.value) {
    console.warn('⚠️ Cannot send message: no current chat room');
    return;
  }

  console.log('📤 Sending message:', messageData);
  console.log('📍 To chat room:', currentChatRoom.value.id);

  const result = await chatStore.sendMessage(currentChatRoom.value.id, messageData);

  if (result.success) {
    console.log('✅ Message sent successfully:', result);
  } else {
    console.error('❌ Failed to send message:', result);
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
  if (result.success) {
    notificationStore.success('Message Deleted', 'Your message has been deleted successfully.');
  } else {
    notificationStore.error('Error', result.message);
  }
};

// Edit message state
const editingMessage = ref(null);

const handleStartEdit = (message) => {
  editingMessage.value = message;
};

const handleCancelEdit = () => {
  editingMessage.value = null;
};

const handleSaveEdit = async (content) => {
  if (!editingMessage.value) return;

  const result = await chatStore.editMessage(currentChatRoom.value.id, editingMessage.value.id, content);
  if (result.success) {
    editingMessage.value = null;
    notificationStore.success('Message Updated', 'Your message has been updated successfully.');
  } else {
    notificationStore.error('Error', result.message);
  }
};

const handleTyping = async (isTyping) => {
  if (!currentChatRoom.value) return;

  await chatStore.sendTypingStatus(currentChatRoom.value.id, isTyping);
};

const loadMoreMessages = async () => {
  if (!currentChatRoom.value) return;

  console.log('Load more messages');
};

const handleChatCreated = async (chatRoom) => {
  // Refresh chat rooms list
  await chatStore.fetchChatRooms();

  // Switch to chats view and select the new chat room
  currentView.value = 'chats';
  await selectChatRoom(chatRoom);

  notificationStore.success('Success', 'Chat room created successfully!');
};

const handleUserSelected = (user) => {
  console.log('User selected:', user);
  // This could be used for other purposes if needed
};

const handleRoomCreated = (room) => {
  showCreateRoomModal.value = false;
  selectChatRoom(room);
  notificationStore.success('Success', 'Chat room created successfully!');
};

const handleSearch = async () => {
  if (currentView.value === 'contacts') {
    // Update search query for local filtering
    usersStore.searchQuery = searchQuery.value;

    // Only call API for longer queries
    if (searchQuery.value.trim() && searchQuery.value.length >= 2) {
      await usersStore.searchUsers(searchQuery.value);
    }
  }
  // For chats, the computed property handles the filtering
};



// Initialize chat
const initializeChat = async () => {
  // Start heartbeat for online status
  usersStore.startHeartbeat();

  // Setup real-time listeners for user status
  usersStore.setupRealtimeListeners();

  // Fetch users data for online status
  const usersResult = await usersStore.fetchUsers();
  if (!usersResult.success) {
    console.warn('⚠️ Failed to fetch users:', usersResult.message);
  }

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
  if (!window.Echo) {
    console.error('❌ Laravel Echo not available');
    return;
  }

  console.log('🔧 Setting up global real-time listeners...');

  try {
    // Listen for new chat rooms
    const chatRoomsChannel = window.Echo.channel('chat-rooms');
    console.log('📡 Chat rooms channel created:', chatRoomsChannel);

    chatRoomsChannel.listen('ChatRoomCreated', (e) => {
      console.log('🎉 New chat room created event received:', e);
      // Add new room to the list if user is participant
      if (e.chatRoom && e.chatRoom.participants && e.chatRoom.participants.some(p => p.id === authStore.user.id)) {
        chatStore.addChatRoom(e.chatRoom);
        notificationStore.info('New Chat', `New chat room: ${e.chatRoom.name}`);
      }
    }).error((error) => {
      console.error('❌ Error on chat-rooms channel:', error);
    });

    // Listen for messages in all chat rooms for sidebar updates
    const userMessagesChannel = window.Echo.channel('user-messages');
    console.log('📡 User messages channel created:', userMessagesChannel);

    userMessagesChannel.listen('MessageSent', (e) => {
      console.log('🎉 Global MessageSent event received:', e);
      console.log('📨 Message data for sidebar update:', e.message);

      if (e.message && e.message.chat_room_id) {
        // Update chat room's latest message and timestamp
        chatStore.updateChatRoomLastMessage(e.message.chat_room_id, e.message);
        console.log('✅ Chat room sidebar updated');

        // Don't add to current room here - private channel handles it
        // This channel is only for sidebar updates
      } else {
        console.warn('⚠️ Invalid message data for sidebar update');
      }
    }).error((error) => {
      console.error('❌ Error on user-messages channel:', error);
    });

    // Add global listener for user-messages channel too
    if (userMessagesChannel.subscription) {
      userMessagesChannel.subscription.bind_global((eventName, data) => {
        console.log('🌍 Global event on user-messages channel:', eventName, data);
      });
    }

    console.log('✅ Global real-time listeners setup complete');
  } catch (error) {
    console.error('❌ Failed to setup real-time listeners:', error);
  }
};

// Setup room-specific listeners
const setupRoomListeners = () => {
  if (!window.Echo || !currentChatRoom.value) {
    console.warn('⚠️ Cannot setup room listeners: Echo or currentChatRoom not available');
    return;
  }

  console.log(`🔧 Setting up listeners for room: ${currentChatRoom.value.id}`);

  try {
    const channel = window.Echo.private(`chat-room.${currentChatRoom.value.id}`);

    console.log('📡 Private channel created:', channel);

    // Listen for all events via global listener (most reliable)
    if (channel.subscription) {
      channel.subscription.bind_global((eventName, data) => {
        console.log('🌍 Global event received on private channel:', eventName, data);
        console.log('🔍 Event details:', {
          eventName,
          hasMessage: !!data.message,
          messageId: data.message?.id,
          chatRoomId: data.message?.chat_room_id,
          currentRoomId: currentChatRoom.value?.id
        });

        if (eventName === 'MessageSent' && data.message) {
          console.log('🎯 MessageSent received via global listener!');
          chatStore.addMessage(data.message);
        } else if (eventName === 'message.updated' && data.message) {
          console.log('🎯 message.updated received via global listener!', data.message);
          console.log('🎯 Calling chatStore.updateMessage...');
          chatStore.updateMessage(data.message);
        } else if (eventName === 'message.deleted' && data.message) {
          console.log('🎯 message.deleted received via global listener!', data.message);
          console.log('🎯 Calling chatStore.markMessageAsDeleted...');
          chatStore.markMessageAsDeleted(data.message);
        } else {
          console.log('🤷 Unknown or unhandled event:', eventName, 'Data:', data);
        }
      });
    }

    // Backup: Standard listeners
    channel.listen('MessageSent', (e) => {
      console.log('🎉 MessageSent event received via standard listener:', e);
      // Don't add message here to avoid duplicates - global listener handles it
    });

    channel.listen('.message.updated', (e) => {
      console.log('🔄 message.updated event received via standard listener:', e);
      // Don't update message here to avoid duplicates - global listener handles it
    });

    channel.listen('.message.deleted', (e) => {
      console.log('🗑️ message.deleted event received via standard listener:', e);
      // Don't delete message here to avoid duplicates - global listener handles it
    });

    // Listen for all events for debugging
    console.log('🔍 Channel object:', channel);
    console.log('🔍 Channel name:', channel.name);
    console.log('🔍 Channel subscription:', channel.subscription);

    // Listen for typing events
    channel.listen('.user.typing', (e) => {
      console.log('⌨️ Typing event received:', e);
      if (e.user && e.user.id !== authStore.user.id) {
        chatStore.updateTypingUsers(e.user, e.is_typing);

        // Clear typing indicator after 3 seconds
        if (e.is_typing) {
          setTimeout(() => {
            chatStore.updateTypingUsers(e.user, false);
          }, 3000);
        }
      }
    });

    // Listen for user joined events
    channel.listen('.user.joined', (e) => {
      console.log('👋 User joined event received:', e);
      if (e.user && e.user.id !== authStore.user.id) {
        notificationStore.info('User Joined', `${e.user.name} joined the chat`);
      }
    });

    // Listen for user left events
    channel.listen('.user.left', (e) => {
      console.log('👋 User left event received:', e);
      if (e.user && e.user.id !== authStore.user.id) {
        notificationStore.info('User Left', `${e.user.name} left the chat`);
      }
    });

    // Error handling
    channel.error((error) => {
      console.error('❌ Error on room channel:', error);
    });

    console.log('✅ Room listeners setup complete for room:', currentChatRoom.value.id);

    // Test if channel is working by triggering a test event
    setTimeout(() => {
      console.log('🧪 Testing channel connectivity...');
      if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
        const pusher = window.Echo.connector.pusher;
        console.log('🔍 Pusher connection state:', pusher.connection.state);
        console.log('🔍 Pusher channels:', Object.keys(pusher.channels.channels));
      }
    }, 1000);
  } catch (error) {
    console.error('❌ Failed to setup room listeners:', error);
  }
};

// Watch for view changes to reset search
watch(currentView, (newView) => {
  if (newView === 'contacts') {
    // Reset search when entering contacts view
    searchQuery.value = '';
    usersStore.clearSearch();
  } else if (newView === 'chats') {
    // Reset search when entering chats view
    searchQuery.value = '';
  }
});

// Watch for chat room changes to setup listeners
watch(currentChatRoom, (newRoom, oldRoom) => {
  console.log('🔄 Chat room changed:', { oldRoom: oldRoom?.id, newRoom: newRoom?.id });

  if (oldRoom && window.Echo) {
    console.log(`🧹 Leaving room channel: ${oldRoom.id}`);
    try {
      window.Echo.leave(`chat-room.${oldRoom.id}`);
      console.log(`✅ Successfully left room channel: ${oldRoom.id}`);
    } catch (error) {
      console.error(`❌ Error leaving room channel ${oldRoom.id}:`, error);
    }
  }

  if (newRoom) {
    console.log(`🔧 Setting up listeners for new room: ${newRoom.id}`);
    setupRoomListeners();
  }
});

// Handle logout
const handleLogout = async () => {
  try {
    await authStore.logout();
    dropdownOpen.value = false;
    router.push('/login');
  } catch (error) {
    notificationStore.error('Logout Failed', 'Failed to logout. Please try again.');
  }
};

onMounted(async () => {
  await initializeChat();

  // Setup global listeners only once
  if (!window.globalListenersSetup) {
    setupRealTimeListeners();
    window.globalListenersSetup = true;
  }
});

onUnmounted(() => {
  console.log('🧹 Chat component unmounting, cleaning up...');

  try {
    // Stop heartbeat
    if (usersStore && usersStore.stopHeartbeat) {
      usersStore.stopHeartbeat();
      console.log('✅ Heartbeat stopped');
    }

    // Leave channels
    if (currentChatRoom.value && window.Echo) {
      console.log('🧹 Leaving chat room channel:', currentChatRoom.value.id);
      window.Echo.leave(`chat-room.${currentChatRoom.value.id}`);
    }

    if (window.Echo) {
      console.log('🧹 Leaving global channels...');
      window.Echo.leave('users-status');
      window.Echo.leave('chat-rooms');
      window.Echo.leave('user-messages');
    }

    // Clear chat store
    if (chatStore && chatStore.clearCurrentChat) {
      chatStore.clearCurrentChat();
      console.log('✅ Chat store cleared');
    }

    // Reset global flags
    if (window.globalListenersSetup) {
      window.globalListenersSetup = false;
    }

    console.log('✅ Chat component cleanup complete');
  } catch (error) {
    console.warn('⚠️ Error during chat component cleanup:', error);
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
</style>
