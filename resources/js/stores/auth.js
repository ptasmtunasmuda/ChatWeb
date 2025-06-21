import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('token'));
    const loading = ref(false);

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
            // Broadcast offline status before logout
            if (window.Echo && user.value) {
                await axios.post('/api/user/update-last-seen');
            }
            
            await axios.post('/api/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
        }
    };

    const fetchUser = async () => {
        if (!token.value) return;

        try {
            const response = await axios.get('/api/auth/user');
            user.value = response.data;
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
