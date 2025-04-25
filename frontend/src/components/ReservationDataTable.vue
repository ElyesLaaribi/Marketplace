<script setup>
import { ref, computed, onMounted } from "vue";
import SearchForm from "./SearchForm.vue";
import api from "../axios";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
import { PrinterIcon } from "@heroicons/vue/24/solid";
import { useUserStore } from "../store/user";
import { useLessorStore } from "../store/lessor";
import { storeToRefs } from "pinia";

const userStore = useUserStore();
const user = userStore.user;

const lessorStore = useLessorStore();
const { lessor } = storeToRefs(lessorStore);

const $toast = useToast();

const reservations = ref([]);
const searchFilter = ref("");
const loading = ref(false);

const fetchReservations = async () => {
  loading.value = true;
  try {
    const response = await api.get("api/reservations");
    reservations.value = response.data.data ?? response.data;
  } catch (error) {
    console.error(error);
    $toast.error("Failed to load reservations");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchReservations();
});

const handleSearch = (search) => {
  searchFilter.value = search.trim().toLowerCase();
};

const filteredItems = computed(() => {
  if (!searchFilter.value) return reservations.value;

  return reservations.value.filter((item) => {
    return (
      item.reservation_id.toString().includes(searchFilter.value) ||
      item.listing_name.toLowerCase().includes(searchFilter.value) ||
      item.client.toLowerCase().includes(searchFilter.value)
    );
  });
});

const getStatus = (endDate) => {
  const today = new Date().setHours(0, 0, 0, 0);
  const end = new Date(endDate).setHours(0, 0, 0, 0);
  return end < today ? "Done" : "Pending";
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <div class="bg-white relative rounded-lg shadow-sm overflow-hidden border border-gray-300 max-w-7xl mx-auto">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <SearchForm @search="handleSearch" />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
              ID
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
              Image
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
              Listing
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
              Client
            </th>
            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
              {{ user?.role === "client" ? (lessor?.role === "lessor" ? "Revenue" : "Price paid") : "Revenue" }}
            </th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
              Start Date
            </th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
              End Date
            </th>
            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
              Status
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="item in filteredItems" :key="item.reservation_id" class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-4 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                #{{ item.reservation_id }}
              </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
              <div class="h-10 w-10 rounded-lg overflow-hidden">
                <img
                  v-if="item.images"
                  :src="item.images"
                  alt="Listing Image"
                  class="h-full w-full object-cover"
                />
                <div v-else class="h-full w-full bg-gray-100 flex items-center justify-center">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
            </td>
            <td class="px-4 py-4">
              <div class="text-sm text-gray-900 font-medium truncate">{{ item.listing_name }}</div>
            </td>
            <td class="px-4 py-4">
              <div class="text-sm text-gray-900 truncate">{{ item.client }}</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-right">
              <div class="text-sm font-medium text-gray-900">{{ item.price }} TND</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-center">
              <div class="text-sm text-gray-900">{{ formatDate(item.start_date) }}</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-center">
              <div class="text-sm text-gray-900">{{ formatDate(item.end_date) }}</div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-center">
              <span
                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="{
                  'bg-green-100 text-green-800': getStatus(item.end_date) === 'Done',
                  'bg-blue-100 text-blue-800': getStatus(item.end_date) === 'Pending'
                }"
              >
                {{ getStatus(item.end_date) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="loading" class="p-8 text-center">
      <div class="inline-flex items-center">
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-500">Loading reservations...</span>
      </div>
    </div>
    <div v-else-if="!filteredItems.length" class="p-8 text-center">
      <div class="text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-2 text-sm">No reservations found.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reserved-day {
  background-color: #fef2f2;
  color: #ef4444;
  text-decoration: line-through;
}
</style>
