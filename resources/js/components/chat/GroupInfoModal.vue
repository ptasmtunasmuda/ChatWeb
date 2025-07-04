<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4">
    <div class="card max-w-sm sm:max-w-lg lg:max-w-2xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
            <!-- Group Avatar Section -->
            <div class="relative group flex-shrink-0">
              <div
                v-if="groupInfo?.avatar"
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden"
              >
                <img :src="groupInfo.avatar" :alt="groupInfo.name" class="w-full h-full object-cover" />
              </div>
              <div
                v-else
                class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-base sm:text-lg"
              >
                <svg v-if="groupInfo?.type === 'group'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>

              <!-- Avatar Upload Overlay (Admin Only) -->
              <div
                v-if="canManage"
                class="absolute inset-0 bg-black bg-opacity-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center cursor-pointer"
                :class="{ 'opacity-100': uploadingAvatar }"
                @click="triggerAvatarUpload"
              >
                <div v-if="uploadingAvatar" class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>

              <!-- Hidden File Input -->
              <input
                ref="avatarInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleAvatarUpload"
              />
            </div>

            <div class="flex-1 min-w-0">
              <!-- Group Name Section -->
              <div v-if="!editingName" class="flex items-center space-x-1 sm:space-x-2">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 truncate">{{ groupInfo?.name || 'Group Info' }}</h3>
                <button
                  v-if="canManage"
                  @click="startEditName"
                  class="p-1 text-gray-400 hover:text-blue-600 transition-colors flex-shrink-0"
                >
                  <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
              </div>

              <!-- Edit Name Input -->
              <div v-else class="flex items-center space-x-1 sm:space-x-2">
                <input
                  v-model="editNameText"
                  @keyup.enter="saveName"
                  @keyup.escape="cancelEditName"
                  class="text-lg sm:text-xl font-bold text-gray-900 bg-transparent border-b-2 border-blue-500 focus:outline-none flex-1 min-w-0"
                  placeholder="Group name..."
                  maxlength="50"
                />
                <button
                  @click="saveName"
                  :disabled="savingName || !editNameText.trim()"
                  class="p-1 text-green-600 hover:text-green-700 disabled:opacity-50 flex-shrink-0"
                >
                  <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </button>
                <button
                  @click="cancelEditName"
                  class="p-1 text-red-600 hover:text-red-700 flex-shrink-0"
                >
                  <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>

              <p class="text-xs sm:text-sm text-gray-500">{{ groupInfo?.members_count || 0 }} members</p>
            </div>
          </div>
          <button
            @click="$emit('close')"
            class="p-1.5 sm:p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200 flex-shrink-0"
          >
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-6 sm:p-8 text-center">
        <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-500 mt-2 text-sm sm:text-base">Loading group info...</p>
      </div>

      <!-- Group Info Content -->
      <div v-else-if="groupInfo" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
        <!-- Group Description -->
        <div v-if="groupInfo.description || canManage">
          <div class="flex items-center justify-between mb-2 sm:mb-3">
            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Description</h4>
            <button
              v-if="canManage && !editingDescription"
              @click="startEditDescription"
              class="text-blue-600 hover:text-blue-700 text-xs sm:text-sm font-medium"
            >
              Edit
            </button>
          </div>

          <!-- Description Display -->
          <div v-if="!editingDescription">
            <p v-if="groupInfo.description" class="text-gray-700">{{ groupInfo.description }}</p>
            <p v-else class="text-gray-500 italic">No description set</p>
          </div>

          <!-- Description Edit -->
          <div v-else class="space-y-3">
            <textarea
              v-model="editDescriptionText"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              placeholder="Enter group description..."
            ></textarea>
            <div class="flex justify-end space-x-2">
              <button
                @click="cancelEditDescription"
                class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
              >
                Cancel
              </button>
              <button
                @click="saveDescription"
                :disabled="savingDescription"
                class="btn-primary disabled:opacity-50"
              >
                <span v-if="savingDescription">Saving...</span>
                <span v-else>Save</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Add Member Section (Admin Only) -->
        <div v-if="canManage" class="border-t pt-6">
          <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-900">Add Member</h4>
          </div>

          <!-- Search Users -->
          <div class="relative mb-3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              v-model="userSearchQuery"
              @input="searchUsers"
              type="text"
              placeholder="Search users to add..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Search Results -->
          <div v-if="searchResults.length > 0" class="mb-4 max-h-32 overflow-y-auto border border-gray-200 rounded-lg">
            <button
              v-for="user in searchResults"
              :key="user.id"
              @click="addMemberToGroup(user)"
              type="button"
              :disabled="addingMember"
              class="w-full flex items-center space-x-3 p-3 hover:bg-gray-50 transition-colors duration-200 disabled:opacity-50"
            >
              <div
                v-if="user.avatar"
                class="w-8 h-8 rounded-full overflow-hidden"
              >
                <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
              </div>
              <div
                v-else
                class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold"
              >
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 text-left">
                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
              <svg v-if="addingMember" class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Members List -->
        <div class="border-t pt-6">
          <h4 class="font-semibold text-gray-900 mb-4">Members ({{ groupInfo.members?.length || 0 }})</h4>

          <div class="space-y-3">
            <div
              v-for="member in groupInfo.members"
              :key="member.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <!-- Avatar -->
                <div class="relative">
                  <div
                    v-if="member.avatar"
                    class="w-10 h-10 rounded-full overflow-hidden"
                  >
                    <img :src="member.avatar" :alt="member.name" class="w-full h-full object-cover" />
                  </div>
                  <div
                    v-else
                    class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold"
                  >
                    {{ member.name.charAt(0).toUpperCase() }}
                  </div>

                  <!-- Online Status -->
                  <div
                    v-if="member.is_online"
                    class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"
                  ></div>
                </div>

                <!-- Member Info -->
                <div class="flex-1">
                  <div class="flex items-center space-x-2">
                    <p class="font-medium text-gray-900">{{ member.name }}</p>

                    <!-- Role Badge -->
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-yellow-100 text-yellow-800': member.role === 'admin',
                        'bg-purple-100 text-purple-800': member.role === 'moderator',
                        'bg-gray-100 text-gray-800': member.role === 'member'
                      }"
                    >
                      {{ member.role === 'admin' ? '👑 Admin' : member.role === 'moderator' ? '🛡️ Moderator' : 'Member' }}
                    </span>

                    <!-- Creator Badge -->
                    <span
                      v-if="groupInfo.created_by === member.id"
                      class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                    >
                      ✨ Creator
                    </span>
                  </div>
                  <p class="text-sm text-gray-500">{{ member.email }}</p>
                  <p v-if="!member.is_online && member.last_seen" class="text-xs text-gray-400">
                    Last seen {{ formatLastSeen(member.last_seen) }}
                  </p>
                </div>
              </div>

              <!-- Member Actions (Admin Only) -->
              <div v-if="canManage && member.id !== currentUser?.id && groupInfo.created_by !== member.id" class="flex items-center space-x-2">
                <!-- Role Change Dropdown -->
                <div class="relative" v-if="member.role !== 'admin' || groupInfo.created_by === currentUser?.id">
                  <button
                    @click="toggleMemberActions(member.id)"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-white"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                  </button>

                  <!-- Actions Dropdown -->
                  <div
                    v-if="memberActionsOpen === member.id"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                  >
                    <!-- Role Actions -->
                    <div class="py-2">
                      <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Change Role</p>
                      <button
                        v-if="member.role !== 'member'"
                        @click="changeMemberRole(member, 'member')"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                      >
                        👤 Make Member
                      </button>
                      <button
                        v-if="member.role !== 'admin'"
                        @click="changeMemberRole(member, 'admin')"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                      >
                        👑 Make Admin
                      </button>
                    </div>

                    <!-- Danger Zone -->
                    <div class="border-t border-gray-100 py-2">
                      <button
                        @click="removeMemberFromGroup(member)"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                      >
                        🗑️ Remove from Group
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Danger Zone (Creator Only) -->
        <div v-if="groupInfo.created_by === currentUser?.id" class="border-t border-red-200 pt-6">
          <h4 class="font-semibold text-red-900 mb-4">⚠️ Danger Zone</h4>
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h5 class="font-medium text-red-900">Delete Group</h5>
                <p class="text-sm text-red-700">This action cannot be undone. All messages will be lost.</p>
              </div>
              <button
                @click="confirmDeleteGroup"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
              >
                Delete Group
              </button>
            </div>
          </div>
        </div>

        <!-- Leave Group (Non-Creator) -->
        <div v-else-if="groupInfo.created_by !== currentUser?.id" class="border-t pt-6">
          <button
            @click="confirmLeaveGroup"
            class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors"
          >
            🚪 Leave Group
          </button>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-8 text-center">
        <div class="text-red-600 mb-4">
          <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-gray-700 font-medium">{{ error }}</p>
        <button
          @click="loadGroupInfo"
          class="mt-4 btn-primary"
        >
          Try Again
        </button>
      </div>
    </div>
  </div>

  <!-- Confirmation Modals -->
  <ConfirmModal
    v-if="showDeleteConfirm"
    title="Delete Group"
    :message="`Are you sure you want to delete '${groupInfo?.name}'? This action cannot be undone and all messages will be lost.`"
    confirm-text="Delete Group"
    confirm-class="bg-red-600 hover:bg-red-700"
    @confirm="deleteGroup"
    @cancel="showDeleteConfirm = false"
  />

  <ConfirmModal
    v-if="showLeaveConfirm"
    title="Leave Group"
    :message="`Are you sure you want to leave '${groupInfo?.name}'?`"
    confirm-text="Leave Group"
    confirm-class="bg-yellow-600 hover:bg-yellow-700"
    @confirm="leaveGroup"
    @cancel="showLeaveConfirm = false"
  />
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useTimestamp } from '../../composables/useTimestamp';
import ConfirmModal from '../common/ConfirmModal.vue';
import axios from 'axios';

