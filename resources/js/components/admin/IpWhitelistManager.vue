<template>
  <div class="ip-whitelist-manager space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h4 class="text-lg font-semibold text-secondary-800">IP Whitelist Management</h4>
      <button
        @click="fetchCurrentIp"
        class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors"
        :disabled="loading"
      >
        <span v-if="loadingCurrentIp" class="flex items-center">
          <svg class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Loading...
        </span>
        <span v-else>Get Current IP</span>
      </button>
    </div>

    <!-- Current IP Display -->
    <div v-if="currentIp" class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-blue-800">Your current IP: <span class="font-mono font-bold">{{ currentIp }}</span></p>
          <p class="text-xs text-blue-600 mt-1">{{ currentIpTimestamp }}</p>
        </div>
        <button
          @click="addCurrentIpToWhitelist"
          class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
          :disabled="loading || isCurrentIpInWhitelist"
        >
          {{ isCurrentIpInWhitelist ? 'Already Added' : 'Add to Whitelist' }}
        </button>
      </div>
    </div>

    <!-- Whitelist Status -->
    <div class="mb-4">
      <div class="flex items-center gap-2 mb-2">
        <div class="flex items-center">
          <div class="w-3 h-3 rounded-full mr-2" :class="whitelistStatus.color"></div>
          <span class="text-sm font-medium" :class="whitelistStatus.textColor">{{ whitelistStatus.text }}</span>
        </div>
      </div>
      <p class="text-xs text-secondary-600">{{ whitelistStatus.description }}</p>
    </div>

    <!-- IP List -->
    <div class="space-y-3">
      <div v-if="allowedIps.length === 0" class="text-center py-8 text-secondary-500">
        <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-sm">No IP restrictions set</p>
        <p class="text-xs text-secondary-400 mt-1">User can access from any IP address</p>
      </div>

      <div v-for="(ip, index) in allowedIps" :key="index" class="flex items-center space-x-3 p-3 bg-secondary-50 rounded-lg border">
        <div class="flex-1">
          <p class="font-mono text-sm text-secondary-800">{{ ip }}</p>
          <p class="text-xs text-secondary-500">{{ getIpInfo(ip) }}</p>
        </div>
        <button
          @click="removeIpFromWhitelist(ip)"
          class="p-2 text-error-500 hover:bg-error-50 rounded-lg transition-colors"
          :disabled="loading"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Add New IP -->
    <div class="mt-4">
      <div class="flex items-center gap-2">
        <input
          v-model="newIp"
          type="text"
          placeholder="192.168.1.100"
          pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
          class="flex-1 px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm font-mono"
          :class="{ 'border-error-300 focus:ring-error-500': !isValidIp(newIp) && newIp }"
          @keyup.enter="addNewIp"
        />
        <button
          @click="addNewIp"
          class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors text-sm"
          :disabled="loading || !isValidIp(newIp) || !newIp || allowedIps.includes(newIp)"
        >
          <span v-if="loading" class="flex items-center">
            <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Adding...
          </span>
          <span v-else>Add IP</span>
        </button>
      </div>
      <p v-if="newIp && !isValidIp(newIp)" class="mt-1 text-sm text-error-600">Please enter a valid IP address</p>
      <p v-if="newIp && allowedIps.includes(newIp)" class="mt-1 text-sm text-warning-600">This IP is already in the whitelist</p>
    </div>

    <!-- Bulk Actions -->
    <div v-if="allowedIps.length > 0" class="mt-6 pt-4 border-t border-secondary-200">
      <div class="flex items-center justify-between">
        <p class="text-sm text-secondary-600">{{ allowedIps.length }} IP address(es) in whitelist</p>
        <button
          @click="clearAllIps"
          class="text-sm text-error-600 hover:text-error-800 hover:underline"
          :disabled="loading"
        >
          Clear All
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['updated']);

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const loadingCurrentIp = ref(false);
const newIp = ref('');
const currentIp = ref('');
const currentIpTimestamp = ref('');

const allowedIps = ref([]);

// Computed properties
const isCurrentIpInWhitelist = computed(() => {
  return currentIp.value && allowedIps.value.includes(currentIp.value);
});

