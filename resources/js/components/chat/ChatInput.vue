<template>
  <div class="bg-white border-t border-gray-200 px-4 py-3 relative">
    <!-- Editing Mode Indicator -->
    <div v-if="props.editingMessage" class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          <span class="text-sm text-blue-700 font-medium">Editing message</span>
        </div>
        <button @click="cancelEdit" class="text-blue-600 hover:text-blue-800">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>

    <div class="flex items-end space-x-3 max-w-4xl mx-auto">
      <!-- File Upload Button -->
      <button
        @click="$refs.fileInput.click()"
        class="flex-shrink-0 w-10 h-10 bg-gray-50 hover:bg-gray-100 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-105"
        title="Attach file"
      >
        <svg class="w-5 h-5 text-gray-500 hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
        </svg>
      </button>

      <!-- Message Input -->
      <div class="flex-1 relative">
        <!-- Reply indicator -->
        <div v-if="replyToMessage" class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
          <div class="flex-1">
            <p class="text-xs text-blue-600 font-medium">Replying to {{ replyToMessage.user.name }}</p>
            <p class="text-sm text-gray-700 truncate">{{ replyToMessage.content }}</p>
          </div>
          <button @click="clearReply" class="ml-2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Input field -->
        <div class="relative">
          <textarea
            ref="messageInput"
            v-model="message"
            @keydown="handleKeydown"
            @input="handleInput"
            @focus="handleFocus"
            @blur="handleBlur"
            placeholder="Type a message..."
            rows="1"
            class="w-full px-4 py-3 pr-12 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:bg-white resize-none transition-all duration-200 placeholder-gray-400"
            :class="{ 'border-red-300 focus:ring-red-500': error }"
            :disabled="disabled"
          ></textarea>

          <!-- Emoji Button -->
          <button
            @click="toggleEmojiPicker"
            class="absolute right-3 bottom-3 text-gray-400 hover:text-gray-600 transition-all duration-200 hover:scale-110"
            title="Add emoji"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </button>
        </div>

        <!-- Error message -->
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>

        <!-- File preview -->
        <div v-if="selectedFiles.length > 0" class="mt-2 space-y-2">
          <div
            v-for="(file, index) in selectedFiles"
            :key="index"
            class="flex items-center justify-between p-2 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
              </svg>
              <span class="text-sm text-gray-700">{{ file.name }}</span>
              <span class="text-xs text-gray-500">({{ formatFileSize(file.size) }})</span>
            </div>
            <button
              @click="removeFile(index)"
              class="text-red-500 hover:text-red-700"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Send Button -->
      <button
        @click="sendMessage"
        :disabled="!canSend || sending"
        class="flex-shrink-0 w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center text-white transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105 shadow-lg hover:shadow-xl"
      >
        <svg v-if="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else-if="props.editingMessage" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
        </svg>
      </button>
    </div>

    <!-- Hidden file input -->
    <input
      ref="fileInput"
      type="file"
      multiple
      accept="image/*,audio/*,.pdf,.doc,.docx,.txt"
      @change="handleFileSelect"
      class="hidden"
    />

    <!-- Emoji Picker -->
    <div v-if="showEmojiPicker" :class="emojiPickerClasses">
      <div class="grid grid-cols-8 gap-1">
        <button
          v-for="emoji in commonEmojis"
          :key="emoji"
          @click="insertEmoji(emoji)"
          class="p-2 hover:bg-gray-100 rounded-lg text-lg transition-all duration-200 hover:scale-110"
        >
          {{ emoji }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  replyToMessage: {
    type: Object,
    default: null
  },
  editingMessage: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['send-message', 'typing', 'clear-reply', 'edit-message', 'cancel-edit']);

const messageInput = ref(null);
const fileInput = ref(null);
const message = ref('');
const selectedFiles = ref([]);
const sending = ref(false);
const error = ref('');
const showEmojiPicker = ref(false);
const isTyping = ref(false);
const typingTimeout = ref(null);

const commonEmojis = ['😀', '😂', '😍', '🤔', '😢', '😡', '👍', '👎', '❤️', '🎉', '🔥', '💯', '😎', '🤗', '😴', '🙄'];

// Computed
const canSend = computed(() => {
  return (message.value.trim() || selectedFiles.value.length > 0) && !props.disabled;
});