const props = defineProps({
  groupId: {
    type: [String, Number],
    required: true
  },
  currentUser: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'group-updated', 'group-deleted', 'member-added', 'member-removed', 'left-group']);

// Composables
const { formatLastSeen } = useTimestamp();

// State
const loading = ref(true);
const error = ref(null);
const groupInfo = ref(null);
const userSearchQuery = ref('');
const searchResults = ref([]);
const searchTimeout = ref(null);
const addingMember = ref(false);
const memberActionsOpen = ref(null);
const showDeleteConfirm = ref(false);
const showLeaveConfirm = ref(false);

// Description editing
const editingDescription = ref(false);
const editDescriptionText = ref('');
const savingDescription = ref(false);

// Name editing
const editingName = ref(false);
const editNameText = ref('');
const savingName = ref(false);

// Avatar upload
const avatarInput = ref(null);
const uploadingAvatar = ref(false);

// Computed
const canManage = computed(() => {
  return groupInfo.value?.can_manage || false;
});

// Methods
const loadGroupInfo = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.get(`/api/groups/${props.groupId}/info`);
    if (response.data.success) {
      groupInfo.value = response.data.group;
    } else {
      error.value = response.data.message || 'Failed to load group info';
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load group info';
    console.error('Load group info error:', err);
  } finally {
    loading.value = false;
  }
};

