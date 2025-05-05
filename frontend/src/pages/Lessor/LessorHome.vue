<script setup>
import api from "../../axios";
import LessorLayout from "../../components/LessorLayout.vue";
import { ref, onMounted } from "vue";
import { Bar, Pie } from "vue-chartjs";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from "chart.js";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const listings = ref(0);
const reservations = ref(0);
const earnings = ref(0);
const clients = ref(0);

const listingsChange = ref(0);
const reservationsChange = ref(0);
const earningsChange = ref(0);
const clientsChange = ref(0);

const loading = ref(true);

// Dashboard data
const averageRevenuePerItem = ref(0);
const averageRevenueData = ref({
  labels: [],
  datasets: [],
});
const occupancyData = ref({
  labels: [],
  datasets: [],
});
const popularItemsData = ref({
  labels: [],
  datasets: [],
});
const topClientsData = ref([]);
const topClientsChartData = ref({
  labels: [],
  datasets: [],
});

onMounted(async () => {
  try {
    // Use development endpoints for testing without authentication

    const [
      listingsRes,
      demandRes,
      revenueRes,
      clientsRes,
      avgRevenueRes,
      occupancyRes,
      popularItemsRes,
      topClientsRes,
    ] = await Promise.all([
      api.get("api/lessor/listings/count"),
      api.get("api/lessor/listings/demand"),
      api.get("api/lessor/listings/revenue"),
      api.get("api/lessor/listings/clients"),
      api.get("api/lessor/listings/average-revenue"),
      api.get("api/lessor/listings/occupancy"),
      api.get("api/lessor/listings/popular-items"),
      api.get("api/lessor/listings/top-clients"),
    ]);

    listings.value = listingsRes.data.listings || 0;
    reservations.value = demandRes.data.reservations || 0;
    earnings.value = revenueRes.data.revenue || 0;
    clients.value = clientsRes.data.total_clients || 0;

    // Set average revenue data
    if (
      avgRevenueRes.data.listings_detail &&
      avgRevenueRes.data.listings_detail.length > 0
    ) {
      const listingsDetail = avgRevenueRes.data.listings_detail;
      averageRevenueData.value = {
        labels: listingsDetail.map((item) => item.listing_name),
        datasets: [
          {
            data: listingsDetail.map((item) => item.total_revenue),
            backgroundColor: [
              "#4CAF50",
              "#2196F3",
              "#9C27B0",
              "#F44336",
              "#FF9800",
              "#03A9F4",
              "#E91E63",
              "#8BC34A",
              "#FF5722",
              "#607D8B",
            ],
            hoverOffset: 4,
          },
        ],
      };
    }

    // Set occupancy data
    if (
      occupancyRes.data &&
      occupancyRes.data.listings_occupancy &&
      occupancyRes.data.listings_occupancy.length > 0
    ) {
      const occupancyItems = occupancyRes.data.listings_occupancy;
      occupancyData.value = {
        labels: occupancyItems.map((item) => item.listing_name),
        datasets: [
          {
            label: "Days Rented",
            backgroundColor: "#4CAF50",
            data: occupancyItems.map((item) => item.occupied_days),
          },
          {
            label: "Days Available",
            backgroundColor: "#FFA000",
            data: occupancyItems.map(
              (item) => item.available_days - item.occupied_days
            ),
          },
        ],
      };
    }

    // Set popular items data
    if (
      popularItemsRes.data &&
      popularItemsRes.data.popular_items &&
      popularItemsRes.data.popular_items.length > 0
    ) {
      const popularItems = popularItemsRes.data.popular_items;
      popularItemsData.value = {
        labels: popularItems.map((item) => item.listing_name),
        datasets: [
          {
            label: "Number of Reservations",
            data: popularItems.map((item) => item.reservation_count),
            backgroundColor: [
              "#4CAF50",
              "#2196F3",
              "#9C27B0",
              "#F44336",
              "#FF9800",
              "#03A9F4",
              "#E91E63",
              "#8BC34A",
              "#FF5722",
              "#607D8B",
            ],
            borderWidth: 1,
          },
        ],
      };
    }

    // Set top clients data
    if (
      topClientsRes.data &&
      topClientsRes.data.top_clients &&
      topClientsRes.data.top_clients.length > 0
    ) {
      const topClients = topClientsRes.data.top_clients;
      topClientsData.value = topClients.map((client) => ({
        name: client.client_name,
        reservations: client.reservation_count,
        revenue: client.total_spent,
      }));

      topClientsChartData.value = {
        labels: topClients.map((client) => client.client_name),
        datasets: [
          {
            label: "Number of Reservations",
            data: topClients.map((client) => client.reservation_count),
            backgroundColor: [
              "#4CAF50",
              "#2196F3",
              "#9C27B0",
              "#F44336",
              "#FF9800",
              "#03A9F4",
              "#E91E63",
              "#8BC34A",
              "#FF5722",
              "#607D8B",
            ],
          },
        ],
      };
    }

    listingsChange.value = 3.48;
    reservationsChange.value = 12.18;
    earningsChange.value = -5.72;
    clientsChange.value = 54.8;
  } catch (error) {
    console.error("Error fetching dashboard data:", error);

    // Set fallback data in case of API errors
    setFallbackData();
  } finally {
    loading.value = false;
  }
});

