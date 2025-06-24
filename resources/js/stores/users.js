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
            console.log(`👤 Updating user ${userId} status to ${isOnline ? 'online' : 'offline'}`);
            users.value[userIndex].is_online = isOnline;
            users.value[userIndex].last_seen = isOnline ? null : new Date().toISOString();
        } else {
            console.warn(`⚠️ User ${userId} not found in users list for status update`);
        }
    };

    const updateUserAvatar = (userData) => {
        console.log('🖼️ Users Store: Updating user avatar:', userData);
        
        const userIndex = users.value.findIndex(user => user.id === userData.id);
        if (userIndex !== -1) {
            // Update the user data with new avatar
            users.value[userIndex] = {
                ...users.value[userIndex],
                ...userData
            };
            console.log(`✅ Avatar updated for user ${userData.id}: ${userData.avatar || 'removed'}`);
        } else {
            console.warn(`⚠️ User ${userData.id} not found in users list for avatar update`);
        }
    };

    const startHeartbeat = () => {
        // Send heartbeat every 15 seconds for better responsiveness
        console.log('💓 Starting heartbeat...');
        heartbeatInterval.value = setInterval(async () => {
            try {
                const response = await axios.post('/api/user/heartbeat');
                console.log('💓 Heartbeat sent successfully:', response.data);
            } catch (error) {
                console.error('💔 Heartbeat failed:', error);
            }
        }, 15000); // 15 seconds
    };

    const stopHeartbeat = () => {
        if (heartbeatInterval.value) {
            console.log('💔 Stopping heartbeat...');
            clearInterval(heartbeatInterval.value);
            heartbeatInterval.value = null;
        }
    };

    const cleanup = () => {
        stopHeartbeat();
        
        // Leave Echo channels
        if (window.Echo) {
            try {
                window.Echo.leave('users-status');
                window.Echo.leave('user-updates');
                console.log('✅ Users store channels cleaned up');
            } catch (error) {
                console.warn('⚠️ Error cleaning up users store channels:', error);
            }
        }
    };

    const setupRealtimeListeners = () => {
        if (!window.Echo) return;

        // Listen for user status updates
        window.Echo.channel('users-status')
            .listen('user.status.updated', (e) => {
                console.log('👤 User status update received:', e);
                updateUserOnlineStatus(e.user_id, e.is_online);
            })
            .error((error) => {
                console.error('❌ Error on users-status channel:', error);
            });

        // Listen for user avatar updates
        window.Echo.channel('user-updates')
            .listen('.user.avatar.updated', (e) => {
                console.log('🖼️ User avatar update received:', e);
                updateUserAvatar(e.user);
            })
            .error((error) => {
                console.error('❌ Error on user-updates channel:', error);
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
        updateUserAvatar,
        startHeartbeat,
        stopHeartbeat,
        setupRealtimeListeners,
        cleanup
    };
});