const whitelistStatus = computed(() => {
  if (allowedIps.value.length === 0) {
    return {
      text: 'No Restrictions',
      description: 'User can access from any IP address',
      color: 'bg-green-400',
      textColor: 'text-green-700'
    };
  } else {
    return {
      text: `${allowedIps.value.length} IP(s) Whitelisted`,
      description: 'User can only access from whitelisted IP addresses',
      color: 'bg-orange-400',
      textColor: 'text-orange-700'
    };
  }
});

// Watch for user changes
watch(() => props.user, (user) => {
  if (user) {
    allowedIps.value = user.allowed_ips ? [...user.allowed_ips] : [];
  }
}, { immediate: true });

// Methods
const isValidIp = (ip) => {
  if (!ip) return true; // Empty is valid
  const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
  return ipRegex.test(ip);
};

const getIpInfo = (ip) => {
  if (ip === currentIp.value) {
    return 'Your current IP';
  }
  // Check if it's a private IP
  const parts = ip.split('.').map(Number);
  if (
    (parts[0] === 10) ||
    (parts[0] === 172 && parts[1] >= 16 && parts[1] <= 31) ||
    (parts[0] === 192 && parts[1] === 168)
  ) {
    return 'Private/Local network';
  }
  return 'Public IP address';
};

const fetchCurrentIp = async () => {
  loadingCurrentIp.value = true;
  try {
    const result = await adminStore.getCurrentUserIp();
    if (result.success) {
      currentIp.value = result.data.current_ip;
      currentIpTimestamp.value = new Date(result.data.timestamp).toLocaleString();
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    console.error('Error fetching current IP:', error);
    notificationStore.error('Error', 'Failed to fetch current IP');
  } finally {
    loadingCurrentIp.value = false;
  }
};

const addCurrentIpToWhitelist = async () => {
  if (!currentIp.value || isCurrentIpInWhitelist.value) return;
  await addIpToWhitelist(currentIp.value);
};

const addNewIp = async () => {
  if (!newIp.value || !isValidIp(newIp.value) || allowedIps.value.includes(newIp.value)) return;
  
  await addIpToWhitelist(newIp.value);
  if (!loading.value) {
    newIp.value = '';
  }
};

const addIpToWhitelist = async (ip) => {
  loading.value = true;
  try {
    const result = await adminStore.addIpToWhitelist(props.user.id, ip);
    if (result.success) {
      allowedIps.value = result.allowedIps || [];
      notificationStore.success('Success', `IP ${ip} added to whitelist`);
      emit('updated', { ...props.user, allowed_ips: allowedIps.value });
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    console.error('Error adding IP to whitelist:', error);
    notificationStore.error('Error', 'Failed to add IP to whitelist');
  } finally {
    loading.value = false;
  }
};

const removeIpFromWhitelist = async (ip) => {
  loading.value = true;
  try {
    const result = await adminStore.removeIpFromWhitelist(props.user.id, ip);
    if (result.success) {
      allowedIps.value = result.allowedIps || [];
      notificationStore.success('Success', `IP ${ip} removed from whitelist`);
      emit('updated', { ...props.user, allowed_ips: allowedIps.value });
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    console.error('Error removing IP from whitelist:', error);
    notificationStore.error('Error', 'Failed to remove IP from whitelist');
  } finally {
    loading.value = false;
  }
};

const clearAllIps = async () => {
  if (!confirm('Are you sure you want to clear all IP restrictions? This will allow access from any IP address.')) {
    return;
  }

  loading.value = true;
  try {
    const result = await adminStore.updateUserIpWhitelist(props.user.id, []);
    if (result.success) {
      allowedIps.value = [];
      notificationStore.success('Success', 'All IP restrictions cleared');
      emit('updated', { ...props.user, allowed_ips: [] });
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    console.error('Error clearing IP whitelist:', error);
    notificationStore.error('Error', 'Failed to clear IP whitelist');
  } finally {
    loading.value = false;
  }
};

// Fetch current IP on mount
fetchCurrentIp();
</script>

<style scoped>
/* Custom styles for this component if needed */
</style>