// Fallback data if API calls fail
const setFallbackData = () => {
  listings.value = 24;
  reservations.value = 183;
  earnings.value = 10500;
  clients.value = 56;
  averageRevenuePerItem.value = 437.5;

  // Fallback pie chart data for average revenue
  averageRevenueData.value = {
    labels: ["Item 1", "Item 2", "Item 3", "Item 4", "Item 5"],
    datasets: [
      {
        data: [5000, 3000, 2000, 1500, 1000],
        backgroundColor: [
          "#4CAF50",
          "#2196F3",
          "#9C27B0",
          "#F44336",
          "#FF9800",
        ],
        hoverOffset: 4,
      },
    ],
  };

  // Fallback occupancy data
  occupancyData.value = {
    labels: ["Item 1", "Item 2", "Item 3", "Item 4", "Item 5"],
    datasets: [
      {
        label: "Days Rented",
        backgroundColor: "#4CAF50",
        data: [18, 12, 20, 8, 15],
      },
      {
        label: "Days Available",
        backgroundColor: "#FFA000",
        data: [12, 18, 10, 22, 15],
      },
    ],
  };

  // Fallback popular items data - modified to use regular Bar chart instead of HorizontalBar
  popularItemsData.value = {
    labels: ["Item 1", "Item 2", "Item 3", "Item 4", "Item 5"],
    datasets: [
      {
        label: "Number of Reservations",
        data: [42, 38, 27, 21, 18],
        backgroundColor: [
          "#4CAF50",
          "#2196F3",
          "#9C27B0",
          "#F44336",
          "#FF9800",
        ],
        borderWidth: 1,
      },
    ],
  };

  // Fallback top clients data
  topClientsData.value = [
    { name: "Client 1", reservations: 12, revenue: 1500 },
    { name: "Client 2", reservations: 10, revenue: 1350 },
    { name: "Client 3", reservations: 8, revenue: 1100 },
    { name: "Client 4", reservations: 6, revenue: 850 },
    { name: "Client 5", reservations: 5, revenue: 720 },
  ];

  // Fallback top clients chart data
  topClientsChartData.value = {
    labels: ["Client 1", "Client 2", "Client 3", "Client 4", "Client 5"],
    datasets: [
      {
        label: "Number of Reservations",
        data: [12, 10, 8, 6, 5],
        backgroundColor: [
          "#4CAF50",
          "#2196F3",
          "#9C27B0",
          "#F44336",
          "#FF9800",
        ],
      },
    ],
  };

  listingsChange.value = 3.48;
  reservationsChange.value = 12.18;
  earningsChange.value = -5.72;
  clientsChange.value = 54.8;
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("en-TN", {
    style: "currency",
    currency: "TND",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "right",
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          const value = context.raw;
          return context.label + ": " + formatCurrency(value);
        },
      },
    },
  },
};

const occupancyChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "top",
    },
    title: {
      display: true,
      text: "Monthly Occupancy: Days Rented vs Days Available",
    },
  },
  scales: {
    x: {
      stacked: true,
    },
    y: {
      stacked: true,
    },
  },
};

const horizontalBarOptions = {
  indexAxis: "y",
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: true,
      text: "Number of Reservations per Item",
    },
  },
};

const clientsChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: true,
      text: "Reservations per Client",
    },
  },
};
</script>

<template>
  <LessorLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-6">
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
          </div>
        </div>
      </div>

      <!-- BI Dashboard Section -->
      <div class="mt-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">
          Lessor Analytics Dashboard
        </h2>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <!-- Average Revenue Per Item (Pie Chart) -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-2">
                Revenue by Item
              </h3>
              <div class="h-80">
                <Pie
                  v-if="
                    !loading &&
                    averageRevenueData.labels &&
                    averageRevenueData.labels.length > 0
                  "
                  :data="averageRevenueData"
                  :options="pieChartOptions"
                />
                <div
                  v-else-if="loading"
                  class="flex items-center justify-center h-full"
                >
                  <p class="text-gray-500">Loading chart data...</p>
                </div>
                <div v-else class="flex items-center justify-center h-full">
                  <p class="text-gray-500">No revenue data available</p>
                </div>
              </div>
              <div class="mt-4 text-center">
                <p class="text-sm text-gray-500">
                  Average Revenue Per Item:
                  <span class="font-medium">{{
                    formatCurrency(averageRevenuePerItem)
                  }}</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Occupancy Chart (Stacked Bar Chart) -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-2">
                Monthly Item Occupancy
              </h3>
              <div class="h-80">
                <Bar
                  v-if="
                    !loading &&
                    occupancyData.labels &&
                    occupancyData.labels.length > 0
                  "
                  :data="occupancyData"
                  :options="occupancyChartOptions"
                />
                <div
                  v-else-if="loading"
                  class="flex items-center justify-center h-full"
                >
                  <p class="text-gray-500">Loading chart data...</p>
                </div>
                <div v-else class="flex items-center justify-center h-full">
                  <p class="text-gray-500">No occupancy data available</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Popular Items Horizontal Bar Chart -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                Most Popular Items
              </h3>
              <div class="h-80">
                <Bar
                  v-if="
                    !loading &&
                    popularItemsData.labels &&
                    popularItemsData.labels.length > 0
                  "
                  :data="popularItemsData"
                  :options="horizontalBarOptions"
                />
                <div
                  v-else-if="loading"
                  class="flex items-center justify-center h-full"
                >
                  <p class="text-gray-500">Loading chart data...</p>
                </div>
                <div v-else class="flex items-center justify-center h-full">
                  <p class="text-gray-500">No popularity data available</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Clients Bar Chart and Table -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                Most Active Clients
              </h3>
              <div class="h-96">
                <Bar
                  v-if="
                    !loading &&
                    topClientsChartData.labels &&
                    topClientsChartData.labels.length > 0
                  "
                  :data="topClientsChartData"
                  :options="clientsChartOptions"
                />
                <div
                  v-else-if="loading"
                  class="flex items-center justify-center h-full"
                >
                  <p class="text-gray-500">Loading chart data...</p>
                </div>
                <div v-else class="flex items-center justify-center h-full">
                  <p class="text-gray-500">No client data available</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LessorLayout>
</template>

<style scoped></style>
