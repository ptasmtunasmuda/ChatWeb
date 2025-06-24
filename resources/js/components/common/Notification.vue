<template>
  <div 
    v-if="show"
    class="notification-container"
    :class="{
      'notification-success': type === 'success',
      'notification-info': type === 'info',
      'notification-warning': type === 'warning',
      'notification-error': type === 'error'
    }"
  >
    <div class="notification-content">
      <!-- Icon -->
      <div class="notification-icon">
        <!-- Success Icon -->
        <svg v-if="type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <!-- Info Icon -->
        <svg v-else-if="type === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <!-- Warning Icon -->
        <svg v-else-if="type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.854-.833-2.624 0L3.232 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <!-- Error Icon -->
        <svg v-else-if="type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <!-- File Conversion Icon (default) -->
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
      </div>

      <!-- Message -->
      <div class="notification-message">
        <p class="notification-title">{{ title }}</p>
        <p v-if="message" class="notification-text">{{ message }}</p>
      </div>

      <!-- Close Button -->
      <button 
        @click="close"
        class="notification-close"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <!-- Progress Bar for auto-close -->
    <div v-if="autoClose && duration > 0" class="notification-progress">
      <div 
        class="notification-progress-bar"
        :style="{ width: `${progressWidth}%` }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'info', 'warning', 'error'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  duration: {
    type: Number,
    default: 5000 // 5 seconds
  },
  autoClose: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close']);

const progressWidth = ref(100);
let progressInterval = null;

const close = () => {
  emit('close');
  if (progressInterval) {
    clearInterval(progressInterval);
  }
};

onMounted(() => {
  if (props.autoClose && props.duration > 0) {
    const startTime = Date.now();
    const endTime = startTime + props.duration;
    
    progressInterval = setInterval(() => {
      const now = Date.now();
      const remaining = Math.max(0, endTime - now);
      progressWidth.value = (remaining / props.duration) * 100;
      
      if (remaining <= 0) {
        close();
      }
    }, 50);
  }
});

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval);
  }
});
</script>

<style scoped>
.w-5 { width: 1.25rem; }
.h-5 { height: 1.25rem; }
.w-4 { width: 1rem; }
.h-4 { height: 1rem; }

.notification-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 50;
  max-width: 24rem;
  width: 100%;
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid #e5e7eb;
  transform: translateX(0);
  transition: all 0.3s;
  animation: slideInRight 0.3s ease-out;
}

.notification-success {
  border-color: #d1fae5;
  background-color: #f0fdf4;
}

.notification-info {
  border-color: #dbeafe;
  background-color: #eff6ff;
}

.notification-warning {
  border-color: #fed7aa;
  background-color: #fffbeb;
}

.notification-error {
  border-color: #fecaca;
  background-color: #fef2f2;
}

.notification-content {
  display: flex;
  align-items: flex-start;
  padding: 1rem;
}

.notification-icon {
  flex-shrink: 0;
  margin-right: 0.75rem;
}

.notification-success .notification-icon {
  color: #059669;
}

.notification-info .notification-icon {
  color: #2563eb;
}

.notification-warning .notification-icon {
  color: #d97706;
}

.notification-error .notification-icon {
  color: #dc2626;
}

.notification-message {
  flex: 1;
}

.notification-title {
  font-size: 0.875rem;
  font-weight: 500;
}

.notification-success .notification-title {
  color: #064e3b;
}

.notification-info .notification-title {
  color: #1e3a8a;
}

.notification-warning .notification-title {
  color: #92400e;
}

.notification-error .notification-title {
  color: #991b1b;
}

.notification-text {
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.notification-success .notification-text {
  color: #047857;
}

.notification-info .notification-text {
  color: #1d4ed8;
}

.notification-warning .notification-text {
  color: #b45309;
}

.notification-error .notification-text {
  color: #b91c1c;
}

.notification-close {
  flex-shrink: 0;
  margin-left: 0.5rem;
  padding: 0.25rem;
  border-radius: 0.375rem;
  transition: all 0.2s;
  border: none;
  background: none;
  cursor: pointer;
}

.notification-success .notification-close {
  color: #10b981;
}

.notification-success .notification-close:hover {
  color: #047857;
  background-color: #d1fae5;
}

.notification-info .notification-close {
  color: #3b82f6;
}

.notification-info .notification-close:hover {
  color: #1d4ed8;
  background-color: #dbeafe;
}

.notification-warning .notification-close {
  color: #f59e0b;
}

.notification-warning .notification-close:hover {
  color: #b45309;
  background-color: #fed7aa;
}

.notification-error .notification-close {
  color: #ef4444;
}

.notification-error .notification-close:hover {
  color: #b91c1c;
  background-color: #fecaca;
}

.notification-progress {
  height: 0.25rem;
  background-color: #f3f4f6;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  overflow: hidden;
}

.notification-progress-bar {
  height: 100%;
  transition: all 75ms ease-linear;
}

.notification-success .notification-progress-bar {
  background-color: #10b981;
}

.notification-info .notification-progress-bar {
  background-color: #3b82f6;
}

.notification-warning .notification-progress-bar {
  background-color: #f59e0b;
}

.notification-error .notification-progress-bar {
  background-color: #ef4444;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .notification-container {
    top: 0.5rem;
    right: 0.5rem;
    left: 0.5rem;
    max-width: none;
  }
}
</style>
