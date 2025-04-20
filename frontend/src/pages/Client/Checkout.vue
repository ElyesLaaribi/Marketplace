<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../axios";

const route = useRoute();
const router = useRouter();

const listingId = route.params.id;

const image = ref(route.query.image || "/images/fallback-image.jpg");
const startDate = ref(route.query.startDate || "");
const endDate = ref(route.query.endDate || "");
const days = ref(parseInt(route.query.days) || 1);
const price = ref(parseFloat(route.query.price) || 0);
const totalPrice = ref(parseFloat(route.query.totalPrice) || 0);
const serviceFee = parseFloat(route.query.service || 10);

const isSubmitting = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

const showDateEditor = ref(false);
const tempStartDate = ref("");
const tempEndDate = ref("");

const today = new Date();
const formattedToday = today.toISOString().split("T")[0];

const openDateEditor = () => {
  tempStartDate.value = startDate.value;
  tempEndDate.value = endDate.value;
  showDateEditor.value = true;
};

const closeDateEditor = () => {
  showDateEditor.value = false;
};

const updateDates = () => {
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

  startDate.value = tempStartDate.value;
  endDate.value = tempEndDate.value;

  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  days.value = diffDays || 1;

  updateTotalPrice();

  closeDateEditor();
};

const updateTotalPrice = () => {
  if (listing.value && listing.value.price) {
    const basePrice = listing.value.price * days.value;
    totalPrice.value = basePrice + serviceFee;
  }
};

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
  { id: "paypal", name: "Paypal" },
  { id: "Visa", name: "Visa" },
]);

const paymentForm = ref({
  cardNumber: "",
  expirationDate: "",
  cvv: "",
  zipCode: "",
});

const nights = computed(() => {
  return days.value || 1;
});

const fetchListingData = async () => {
  try {
    isLoading.value = true;
    const response = await api.get(`/api/public-listings/${listingId}`);
    if (response?.data?.data) {
      listing.value = response.data.data;
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

const submitReservation = async () => {
  try {
    isSubmitting.value = true;
    errorMessage.value = "";

    if (!startDate.value || !endDate.value) {
      errorMessage.value = "Please select start and end dates";
      return;
    }

    const startDateObj = new Date(startDate.value);
    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);

    if (startDateObj < todayDate) {
      errorMessage.value = "Start date cannot be in the past";
      isSubmitting.value = false;
      return;
    }

    const response = await api.post("/api/reservations", {
      listing_id: listingId,
      start_date: startDate.value,
      end_date: endDate.value,
    });

    successMessage.value = "Reservation created successfully!";

    setTimeout(() => {
      router.push("/home");
    }, 5000);
  } catch (err) {
    console.error("Error creating reservation:", err);
    errorMessage.value =
      err.response?.data?.message ||
      "Failed to create reservation. Please try again.";
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  window.scrollTo({ top: 0, behavior: "smooth" });
  fetchListingData();

  // Set default dates if they're not provided
  if (!startDate.value) {
    // Set default start date to tomorrow if not provided
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    startDate.value = tomorrow.toISOString().split("T")[0];
  }

  if (!endDate.value && startDate.value) {
    // Set default end date to day after start date
    const endDateObj = new Date(startDate.value);
    endDateObj.setDate(endDateObj.getDate() + 1);
    endDate.value = endDateObj.toISOString().split("T")[0];
  }
});
</script>

<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-4 h-24 flex items-center">
      <router-link to="/home" class="flex items-center">
        <img
          class="h-15 w-auto"
          src="../../assets/images/logo.png"
          alt="Company Logo"
        />
      </router-link>
    </div>
  </header>

  <div class="min-h-screen bg-white py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Confirm and pay</h1>

      <div
        v-if="errorMessage"
        class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg"
      >
        {{ errorMessage }}
      </div>

      <div
        v-if="successMessage"
        class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg"
      >
        {{ successMessage }}
      </div>

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
                    v-model="paymentForm.cardNumber"
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
                    v-model="paymentForm.expirationDate"
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
                    v-model="paymentForm.cvv"
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
                    v-model="paymentForm.zipCode"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="12345"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="border-t border-gray-200 pt-4">
            <h2 class="text-xl font-semibold mb-4">Cancellation policy</h2>
            <p class="text-gray-800 mb-4">
              This reservation is non-refundable.
            </p>
          </div>
          <div class="border-t border-gray-200 pt-4">
            <h2 class="text-xl font-semibold mb-4">Ground rules</h2>
            <p class="text-gray-800 mb-4">
              We ask every client to remember a few simple things about what
              makes a great client.
            </p>
            <ul class="list-disc pl-5 text-gray-800 text-base space-y-1 mb-8">
              <li>Stick to the rules set by the lessor</li>
              <li>Treat rented items like they're your own</li>
            </ul>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <button
              class="w-full bg-[#002D4A] hover:bg-[#036F8B] text-white font-medium py-3 px-4 rounded-lg transition duration-150"
              @click="submitReservation"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? "Processing..." : "Confirm and pay" }}
            </button>
            <button
              class="mt-4 w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 font-medium py-3 px-4 rounded-lg transition duration-150"
              @click="$router.push('/home')"
              :disabled="isSubmitting"
            >
              Cancel
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
                  :src="image"
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
              :min="formattedToday"
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
              :min="formattedToday || formattedToday"
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
            class="px-4 py-2 bg-[#002D4A] text-white rounded-md hover:bg-[#036F8B]"
            @click="updateDates"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
