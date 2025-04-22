<script setup>
import { ref, computed, onMounted } from "vue";
import SearchForm from "./SearchForm.vue";
import api from "../axios";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";

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

// Determine reservation status based on end date
const getStatus = (endDate) => {
  const today = new Date().setHours(0, 0, 0, 0);
  const end = new Date(endDate).setHours(0, 0, 0, 0);
  return end < today ? "Done" : "Pending";
};
</script>

<template>
  <div
    class="bg-white relative rounded-lg shadow-sm overflow-hidden border border-gray-300"
  >
    <div class="flex items-center justify-between p-4">
      <SearchForm @search="handleSearch" />
    </div>

    <table class="min-w-full divide-y divide-gray-300">
      <thead class="bg-white">
        <tr>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            ID
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Image
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Listing
          </th>
          <th
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Client
          </th>
          <th
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Start Date
          </th>
          <th
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            End Date
          </th>
          <th
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Status
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-300">
        <tr
          v-for="item in filteredItems"
          :key="item.reservation_id"
          class="hover:bg-gray-50"
        >
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-semibold text-gray-900">
              {{ item.reservation_id }}
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <img
              v-if="item.images"
              :src="item.images"
              alt="Listing Image"
              class="w-10 h-10 object-cover rounded"
            />
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ item.listing_name }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="text-sm text-gray-900">{{ item.client }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="text-sm text-gray-900">{{ item.start_date }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="text-sm text-gray-900">{{ item.end_date }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <span
              class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full"
              :class="
                item.end_date &&
                (new Date(item.end_date).setHours(0, 0, 0, 0) <
                new Date().setHours(0, 0, 0, 0)
                  ? 'bg-green-100 text-green-800'
                  : 'bg-blue-100 text-blue-800')
              "
            >
              {{ getStatus(item.end_date) }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="p-4 text-center text-gray-500">
      Loading reservations...
    </div>
    <div
      v-else-if="!filteredItems.length"
      class="p-4 text-center text-gray-500"
    >
      No reservations found.
    </div>
  </div>
</template>
