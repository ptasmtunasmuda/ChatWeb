<template>
  <div class="activity-log-viewer space-y-4">
    <!-- Header and Filters -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <h5 class="text-lg font-semibold text-secondary-900">Activity Log</h5>
        <p class="text-sm text-secondary-600">Track all user activities and system events</p>
      </div>
      
      <!-- Filters -->
      <div class="flex items-center gap-3">
        <!-- Date Range Filter -->
        <div class="flex items-center gap-2">
          <input
            v-model="filters.dateFrom"
            type="date"
            class="px-3 py-1 text-xs border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            placeholder="From"
          />
          <span class="text-xs text-secondary-500">to</span>
          <input
            v-model="filters.dateTo"
            type="date"
            class="px-3 py-1 text-xs border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            placeholder="To"
          />
        </div>

        <!-- Action Filter -->
        <select
          v-model="filters.action"
          class="px-3 py-1 text-xs border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
          <option value="">All Actions</option>
          <option v-for="(label, value) in availableActions" :key="value" :value="value">
            {{ label }}
          </option>
        </select>

        <!-- Search -->
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search activities..."
          class="px-3 py-1 text-xs border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 w-40"
        />

        <!-- Refresh Button -->
        <button
          @click="loadActivityLogs"
          class="px-3 py-1 bg-primary-500 text-white text-xs rounded-lg hover:bg-primary-600 transition-colors flex items-center"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loading ? 'Loading...' : 'Refresh' }}</span>
        </button>
      </div>
    </div>

    <!-- Activity List -->
    <div class="activity-list">
      <!-- Loading State -->
      <div v-if="loading && !logs.length" class="text-center py-12">
        <div class="flex items-center justify-center">
          <div class="spinner-sm mr-3"></div>
          <span class="text-secondary-600">Loading activity logs...</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && !logs.length" class="text-center py-12 text-secondary-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="text-lg font-medium text-secondary-900 mb-2">No Activity Found</h3>
        <p class="text-secondary-600">No activities match your current filters</p>
      </div>

      <!-- Activity Items -->
      <div v-else class="space-y-3">
        <div
          v-for="log in logs"
          :key="log.id"
          class="activity-item flex items-start space-x-4 p-4 bg-white rounded-lg border border-secondary-200 hover:shadow-md transition-shadow"
        >
          <!-- Activity Icon -->
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="getActivityIcon(log.action).bg">
              <svg class="w-5 h-5" :class="getActivityIcon(log.action).color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(log.action).path"></path>
              </svg>
            </div>
          </div>

          <!-- Activity Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-900">
                  {{ log.formatted_action }}
                </p>
                <p v-if="log.description" class="text-sm text-secondary-600 mt-1">
                  {{ log.description }}
                </p>
                
                <!-- Metadata -->
                <div v-if="log.metadata_formatted" class="mt-2">
                  <div class="bg-secondary-50 rounded-lg p-3">
                    <p class="text-xs font-medium text-secondary-700 mb-2">Details:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                      <div v-for="(value, key) in log.metadata_formatted" :key="key">
                        <span class="font-medium text-secondary-600">{{ key }}:</span>
                        <span class="text-secondary-800 ml-1">{{ value }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- IP and User Agent -->
                <div v-if="log.ip_address || log.user_agent" class="mt-2 text-xs text-secondary-500">
                  <div v-if="log.ip_address" class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                    </svg>
                    IP: {{ log.ip_address }}
                  </div>
                  <div v-if="log.user_agent" class="flex items-center mt-1 max-w-md">
                    <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="truncate">{{ getUserAgentInfo(log.user_agent) }}</span>
                  </div>
                </div>
              </div>

              <!-- Timestamp -->
              <div class="flex-shrink-0 text-right">
                <p class="text-xs text-secondary-500">{{ log.time_ago }}</p>
                <p class="text-xs text-secondary-400">{{ log.formatted_date }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Load More Button -->
      <div v-if="pagination && pagination.current_page < pagination.last_page" class="text-center pt-6">
        <button
          @click="loadMore"
          class="px-4 py-2 bg-secondary-100 text-secondary-700 rounded-lg hover:bg-secondary-200 transition-colors flex items-center mx-auto"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loading ? 'Loading...' : 'Load More' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';
import { debounce } from 'lodash-es';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const logs = ref([]);
const pagination = ref(null);
const availableActions = ref({});

const filters = reactive({
  dateFrom: '',
  dateTo: '',
  action: '',
  search: ''
});

// Watch filters for real-time filtering
const debouncedLoadLogs = debounce(() => {
  loadActivityLogs(true);
}, 500);

watch(filters, debouncedLoadLogs, { deep: true });

// Methods
const loadActivityLogs = async (reset = false) => {
  if (reset) {
    logs.value = [];
    pagination.value = null;
  }

  loading.value = true;

  try {
    const params = {
      page: reset ? 1 : (pagination.value?.current_page || 0) + 1,
      per_page: 20,
      ...filters
    };

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    const result = await adminStore.fetchUserActivityLogs(props.user.id, params);

    if (result.success) {
      if (reset) {
        logs.value = result.data.logs.data;
      } else {
        logs.value.push(...result.data.logs.data);
      }
      
      pagination.value = {
        current_page: result.data.logs.current_page,
        last_page: result.data.logs.last_page,
        total: result.data.logs.total
      };

      availableActions.value = result.data.available_actions;
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    console.error('Error loading activity logs:', error);
    notificationStore.error('Error', 'Failed to load activity logs');
  } finally {
    loading.value = false;
  }
};

const loadMore = () => {
  loadActivityLogs(false);
};

const getActivityIcon = (action) => {
  const icons = {
    login: {
      path: "M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1",
      bg: "bg-green-100",
      color: "text-green-600"
    },
    logout: {
      path: "M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1",
      bg: "bg-gray-100",
      color: "text-gray-600"
    },
    access_blocked: {
      path: "M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z",
      bg: "bg-red-100",
      color: "text-red-600"
    },
    profile_updated: {
      path: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
      bg: "bg-blue-100",
      color: "text-blue-600"
    },
    password_changed: {
      path: "M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z",
      bg: "bg-yellow-100",
      color: "text-yellow-600"
    },
    ip_whitelist_updated: {
      path: "M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9",
      bg: "bg-orange-100",
      color: "text-orange-600"
    },
    message_sent: {
      path: "M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z",
      bg: "bg-purple-100",
      color: "text-purple-600"
    },
    file_uploaded: {
      path: "M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12",
      bg: "bg-green-100",
      color: "text-green-600"
    },
    default: {
      path: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
      bg: "bg-secondary-100",
      color: "text-secondary-600"
    }
  };

  return icons[action] || icons.default;
};

const getUserAgentInfo = (userAgent) => {
  if (!userAgent) return 'Unknown';
  
  // Simple user agent parsing
  if (userAgent.includes('Chrome')) return 'Chrome Browser';
  if (userAgent.includes('Firefox')) return 'Firefox Browser';
  if (userAgent.includes('Safari')) return 'Safari Browser';
  if (userAgent.includes('Edge')) return 'Edge Browser';
  if (userAgent.includes('Mobile')) return 'Mobile Device';
  
  return 'Desktop Browser';
};

// Load logs on component mount
onMounted(() => {
  loadActivityLogs(true);
});
</script>

<style scoped>
.activity-item {
  transition: all 0.2s ease;
}

.activity-item:hover {
  transform: translateY(-1px);
}

/* Utility classes untuk memastikan sizing */
.w-3 { width: 0.75rem; }
.w-4 { width: 1rem; }
.w-5 { width: 1.25rem; }
.w-10 { width: 2.5rem; }
.w-16 { width: 4rem; }
.w-40 { width: 10rem; }

.h-3 { height: 0.75rem; }
.h-4 { height: 1rem; }
.h-5 { height: 1.25rem; }
.h-10 { height: 2.5rem; }
.h-16 { height: 4rem; }

.space-y-3 > * + * {
  margin-top: 0.75rem;
}

.space-y-4 > * + * {
  margin-top: 1rem;
}

.space-x-4 > * + * {
  margin-left: 1rem;
}

.gap-2 {
  gap: 0.5rem;
}

.gap-3 {
  gap: 0.75rem;
}

.gap-4 {
  gap: 1rem;
}
</style>