// Dynamic emoji picker position - use absolute positioning relative to chat input
const emojiPickerClasses = computed(() => {
  return 'emoji-picker absolute bottom-full right-0 mb-2 z-50 card';
});

// Watch for editing message changes
watch(() => props.editingMessage, (newEditingMessage) => {
  if (newEditingMessage) {
    message.value = newEditingMessage.content;
    nextTick(() => {
      messageInput.value?.focus();
    });
  }
});

// Methods
const cancelEdit = () => {
  message.value = '';
  emit('cancel-edit');
};
const handleKeydown = (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    sendMessage();
  }
};

const handleInput = () => {
  // Auto-resize textarea
  nextTick(() => {
    const textarea = messageInput.value;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
  });

  // Handle typing indicator
  if (!isTyping.value && message.value.trim()) {
    isTyping.value = true;
    emit('typing', true);
  }

  // Clear typing timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }

  // Set new timeout
  typingTimeout.value = setTimeout(() => {
    if (isTyping.value) {
      isTyping.value = false;
      emit('typing', false);
    }
  }, 1000);
};

const handleFocus = () => {
  error.value = '';
};

const handleBlur = () => {
  // Stop typing indicator when input loses focus
  if (isTyping.value) {
    isTyping.value = false;
    emit('typing', false);
  }
};

const sendMessage = async () => {
  if (!canSend.value || sending.value) return;

  const messageContent = message.value.trim();

  if (!messageContent) return;

  sending.value = true;
  error.value = '';

  try {
    if (props.editingMessage) {
      // Edit mode
      emit('edit-message', messageContent);
    } else {
      // Send mode
      const files = [...selectedFiles.value];
      const messageData = {
        content: messageContent,
        type: files.length > 0 ? 'file' : 'text',
        reply_to_message_id: props.replyToMessage?.id || null
      };

      // If there are files, we need to send as FormData
      if (files.length > 0) {
        const formData = new FormData();
        formData.append('content', messageContent);
        formData.append('type', 'file');

        if (props.replyToMessage?.id) {
          formData.append('reply_to_message_id', props.replyToMessage.id);
        }

        files.forEach((file, index) => {
          formData.append(`attachments[${index}]`, file);
        });

        messageData.formData = formData;
      }

      emit('send-message', messageData);
    }

    // Clear input
    message.value = '';
    selectedFiles.value = [];
    clearReply();

    // Stop typing indicator
    if (isTyping.value) {
      isTyping.value = false;
      emit('typing', false);
    }

    // Reset textarea height
    nextTick(() => {
      if (messageInput.value) {
        messageInput.value.style.height = 'auto';
      }
    });

  } catch (err) {
    error.value = 'Failed to send message. Please try again.';
  } finally {
    sending.value = false;
  }
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  const maxSize = 10 * 1024 * 1024; // 10MB
  const maxFiles = 5;

  // Validate file count
  if (selectedFiles.value.length + files.length > maxFiles) {
    error.value = `You can only upload up to ${maxFiles} files at once.`;
    return;
  }

  // Validate file sizes
  const invalidFiles = files.filter(file => file.size > maxSize);
  if (invalidFiles.length > 0) {
    error.value = 'Some files are too large. Maximum file size is 10MB.';
    return;
  }

  selectedFiles.value.push(...files);
  error.value = '';

  // Clear file input
  event.target.value = '';
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const toggleEmojiPicker = () => {
  showEmojiPicker.value = !showEmojiPicker.value;
};

const insertEmoji = (emoji) => {
  const textarea = messageInput.value;
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;

  message.value = message.value.substring(0, start) + emoji + message.value.substring(end);

  nextTick(() => {
    textarea.focus();
    textarea.setSelectionRange(start + emoji.length, start + emoji.length);
  });

  showEmojiPicker.value = false;
};

const clearReply = () => {
  emit('clear-reply');
};

// Click outside handler for emoji picker
const handleClickOutside = (event) => {
  if (showEmojiPicker.value && !event.target.closest('.emoji-picker') && !event.target.closest('[title="Add emoji"]')) {
    showEmojiPicker.value = false;
  }
};

// Setup click outside listener
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Watch for reply changes to focus input
watch(() => props.replyToMessage, (newReply) => {
  if (newReply) {
    nextTick(() => {
      messageInput.value?.focus();
    });
  }
});
</script>

<style scoped>
/* Custom scrollbar for textarea */
textarea::-webkit-scrollbar {
  width: 4px;
}

textarea::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
