<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
      <!-- Header -->
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      </div>
      
      <!-- Content -->
      <div class="p-6">
        <p class="text-gray-700">{{ message }}</p>
      </div>
      
      <!-- Actions -->
      <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
        <button
          @click="$emit('cancel')"
          class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium transition-colors"
        >
          {{ cancelText }}
        </button>
        <button
          @click="$emit('confirm')"
          :class="confirmClass"
          class="px-4 py-2 text-white rounded-lg font-medium transition-colors"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmClass: {
    type: String,
    default: 'bg-blue-600 hover:bg-blue-700'
  }
});

defineEmits(['confirm', 'cancel']);
</script>
