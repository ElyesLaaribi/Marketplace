<template>
  <div class="p-4">
    <div class="mb-4 bg-white shadow rounded-lg p-4">
      <h2 class="text-xl font-semibold mb-2">Dashboard</h2>
      
      <!-- Notification Test Button -->
      <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <h3 class="font-medium mb-2">Notifications</h3>
        <p class="text-sm text-gray-600 mb-2">
          If you're not receiving notifications, you can test your setup here:
        </p>
        <div class="flex items-center space-x-4">
          <button 
            @click="testNotification"
            class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
            :disabled="notificationLoading"
          >
            {{ notificationLoading ? 'Sending...' : 'Test Notification' }}
          </button>
          <button 
            @click="testDirectFCM"
            class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
            :disabled="directFCMLoading"
          >
            {{ directFCMLoading ? 'Sending...' : 'Test Direct FCM' }}
          </button>
        </div>
        <div v-if="notificationResult" class="mt-2 p-2 bg-gray-100 rounded text-sm">
          {{ notificationResult }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useFirebaseMessaging } from "../../composables/useFirebaseMessaging";
import { onMounted, ref } from "vue";
import api from "../../axios.js";

const { requestPermissionAndGetToken, error: fcmError } = useFirebaseMessaging();
const notificationLoading = ref(false);
const directFCMLoading = ref(false);
const notificationResult = ref('');

onMounted(async () => {
  console.log("Dashboard mounted, requesting notification permissions...");
  try {
    await requestPermissionAndGetToken();
  } catch (err) {
    console.error("Error setting up notifications:", err);
  }
});

const testNotification = async () => {
  try {
    notificationLoading.value = true;
    notificationResult.value = 'Sending notification...';
    
    const response = await api.post('/api/send-test-notification');
    console.log('Notification test response:', response.data);
    
    notificationResult.value = `Success: ${response.data.message}`;
  } catch (error) {
    console.error('Error testing notification:', error);
    notificationResult.value = `Error: ${error.response?.data?.error || error.message}`;
  } finally {
    notificationLoading.value = false;
  }
};

const testDirectFCM = async () => {
  try {
    directFCMLoading.value = true;
    notificationResult.value = 'Sending direct FCM test...';
    
    const response = await api.post('/api/test-direct-fcm');
    console.log('Direct FCM test response:', response.data);
    
    notificationResult.value = `Direct FCM response: ${JSON.stringify(response.data.fcm_response)}`;
  } catch (error) {
    console.error('Error testing direct FCM:', error);
    notificationResult.value = `Error: ${error.response?.data?.error || error.message}`;
  } finally {
    directFCMLoading.value = false;
  }
};
</script> 