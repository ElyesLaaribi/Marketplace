// public/firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/10.8.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.1/firebase-messaging-compat.js');

const firebaseConfig = {
  apiKey: "AIzaSyDHHbtG6Ka7j_WzWECxT9VFq0eL_c65z5Q",
  authDomain: "rentease-7d991.firebaseapp.com",
  projectId: "rentease-7d991",
  storageBucket: "rentease-7d991.firebasestorage.app",
  messagingSenderId: "977313734840",
  appId: "1:977313734840:web:fdc9d74b7a672981eed44d"
};



// Initialize Firebase once
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();
const broadcastChannel = new BroadcastChannel('notifications-channel');


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
  
  // Broadcast the notification to the main app
  broadcastChannel.postMessage(notificationInfo);
  
  // Also store in localStorage as a backup
  try {
    const storedNotifications = JSON.parse(localStorage.getItem('notifications') || '[]');
    storedNotifications.unshift(notificationInfo);
    localStorage.setItem('notifications', JSON.stringify(storedNotifications));
  } catch (e) {
    console.error('Error storing notification in localStorage:', e);
  }
  
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
    }
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