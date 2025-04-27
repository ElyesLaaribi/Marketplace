import { ref, onMounted, onUnmounted, watch } from 'vue';
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import { initializeApp } from "firebase/app";
import api from '../axios';
import { useUserStore } from '../store/user';
import { useRouter } from 'vue-router';

const firebaseConfig = {
  apiKey: "AIzaSyDHHbtG6Ka7j_WzWECxT9VFq0eL_c65z5Q",
  authDomain: "rentease-7d991.firebaseapp.com",
  projectId: "rentease-7d991",
  storageBucket: "rentease-7d991.firebasestorage.app",
  messagingSenderId: "977313734840",
  appId: "1:977313734840:web:fdc9d74b7a672981eed44d"
};

// Initialize Firebase app once
const firebaseApp = initializeApp(firebaseConfig);
const messaging = getMessaging(firebaseApp);

// Initialize IndexedDB
let db;
let dbReady = false;
const request = indexedDB.open('notificationsDB', 2);

request.onerror = (event) => {
    console.error('Error opening IndexedDB:', event.target.error);
};

request.onsuccess = (event) => {
    db = event.target.result;
    dbReady = true;
    console.log('IndexedDB initialized successfully');
};

request.onupgradeneeded = (event) => {
    const db = event.target.result;
    if (!db.objectStoreNames.contains('notifications')) {
        db.createObjectStore('notifications', { keyPath: 'id' });
    }
};

