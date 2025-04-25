importScripts('https://www.gstatic.com/firebasejs/10.8.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.1/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyBvV4sh9NpR4u1WQZFB9B2t5z4d4K4qK4k",
    authDomain: "rental-app-12345.firebaseapp.com",
    projectId: "rental-app-12345",
    storageBucket: "rental-app-12345.appspot.com",
    messagingSenderId: "977313734840",
    appId: "1:977313734840:web:1234567890abcdef"
};

// Initialize Firebase once
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();
const broadcastChannel = new BroadcastChannel('notifications-channel');

// Initialize IndexedDB
let db;
const request = indexedDB.open('notificationsDB', 1);

request.onerror = (event) => {
    console.error('Error opening IndexedDB:', event.target.error);
};

request.onsuccess = (event) => {
    db = event.target.result;
};

request.onupgradeneeded = (event) => {
    const db = event.target.result;
    if (!db.objectStoreNames.contains('notifications')) {
        db.createObjectStore('notifications', { keyPath: 'id' });
    }
};

// Function to store notification in IndexedDB
function storeNotification(notification) {
    if (!db) {
        console.error('IndexedDB not initialized');
        return;
    }

    const transaction = db.transaction(['notifications'], 'readwrite');
    const store = transaction.objectStore('notifications');
    const request = store.add(notification);

    request.onsuccess = () => {
        console.log('Notification stored in IndexedDB');
        // Broadcast the notification to the main app
        broadcastChannel.postMessage(notification);
    };

    request.onerror = (event) => {
        console.error('Error storing notification in IndexedDB:', event.target.error);
    };
}

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('Received background message:', payload);

    // Extract notification data
    const notificationData = payload.notification || {};
    const data = payload.data || {};

    // Create the notification object
    const notificationInfo = {
        id: data.rental_id || `msg-${Date.now()}`,
        title: notificationData.title || 'New Rental Reminder',
        body: notificationData.body || data.message || '',
        listingName: data.listing_name || "",
        startDate: data.start_date
            ? new Date(data.start_date).toLocaleDateString()
            : "Soon",
        type: "rental_reminder",
        timestamp: new Date().toISOString(),
        read: false
    };

    // Store in IndexedDB
    storeNotification(notificationInfo);

    // Create notification with all available info
    const notificationTitle = notificationInfo.title;
    const notificationOptions = {
        body: notificationInfo.body,
        icon: '/favicon.ico',
        data: {
            url: data.url || '/', 
            rental_id: data.rental_id,
            listing_name: data.listing_name,
            start_date: data.start_date
        },
        requireInteraction: true
    };
    
    // Show the notification
    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    // Get the notification data
    const data = event.notification.data || {};
    const rentalId = data.rental_id;
    let urlToOpen = new URL('/', self.location.origin);
    if (rentalId) {
        urlToOpen = new URL(`/rental/${rentalId}`, self.location.origin);
    }
    
    event.waitUntil(
        clients.matchAll({type: 'window'}).then(windowClients => {
            // Check if there is already a window/tab open with the target URL
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                // If so, focus it
                if ('focus' in client) {
                    client.focus();
                    // And navigate if needed
                    if (client.url !== urlToOpen.href) {
                        client.navigate(urlToOpen);
                    }
                    return;
                }
            }
            // If not, open a new window/tab
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
}); 