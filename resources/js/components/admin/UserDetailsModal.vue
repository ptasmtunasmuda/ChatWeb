<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="card max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-bounce-in">
      <div class="p-6 border-b border-secondary-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold gradient-text font-display">User Details</h3>
          <button @click="$emit('close')" class="p-2 hover:bg-secondary-100 rounded-lg">
            <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      <div class="p-6">
        <div v-if="user" class="space-y-6">
          <!-- User Profile -->
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <h4 class="text-xl font-bold text-secondary-900">{{ user.name }}</h4>
              <p class="text-secondary-600">{{ user.email }}</p>
              <div class="flex items-center space-x-2 mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                  'bg-primary-100 text-primary-800': user.role === 'admin',
                  'bg-secondary-100 text-secondary-800': user.role === 'user'
                }">
                  {{ user.role }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                  'bg-success-100 text-success-800': user.is_active,
                  'bg-error-100 text-error-800': !user.is_active
                }">
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>

          <!-- User Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ user.chat_rooms_count || 0 }}</div>
              <div class="text-sm text-blue-600">Chat Rooms</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ user.messages_count || 0 }}</div>
              <div class="text-sm text-green-600">Messages</div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ formatDate(user.last_seen_at) }}</div>
              <div class="text-sm text-blue-600">Last Seen</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
              <div class="text-2xl font-bold text-orange-600">{{ formatDate(user.created_at) }}</div>
              <div class="text-sm text-orange-600">Joined</div>
            </div>
          </div>

          <!-- User Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h5 class="text-lg font-semibold text-secondary-900 mb-3">Account Information</h5>
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-secondary-700">User ID</label>
                  <p class="text-secondary-900">{{ user.id }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-secondary-700">Email Verified</label>
                  <p class="text-secondary-900">{{ user.email_verified_at ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-secondary-700">Account Status</label>
                  <p class="text-secondary-900">{{ user.is_active ? 'Active' : 'Inactive' }}</p>
                </div>
              </div>
            </div>
            <div>
              <h5 class="text-lg font-semibold text-secondary-900 mb-3">Activity</h5>
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-secondary-700">Last Activity</label>
                  <p class="text-secondary-900">{{ formatDateTime(user.last_seen_at) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-secondary-700">Registration Date</label>
                  <p class="text-secondary-900">{{ formatDateTime(user.created_at) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-secondary-700">Last Updated</label>
                  <p class="text-secondary-900">{{ formatDateTime(user.updated_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div v-if="user.recent_messages && user.recent_messages.length > 0">
            <h5 class="text-lg font-semibold text-secondary-900 mb-3">Recent Messages</h5>
            <div class="space-y-2 max-h-40 overflow-y-auto">
              <div v-for="message in user.recent_messages" :key="message.id" class="p-3 bg-secondary-50 rounded-lg">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <p class="text-sm text-secondary-900">{{ message.content }}</p>
                    <p class="text-xs text-secondary-500 mt-1">in {{ message.chat_room?.name }}</p>
                  </div>
                  <span class="text-xs text-secondary-500">{{ formatDateTime(message.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Chat Rooms -->
          <div v-if="user.recent_chat_rooms && user.recent_chat_rooms.length > 0">
            <h5 class="text-lg font-semibold text-secondary-900 mb-3">Recent Chat Rooms</h5>
            <div class="space-y-2">
              <div v-for="room in user.recent_chat_rooms" :key="room.id" class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg">
                <div>
                  <p class="font-medium text-secondary-900">{{ room.name }}</p>
                  <p class="text-sm text-secondary-500">{{ room.type }} • {{ room.participants_count }} participants</p>
                </div>
                <span class="text-xs text-secondary-500">{{ formatDateTime(room.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <button @click="$emit('close')" class="btn-secondary">Close</button>
          <button @click="editUser" class="btn-primary">Edit User</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps(['user']);
const emit = defineEmits(['close', 'edit']);

const formatDate = (dateString) => {
  if (!dateString) return 'Never';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatDateTime = (dateString) => {
  if (!dateString) return 'Never';
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const editUser = () => {
  emit('edit', props.user);
};
</script>

<style scoped>
.gradient-text {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn-secondary {
  background: white;
  color: #475569;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  color: white;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.25);
  transition: all 0.3s ease;
}

.btn-primary:hover {
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.3);
  transform: scale(1.05);
}

.animate-bounce-in {
  animation: bounceIn 0.3s ease-out;
}

@keyframes bounceIn {
  0% { transform: scale(0.9); opacity: 0; }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); opacity: 1; }
}
</style>
