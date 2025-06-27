<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold gradient-text font-display">
              User Management
            </h1>
            <p class="text-secondary-600 mt-2">
              Manage users, roles, and permissions
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="showCreateUserModal = true"
              class="btn-primary"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add User
            </button>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input
                v-model="filters.search"
                @input="debouncedSearch"
                type="text"
                placeholder="Search users..."
                class="w-full pl-10 pr-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Role Filter -->
          <div>
            <select
              v-model="filters.role"
              @change="fetchUsers"
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="user">User</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <select
              v-model="filters.status"
              @change="fetchUsers"
              class="w-full px-3 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedUsers.length > 0" class="mt-4 p-4 bg-primary-50 rounded-lg border border-primary-200">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-primary-700">
              {{ selectedUsers.length }} user(s) selected
            </span>
            <div class="flex items-center space-x-2">
              <select
                v-model="bulkAction"
                class="px-3 py-1 text-sm border border-primary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Select Action</option>
                <option value="update_role">Update Role</option>
                <option value="delete">Delete</option>
              </select>
              <select
                v-if="bulkAction === 'update_role'"
                v-model="bulkRole"
                class="px-3 py-1 text-sm border border-primary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
              </select>
              <button
                @click="performBulkAction"
                :disabled="!bulkAction || (bulkAction === 'update_role' && !bulkRole)"
                class="px-4 py-1 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Apply
              </button>
              <button
                @click="clearSelection"
                class="px-4 py-1 text-sm text-primary-600 hover:text-primary-700"
              >
                Clear
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-6 py-3 text-left">
                  <input
                    type="checkbox"
                    @change="toggleSelectAll"
                    :checked="selectedUsers.length === users.length && users.length > 0"
                    :indeterminate="selectedUsers.length > 0 && selectedUsers.length < users.length"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  User
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  Role
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  IP Access
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  Activity
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  Joined
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
              <!-- Loading -->
              <tr v-if="loading">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="flex items-center justify-center">
                    <div class="spinner mr-3"></div>
                    <span class="text-secondary-600">Loading users...</span>
                  </div>
                </td>
              </tr>

              <!-- No Users -->
              <tr v-else-if="users.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <svg class="mx-auto h-12 w-12 text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                  <h3 class="text-lg font-medium text-secondary-900 mb-2">No users found</h3>
                  <p class="text-secondary-600">Try adjusting your search or filter criteria</p>
                </td>
              </tr>

              <!-- Users -->
              <tr v-else v-for="user in users" :key="user.id" class="hover:bg-secondary-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedUsers"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-primary-400 to-accent-400 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-secondary-900">{{ user.name }}</div>
                      <div class="text-sm text-secondary-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                    'bg-primary-100 text-primary-800': user.role === 'admin',
                    'bg-secondary-100 text-secondary-800': user.role === 'user'
                  }">
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                    'bg-success-100 text-success-800': user.is_online,
                    'bg-secondary-100 text-secondary-800': !user.is_online
                  }">
                    {{ user.is_online ? 'Online' : 'Offline' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-2">
                    <span v-if="!user.allowed_ips || user.allowed_ips.length === 0" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
                      </svg>
                      Any IP
                    </span>
                    <span v-else 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                      </svg>
                      {{ user.allowed_ips.length }} IP(s)
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                  <div>{{ user.messages_count || 0 }} messages</div>
                  <div>{{ user.chat_rooms_count || 0 }} chats</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="viewUser(user)"
                      class="text-primary-600 hover:text-primary-900 p-1 rounded"
                      title="View Details"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button
                      @click="editUser(user)"
                      class="text-secondary-600 hover:text-secondary-900 p-1 rounded"
                      title="Edit User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button
                      @click="deleteUser(user)"
                      class="text-error-600 hover:text-error-900 p-1 rounded"
                      title="Delete User"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-secondary-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-secondary-700">
              Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-1 text-sm border border-secondary-300 rounded-lg hover:bg-secondary-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <span class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-lg">
                {{ pagination.current_page }}
              </span>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 text-sm border border-secondary-300 rounded-lg hover:bg-secondary-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CreateUserModal
      v-if="showCreateUserModal"
      @close="showCreateUserModal = false"
      @created="handleUserCreated"
    />

    <EditUserModal
      v-if="showEditUserModal"
      :user="selectedUser"
      @close="showEditUserModal = false"
      @updated="handleUserUpdated"
    />

    <UserDetailsModal
      v-if="showUserDetailsModal"
      :user="selectedUser"
      @close="showUserDetailsModal = false"
      @edit="handleEditFromDetails"
      @updated="handleUserUpdatedFromDetails"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';
import CreateUserModal from './CreateUserModal.vue';
import EditUserModal from './EditUserModal.vue';
import UserDetailsModal from './UserDetailsModal.vue';
import { debounce } from 'lodash-es';

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = computed(() => adminStore.usersLoading);
const users = computed(() => adminStore.users);

const showCreateUserModal = ref(false);
const showEditUserModal = ref(false);
const showUserDetailsModal = ref(false);
const selectedUser = ref(null);
const selectedUsers = ref([]);
const pagination = ref(null);

const filters = reactive({
  search: '',
  role: '',
  status: ''
});

const bulkAction = ref('');
const bulkRole = ref('');

// Methods
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const fetchUsers = async (page = 1) => {
  const params = {
    page,
    per_page: 15,
    ...filters
  };

  const result = await adminStore.fetchUsers(params);
  if (result.success) {
    pagination.value = result.pagination;
  } else {
    notificationStore.error('Error', result.message);
  }
};

const debouncedSearch = debounce(() => {
  fetchUsers();
}, 500);

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page);
  }
};

