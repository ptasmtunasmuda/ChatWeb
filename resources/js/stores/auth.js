import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useNotificationStore } from './notifications';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('token'));
    const loading = ref(false);

    // Get notification store
    const notificationStore = useNotificationStore();

    // Computed properties
    const isAuthenticated = computed(() => !!token.value);
    const isAdmin = computed(() => user.value?.role === 'admin');

    // Set axios default authorization header
    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    }

    // Actions
    const login = async (credentials) => {
        loading.value = true;
        try {
            const response = await axios.post('/api/auth/login', credentials);
            const { user: userData, token: userToken } = response.data;

            user.value = userData;
            token.value = userToken;

            localStorage.setItem('token', userToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${userToken}`;

            // Initialize Echo with auth token for private channels
            if (window.initializeEcho) {
                console.log('🔐 Auth Store: Initializing Echo with auth token');
                await window.initializeEcho(userToken);
            }

            // Show success notification
            notificationStore.success('Login Successful', `Welcome back, ${userData.name}!`);

            return { success: true };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Login failed'
            };
        } finally {
            loading.value = false;
        }
    };

    const register = async (userData) => {
        loading.value = true;
        try {
            const response = await axios.post('/api/auth/register', userData);
            const { user: newUser, token: userToken } = response.data;

            user.value = newUser;
            token.value = userToken;

            localStorage.setItem('token', userToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${userToken}`;

            // Show success notification
            notificationStore.success('Registration Successful', `Welcome to ChatWeb, ${newUser.name}!`);

            return { success: true };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Registration failed'
            };
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            console.log('🔐 Auth Store: Starting logout process...');

            // Broadcast offline status before logout
            if (window.Echo && user.value) {
                try {
                    await axios.post('/api/user/update-last-seen');
                    console.log('✅ Last seen updated');
                } catch (lastSeenError) {
                    console.warn('⚠️ Error updating last seen:', lastSeenError);
                }
            }

            await axios.post('/api/auth/logout');
            console.log('✅ Logout API call successful');

            // Show success notification
            notificationStore.success('Logout Successful', 'You have been logged out successfully');
        } catch (error) {
            console.error('❌ Logout error:', error);
            // Show error notification
            notificationStore.error('Logout Error', 'There was an error during logout, but you have been logged out locally');
        } finally {
            // Clear user data first
            user.value = null;
            token.value = null;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
            console.log('✅ Auth data cleared');

            // Disconnect Echo with error handling
            if (window.Echo) {
                try {
                    console.log('🔌 Disconnecting Echo...');
                    window.Echo.disconnect();
                    window.Echo = null;
                    window.echoInitialized = false;
                    console.log('✅ Echo disconnected successfully');
                } catch (echoError) {
                    console.warn('⚠️ Error disconnecting Echo:', echoError);
                }
            }

            console.log('✅ Logout process complete');
        }
    };

    const fetchUser = async () => {
        if (!token.value) return;

        try {
            const response = await axios.get('/api/auth/user');
            user.value = response.data;

            // Initialize Echo with auth token if not already initialized
            if (window.initializeEcho && token.value && !window.echoInitialized) {
                console.log('🔐 Auth Store: Initializing Echo from fetchUser');
                await window.initializeEcho(token.value);
            }
        } catch (error) {
            console.error('Fetch user error:', error);
            // If token is invalid, logout
            if (error.response?.status === 401) {
                await logout();
            }
        }
    };

    const updateProfile = async (profileData) => {
        loading.value = true;
        try {
            const response = await axios.put('/api/auth/profile', profileData);
            user.value = response.data;
            return { success: true };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Profile update failed'
            };
        } finally {
            loading.value = false;
        }
    };

    const changePassword = async (passwordData) => {
        loading.value = true;
        try {
            await axios.put('/api/auth/password', passwordData);
            return { success: true };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Password change failed'
            };
        } finally {
            loading.value = false;
        }
    };

    // Initialize user if token exists
    if (token.value && !user.value) {
        fetchUser();
    }

    return {
        user,
        token,
        loading,
        isAuthenticated,
        isAdmin,
        login,
        register,
        logout,
        fetchUser,
        updateProfile,
        changePassword
    };
});
