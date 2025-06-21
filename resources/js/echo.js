import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Track if Echo is already initialized with auth
let echoInitialized = false;
window.echoInitialized = false;

// Function to initialize Echo with auth token
window.initializeEcho = async (token) => {
    console.log('🔧 Initializing Echo with token:', token ? token.substring(0, 10) + '...' : 'null');

    // Prevent multiple initializations
    if (echoInitialized && window.Echo) {
        console.log('✅ Echo already initialized with auth, skipping re-initialization');
        return window.Echo;
    }

    // Only disconnect if we're switching to authenticated mode
    if (window.Echo && !echoInitialized) {
        try {
            console.log('🔄 Disconnecting public Echo instance for auth upgrade...');
            window.Echo.disconnect();
            window.Echo = null;
            // Wait for cleanup
            await new Promise(resolve => setTimeout(resolve, 200));
        } catch (error) {
            console.warn('⚠️ Error disconnecting previous Echo instance:', error);
        }
    }

    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            },
            authEndpoint: '/api/broadcasting/auth',
            // Add connection options
            cluster: import.meta.env.VITE_REVERB_CLUSTER || 'mt1',
            encrypted: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        });

        // Add connection event listeners for debugging
        if (window.Echo.connector && window.Echo.connector.pusher) {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                console.log('🔗 WebSocket connected successfully');
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                console.log('🔌 WebSocket disconnected');
            });

            window.Echo.connector.pusher.connection.bind('error', (error) => {
                console.error('❌ WebSocket connection error:', error);
            });

            window.Echo.connector.pusher.connection.bind('failed', () => {
                console.error('❌ WebSocket connection failed');
            });
        }

        echoInitialized = true;
        window.echoInitialized = true;
        console.log('✅ Echo initialized with auth token');
        return window.Echo;
    } catch (error) {
        console.error('❌ Failed to initialize Echo:', error);
        echoInitialized = false;
        window.echoInitialized = false;
        return null;
    }
};

// Initialize Echo without auth for public channels only
try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        // Add connection options
        cluster: import.meta.env.VITE_REVERB_CLUSTER || 'mt1',
        encrypted: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    });
    console.log('✅ Initial Echo instance created for public channels');
} catch (error) {
    console.error('❌ Failed to create initial Echo instance:', error);
    window.Echo = null;
}