const searchUsers = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }

  searchTimeout.value = setTimeout(async () => {
    if (userSearchQuery.value.trim().length < 2) {
      searchResults.value = [];
      return;
    }

    try {
      const response = await axios.get('/api/user/search', {
        params: { query: userSearchQuery.value.trim() }
      });

      // Filter out users who are already members
      const existingMemberIds = groupInfo.value?.members?.map(m => m.id) || [];
      searchResults.value = response.data.filter(user =>
        !existingMemberIds.includes(user.id)
      );
    } catch (error) {
      console.error('Error searching users:', error);
    }
  }, 300);
};

const addMemberToGroup = async (user) => {
  addingMember.value = true;

  try {
    const response = await axios.post(`/api/groups/${props.groupId}/members`, {
      user_id: user.id,
      role: 'member'
    });

    if (response.data.success) {
      // Add member to local state
      if (groupInfo.value.members) {
        groupInfo.value.members.push(response.data.member);
        groupInfo.value.members_count++;
      }

      // Clear search
      userSearchQuery.value = '';
      searchResults.value = [];

      emit('member-added', { group: groupInfo.value, member: response.data.member });
    }
  } catch (error) {
    console.error('Add member error:', error);
    alert(error.response?.data?.message || 'Failed to add member');
  } finally {
    addingMember.value = false;
  }
};

const removeMemberFromGroup = async (member) => {
  if (!confirm(`Remove ${member.name} from the group?`)) return;

  try {
    const response = await axios.delete(`/api/groups/${props.groupId}/members/${member.id}`);

    if (response.data.success) {
      // Remove member from local state
      if (groupInfo.value.members) {
        groupInfo.value.members = groupInfo.value.members.filter(m => m.id !== member.id);
        groupInfo.value.members_count--;
      }

      memberActionsOpen.value = null;
      emit('member-removed', { group: groupInfo.value, member });
    }
  } catch (error) {
    console.error('Remove member error:', error);
    alert(error.response?.data?.message || 'Failed to remove member');
  }
};

