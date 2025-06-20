<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-purple-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold gradient-text font-display">Chat Management</h1>
            <p class="text-secondary-600 mt-2">Monitor and manage chat rooms and messages</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="showCreateRoomModal = true"
              class="btn-primary flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              <span>Create Room</span>
            </button>
            <button
              @click="exportData"
              class="btn-secondary flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>Export</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Total Rooms</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.total_rooms || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Total Messages</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.total_messages || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Active Users</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.active_users || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Today's Messages</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.today_messages || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mb-6">
        <div class="border-b border-secondary-200">
          <nav class="-mb-px flex space-x-8">
            <button
              @click="activeTab = 'rooms'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'rooms'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'
              ]"
            >
              Chat Rooms
            </button>
            <button
              @click="activeTab = 'messages'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'messages'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'
              ]"
            >
              Messages
            </button>
            <button
              @click="activeTab = 'reports'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'reports'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'
              ]"
            >
              Reports
            </button>
          </nav>
        </div>
      </div>

      <!-- Chat Rooms Tab -->
      <div v-if="activeTab === 'rooms'">
        <!-- Filters -->
        <div class="card mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
              <!-- Search -->
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input
                  v-model="roomFilters.search"
                  @input="debouncedSearchRooms"
                  type="text"
                  class="pl-10 pr-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="Search rooms..."
                />
              </div>

              <!-- Type Filter -->
              <select
                v-model="roomFilters.type"
                @change="fetchRooms"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">All Types</option>
                <option value="group">Group</option>
                <option value="private">Private</option>
              </select>

              <!-- Status Filter -->
              <select
                v-model="roomFilters.status"
                @change="fetchRooms"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center space-x-2" v-if="selectedRooms.length > 0">
              <span class="text-sm text-secondary-600">{{ selectedRooms.length }} selected</span>
              <button
                @click="bulkRoomAction('activate')"
                class="px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors"
              >
                Activate
              </button>
              <button
                @click="bulkRoomAction('deactivate')"
                class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
              >
                Deactivate
              </button>
            </div>
          </div>
        </div>

        <!-- Rooms Table -->
        <div class="card">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
              <thead class="bg-secondary-50">
                <tr>
                  <th class="px-6 py-3 text-left">
                    <input
                      type="checkbox"
                      @change="toggleSelectAllRooms"
                      :checked="selectedRooms.length === (rooms?.length || 0) && (rooms?.length || 0) > 0"
                      class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Room
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Participants
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Messages
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-secondary-200">
                <!-- Loading State -->
                <tr v-if="loading">
                  <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center">
                      <svg class="animate-spin h-8 w-8 text-primary-600 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="text-secondary-600">Loading chat rooms...</span>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="!rooms || rooms.length === 0">
                  <td colspan="8" class="px-6 py-12 text-center">
                    <div class="text-secondary-500">
                      <svg class="mx-auto h-12 w-12 text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                      <p class="text-lg font-medium text-secondary-900 mb-2">No chat rooms found</p>
                      <p class="text-secondary-500">Get started by creating your first chat room.</p>
                    </div>
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="room in rooms" :key="room.id" class="hover:bg-secondary-50">
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      :value="room.id"
                      v-model="selectedRooms"
                      class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-primary-500 to-accent-500 flex items-center justify-center text-white font-medium">
                          {{ room.name.charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-secondary-900">{{ room.name }}</div>
                        <div class="text-sm text-secondary-500">{{ room.description || 'No description' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getTypeBadgeClass(room.type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ room.type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-900">
                    {{ room.participants_count || 0 }}
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-900">
                    {{ room.messages_count || 0 }}
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getStatusBadgeClass(room.is_active)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ room.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-500">
                    {{ formatDate(room.created_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm font-medium space-x-2">
                    <button
                      @click="viewRoom(room)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      View
                    </button>
                    <button
                      @click="editRoom(room)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteRoom(room)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';
import { debounce } from 'lodash-es';

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const activeTab = ref('rooms');
const showCreateRoomModal = ref(false);

// Chat Rooms
const rooms = ref([]);
const selectedRooms = ref([]);
const roomFilters = reactive({
  search: '',
  type: '',
  status: ''
});

// Messages
const messages = ref([]);
const selectedMessages = ref([]);
const messageFilters = reactive({
  search: '',
  room_id: '',
  user_id: '',
  date_from: '',
  date_to: ''
});

// Stats
const stats = ref({
  total_rooms: 0,
  total_messages: 0,
  active_users: 0,
  today_messages: 0
});

const fetchRooms = async () => {
  try {
    loading.value = true;
    const response = await adminStore.fetchChatRooms(roomFilters);
    if (response.success) {
      rooms.value = Array.isArray(response.data) ? response.data : [];
      updateStats();
    } else {
      rooms.value = [];
      notificationStore.error('Error', response.message || 'Failed to fetch chat rooms');
    }
  } catch (error) {
    console.error('Error fetching rooms:', error);
    rooms.value = [];
    notificationStore.error('Error', 'Failed to fetch chat rooms');
  } finally {
    loading.value = false;
  }
};

const fetchMessages = async () => {
  try {
    loading.value = true;
    const response = await adminStore.fetchMessages(messageFilters);
    if (response.success) {
      messages.value = response.data;
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
    notificationStore.error('Error', 'Failed to fetch messages');
  } finally {
    loading.value = false;
  }
};

const updateStats = () => {
  const roomsArray = Array.isArray(rooms.value) ? rooms.value : [];

  stats.value = {
    total_rooms: roomsArray.length,
    total_messages: roomsArray.reduce((sum, room) => sum + (room.messages_count || 0), 0),
    active_users: new Set(roomsArray.flatMap(room => room.participants || [])).size,
    today_messages: Math.floor(Math.random() * 100) // Mock data
  };
};

const debouncedSearchRooms = debounce(() => {
  fetchRooms();
}, 300);

const toggleSelectAllRooms = () => {
  const roomsArray = Array.isArray(rooms.value) ? rooms.value : [];

  if (selectedRooms.value.length === roomsArray.length && roomsArray.length > 0) {
    selectedRooms.value = [];
  } else {
    selectedRooms.value = roomsArray.map(room => room.id);
  }
};

const getTypeBadgeClass = (type) => {
  return type === 'group'
    ? 'bg-blue-100 text-blue-800'
    : 'bg-purple-100 text-purple-800';
};

const getStatusBadgeClass = (isActive) => {
  return isActive
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString();
};

const viewRoom = (room) => {
  notificationStore.info('Info', `View room: ${room.name}`);
};

const editRoom = (room) => {
  notificationStore.info('Info', `Edit room: ${room.name}`);
};

const deleteRoom = async (room) => {
  if (confirm(`Are you sure you want to delete room "${room.name}"?`)) {
    try {
      const result = await adminStore.deleteRoom(room.id);
      if (result.success) {
        notificationStore.success('Success', 'Room deleted successfully');
        fetchRooms();
      } else {
        notificationStore.error('Error', result.message);
      }
    } catch (error) {
      notificationStore.error('Error', 'Failed to delete room');
    }
  }
};

const bulkRoomAction = async (action) => {
  if (selectedRooms.value.length === 0) return;

  try {
    const result = await adminStore.bulkRoomAction(action, selectedRooms.value);
    if (result.success) {
      notificationStore.success('Success', `Rooms ${action}d successfully`);
      selectedRooms.value = [];
      fetchRooms();
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    notificationStore.error('Error', `Failed to ${action} rooms`);
  }
};

const exportData = () => {
  notificationStore.info('Info', 'Export functionality will be implemented');
};

onMounted(() => {
  fetchRooms();
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

.btn-secondary {
  background: white;
  color: #475569;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: scale(1.05);
}

.card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
