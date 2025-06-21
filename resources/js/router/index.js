import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Import components
import Login from '../components/auth/Login.vue';
import Register from '../components/auth/Register.vue';
import Dashboard from '../components/Dashboard.vue';
import Chat from '../components/chat/Chat.vue';
import AdminDashboard from '../components/admin/AdminDashboard.vue';
import AdminUsers from '../components/admin/AdminUsers.vue';
import AdminChats from '../components/admin/AdminChats.vue';

const routes = [
    {
        path: '/',
        redirect: '/chat'  // Default ke chat, nanti diatur di navigation guard
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { requiresGuest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { requiresGuest: true }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/chat/:id?',
        name: 'Chat',
        component: Chat,
        meta: { requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('../components/Profile.vue'),
        meta: { requiresAuth: true }
    },
    // Admin routes - hanya untuk admin
    {
        path: '/admin',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/users',
        name: 'AdminUsers',
        component: AdminUsers,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/chats',
        name: 'AdminChats',
        component: AdminChats,
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
        // Admin tetap ke dashboard, user biasa ke chat
        if (authStore.isAdmin) {
            next('/dashboard');
        } else {
            next('/chat');
        }
    } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
        next('/chat');
    } else if (to.path === '/' && authStore.isAuthenticated) {
        // Handle redirect dari root path berdasarkan role
        if (authStore.isAdmin) {
            next('/dashboard');
        } else {
            next('/chat');
        }
    } else {
        next();
    }
});

export default router;
