import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useUsersStore = defineStore('users', () => {
    const users = ref([]);
    const loading = ref(false);
    const searchQuery = ref('');
    const heartbeatInterval = ref(null);

    // Computed properties
    const filteredUsers = computed(() => {
        if (!searchQuery.value || searchQuery.value.length < 2) {
            return users.value;
        }
        
        const query = searchQuery.value.toLowerCase();
        return users.value.filter(user =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query)
        );
    });

    const onlineUsers = computed(() => {
        return filteredUsers.value.filter(user => user.is_online);
    });

    const offlineUsers = computed(() => {
        return filteredUsers.value.filter(user => !user.is_online);
    });

    // Actions
    const fetchUsers = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/api/users');
            users.value = response.data.data || [];
            return { success: true };
        } catch (error) {
            console.error('Error fetching users:', error);
            return { 
                success: false, 
                message: error.response?.data?.message || 'Failed to fetch users' 
            };
        } finally {
            loading.value = false;
        }
    };

    const searchUsers = async (query) => {
        searchQuery.value = query;
        
        // If query is empty or too short, just filter existing users locally
        if (!query.trim() || query.length < 2) {
            return { success: true };
        }

        loading.value = true;
        try {
            const response = await axios.get('/api/user/search', {
                params: { query: query }
            });
            users.value = response.data || [];
            return { success: true };
        } catch (error) {
            console.error('Error searching users:', error);
            
            // If search fails, fallback to local filtering
            console.log('Falling back to local search...');
            return { success: true };
        } finally {
            loading.value = false;
        }
    };

    const createPrivateChat = async (userId) => {
        loading.value = true;
        try {
            const response = await axios.post('/api/chat-rooms', {
                type: 'private',
                participants: [userId]
            });
            return { 
                success: true, 
                data: response.data.data // Note: response structure is { message, data }
            };
        } catch (error) {
            console.error('Error creating private chat:', error);
            return { 
                success: false, 
                message: error.response?.data?.message || 'Failed to create chat' 
            };
        } finally {
            loading.value = false;
        }
    };

    const clearSearch = () => {
        searchQuery.value = '';
        // Don't refetch, just rely on filteredUsers computed property
    };

    const updateUserOnlineStatus = (userId, isOnline) => {
        const userIndex = users.value.findIndex(user => user.id === userId);
        if (userIndex !== -1) {
            users.value[userIndex].is_online = isOnline;
            users.value[userIndex].last_seen = isOnline ? null : new Date().toISOString();
        }
    };

    const startHeartbeat = () => {
        // Send heartbeat every 30 seconds
        heartbeatInterval.value = setInterval(async () => {
            try {
                await axios.post('/api/user/heartbeat');
            } catch (error) {
                console.error('Heartbeat failed:', error);
            }
        }, 30000); // 30 seconds
    };

    const stopHeartbeat = () => {
        if (heartbeatInterval.value) {
            clearInterval(heartbeatInterval.value);
            heartbeatInterval.value = null;
        }
    };

    const setupRealtimeListeners = () => {
        if (!window.Echo) return;

        // Listen for user status updates
        window.Echo.channel('users-status')
            .listen('.user.status.updated', (e) => {
                updateUserOnlineStatus(e.user_id, e.is_online);
            });
    };

    return {
        users,
        loading,
        searchQuery,
        filteredUsers,
        onlineUsers,
        offlineUsers,
        fetchUsers,
        searchUsers,
        createPrivateChat,
        clearSearch,
        updateUserOnlineStatus,
        startHeartbeat,
        stopHeartbeat,
        setupRealtimeListeners
    };
});
