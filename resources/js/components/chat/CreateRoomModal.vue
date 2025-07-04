<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4">
    <div class="card max-w-xs sm:max-w-md lg:max-w-lg w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">Create New Chat</h3>
          <button
            @click="closeModal"
            class="p-1.5 sm:p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200"
          >
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="createRoom" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
        <!-- Room Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2 sm:mb-3">Chat Type</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
            <button
              type="button"
              @click="form.type = 'private'"
              class="p-3 sm:p-4 border-2 rounded-lg transition-all duration-200"
              :class="{
                'border-blue-500 bg-blue-50': form.type === 'private',
                'border-gray-200 hover:border-gray-300': form.type !== 'private'
              }"
            >
              <svg class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-2" :class="form.type === 'private' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <p class="text-sm font-medium" :class="form.type === 'private' ? 'text-blue-700' : 'text-gray-700'">Private</p>
              <p class="text-xs text-gray-500">One-on-one chat</p>
            </button>

            <button
              type="button"
              @click="form.type = 'group'"
              class="p-3 sm:p-4 border-2 rounded-lg transition-all duration-200"
              :class="{
                'border-blue-500 bg-blue-50': form.type === 'group',
                'border-gray-200 hover:border-gray-300': form.type !== 'group'
              }"
            >
              <svg class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-2" :class="form.type === 'group' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              <p class="text-sm font-medium" :class="form.type === 'group' ? 'text-blue-700' : 'text-gray-700'">Group</p>
              <p class="text-xs text-gray-500">Multiple participants</p>
            </button>
          </div>
        </div>

        <!-- Room Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            {{ form.type === 'private' ? 'Chat Name' : 'Group Name' }}
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200"
            :class="{ 'border-red-300 focus:ring-red-500': errors.name }"
            :placeholder="form.type === 'private' ? 'Enter chat name' : 'Enter group name'"
          />
          <p v-if="errors.name" class="mt-1 text-xs sm:text-sm text-red-600">{{ errors.name[0] }}</p>
        </div>

        <!-- Description -->
        <div v-if="form.type === 'group'">
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description (Optional)
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none transition-all duration-200"
            placeholder="Describe what this group is about..."
          ></textarea>
        </div>

        <!-- Participants -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Add Participants
          </label>

          <!-- Search Users -->
          <div class="relative mb-2 sm:mb-3">
            <div class="absolute inset-y-0 left-0 pl-2 sm:pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              v-model="userSearchQuery"
              @input="searchUsers"
              type="text"
              placeholder="Search users..."
              class="w-full pl-8 sm:pl-10 pr-3 sm:pr-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>

          <!-- Search Results -->
          <div v-if="searchResults.length > 0" class="mb-3 max-h-32 overflow-y-auto border border-gray-200 rounded-lg">
            <button
              v-for="user in searchResults"
              :key="user.id"
              @click="addParticipant(user)"
              type="button"
              class="w-full flex items-center space-x-3 p-3 hover:bg-gray-50 transition-colors duration-200"
            >
              <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 text-left">
                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
            </button>
          </div>

          <!-- Selected Participants -->
          <div v-if="selectedParticipants.length > 0" class="space-y-2">
            <p class="text-sm font-medium text-gray-700">Selected Participants:</p>
            <div class="flex flex-wrap gap-2">
              <div
                v-for="participant in selectedParticipants"
                :key="participant.id"
                class="flex items-center space-x-2 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm"
              >
                <span>{{ participant.name }}</span>
                <button
                  @click="removeParticipant(participant.id)"
                  type="button"
                  class="text-blue-500 hover:text-blue-700"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-3 sm:pt-4">
          <button
            type="button"
            @click="closeModal"
            class="w-full sm:w-auto px-4 sm:px-6 py-2 text-sm sm:text-base text-gray-700 hover:text-gray-900 font-medium transition-colors duration-200 order-2 sm:order-1"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !form.name.trim()"
            class="w-full sm:w-auto btn-primary disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base order-1 sm:order-2"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating...
            </span>
            <span v-else>Create Chat</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted } from 'vue';
import { useChatStore } from '../../stores/chat';
import { useNotificationStore } from '../../stores/notifications';
import axios from 'axios';

const emit = defineEmits(['close', 'created']);

const chatStore = useChatStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const errors = ref({});
const userSearchQuery = ref('');
const searchResults = ref([]);
const selectedParticipants = ref([]);
const searchTimeout = ref(null);

const form = reactive({
  name: '',
  description: '',
  type: 'private',
  participants: []
});

// Methods
const searchUsers = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = setTimeout(async () => {
    if (userSearchQuery.value.trim().length < 2) {
      searchResults.value = [];
      return;
    }

    try {
      const response = await axios.get('/api/user/search', {
        params: { query: userSearchQuery.value.trim() }
      });

      // Filter out already selected participants
      searchResults.value = response.data.filter(user =>
        !selectedParticipants.value.find(p => p.id === user.id)
      );
    } catch (error) {
      console.error('Error searching users:', error);
    }
  }, 300);
};

const addParticipant = (user) => {
  if (!selectedParticipants.value.find(p => p.id === user.id)) {
    selectedParticipants.value.push(user);
    searchResults.value = searchResults.value.filter(u => u.id !== user.id);
    userSearchQuery.value = '';
  }
};

const removeParticipant = (userId) => {
  selectedParticipants.value = selectedParticipants.value.filter(p => p.id !== userId);
};

// Handle modal close
const closeModal = () => {
  // Reset form when closing
  form.name = '';
  form.description = '';
  form.type = 'private';
  selectedParticipants.value = [];
  searchResults.value = [];
  userSearchQuery.value = '';
  errors.value = {};

  emit('close');
};

// Handle escape key
const handleEscKey = (event) => {
  if (event.key === 'Escape' && !loading.value) {
    closeModal();
  }
};

const createRoom = async () => {
  loading.value = true;
  errors.value = {};

  try {
    const roomData = {
      name: form.name.trim(),
      description: form.description?.trim() || null,
      type: form.type,
      participants: selectedParticipants.value.map(p => p.id)
    };

    const result = await chatStore.createChatRoom(roomData);

    if (result.success) {
      // Reset form
      form.name = '';
      form.description = '';
      form.type = 'private';
      selectedParticipants.value = [];
      searchResults.value = [];
      userSearchQuery.value = '';

      // Emit created event
      emit('created', result.chatRoom);

      // Close modal will be handled by parent component
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    notificationStore.error('Error', error.response?.data?.message || 'Failed to create chat room');
  } finally {
    loading.value = false;
  }
};

// Watch for type changes to clear participants if switching to private
watch(() => form.type, (newType) => {
  if (newType === 'private' && selectedParticipants.value.length > 1) {
    selectedParticipants.value = selectedParticipants.value.slice(0, 1);
  }
});

// Setup escape key listener
onMounted(() => {
  document.addEventListener('keydown', handleEscKey);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscKey);
});
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