const toggleSelectAll = () => {
  if (selectedUsers.value.length === users.value.length) {
    selectedUsers.value = [];
  } else {
    selectedUsers.value = users.value.map(user => user.id);
  }
};

const clearSelection = () => {
  selectedUsers.value = [];
  bulkAction.value = '';
  bulkRole.value = '';
};

const performBulkAction = async () => {
  if (!bulkAction.value || selectedUsers.value.length === 0) return;

  const confirmMessage = bulkAction.value === 'delete'
    ? `Are you sure you want to delete ${selectedUsers.value.length} user(s)?`
    : `Are you sure you want to update the role of ${selectedUsers.value.length} user(s)?`;

  if (!confirm(confirmMessage)) return;

  const result = await adminStore.bulkUserAction(
    bulkAction.value,
    selectedUsers.value,
    bulkRole.value
  );

  if (result.success) {
    notificationStore.success('Success', `Bulk action completed. ${result.affectedCount} user(s) affected.`);
    clearSelection();
    fetchUsers();
  } else {
    notificationStore.error('Error', result.message);
  }
};

const viewUser = async (user) => {
  selectedUser.value = user;
  const result = await adminStore.fetchUser(user.id);
  if (result.success) {
    showUserDetailsModal.value = true;
  } else {
    notificationStore.error('Error', result.message);
  }
};

const editUser = (user) => {
  selectedUser.value = user;
  showEditUserModal.value = true;
};

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) return;

  const result = await adminStore.deleteUser(user.id);
  if (result.success) {
    notificationStore.success('Success', 'User deleted successfully');
    fetchUsers();
  } else {
    notificationStore.error('Error', result.message);
  }
};

const handleUserCreated = (user) => {
  showCreateUserModal.value = false;
  notificationStore.success('Success', 'User created successfully');
  fetchUsers();
};

const handleUserUpdated = (user) => {
  showEditUserModal.value = false;
  notificationStore.success('Success', 'User updated successfully');
  fetchUsers();
};

// Handle user update from details modal (IP whitelist changes)
const handleUserUpdatedFromDetails = (updatedUser) => {
  // Update the selectedUser with new data
  selectedUser.value = updatedUser;
  
  // Update the user in the users list
  const userIndex = users.value.findIndex(u => u.id === updatedUser.id);
  if (userIndex !== -1) {
    users.value[userIndex] = { ...users.value[userIndex], ...updatedUser };
  }
};

const handleEditFromDetails = (user) => {
  // Close details modal and open edit modal
  showUserDetailsModal.value = false;
  selectedUser.value = user;
  showEditUserModal.value = true;
};

onMounted(() => {
  fetchUsers();
});
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
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.25);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover {
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.3);
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

.spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #e2e8f0;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Table styles */
table {
  border-collapse: separate;
  border-spacing: 0;
}

th:first-child {
  border-top-left-radius: 0.5rem;
}

th:last-child {
  border-top-right-radius: 0.5rem;
}

tr:last-child td:first-child {
  border-bottom-left-radius: 0.5rem;
}

tr:last-child td:last-child {
  border-bottom-right-radius: 0.5rem;
}
</style>
