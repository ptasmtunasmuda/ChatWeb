<template>
  <div class="h-full overflow-y-auto bg-gray-50 p-2 sm:p-3 lg:p-4 space-y-2 sm:space-y-3 lg:space-y-4 scroll-smooth" ref="messagesContainer">
    <!-- Loading -->
    <div v-if="loading" class="space-y-3 sm:space-y-4">
      <div v-for="i in 5" :key="i" class="animate-pulse">
        <div class="flex items-start space-x-2 sm:space-x-3">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full"></div>
          <div class="flex-1 space-y-1 sm:space-y-2">
            <div class="h-3 sm:h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-2 sm:h-3 bg-gray-200 rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Messages -->
    <div v-else-if="messages.length === 0" class="flex-1 flex items-center justify-center px-4">
      <div class="text-center">
        <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-1 sm:mb-2">No messages yet</h3>
        <p class="text-sm sm:text-base text-gray-600">Start the conversation by sending a message</p>
      </div>
    </div>

    <!-- Messages -->
    <div v-else class="space-y-2 sm:space-y-3 lg:space-y-4">
      <!-- Date divider -->
      <div class="text-center">
        <div class="inline-block bg-white px-2 sm:px-3 py-1 rounded-lg text-xs sm:text-sm text-gray-500 border border-gray-200">
          {{ formatDateDivider(messages[0]?.created_at) }}
        </div>
      </div>

      <div
        v-for="message in messages"
        :key="message.id"
        class="flex items-end space-x-1 sm:space-x-2 group"
        :class="{ 'flex-row-reverse space-x-reverse': currentUser && message.user && message.user.id === currentUser.id }"
      >
        <!-- System Message -->
        <div v-if="message.is_system" class="w-full">
          <div class="flex justify-center my-2 sm:my-4">
            <div class="bg-gray-100 text-gray-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium max-w-xs sm:max-w-sm">
              <div class="flex items-center space-x-1 sm:space-x-2">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ message.content }}</span>
              </div>
              <div class="text-xs text-gray-500 mt-1 text-center">
                {{ formatTime(message.created_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Regular User Message -->
        <template v-else>
          <!-- Avatar -->
          <div class="flex-shrink-0">
            <div class="relative">
              <!-- User Avatar with Image -->
              <div
                v-if="message.user && message.user.avatar"
                class="w-6 h-6 sm:w-8 sm:h-8 rounded-full overflow-hidden border-2 border-white shadow-sm"
              >
                <img
                  :src="message.user.avatar"
                  :alt="message.user.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <!-- Fallback Avatar with Initials -->
              <div
                v-else-if="message.user"
                class="w-6 h-6 sm:w-8 sm:h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-semibold"
              >
                {{ message.user.name.charAt(0).toUpperCase() }}
              </div>
              <!-- System message fallback -->
              <div
                v-else
                class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-semibold"
              >
                S
              </div>

              <!-- Online Status Indicator (only for other users) -->
              <div
                v-if="currentUser && message.user && message.user.id !== currentUser.id && message.user.is_online"
                class="absolute -bottom-0.5 -right-0.5 w-2 h-2 sm:w-3 sm:h-3 bg-green-500 border-2 border-white rounded-full"
              ></div>
            </div>
          </div>

          <!-- Message Content -->
          <div class="max-w-[250px] sm:max-w-xs lg:max-w-md xl:max-w-lg">
          <!-- Reply to message -->
          <div v-if="message.reply_to_message" class="mb-1 sm:mb-2 p-1.5 sm:p-2 bg-gray-100 rounded-lg border-l-4 border-blue-400">
            <p class="text-xs text-blue-600 font-medium">{{ message.reply_to_message.user.name }}</p>
            <p class="text-xs sm:text-sm text-gray-700 truncate">{{ message.reply_to_message.content }}</p>
          </div>

          <!-- Message bubble -->
          <div
            class="message-bubble px-2 sm:px-3 lg:px-4 py-1.5 sm:py-2 lg:py-3 rounded-2xl shadow-sm transition-all duration-300 hover:shadow-md"
            :class="{
              'sent bg-gradient-to-r from-primary-500 to-accent-500 text-white ml-auto': currentUser && message.user.id === currentUser.id && !message.is_deleted,
              'received bg-white border border-secondary-200 text-secondary-800': (!currentUser || message.user.id !== currentUser.id) && !message.is_deleted,
              'deleted bg-gray-100 text-gray-500': message.is_deleted
            }"
          >
            <!-- Deleted message -->
            <div v-if="message.is_deleted" class="flex items-center space-x-1 sm:space-x-2">
              <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              <span class="text-xs sm:text-sm italic">This message was deleted</span>
            </div>

            <!-- Regular message content -->
            <div v-else>
              <!-- Regular message display -->
              <div>
                <p v-if="message.content" class="text-xs sm:text-sm lg:text-base whitespace-pre-wrap break-words">{{ message.content }}</p>

                <!-- File attachments -->
                <div v-if="hasAttachments(message)" class="mt-2 sm:mt-3 space-y-2 sm:space-y-3">
                  <FilePreview
                    v-for="(file, index) in getAttachments(message)"
                    :key="index"
                    :file="file"
                    :is-sent-message="currentUser && message.user.id === currentUser.id"
                    @download="downloadFile(message, index)"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Message footer -->
          <div class="flex items-center mt-1 space-x-1 sm:space-x-2">
            <div class="flex items-center space-x-1">
              <span class="text-xs text-gray-500">{{ formatTime(message.created_at) }}</span>
              <span v-if="message.is_edited && !message.is_deleted" class="text-xs text-gray-400">(edited)</span>
            </div>

            <!-- Message actions (only for non-deleted messages) -->
            <div v-if="currentUser && message.user.id === currentUser.id && !message.is_deleted" class="flex items-center space-x-0.5 sm:space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <button
                @click="startEdit(message)"
                class="p-0.5 sm:p-1 text-gray-400 hover:text-gray-600 rounded"
                title="Edit message"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button
                @click="deleteMessage(message.id)"
                class="p-0.5 sm:p-1 text-red-400 hover:text-red-600 rounded"
                title="Delete message"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>

            <!-- Reaction button for other users' messages (only for non-deleted messages) -->
            <button
              v-if="currentUser && message.user && message.user.id !== currentUser.id && !message.is_deleted"
              class="p-0.5 sm:p-1 rounded-full hover:bg-gray-200 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
            >
              <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.01M15 10h1.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
        </div>
        </template>
      </div>
    </div>

    <!-- Typing indicator -->
    <div v-if="typingUsers.length > 0" class="flex items-end space-x-2">
      <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
        <div class="typing-indicator">
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
        </div>
      </div>
      <div class="bg-white rounded-lg px-4 py-2 border border-gray-200">
        <p class="text-sm text-gray-600">
          {{ typingUsers.map(u => u.name).join(', ') }}
          {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
        </p>
      </div>
    </div>

    <!-- Delete Confirmation Modal - Simple -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 flex items-center justify-center z-50">
      <div class="card w-full max-w-sm mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Delete Message?</h3>

        <p class="text-gray-600 text-sm mb-4">
          This message will be deleted for everyone.
        </p>

        <div class="flex justify-end space-x-2">
          <button
            @click="cancelDelete"
            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            class="px-3 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </div>


  </div>
</template>

<script setup>
import { ref, nextTick, watch, onMounted, onUnmounted } from 'vue';
import { useTimestamp } from '../../composables/useTimestamp';
import FilePreview from './attachments/FilePreview.vue';
import axios from 'axios';

const props = defineProps({
  messages: {
    type: Array,
    default: () => []
  },
  currentUser: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  typingUsers: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['edit-message', 'delete-message', 'load-more', 'start-edit', 'download-error']);

const messagesContainer = ref(null);
const showDeleteConfirm = ref(false);
const messageToDelete = ref(null);

// Use timestamp composable for real-time updates
const { formatTime, formatDateDivider } = useTimestamp();

const scrollToBottom = (force = false) => {
  nextTick(() => {
    if (messagesContainer.value) {
      const container = messagesContainer.value;

      console.log('🔽 Scrolling to bottom:', {
        scrollHeight: container.scrollHeight,
        clientHeight: container.clientHeight,
        currentScrollTop: container.scrollTop,
        force: force
      });

      // Force scroll to bottom
      container.scrollTop = container.scrollHeight;

      // Double-check after a small delay (for images/content that might load)
      if (force) {
        setTimeout(() => {
          container.scrollTop = container.scrollHeight;
          console.log('🔽 Force scroll check 1:', container.scrollTop);
        }, 50);

        // Triple-check for stubborn cases
        setTimeout(() => {
          container.scrollTop = container.scrollHeight;
          console.log('🔽 Force scroll check 2:', container.scrollTop);
        }, 200);
      }
    }
  });
};

const startEdit = (message) => {
  // Emit to parent to handle edit in chat input
  emit('start-edit', message);
};

// File attachment methods
const hasAttachments = (message) => {
  return message.attachment_info && message.attachment_info.files && message.attachment_info.files.length > 0;
};

const getAttachments = (message) => {
  return message.attachment_info?.files || [];
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const downloadFile = (message, fileIndex) => {
  try {
    // Get file info from attachment_info
    const attachments = message.attachment_info?.files || [];
    if (!attachments[fileIndex]) {
      console.error('File not found at index:', fileIndex);
      alert('File not found');
      return;
    }

    const file = attachments[fileIndex];
    console.log('Downloading file:', file);

    // Use direct URL download (simpler approach)
    let downloadUrl = '';

    // Try different URL sources
    if (file.url) {
      downloadUrl = file.url;
    } else if (file.path) {
      downloadUrl = `/storage/${file.path}`;
    } else if (file.file_path) {
      downloadUrl = file.file_path;
    } else if (file.original_name) {
      // Fallback: construct URL based on message and file info
      downloadUrl = `/storage/chat-files/${message.id}/${file.original_name}`;
    } else {
      console.error('No valid download URL found for file:', file);
      alert('Unable to download file - no valid URL found');
      return;
    }

    console.log('Downloading file from URL:', downloadUrl);

    // Create download link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = file.original_name || file.name || `file_${fileIndex}`;
    link.target = '_blank'; // Open in new tab as fallback
    link.style.display = 'none';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

  } catch (error) {
    console.error('Download error:', error);
    alert('Failed to download file: ' + error.message);
  }
};

const deleteMessage = (messageId) => {
  messageToDelete.value = messageId;
  showDeleteConfirm.value = true;

  // Add keyboard event listener when modal opens
  nextTick(() => {
    document.addEventListener('keydown', handleKeydown);
  });
};

const confirmDelete = () => {
  if (messageToDelete.value) {
    emit('delete-message', messageToDelete.value);
  }
  cancelDelete();
};

const cancelDelete = () => {
  showDeleteConfirm.value = false;
  messageToDelete.value = null;

  // Remove keyboard event listener when modal closes
  document.removeEventListener('keydown', handleKeydown);
};

const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    cancelDelete();
  } else if (event.key === 'Enter') {
    confirmDelete();
  }
};



// Watch for new messages and scroll to bottom
watch(() => props.messages.length, () => {
  scrollToBottom();
});

// Watch for messages content change (when switching rooms)
watch(() => props.messages, (newMessages) => {
  if (newMessages && newMessages.length > 0) {
    scrollToBottom(true); // Force scroll when switching rooms
  }
}, { immediate: true, deep: true });

// Watch for loading state to scroll when messages finish loading
watch(() => props.loading, (isLoading) => {
  if (!isLoading && props.messages.length > 0) {
    // Small delay to ensure DOM is updated
    setTimeout(() => {
      scrollToBottom(true); // Force scroll after loading
    }, 100);
  }
});

onMounted(() => {
  scrollToBottom(true); // Force scroll on mount
});

onUnmounted(() => {
  // Cleanup keyboard event listener if modal is open
  document.removeEventListener('keydown', handleKeydown);
});

// Expose scrollToBottom function to parent component
defineExpose({
  scrollToBottom
});
</script>

<style scoped>
.message-bubble {
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  max-width: 100%;
  word-wrap: break-word;
  transition: all 0.2s ease;
}

.message-bubble.sent {
  background: #3b82f6;
  color: white;
  border-bottom-right-radius: 0.25rem;
}

.message-bubble.received {
  background: white;
  border: 1px solid #e5e7eb;
  color: #374151;
  border-bottom-left-radius: 0.25rem;
}

.message-bubble.deleted {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  color: #6b7280;
  border-radius: 1rem;
  opacity: 0.7;
}

.typing-indicator {
  display: flex;
  gap: 0.25rem;
}

.typing-dot {
  width: 0.375rem;
  height: 0.375rem;
  background-color: #6b7280;
  border-radius: 50%;
  animation: typing 1.5s ease-in-out infinite;
}

.typing-dot:nth-child(2) {
  animation-delay: 0.1s;
}

.typing-dot:nth-child(3) {
  animation-delay: 0.2s;
}

@keyframes typing {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

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
