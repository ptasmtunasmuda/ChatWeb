<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold gradient-text font-display">
              Admin Dashboard
            </h1>
            <p class="text-secondary-600 mt-2">
              Monitor and manage your ChatWeb application
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="refreshData"
              :disabled="loading"
              class="btn-secondary"
            >
              <svg class="w-5 h-5 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Refresh
            </button>
            <button
              @click="showExportModal = true"
              class="btn-primary"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Export Data
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card group hover:shadow-purple-lg">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-accent-500 rounded-xl flex items-center justify-center shadow-purple group-hover:shadow-purple-lg transition-all duration-300 group-hover:scale-110">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-secondary-600">Total Users</p>
              <p class="text-2xl font-bold text-secondary-900">{{ adminStore.totalUsers }}</p>
              <p class="text-xs text-success-600" v-if="dashboardStats.today_users">
                +{{ dashboardStats.today_users }} today
              </p>
            </div>
          </div>
        </div>

        <div class="card group hover:shadow-purple-lg">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-r from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-secondary-600">Chat Rooms</p>
              <p class="text-2xl font-bold text-secondary-900">{{ adminStore.totalChatRooms }}</p>
              <p class="text-xs text-success-600" v-if="dashboardStats.today_chat_rooms">
                +{{ dashboardStats.today_chat_rooms }} today
              </p>
            </div>
          </div>
        </div>

        <div class="card group hover:shadow-purple-lg">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-r from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-secondary-600">Messages</p>
              <p class="text-2xl font-bold text-secondary-900">{{ adminStore.totalMessages }}</p>
              <p class="text-xs text-success-600" v-if="dashboardStats.today_messages">
                +{{ dashboardStats.today_messages }} today
              </p>
            </div>
          </div>
        </div>

        <div class="card group hover:shadow-purple-lg">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-r from-accent-500 to-accent-600 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-lg transition-all duration-300 group-hover:scale-110">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-secondary-600">Active Users</p>
              <p class="text-2xl font-bold text-secondary-900">{{ adminStore.activeUsers }}</p>
              <p class="text-xs text-primary-600" v-if="dashboardStats.user_activity_rate">
                {{ dashboardStats.user_activity_rate }}% activity rate
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- User Growth Chart -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-secondary-900">User Growth (30 Days)</h3>
            <div class="flex items-center space-x-2">
              <div class="w-3 h-3 bg-primary-500 rounded-full"></div>
              <span class="text-sm text-secondary-600">New Users</span>
            </div>
          </div>
          <div class="h-64">
            <canvas ref="userGrowthChart"></canvas>
          </div>
        </div>

        <!-- Message Activity Chart -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-secondary-900">Message Activity (7 Days)</h3>
            <div class="flex items-center space-x-2">
              <div class="w-3 h-3 bg-accent-500 rounded-full"></div>
              <span class="text-sm text-secondary-600">Messages</span>
            </div>
          </div>
          <div class="h-64">
            <canvas ref="messageActivityChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Users -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-secondary-900">Recent Users</h3>
            <router-link to="/admin/users" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
              View All
            </router-link>
          </div>

          <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="animate-pulse">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-secondary-200 rounded-full"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-4 bg-secondary-200 rounded w-3/4"></div>
                  <div class="h-3 bg-secondary-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="recentActivity.recent_users?.length === 0" class="text-center py-6">
            <p class="text-sm text-secondary-500">No recent users</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="user in recentActivity.recent_users"
              :key="user.id"
              class="flex items-center space-x-3 p-3 rounded-xl hover:bg-secondary-50 transition-all duration-300 cursor-pointer group"
              @click="$router.push(`/admin/users/${user.id}`)"
            >
              <div class="w-10 h-10 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-sm group-hover:shadow-purple transition-all duration-300">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-secondary-900 truncate">{{ user.name }}</p>
                <p class="text-xs text-secondary-500">{{ formatTime(user.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Chat Rooms -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-secondary-900">Recent Chats</h3>
            <router-link to="/admin/chats" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
              View All
            </router-link>
          </div>

          <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="animate-pulse">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-secondary-200 rounded-full"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-4 bg-secondary-200 rounded w-3/4"></div>
                  <div class="h-3 bg-secondary-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="recentActivity.recent_chat_rooms?.length === 0" class="text-center py-6">
            <p class="text-sm text-secondary-500">No recent chat rooms</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="room in recentActivity.recent_chat_rooms"
              :key="room.id"
              class="flex items-center space-x-3 p-3 rounded-xl hover:bg-secondary-50 transition-all duration-300 cursor-pointer group"
              @click="$router.push(`/admin/chats/${room.id}`)"
            >
              <div class="w-10 h-10 bg-gradient-to-r from-success-400 to-success-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-sm group-hover:shadow-lg transition-all duration-300">
                {{ room.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-secondary-900 truncate">{{ room.name }}</p>
                <p class="text-xs text-secondary-500">{{ room.type }} • {{ formatTime(room.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- System Health -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-secondary-900">System Health</h3>
            <button
              @click="checkSystemHealth"
              class="text-primary-600 hover:text-primary-700 font-medium text-sm"
            >
              Check Now
            </button>
          </div>

          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg bg-secondary-50">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full" :class="{
                  'bg-success-500': systemHealth.database?.status === 'healthy',
                  'bg-error-500': systemHealth.database?.status === 'error',
                  'bg-warning-500': !systemHealth.database?.status
                }"></div>
                <span class="text-sm font-medium text-secondary-700">Database</span>
              </div>
              <span class="text-xs text-secondary-500">
                {{ systemHealth.database?.status || 'Unknown' }}
              </span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-lg bg-secondary-50">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 bg-success-500 rounded-full"></div>
                <span class="text-sm font-medium text-secondary-700">Storage</span>
              </div>
              <span class="text-xs text-secondary-500">
                {{ systemHealth.storage?.usage_percentage || 0 }}% used
              </span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-lg bg-secondary-50">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full" :class="{
                  'bg-success-500': systemHealth.queue?.status === 'running',
                  'bg-error-500': systemHealth.queue?.status === 'error',
                  'bg-warning-500': !systemHealth.queue?.status
                }"></div>
                <span class="text-sm font-medium text-secondary-700">Queue</span>
              </div>
              <span class="text-xs text-secondary-500">
                {{ systemHealth.queue?.status || 'Unknown' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Modal -->
    <ExportModal
      v-if="showExportModal"
      @close="showExportModal = false"
      @export="handleExport"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';
import ExportModal from './ExportModal.vue';
import Chart from 'chart.js/auto';

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const showExportModal = ref(false);
const userGrowthChart = ref(null);
const messageActivityChart = ref(null);
const userGrowthChartInstance = ref(null);
const messageActivityChartInstance = ref(null);

// Computed properties
const dashboardStats = computed(() => adminStore.dashboardStats);
const recentActivity = computed(() => adminStore.recentActivity);
const userGrowth = computed(() => adminStore.userGrowth);
const messageStats = computed(() => adminStore.messageStats);
const systemHealth = computed(() => adminStore.systemHealth);

// Methods
const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diffInHours = (now - date) / (1000 * 60 * 60);

  if (diffInHours < 1) {
    return 'Just now';
  } else if (diffInHours < 24) {
    return `${Math.floor(diffInHours)}h ago`;
  } else if (diffInHours < 168) {
    return `${Math.floor(diffInHours / 24)}d ago`;
  } else {
    return date.toLocaleDateString();
  }
};

const refreshData = async () => {
  loading.value = true;
  try {
    const result = await adminStore.fetchDashboardData();
    if (result.success) {
      await checkSystemHealth();
      await nextTick();
      updateCharts();
      notificationStore.success('Success', 'Dashboard data refreshed successfully');
    } else {
      notificationStore.error('Error', result.message);
    }
  } finally {
    loading.value = false;
  }
};

const checkSystemHealth = async () => {
  const result = await adminStore.fetchSystemHealth();
  if (!result.success) {
    notificationStore.error('Error', result.message);
  }
};

const handleExport = async (exportData) => {
  const result = await adminStore.exportData(
    exportData.type,
    exportData.format,
    exportData.dateFrom,
    exportData.dateTo
  );

  if (result.success) {
    notificationStore.success('Success', 'Data exported successfully');
  } else {
    notificationStore.error('Error', result.message);
  }
};

const createUserGrowthChart = () => {
  if (!userGrowthChart.value || !userGrowth.value.length) return;

  const ctx = userGrowthChart.value.getContext('2d');

  if (userGrowthChartInstance.value) {
    userGrowthChartInstance.value.destroy();
  }

  userGrowthChartInstance.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: userGrowth.value.map(item => item.label),
      datasets: [{
        label: 'New Users',
        data: userGrowth.value.map(item => item.count),
        borderColor: '#a855f7',
        backgroundColor: 'rgba(168, 85, 247, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#a855f7',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#64748b'
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9'
          },
          ticks: {
            color: '#64748b'
          }
        }
      },
      elements: {
        point: {
          hoverBackgroundColor: '#a855f7'
        }
      }
    }
  });
};

const createMessageActivityChart = () => {
  if (!messageActivityChart.value || !messageStats.value.length) return;

  const ctx = messageActivityChart.value.getContext('2d');

  if (messageActivityChartInstance.value) {
    messageActivityChartInstance.value.destroy();
  }

  messageActivityChartInstance.value = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: messageStats.value.map(item => item.label),
      datasets: [{
        label: 'Messages',
        data: messageStats.value.map(item => item.count),
        backgroundColor: 'rgba(217, 70, 239, 0.8)',
        borderColor: '#d946ef',
        borderWidth: 1,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#64748b'
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#f1f5f9'
          },
          ticks: {
            color: '#64748b'
          }
        }
      }
    }
  });
};

const updateCharts = () => {
  nextTick(() => {
    createUserGrowthChart();
    createMessageActivityChart();
  });
};

const initializeDashboard = async () => {
  loading.value = true;
  try {
    const result = await adminStore.fetchDashboardData();
    if (result.success) {
      await checkSystemHealth();
      await nextTick();
      updateCharts();
    } else {
      notificationStore.error('Error', result.message);
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  initializeDashboard();
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
  transform: translateY(-2px);
}
</style>
