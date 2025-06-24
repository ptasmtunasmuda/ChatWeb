<template>
  <div 
    class="file-preview-container"
    :class="{
      'file-preview-sent': isSentMessage,
      'file-preview-received': !isSentMessage
    }"
  >
    <!-- Image Preview -->
    <div v-if="file.category === 'image'" class="image-preview-container">
      <div class="relative group cursor-pointer" @click="openImageModal">
        <img 
          v-if="file.url || file.preview_url"
          :src="file.preview_url || file.url"
          :alt="file.original_name"
          class="image-preview"
          :class="{
            'image-preview-sent': isSentMessage,
            'image-preview-received': !isSentMessage
          }"
          @error="handleImageError"
          loading="lazy"
        />
        
        <!-- Image overlay on hover -->
        <div class="image-overlay">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>
      
      <!-- Image info -->
      <div class="image-info">
        <p class="image-filename" :class="getTextColor()">
          {{ file.original_name }}
        </p>
        <p class="image-size" :class="getSecondaryTextColor()">
          {{ formatFileSize(file.size) }}
        </p>
      </div>
    </div>

    <!-- Other File Types -->
    <div v-else class="file-container">
      <div class="flex items-center space-x-3">
        <!-- File Icon -->
        <div class="file-icon-wrapper" :class="getFileIconBg()">
          <svg class="file-icon" :class="getFileIconColor()" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <!-- Document icon -->
            <path v-if="file.category === 'document'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            <!-- Audio icon -->
            <path v-else-if="file.category === 'audio'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            <!-- Video icon -->
            <path v-else-if="file.category === 'video'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            <!-- Default file icon -->
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        
        <!-- File Info -->
        <div class="flex-1 min-w-0">
          <p class="file-name" :class="getTextColor()">
            {{ file.original_name }}
          </p>
          <div class="file-meta">
            <span class="file-size" :class="getSecondaryTextColor()">
              {{ formatFileSize(file.size) }}
            </span>
            <span v-if="file.was_converted" class="conversion-badge">
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Converted to ZIP
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Download Button -->
    <button 
      @click="$emit('download')"
      class="download-button"
      :class="{
        'download-button-sent': isSentMessage,
        'download-button-received': !isSentMessage
      }"
      title="Download file"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <span class="download-text">Download</span>
    </button>

    <!-- Image Modal -->
    <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
      <div class="image-modal-content" @click.stop>
        <img 
          :src="file.url || file.preview_url"
          :alt="file.original_name"
          class="modal-image"
        />
        <button @click="closeImageModal" class="modal-close-btn">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  file: {
    type: Object,
    required: true
  },
  isSentMessage: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['download']);

const showImageModal = ref(false);

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIconBg = () => {
  switch (props.file.category) {
    case 'image': return 'bg-green-50';
    case 'document': return 'bg-blue-50';
    case 'audio': return 'bg-purple-50';
    case 'video': return 'bg-red-50';
    default: return 'bg-gray-50';
  }
};

const getFileIconColor = () => {
  switch (props.file.category) {
    case 'image': return 'text-green-600';
    case 'document': return 'text-blue-600';
    case 'audio': return 'text-purple-600';
    case 'video': return 'text-red-600';
    default: return 'text-gray-600';
  }
};

const getTextColor = () => {
  return props.isSentMessage ? 'text-white' : 'text-gray-900';
};

const getSecondaryTextColor = () => {
  return props.isSentMessage ? 'text-blue-100' : 'text-gray-500';
};

const handleImageError = (event) => {
  event.target.style.display = 'none';
};

const openImageModal = () => {
  showImageModal.value = true;
  document.body.style.overflow = 'hidden';
};

const closeImageModal = () => {
  showImageModal.value = false;
  document.body.style.overflow = 'auto';
};
</script>

<style scoped>
.file-preview-container {
  border-radius: 0.75rem;
  padding: 0.75rem;
  transition: all 0.2s;
  backdrop-filter: blur(10px);
}

.file-preview-sent {
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.file-preview-received {
  background-color: white;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.file-preview-received:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.image-preview-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.image-preview {
  width: 100%;
  max-width: 20rem;
  border-radius: 0.5rem;
  object-fit: cover;
  transition: all 0.2s;
  max-height: 200px;
}

.image-preview:hover {
  transform: scale(1.05);
}

.image-preview-sent {
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.image-preview-received {
  border: 1px solid #e5e7eb;
}

.image-overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: opacity 0.2s;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.group:hover .image-overlay {
  opacity: 1;
}

.image-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.image-filename {
  font-size: 0.875rem;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.image-size {
  font-size: 0.75rem;
}

.file-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.file-icon-wrapper {
  width: 3rem;
  height: 3rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.file-icon {
  width: 1.5rem;
  height: 1.5rem;
}

.file-name {
  font-size: 0.875rem;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.file-size {
  font-size: 0.75rem;
}

.conversion-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  background-color: #fed7aa;
  color: #9a3412;
}

.download-button {
  display: flex;
  align-items: center;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 500;
  transition: all 0.2s;
  margin-left: 0.75rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.download-button:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.download-button-sent {
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.download-button-sent:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

.download-button-received {
  background-color: #3b82f6;
  color: white;
}

.download-button-received:hover {
  background-color: #2563eb;
}

.download-text {
  margin-left: 0.25rem;
}

.image-modal {
  position: fixed;
  inset: 0;
  z-index: 50;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  backdrop-filter: blur(8px);
}

.image-modal-content {
  position: relative;
  max-width: 56rem;
  max-height: 100%;
}

.modal-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 0.5rem;
}

.modal-close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem;
  background-color: rgba(0, 0, 0, 0.5);
  color: white;
  border-radius: 9999px;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.modal-close-btn:hover {
  background-color: rgba(0, 0, 0, 0.7);
}

@media (max-width: 640px) {
  .image-preview {
    max-width: 100%;
  }
  
  .file-preview-container {
    padding: 0.5rem;
  }
  
  .download-button {
    padding: 0.5rem 0.25rem;
  }
  
  .download-text {
    display: none;
  }
}
</style>