const changeMemberRole = async (member, newRole) => {
  try {
    const response = await axios.put(`/api/groups/${props.groupId}/members/${member.id}/role`, {
      role: newRole
    });

    if (response.data.success) {
      // Update member role in local state
      const memberIndex = groupInfo.value.members.findIndex(m => m.id === member.id);
      if (memberIndex !== -1) {
        groupInfo.value.members[memberIndex].role = newRole;
      }

      memberActionsOpen.value = null;
    }
  } catch (error) {
    console.error('Change role error:', error);
    alert(error.response?.data?.message || 'Failed to change member role');
  }
};

const toggleMemberActions = (memberId) => {
  memberActionsOpen.value = memberActionsOpen.value === memberId ? null : memberId;
};

// Description editing
const startEditDescription = () => {
  editDescriptionText.value = groupInfo.value.description || '';
  editingDescription.value = true;
};

const cancelEditDescription = () => {
  editDescriptionText.value = '';
  editingDescription.value = false;
};

const saveDescription = async () => {
  savingDescription.value = true;

  try {
    const response = await axios.put(`/api/groups/${props.groupId}/info`, {
      description: editDescriptionText.value.trim() || null
    });

    if (response.data.success) {
      groupInfo.value.description = editDescriptionText.value.trim() || null;
      editingDescription.value = false;
      emit('group-updated', groupInfo.value);
    }
  } catch (error) {
    console.error('Update description error:', error);
    alert(error.response?.data?.message || 'Failed to update description');
  } finally {
    savingDescription.value = false;
  }
};

// Name editing
const startEditName = () => {
  editNameText.value = groupInfo.value.name || '';
  editingName.value = true;
};

const cancelEditName = () => {
  editNameText.value = '';
  editingName.value = false;
};

const saveName = async () => {
  if (!editNameText.value.trim()) return;

  savingName.value = true;

  try {
    const response = await axios.put(`/api/groups/${props.groupId}/info`, {
      name: editNameText.value.trim()
    });

    if (response.data.success) {
      groupInfo.value.name = editNameText.value.trim();
      editingName.value = false;
      emit('group-updated', groupInfo.value);
    }
  } catch (error) {
    console.error('Update name error:', error);
    alert(error.response?.data?.message || 'Failed to update group name');
  } finally {
    savingName.value = false;
  }
};

// Avatar upload
const triggerAvatarUpload = () => {
  avatarInput.value?.click();
};

const handleAvatarUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Please select an image file');
    return;
  }

  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('Image size must be less than 5MB');
    return;
  }

  uploadingAvatar.value = true;

  try {
    const formData = new FormData();
    formData.append('avatar', file);

    // Debug logging
    console.log('Uploading file:', {
      name: file.name,
      type: file.type,
      size: file.size
    });

    const response = await axios.post(`/api/groups/${props.groupId}/avatar`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      timeout: 30000, // 30 seconds timeout
    });

    if (response.data.success) {
      groupInfo.value.avatar = response.data.avatar_url;
      emit('group-updated', groupInfo.value);
      console.log('Avatar uploaded successfully:', response.data.avatar_url);
    } else {
      throw new Error(response.data.message || 'Upload failed');
    }
  } catch (error) {
    console.error('Upload avatar error:', error);
    let errorMessage = 'Failed to upload avatar';

    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    alert(errorMessage);
  } finally {
    uploadingAvatar.value = false;
    event.target.value = ''; // Reset file input
  }
};

// Group actions
const confirmDeleteGroup = () => {
  showDeleteConfirm.value = true;
};

const deleteGroup = async () => {
  try {
    const response = await axios.delete(`/api/groups/${props.groupId}`);

    if (response.data.success) {
      emit('group-deleted', groupInfo.value);
      emit('close');
    }
  } catch (error) {
    console.error('Delete group error:', error);
    alert(error.response?.data?.message || 'Failed to delete group');
  } finally {
    showDeleteConfirm.value = false;
  }
};

const confirmLeaveGroup = () => {
  showLeaveConfirm.value = true;
};

const leaveGroup = async () => {
  try {
    const response = await axios.post(`/api/groups/${props.groupId}/leave`);

    if (response.data.success) {
      emit('left-group', groupInfo.value);
      emit('close');
    }
  } catch (error) {
    console.error('Leave group error:', error);
    alert(error.response?.data?.message || 'Failed to leave group');
  } finally {
    showLeaveConfirm.value = false;
  }
};

// Click outside to close member actions
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    memberActionsOpen.value = null;
  }
};

onMounted(() => {
  loadGroupInfo();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
