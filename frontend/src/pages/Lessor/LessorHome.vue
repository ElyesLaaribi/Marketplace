<script setup>
import api from "../../axios";
import LessorLayout from "../../components/LessorLayout.vue";
import { ref, onMounted } from "vue";

const listings = ref(0);
const reservations = ref(0);
const earnings = ref(0);
const clients = ref(0);

const listingsChange = ref(0);
const reservationsChange = ref(0);
const earningsChange = ref(0);
const clientsChange = ref(0);

const loading = ref(true);

onMounted(async () => {
  try {
    const [listingsRes, demandRes, revenueRes, clientsRes] = await Promise.all([
      api.get("api/lessor/listings/count"),
      api.get("api/lessor/listings/demand"),
      api.get("api/lessor/listings/revenue"),
      api.get("api/lessor/listings/clients"),
    ]);

    listings.value = listingsRes.data.listings || 0;
    reservations.value = demandRes.data.reservations || 0;
    earnings.value = revenueRes.data.revenue || 0;
    clients.value = clientsRes.data.total_clients || 0;

    listingsChange.value = 3.48;
    reservationsChange.value = 12.18;
    earningsChange.value = -5.72;
    clientsChange.value = 54.8;
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    loading.value = false;
  }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat("en-TN", {
    style: "currency",
    currency: "TND",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};
</script>

<template>
  <LessorLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-6">
      <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-6">
        Welcome back
      </h1>

      <!-- Stats Cards Grid -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Listings Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  TOTAL LISTINGS
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : listings }}
                </div>
              </div>
              <div class="bg-blue-500 p-3 rounded-full">
                <svg
                  class="h-6 w-6 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <div
                :class="listingsChange >= 0 ? 'text-green-500' : 'text-red-500'"
                class="flex items-center text-sm"
              >
                <span v-if="listingsChange >= 0" class="text-green-500">↑</span>
                <span v-else class="text-red-500">↓</span>
                <span class="font-medium"
                  >{{ Math.abs(listingsChange).toFixed(2) }}%</span
                >
              </div>
              <span class="text-gray-500 text-sm ml-2">Since last month</span>
            </div>
          </div>
        </div>

        <!-- Reservations Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  RESERVATIONS
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : reservations }}
                </div>
              </div>
              <div class="bg-orange-500 p-3 rounded-full">
                <svg
                  class="h-6 w-6 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <div
                :class="
                  reservationsChange >= 0 ? 'text-green-500' : 'text-red-500'
                "
                class="flex items-center text-sm"
              >
                <span v-if="reservationsChange >= 0" class="text-green-500"
                  >↑</span
                >
                <span v-else class="text-red-500">↓</span>
                <span class="font-medium"
                  >{{ Math.abs(reservationsChange).toFixed(2) }}%</span
                >
              </div>
              <span class="text-gray-500 text-sm ml-2">Since last month</span>
            </div>
          </div>
        </div>

        <!-- Earnings Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  TOTAL EARNINGS
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : formatCurrency(earnings) }}
                </div>
              </div>
              <div class="bg-green-500 p-3 rounded-full">
                <svg
                  class="h-6 w-6 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <div
                :class="earningsChange >= 0 ? 'text-green-500' : 'text-red-500'"
                class="flex items-center text-sm"
              >
                <span v-if="earningsChange >= 0" class="text-green-500">↑</span>
                <span v-else class="text-red-500">↓</span>
                <span class="font-medium"
                  >{{ Math.abs(earningsChange).toFixed(2) }}%</span
                >
              </div>
              <span class="text-gray-500 text-sm ml-2">Since last month</span>
            </div>
          </div>
        </div>

        <!-- Clients Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  TOTAL CLIENTS
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : clients }}
                </div>
              </div>
              <div class="bg-blue-500 p-3 rounded-full">
                <svg
                  class="h-6 w-6 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
            </div>
            <div class="mt-4 flex items-center">
              <div
                :class="clientsChange >= 0 ? 'text-green-500' : 'text-red-500'"
                class="flex items-center text-sm"
              >
                <span v-if="clientsChange >= 0" class="text-green-500">↑</span>
                <span v-else class="text-red-500">↓</span>
                <span class="font-medium"
                  >{{ Math.abs(clientsChange).toFixed(2) }}%</span
                >
              </div>
              <span class="text-gray-500 text-sm ml-2">Since last month</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LessorLayout>
</template>
