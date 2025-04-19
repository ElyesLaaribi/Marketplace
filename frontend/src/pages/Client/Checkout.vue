<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../axios";

const route = useRoute();
const router = useRouter();

// Get listing ID from route params
const listingId = route.params.id;

// Get booking details from query params
const startDate = ref(route.query.startDate || "");
const endDate = ref(route.query.endDate || "");
const days = ref(parseInt(route.query.days) || 1);
const price = ref(parseFloat(route.query.price) || 0);
const totalPrice = ref(parseFloat(route.query.totalPrice) || 0);
const serviceFee = parseFloat(route.query.service || 0);

// Date editing
const showDateEditor = ref(false);
const tempStartDate = ref("");
const tempEndDate = ref("");

const openDateEditor = () => {
  tempStartDate.value = startDate.value;
  tempEndDate.value = endDate.value;
  showDateEditor.value = true;
};

const closeDateEditor = () => {
  showDateEditor.value = false;
};

const updateDates = () => {
  // Validate dates first
  if (!tempStartDate.value || !tempEndDate.value) {
    alert("Please select both start and end dates");
    return;
  }

  const start = new Date(tempStartDate.value);
  const end = new Date(tempEndDate.value);

  if (start > end) {
    alert("End date must be after start date");
    return;
  }

  // Update the dates
  startDate.value = tempStartDate.value;
  endDate.value = tempEndDate.value;

  // Recalculate days
  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  days.value = diffDays || 1;

  // Recalculate total price
  updateTotalPrice();

  closeDateEditor();
};

const updateTotalPrice = () => {
  if (listing.value && listing.value.price) {
    const basePrice = listing.value.price * days.value;
    totalPrice.value = basePrice + serviceFee;
  }
};

// Fetch listing data based on ID
const listing = ref({});
const isLoading = ref(true);
const error = ref(null);

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

const formatCurrency = (amount) => {
  if (amount === undefined || amount === null) return "TND 0";
  return `TND ${amount.toFixed(2)}`;
};

const selectedPaymentMethod = ref("card");
const paymentMethods = ref([
  { id: "card", name: "Credit Card" },
  { id: "Visa", name: "Visa" },
]);

// Compute nights based on start and end dates
const nights = computed(() => {
  return days.value || 1;
});

const fetchListingData = async () => {
  try {
    isLoading.value = true;
    const response = await api.get(`/api/public-listings/${listingId}`);
    if (response?.data?.data) {
      listing.value = response.data.data;
      // If price wasn't passed in query, use the one from listing
      if (!route.query.price) {
        price.value = listing.value.price || 0;
      }
      updateTotalPrice();
    } else {
      throw new Error("Failed to load listing details");
    }
  } catch (err) {
    error.value = `Error loading listing details: ${err.message}`;
    console.error("Error fetching listing data:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchListingData();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Confirm and pay</h1>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Payment Form Section -->
        <div class="w-full lg:w-7/12 bg-white rounded-lg shadow p-6">
          <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Your details</h2>
            <div class="flex justify-between mb-4">
              <div>
                <h3 class="font-medium">Dates</h3>
                <p class="text-gray-600">
                  {{ formatDate(startDate) }} - {{ formatDate(endDate) }}
                </p>
              </div>
              <button
                class="text-black underline font-medium"
                @click="openDateEditor"
              >
                Edit
              </button>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Pay with</h2>
            <div class="flex space-x-2 mb-4">
              <button
                v-for="method in paymentMethods"
                :key="method.id"
                :class="[
                  'px-4 py-2 border rounded-full text-sm',
                  selectedPaymentMethod === method.id
                    ? 'border-black bg-gray-100'
                    : 'border-gray-300 hover:border-gray-500',
                ]"
                @click="selectedPaymentMethod = method.id"
              >
                {{ method.name }}
              </button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Card number</label
                  >
                  <input
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="1234 5678 9012 3456"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Expiration date</label
                  >
                  <input
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="MM/YY"
                  />
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >CVV</label
                  >
                  <input
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="123"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >ZIP Code</label
                  >
                  <input
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="12345"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <button
              class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-3 px-4 rounded-lg transition duration-150"
            >
              Confirm and pay
            </button>
            <p class="mt-4 text-sm text-gray-500 text-center">
              You won't be charged yet
            </p>
          </div>
        </div>

        <!-- Price Details Section -->
        <div class="w-full lg:w-5/12">
          <div class="bg-white rounded-lg shadow p-6 sticky top-8">
            <div class="flex items-start gap-4 pb-6 border-b border-gray-200">
              <div class="w-24 h-20 rounded-lg overflow-hidden flex-shrink-0">
                <img
                  :src="listing.image"
                  alt="Property"
                  class="w-full h-full object-cover"
                />
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ listing.type }}</p>
                <h3 class="font-medium">{{ listing.name }}</h3>
              </div>
            </div>

            <div class="py-6 border-b border-gray-200">
              <h3 class="text-lg font-semibold mb-4">Price details</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <div>
                    {{ formatCurrency(listing.price) }} x {{ nights }} days
                  </div>
                  <div>{{ formatCurrency(listing.price * nights) }}</div>
                </div>

                <div class="flex justify-between">
                  <div>Service fee</div>
                  <div>{{ formatCurrency(serviceFee) }}</div>
                </div>
              </div>
            </div>

            <div class="pt-6">
              <div class="flex justify-between font-bold">
                <div>Total</div>
                <div>{{ formatCurrency(totalPrice) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showDateEditor"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
        <h3 class="text-xl font-semibold mb-4">Edit dates</h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Start date</label
            >
            <input
              type="date"
              v-model="tempStartDate"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >End date</label
            >
            <input
              type="date"
              v-model="tempEndDate"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            @click="closeDateEditor"
          >
            Cancel
          </button>
          <button
            class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700"
            @click="updateDates"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
