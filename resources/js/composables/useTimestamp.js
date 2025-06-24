import { ref, onMounted, onUnmounted } from 'vue';

export function useTimestamp() {
  const currentTime = ref(Date.now());
  const isUpdating = ref(false);
  let timestampInterval = null;

  const startTimestampUpdates = () => {
    // Update every 30 seconds for responsive timestamp updates
    timestampInterval = setInterval(() => {
      isUpdating.value = true;
      currentTime.value = Date.now();
      
      // Reset updating indicator after a short delay
      setTimeout(() => {
        isUpdating.value = false;
      }, 100);
    }, 30000);
  };

  const stopTimestampUpdates = () => {
    if (timestampInterval) {
      clearInterval(timestampInterval);
      timestampInterval = null;
    }
  };

  const formatTime = (timestamp) => {
    if (!timestamp) return '';

    // Handle different timestamp formats
    let date;
    if (typeof timestamp === 'string') {
      // Laravel sends ISO string like "2024-06-22T06:12:35.000000Z"
      date = new Date(timestamp);
    } else {
      date = new Date(timestamp);
    }

    // Validate date
    if (isNaN(date.getTime())) {
      console.error('Invalid timestamp:', timestamp);
      return 'Invalid date';
    }

    // Use currentTime.value to force reactivity and real-time updates
    const now = new Date(currentTime.value);
    const diffInMilliseconds = now.getTime() - date.getTime();
    const diffInMinutes = diffInMilliseconds / (1000 * 60);
    const diffInHours = diffInMilliseconds / (1000 * 60 * 60);

    if (diffInMinutes < 1) {
      return 'Just now';
    } else if (diffInMinutes < 60) {
      return `${Math.floor(diffInMinutes)}m ago`;
    } else if (diffInHours < 24) {
      return `${Math.floor(diffInHours)}h ago`;
    } else if (diffInHours < 168) { // 7 days
      return `${Math.floor(diffInHours / 24)}d ago`;
    } else {
      return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
    }
  };

  const formatLastSeen = (timestamp) => {
    if (!timestamp) return 'Long time ago';

    const date = new Date(timestamp);
    const now = new Date(currentTime.value);
    const diffInMinutes = (now - date) / (1000 * 60);

    if (diffInMinutes < 1) {
      return 'Just now';
    } else if (diffInMinutes < 60) {
      return `${Math.floor(diffInMinutes)} minutes ago`;
    } else if (diffInMinutes < 1440) {
      return `${Math.floor(diffInMinutes / 60)} hours ago`;
    } else {
      const days = Math.floor(diffInMinutes / 1440);
      return `${days} day${days > 1 ? 's' : ''} ago`;
    }
  };

  const formatDateDivider = (timestamp) => {
    if (!timestamp) return 'Today';

    const date = new Date(timestamp);
    const now = new Date(currentTime.value);
    const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

    if (diffInDays === 0) {
      return 'Today';
    } else if (diffInDays === 1) {
      return 'Yesterday';
    } else if (diffInDays < 7) {
      return date.toLocaleDateString('id-ID', { weekday: 'long' });
    } else {
      return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
    }
  };

  // Auto-start and stop timestamps when component mounts/unmounts
  onMounted(() => {
    startTimestampUpdates();
  });

  onUnmounted(() => {
    stopTimestampUpdates();
  });

  return {
    currentTime,
    isUpdating,
    formatTime,
    formatLastSeen,
    formatDateDivider,
    startTimestampUpdates,
    stopTimestampUpdates
  };
}
