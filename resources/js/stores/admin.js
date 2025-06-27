import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAdminStore = defineStore('admin', () => {
    const dashboardStats = ref({});
    const recentActivity = ref({});
    const userGrowth = ref([]);
    const messageStats = ref([]);
    const systemHealth = ref({});

    const users = ref([]);
    const currentUser = ref(null);
    const deletedUsers = ref([]);

    const chatRooms = ref([]);
    const currentChatRoom = ref(null);
    const deletedChatRooms = ref([]);
    const chatMessages = ref([]);

    const loading = ref(false);
    const usersLoading = ref(false);
    const chatRoomsLoading = ref(false);

    // Computed properties
    const totalUsers = computed(() => dashboardStats.value.total_users || 0);
    const activeUsers = computed(() => dashboardStats.value.active_users || 0);
    const totalChatRooms = computed(() => dashboardStats.value.total_chat_rooms || 0);
    const totalMessages = computed(() => dashboardStats.value.total_messages || 0);

    // Dashboard Actions
    const fetchDashboardData = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/api/admin/dashboard');
            dashboardStats.value = response.data.stats;
            recentActivity.value = response.data.recent_activity;
            userGrowth.value = response.data.user_growth;
            messageStats.value = response.data.message_stats;
            return { success: true };
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch dashboard data' };
        } finally {
            loading.value = false;
        }
    };

    const fetchSystemHealth = async () => {
        try {
            const response = await axios.get('/api/admin/system-health');
            systemHealth.value = response.data;
            return { success: true };
        } catch (error) {
            console.error('Error fetching system health:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch system health' };
        }
    };

    const exportData = async (type, format, dateFrom = null, dateTo = null) => {
        try {
            const params = { type, format };
            if (dateFrom) params.date_from = dateFrom;
            if (dateTo) params.date_to = dateTo;

            const response = await axios.post('/api/admin/export-data', params, {
                responseType: format === 'csv' ? 'blob' : 'json'
            });

            if (format === 'csv') {
                // Handle CSV download
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `${type}_export_${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            }

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error exporting data:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to export data' };
        }
    };

    // User Management Actions
    const fetchUsers = async (params = {}) => {
        usersLoading.value = true;
        try {
            const response = await axios.get('/api/admin/users', { params });
            users.value = response.data.data;
            return { success: true, pagination: response.data };
        } catch (error) {
            console.error('Error fetching users:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch users' };
        } finally {
            usersLoading.value = false;
        }
    };

    const fetchUser = async (id) => {
        loading.value = true;
        try {
            const response = await axios.get(`/api/admin/users/${id}`);
            currentUser.value = response.data;
            return { success: true };
        } catch (error) {
            console.error('Error fetching user:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch user' };
        } finally {
            loading.value = false;
        }
    };

    const createUser = async (userData) => {
        try {
            const response = await axios.post('/api/admin/users', userData);
            users.value.unshift(response.data.user);
            return { success: true, user: response.data.user };
        } catch (error) {
            console.error('Error creating user:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to create user', errors: error.response?.data?.errors };
        }
    };

    const updateUser = async (id, userData) => {
        try {
            const response = await axios.put(`/api/admin/users/${id}`, userData);
            const index = users.value.findIndex(user => user.id === id);
            if (index !== -1) {
                users.value[index] = response.data.user;
            }
            if (currentUser.value?.user?.id === id) {
                currentUser.value.user = response.data.user;
            }
            return { success: true, user: response.data.user };
        } catch (error) {
            console.error('Error updating user:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to update user', errors: error.response?.data?.errors };
        }
    };

    const deleteUser = async (id) => {
        try {
            await axios.delete(`/api/admin/users/${id}`);
            users.value = users.value.filter(user => user.id !== id);
            return { success: true };
        } catch (error) {
            console.error('Error deleting user:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to delete user' };
        }
    };

    const restoreUser = async (id) => {
        try {
            const response = await axios.post(`/api/admin/users/${id}/restore`);
            deletedUsers.value = deletedUsers.value.filter(user => user.id !== id);
            return { success: true, user: response.data.user };
        } catch (error) {
            console.error('Error restoring user:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to restore user' };
        }
    };

    const bulkUserAction = async (action, userIds, role = null) => {
        try {
            const data = { action, user_ids: userIds };
            if (role) data.role = role;

            const response = await axios.post('/api/admin/users/bulk-action', data);

            // Refresh users list
            await fetchUsers();

            return { success: true, affectedCount: response.data.affected_count };
        } catch (error) {
            console.error('Error performing bulk action:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to perform bulk action' };
        }
    };

    const fetchDeletedUsers = async (params = {}) => {
        try {
            const response = await axios.get('/api/admin/users-deleted', { params });
            deletedUsers.value = response.data.data;
            return { success: true, pagination: response.data };
        } catch (error) {
            console.error('Error fetching deleted users:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch deleted users' };
        }
    };

    // IP Whitelist Management
    const getCurrentUserIp = async () => {
        try {
            const response = await axios.get('/api/admin/current-ip');
            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error getting current IP:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to get current IP' };
        }
    };

    const updateUserIpWhitelist = async (userId, allowedIps) => {
        try {
            const response = await axios.put(`/api/admin/users/${userId}/ip-whitelist`, {
                allowed_ips: allowedIps
            });
            
            // Update user in the users array
            const index = users.value.findIndex(user => user.id === userId);
            if (index !== -1) {
                users.value[index].allowed_ips = response.data.allowed_ips;
            }
            
            // Update current user if it's the same user
            if (currentUser.value?.user?.id === userId) {
                currentUser.value.user.allowed_ips = response.data.allowed_ips;
            }
            
            return { success: true, allowedIps: response.data.allowed_ips };
        } catch (error) {
            console.error('Error updating IP whitelist:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to update IP whitelist', errors: error.response?.data?.errors };
        }
    };

    const addIpToWhitelist = async (userId, ip) => {
        try {
            const response = await axios.post(`/api/admin/users/${userId}/ip-whitelist/add`, { ip });
            
            // Update user in the users array
            const index = users.value.findIndex(user => user.id === userId);
            if (index !== -1) {
                users.value[index].allowed_ips = response.data.allowed_ips;
            }
            
            // Update current user if it's the same user
            if (currentUser.value?.user?.id === userId) {
                currentUser.value.user.allowed_ips = response.data.allowed_ips;
            }
            
            return { success: true, allowedIps: response.data.allowed_ips };
        } catch (error) {
            console.error('Error adding IP to whitelist:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to add IP to whitelist', errors: error.response?.data?.errors };
        }
    };

    const removeIpFromWhitelist = async (userId, ip) => {
        try {
            const response = await axios.delete(`/api/admin/users/${userId}/ip-whitelist/remove`, { 
                data: { ip } 
            });
            
            // Update user in the users array
            const index = users.value.findIndex(user => user.id === userId);
            if (index !== -1) {
                users.value[index].allowed_ips = response.data.allowed_ips;
            }
            
            // Update current user if it's the same user
            if (currentUser.value?.user?.id === userId) {
                currentUser.value.user.allowed_ips = response.data.allowed_ips;
            }
            
            return { success: true, allowedIps: response.data.allowed_ips };
        } catch (error) {
            console.error('Error removing IP from whitelist:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to remove IP from whitelist', errors: error.response?.data?.errors };
        }
    };

    // Chat Room Management Actions
    const fetchChatRooms = async (params = {}) => {
        chatRoomsLoading.value = true;
        try {
            const response = await axios.get('/api/admin/chat-rooms', { params });
            chatRooms.value = response.data.data;
            return { success: true, pagination: response.data };
        } catch (error) {
            console.error('Error fetching chat rooms:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch chat rooms' };
        } finally {
            chatRoomsLoading.value = false;
        }
    };

    const fetchChatRoom = async (id) => {
        loading.value = true;
        try {
            const response = await axios.get(`/api/admin/chat-rooms/${id}`);
            currentChatRoom.value = response.data;
            return { success: true };
        } catch (error) {
            console.error('Error fetching chat room:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch chat room' };
        } finally {
            loading.value = false;
        }
    };

    const updateChatRoom = async (id, roomData) => {
        try {
            const response = await axios.put(`/api/admin/chat-rooms/${id}`, roomData);
            const index = chatRooms.value.findIndex(room => room.id === id);
            if (index !== -1) {
                chatRooms.value[index] = response.data.chat_room;
            }
            if (currentChatRoom.value?.chat_room?.id === id) {
                currentChatRoom.value.chat_room = response.data.chat_room;
            }
            return { success: true, chatRoom: response.data.chat_room };
        } catch (error) {
            console.error('Error updating chat room:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to update chat room', errors: error.response?.data?.errors };
        }
    };

    const deleteChatRoom = async (id) => {
        try {
            await axios.delete(`/api/admin/chat-rooms/${id}`);
            chatRooms.value = chatRooms.value.filter(room => room.id !== id);
            return { success: true };
        } catch (error) {
            console.error('Error deleting chat room:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to delete chat room' };
        }
    };

    const restoreChatRoom = async (id) => {
        try {
            const response = await axios.post(`/api/admin/chat-rooms/${id}/restore`);
            deletedChatRooms.value = deletedChatRooms.value.filter(room => room.id !== id);
            return { success: true, chatRoom: response.data.chat_room };
        } catch (error) {
            console.error('Error restoring chat room:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to restore chat room' };
        }
    };

    const fetchChatMessages = async (chatRoomId, params = {}) => {
        try {
            const response = await axios.get(`/api/admin/chat-rooms/${chatRoomId}/messages`, { params });
            chatMessages.value = response.data.data;
            return { success: true, pagination: response.data };
        } catch (error) {
            console.error('Error fetching chat messages:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch messages' };
        }
    };

    const deleteMessage = async (chatRoomId, messageId) => {
        try {
            await axios.delete(`/api/admin/chat-rooms/${chatRoomId}/messages/${messageId}`);
            chatMessages.value = chatMessages.value.filter(msg => msg.id !== messageId);
            return { success: true };
        } catch (error) {
            console.error('Error deleting message:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to delete message' };
        }
    };

    const restoreMessage = async (chatRoomId, messageId) => {
        try {
            await axios.post(`/api/admin/chat-rooms/${chatRoomId}/messages/${messageId}/restore`);
            return { success: true };
        } catch (error) {
            console.error('Error restoring message:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to restore message' };
        }
    };

    const bulkDeleteMessages = async (chatRoomId, messageIds, permanent = false) => {
        try {
            const response = await axios.post(`/api/admin/chat-rooms/${chatRoomId}/messages/bulk-delete`, {
                message_ids: messageIds,
                permanent
            });

            // Refresh messages
            await fetchChatMessages(chatRoomId);

            return { success: true, affectedCount: response.data.affected_count };
        } catch (error) {
            console.error('Error bulk deleting messages:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to bulk delete messages' };
        }
    };

    const getChatRoomAnalytics = async (chatRoomId) => {
        try {
            const response = await axios.get(`/api/admin/chat-rooms/${chatRoomId}/analytics`);
            return { success: true, analytics: response.data };
        } catch (error) {
            console.error('Error fetching chat room analytics:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch analytics' };
        }
    };

    // Clear functions
    const clearCurrentUser = () => {
        currentUser.value = null;
    };

    const clearCurrentChatRoom = () => {
        currentChatRoom.value = null;
        chatMessages.value = [];
    };

    const clearAll = () => {
        dashboardStats.value = {};
        recentActivity.value = {};
        userGrowth.value = [];
        messageStats.value = [];
        systemHealth.value = {};
        users.value = [];
        currentUser.value = null;
        deletedUsers.value = [];
        chatRooms.value = [];
        currentChatRoom.value = null;
        deletedChatRooms.value = [];
        chatMessages.value = [];
    };

    return {
        // State
        dashboardStats,
        recentActivity,
        userGrowth,
        messageStats,
        systemHealth,
        users,
        currentUser,
        deletedUsers,
        chatRooms,
        currentChatRoom,
        deletedChatRooms,
        chatMessages,
        loading,
        usersLoading,
        chatRoomsLoading,

        // Computed
        totalUsers,
        activeUsers,
        totalChatRooms,
        totalMessages,

        // Dashboard Actions
        fetchDashboardData,
        fetchSystemHealth,
        exportData,

        // User Management
        fetchUsers,
        fetchUser,
        createUser,
        updateUser,
        deleteUser,
        restoreUser,
        bulkUserAction,
        fetchDeletedUsers,

        // IP Whitelist Management
        getCurrentUserIp,
        updateUserIpWhitelist,
        addIpToWhitelist,
        removeIpFromWhitelist,

        // Chat Room Management
        fetchChatRooms,
        fetchChatRoom,
        updateChatRoom,
        deleteChatRoom: deleteChatRoom,
        deleteRoom: deleteChatRoom, // Alias for AdminChats.vue
        restoreChatRoom,
        fetchChatMessages,
        fetchMessages: fetchChatMessages, // Alias for AdminChats.vue
        deleteMessage,
        restoreMessage,
        bulkDeleteMessages,
        bulkRoomAction: async (action, roomIds) => {
            try {
                const response = await axios.post('/api/admin/chat-rooms/bulk-action', {
                    action,
                    room_ids: roomIds
                });

                // Refresh chat rooms list
                await fetchChatRooms();

                return { success: true, affectedCount: response.data.affected_count };
            } catch (error) {
                console.error('Error performing bulk room action:', error);
                return { success: false, message: error.response?.data?.message || 'Failed to perform bulk action' };
            }
        },
        getChatRoomAnalytics,

        // Clear functions
        clearCurrentUser,
        clearCurrentChatRoom,
        clearAll
    };
});
