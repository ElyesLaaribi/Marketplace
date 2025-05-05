<script setup>
import AdminLayout from "../../components/AdminLayout.vue";
import api from "../../axios";
import { ref, onMounted } from "vue";
import { Pie, Bar } from "vue-chartjs";
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

const loading = ref(true);

// Admin dashboard data
const totalClients = ref(0);
const totalLessors = ref(0);
const totalCategories = ref(0);
const topLessorsData = ref({
  labels: [],
  datasets: [],
});
const categoriesData = ref({
  labels: [],
  datasets: [],
});

onMounted(async () => {
  try {
    // Use development endpoints for testing without authentication

    const [
      clientsRes,
      lessorsRes,
      categoriesCountRes,
      topLessorsRes,
      categoriesRes,
    ] = await Promise.all([
      api.get("api/admin/clients/count"),
      api.get("api/admin/lessors/count"),
      api.get("api/admin/categories/count"),
      api.get("api/admin/lessors/top"),
      api.get("api/admin/categories"),
    ]);

    totalClients.value = clientsRes.data.total_clients || 0;
    totalLessors.value = lessorsRes.data.total_lessors || 0;
    totalCategories.value = categoriesCountRes.data.total_categories || 0;

    // Process lessors data for bar chart
    if (topLessorsRes.data && topLessorsRes.data.chartData) {
      topLessorsData.value = topLessorsRes.data.chartData;
    }

    // Process categories data for pie chart
    if (categoriesRes.data && categoriesRes.data.chartData) {
      categoriesData.value = categoriesRes.data.chartData;
    }
  } catch (error) {
    console.error("Error fetching admin dashboard data:", error);
    // Use fallback data in case of API errors
    setFallbackData();
  } finally {
    loading.value = false;
  }
});

// Fallback data if API calls fail
const setFallbackData = () => {
  totalClients.value = 287;
  totalLessors.value = 53;
  totalCategories.value = 12;

  topLessorsData.value = {
    labels: ["Lessor 1", "Lessor 2", "Lessor 3", "Lessor 4", "Lessor 5"],
    datasets: [
      {
        label: "Number of Listings",
        backgroundColor: [
          "#4CAF50",
          "#2196F3",
          "#9C27B0",
          "#F44336",
          "#FF9800",
        ],
        data: [24, 18, 15, 12, 9],
      },
    ],
  };

  categoriesData.value = {
    labels: [
      "Category 1",
      "Category 2",
      "Category 3",
      "Category 4",
      "Category 5",
    ],
    datasets: [
      {
        backgroundColor: [
          "#FF6384",
          "#36A2EB",
          "#FFCE56",
          "#4BC0C0",
          "#9966FF",
        ],
        data: [45, 30, 25, 20, 15],
      },
    ],
  };
};

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "bottom",
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          return context.label + ": " + context.raw + " listings";
        },
      },
    },
  },
};

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: true,
      text: "Number of Listings by Lessor",
    },
  },
};
</script>

<template>
  <AdminLayout>
    <div class="p-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 lg:grid-cols-3 mb-8">
        <!-- Total Clients Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  TOTAL CLIENTS
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : totalClients }}
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
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">
              Total registered clients on the platform
            </p>
          </div>
        </div>

        <!-- Total Lessors Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  TOTAL LESSORS
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : totalLessors }}
                </div>
              </div>
              <div class="bg-purple-500 p-3 rounded-full">
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
            <p class="mt-2 text-sm text-gray-500">
              Total registered lessors on the platform
            </p>
          </div>
        </div>

        <!-- Total Categories Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-500 truncate">
                  TOTAL CATEGORIES
                </div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">
                  {{ loading ? "..." : totalCategories }}
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
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                  />
                </svg>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">
              Total available item categories
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Top Lessors Bar Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              Top 5 Lessors by Listings
            </h3>
            <div class="h-80">
              <Bar
                v-if="!loading"
                :data="topLessorsData"
                :options="barChartOptions"
              />
              <div v-else class="flex items-center justify-center h-full">
                <p class="text-gray-500">Loading chart data...</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Categories Pie Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              Item Categories Distribution
            </h3>
            <div class="h-80">
              <Pie
                v-if="!loading"
                :data="categoriesData"
                :options="pieChartOptions"
              />
              <div v-else class="flex items-center justify-center h-full">
                <p class="text-gray-500">Loading chart data...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped></style>