// Function to get all notifications from IndexedDB for the current user
function getAllNotifications(userId) {
    return new Promise((resolve, reject) => {
        if (!db || !dbReady) {
            reject(new Error('IndexedDB not initialized'));
            return;
        }

        const transaction = db.transaction(['notifications'], 'readonly');
        const store = transaction.objectStore('notifications');
        const request = store.getAll();

        request.onsuccess = () => {
            // Filter notifications by user_id - only return notifications for current user
            const notifications = request.result || [];
            // If we have a userId, filter by it - otherwise return all (no filtering)
            const filteredNotifications = userId 
                ? notifications.filter(n => {
                    // If the notification has a user_id, ensure it matches current user
                    return !n.user_id || String(n.user_id) === String(userId);
                })
                : notifications;
            resolve(filteredNotifications);
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

// Function to add notification to IndexedDB
function addNotification(notification) {
    return new Promise((resolve, reject) => {
        if (!db || !dbReady) {
            reject(new Error('IndexedDB not initialized'));
            return;
        }

        const transaction = db.transaction(['notifications'], 'readwrite');
        const store = transaction.objectStore('notifications');
        const request = store.add(notification);

        request.onsuccess = () => {
            resolve();
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

// Function to mark notification as read in IndexedDB
function markNotificationAsRead(id) {
    return new Promise((resolve, reject) => {
        if (!db || !dbReady) {
            reject(new Error('IndexedDB not initialized'));
            return;
        }

        const transaction = db.transaction(['notifications'], 'readwrite');
        const store = transaction.objectStore('notifications');
        const getRequest = store.get(id);

        getRequest.onsuccess = () => {
            const notification = getRequest.result;
            if (notification) {
                notification.read = true;
                const updateRequest = store.put(notification);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = (event) => reject(event.target.error);
            } else {
                resolve();
            }
        };

        getRequest.onerror = (event) => reject(event.target.error);
    });
}

export function useFirebaseMessaging() {
  const userStore = useUserStore();
  const fcmToken = ref(null);
  const notificationPermission = ref('default');
  const notificationMessage = ref(null);
  const error = ref(null);
  const notifications = ref([]);
  const unreadCount = ref(0);
  let unsubscribe = null;
  const router = useRouter();
  const broadcastChannel = new BroadcastChannel('notifications-channel');

  // Load notifications from IndexedDB
  const loadNotifications = async () => {
    try {
      // Safely access user.id - handle the case where user might be null/undefined
      const currentUserId = userStore.user?.id;
      
      // Skip loading if no user is logged in
      if (!currentUserId) {
        console.log('No user logged in, skipping notification loading');
        return;
      }
      
      // Check if database is ready
      if (!dbReady) {
        console.log('Database not ready yet, waiting...');
        // Retry after a short delay
        setTimeout(() => loadNotifications(), 500);
        return;
      }
      
      const storedNotifications = await getAllNotifications(currentUserId);
      if (storedNotifications.length > 0) {
        notifications.value = storedNotifications;
        updateUnreadCount();
      }
    } catch (e) {
      console.error('Error loading notifications from IndexedDB:', e);
    }
  };

  // Save notifications to IndexedDB
  const saveNotificationsToIndexedDB = async (notification) => {
    try {
      await addNotification(notification);
    } catch (e) {
      console.error('Error saving notification to IndexedDB:', e);
    }
  };

  // Update unread count
  const updateUnreadCount = () => {
    unreadCount.value = notifications.value.filter(n => !n.read).length;
  };

  // Process notification data
  const processNotification = (payload) => {
    console.log('Processing notification payload (DETAILED):', JSON.stringify(payload));
    
    try {
      // Handle both direct Firebase messages and broadcast channel messages
      const notificationData = payload.notification || {};
      const data = payload.data || {};
      
      // Get the target user ID from the payload
      const targetUserId = data.user_id || payload.user_id;
      const currentUserId = userStore.user?.id;
      
      // Skip notifications not meant for this user
      if (targetUserId && currentUserId && String(targetUserId) !== String(currentUserId)) {
        console.log(`Notification for user ${targetUserId} skipped (current user: ${currentUserId})`);
        return null;
      }
      
      const newNotification = {
        id: data.rental_id || payload.id || `msg-${Date.now()}`,
        user_id: currentUserId ? String(currentUserId) : undefined, // Add user_id if user is logged in
        title: notificationData.title || payload.title || "New Rental Reminder",
        body: data.message || notificationData.body || payload.body || "",
        listingName: data.listing_name || payload.listingName || "",
        startDate: data.start_date
          ? new Date(data.start_date).toLocaleDateString()
          : payload.startDate || "Soon",
        type: payload.type || data.type || "rental_reminder",
        timestamp: payload.timestamp || new Date().toISOString(),
        read: false,
      };

      console.log('Created newNotification object:', newNotification);

      // Add to collection if not already present
      const exists = notifications.value.some(n => n.id === newNotification.id);
      if (!exists) {
        console.log('Adding new notification to collection');
        notifications.value = [newNotification, ...notifications.value];
        console.log('Updated collection:', notifications.value);
        updateUnreadCount();
        saveNotificationsToIndexedDB(newNotification);
      } else {
        console.log('Notification already exists in collection');
      }
      
      return newNotification;
    } catch (err) {
      console.error("Error processing notification:", err);
      return null;
    }
  };

  // Setup foreground message handler
  const setupForegroundHandler = () => {
    return onMessage(messaging, (payload) => {
      console.log('Foreground message received:', payload);
      notificationMessage.value = payload;
      processNotification(payload);
    });
  };

  // Setup broadcast channel for service worker communication
  const setupBroadcastChannel = () => {
    try {
      broadcastChannel.onmessage = (event) => {
        console.log("Received notification via broadcast channel:", event.data);
        processNotification(event.data);
      };
    } catch (e) {
      console.error("Error setting up broadcast channel:", e);
    }
  };

  const requestPermissionAndGetToken = async () => {
    error.value = null;
    
    try {
      console.log('Requesting notification permission...');
      
      if (!('Notification' in window)) {
        console.error('Browser doesn\'t support notifications');
        error.value = 'Browser doesn\'t support notifications';
        return;
      }
      
      const permission = await Notification.requestPermission();
      notificationPermission.value = permission;
      console.log('Notification permission result:', permission);
      
      if (permission !== 'granted') {
        console.warn('Notification permission denied by user');
        error.value = 'Notification permission denied';
        return;
      }
      
      console.log('Notification permission granted, registering service worker...');
      
      // Register service worker if needed
      if ('serviceWorker' in navigator) {
        try {
          const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
            scope: '/'
          });
          console.log('Service Worker registered with scope:', registration.scope);
        } catch (err) {
          console.error('Service Worker registration failed:', err);
        }
      }
      
      console.log('Getting FCM token...');
      
      const vapidKey = 'BKvTfTSeLOnA3bb4v1SbulJdqR2fXvhgi2WvozfAL5mt57RO5YcQAD1hMAl6GiqXJIJ6Dr7uabTTnS3uS0dN3eo';
      
      // Check if service worker is already registered
      if ('serviceWorker' in navigator) {
        const serviceWorkerRegistration = await navigator.serviceWorker.ready;
        console.log('Service Worker is ready for FCM');
        
        try {
          const currentToken = await getToken(messaging, { 
            vapidKey,
            serviceWorkerRegistration
          });
          
          if (currentToken) {
            console.log('FCM registration token obtained:', currentToken);
            fcmToken.value = currentToken;
            
            try {
              const response = await api.post('/api/users/update-device-token', { 
                device_token: currentToken 
              });
              console.log('Token sent to server successfully:', response.data);
            } catch (serverError) {
              console.error('Error sending token to server:', serverError);
              error.value = 'Error sending token to server: ' + serverError.message;
            }
          } else {
            console.warn('No registration token available');
            error.value = 'Failed to get notification token. Check your browser permissions.';
          }
        } catch (tokenError) {
          console.error('Error getting FCM token:', tokenError);
          error.value = 'Error getting FCM token: ' + tokenError.message;
        }
      } else {
        console.error('Service Worker not available');
        error.value = 'Service Worker not available in this browser';
      }
    } catch (err) {
      console.error('FCM Setup Error:', err);
      error.value = `Notifications setup error: ${err.message}`;
    }
  };

  // Watch for notification message changes
  watch(notificationMessage, (newMessage) => {
    if (!newMessage) return;
    processNotification(newMessage);
  });

  // Watch for user login/logout
  watch(() => userStore.user?.id, (newUserId, oldUserId) => {
    console.log('User ID changed:', oldUserId, '->', newUserId);
    if (newUserId) {
      // User logged in - load their notifications
      loadNotifications();
    } else {
      // User logged out - clear notifications
      notifications.value = [];
      unreadCount.value = 0;
    }
  });

  onMounted(async () => {
    // Load existing notifications
    await loadNotifications();
    
    // Setup broadcast channel
    setupBroadcastChannel();
    
    // Setup foreground message handler if service worker is ready
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.ready.then(() => {
        unsubscribe = setupForegroundHandler();
      });
    }
  });

  onUnmounted(() => {
    if (unsubscribe) unsubscribe();
    if (broadcastChannel) broadcastChannel.close();
  });

  return {
    fcmToken,
    notificationPermission,
    notificationMessage,
    error,
    notifications,
    unreadCount,
    loadNotifications,
    requestPermissionAndGetToken,
    markAllAsRead: async () => {
      try {
        const currentUserId = userStore.user?.id;
        
        // Skip if no user is logged in or DB not ready
        if (!currentUserId) {
          console.warn('Cannot mark notifications as read: No user logged in');
          return;
        }
        
        if (!dbReady) {
          console.warn('Cannot mark notifications as read: Database not ready');
          return;
        }
        
        // Update all notifications in IndexedDB
        const transaction = db.transaction(['notifications'], 'readwrite');
        const store = transaction.objectStore('notifications');
        const getAllRequest = store.getAll();

        getAllRequest.onsuccess = async () => {
          const allNotifications = getAllRequest.result;
          for (const notification of allNotifications) {
            // Only update notifications belonging to current user
            if (!notification.user_id || String(notification.user_id) === String(currentUserId)) {
              notification.read = true;
              await new Promise((resolve, reject) => {
                const updateRequest = store.put(notification);
                updateRequest.onsuccess = () => resolve();
                updateRequest.onerror = (event) => reject(event.target.error);
              });
            }
          }
        };

        // Update local state - only mark current user's notifications as read
        notifications.value = notifications.value.map(n => {
          if (!n.user_id || String(n.user_id) === String(currentUserId)) {
            return {...n, read: true};
          }
          return n;
        });
        updateUnreadCount();
      } catch (error) {
        console.error('Error marking all notifications as read:', error);
      }
    },
    markAsRead: async (id) => {
      try {
        const currentUserId = userStore.user?.id;
        
        // Skip if no user is logged in
        if (!currentUserId) {
          console.warn('Cannot mark notification as read: No user logged in');
          return;
        }
        
        await markNotificationAsRead(id);
        const notification = notifications.value.find(n => n.id === id);
        
        // Only mark if it's current user's notification
        if (notification && !notification.read && 
            (!notification.user_id || String(notification.user_id) === String(currentUserId))) {
          notification.read = true;
          updateUnreadCount();
        }
      } catch (error) {
        console.error('Error marking notification as read:', error);
      }
    },
    clearAll: async () => {
      try {
        const currentUserId = userStore.user?.id;
        
        // Skip if no user is logged in or DB not ready
        if (!currentUserId) {
          console.warn('Cannot clear notifications: No user logged in');
          return;
        }
        
        if (!dbReady) {
          console.warn('Cannot clear notifications: Database not ready');
          return;
        }
        
        // Get all notifications from IndexedDB
        const transaction = db.transaction(['notifications'], 'readwrite');
        const store = transaction.objectStore('notifications');
        const getAllRequest = store.getAll();
        
        getAllRequest.onsuccess = async () => {
          const allNotifications = getAllRequest.result;
          
          // Delete only current user's notifications
          for (const notification of allNotifications) {
            if (!notification.user_id || String(notification.user_id) === String(currentUserId)) {
              await new Promise((resolve, reject) => {
                const deleteRequest = store.delete(notification.id);
                deleteRequest.onsuccess = () => resolve();
                deleteRequest.onerror = (event) => reject(event.target.error);
              });
            }
          }
        };

        // Update local state - remove only current user's notifications
        notifications.value = notifications.value.filter(n => 
          n.user_id && String(n.user_id) !== String(currentUserId)
        );
        updateUnreadCount();
      } catch (error) {
        console.error('Error clearing notifications:', error);
      }
    }
  };
}