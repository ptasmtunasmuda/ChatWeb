<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="card max-w-md w-full animate-bounce-in">
      <!-- Header -->
      <div class="p-6 border-b border-secondary-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold gradient-text font-display">Export Data</h3>
          <button 
            @click="$emit('close')"
            class="p-2 hover:bg-secondary-100 rounded-lg transition-colors duration-200"
          >
            <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleExport" class="p-6 space-y-6">
        <!-- Data Type -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-3">Data Type</label>
          <div class="grid grid-cols-1 gap-3">
            <label class="flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all duration-300" :class="{
              'border-primary-500 bg-primary-50': form.type === 'users',
              'border-secondary-200 hover:border-secondary-300': form.type !== 'users'
            }">
              <input 
                type="radio" 
                v-model="form.type" 
                value="users" 
                class="sr-only"
              />
              <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" :class="form.type === 'users' ? 'text-primary-600' : 'text-secondary-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <div>
                  <p class="font-medium" :class="form.type === 'users' ? 'text-primary-700' : 'text-secondary-700'">Users</p>
                  <p class="text-xs text-secondary-500">Export user data and statistics</p>
                </div>
              </div>
            </label>

            <label class="flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all duration-300" :class="{
              'border-primary-500 bg-primary-50': form.type === 'chat_rooms',
              'border-secondary-200 hover:border-secondary-300': form.type !== 'chat_rooms'
            }">
              <input 
                type="radio" 
                v-model="form.type" 
                value="chat_rooms" 
                class="sr-only"
              />
              <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" :class="form.type === 'chat_rooms' ? 'text-primary-600' : 'text-secondary-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <div>
                  <p class="font-medium" :class="form.type === 'chat_rooms' ? 'text-primary-700' : 'text-secondary-700'">Chat Rooms</p>
                  <p class="text-xs text-secondary-500">Export chat room data and analytics</p>
                </div>
              </div>
            </label>

            <label class="flex items-center p-3 border-2 rounded-xl cursor-pointer transition-all duration-300" :class="{
              'border-primary-500 bg-primary-50': form.type === 'messages',
              'border-secondary-200 hover:border-secondary-300': form.type !== 'messages'
            }">
              <input 
                type="radio" 
                v-model="form.type" 
                value="messages" 
                class="sr-only"
              />
              <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" :class="form.type === 'messages' ? 'text-primary-600' : 'text-secondary-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
                </svg>
                <div>
                  <p class="font-medium" :class="form.type === 'messages' ? 'text-primary-700' : 'text-secondary-700'">Messages</p>
                  <p class="text-xs text-secondary-500">Export message data and content</p>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Format -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-3">Export Format</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition-all duration-300" :class="{
              'border-primary-500 bg-primary-50': form.format === 'csv',
              'border-secondary-200 hover:border-secondary-300': form.format !== 'csv'
            }">
              <input 
                type="radio" 
                v-model="form.format" 
                value="csv" 
                class="sr-only"
              />
              <div class="text-center">
                <svg class="w-6 h-6 mx-auto mb-2" :class="form.format === 'csv' ? 'text-primary-600' : 'text-secondary-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="font-medium text-sm" :class="form.format === 'csv' ? 'text-primary-700' : 'text-secondary-700'">CSV</p>
              </div>
            </label>

            <label class="flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer transition-all duration-300" :class="{
              'border-primary-500 bg-primary-50': form.format === 'json',
              'border-secondary-200 hover:border-secondary-300': form.format !== 'json'
            }">
              <input 
                type="radio" 
                v-model="form.format" 
                value="json" 
                class="sr-only"
              />
              <div class="text-center">
                <svg class="w-6 h-6 mx-auto mb-2" :class="form.format === 'json' ? 'text-primary-600' : 'text-secondary-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                <p class="font-medium text-sm" :class="form.format === 'json' ? 'text-primary-700' : 'text-secondary-700'">JSON</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-3">Date Range (Optional)</label>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label for="date_from" class="block text-xs font-medium text-secondary-600 mb-1">From</label>
              <input
                id="date_from"
                v-model="form.dateFrom"
                type="date"
                class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
              />
            </div>
            <div>
              <label for="date_to" class="block text-xs font-medium text-secondary-600 mb-1">To</label>
              <input
                id="date_to"
                v-model="form.dateTo"
                type="date"
                class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="px-6 py-2 text-secondary-700 hover:text-secondary-900 font-medium transition-colors duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !form.type || !form.format"
            class="btn-primary"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Exporting...
            </span>
            <span v-else class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Export Data
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const emit = defineEmits(['close', 'export']);

const loading = ref(false);

const form = reactive({
  type: 'users',
  format: 'csv',
  dateFrom: '',
  dateTo: ''
});

const handleExport = async () => {
  loading.value = true;
  
  try {
    await emit('export', {
      type: form.type,
      format: form.format,
      dateFrom: form.dateFrom || null,
      dateTo: form.dateTo || null
    });
    
    emit('close');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.gradient-text {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  color: white;
  font-weight: 600;
  padding: 0.5rem 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.25);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover:not(:disabled) {
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.3);
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
  0% {
    transform: scale(0.9);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
