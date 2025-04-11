<script setup>
import { onMounted, ref, computed, watch, nextTick } from "vue";
import { StarIcon } from "@heroicons/vue/20/solid";
import { UserCircleIcon } from "@heroicons/vue/24/solid";
import DefaultLayout from "../../components/DefaultLayout.vue";
import api from "../../axios";
import { useRoute, useRouter } from "vue-router";
import L from "leaflet";

const isLoading = ref(true);
const error = ref(null);
const activeImageIndex = ref(0);
const showAllImages = ref(false);
const rentalStart = ref("");
const rentalEnd = ref("");
const totalDays = ref(1);
const reviews = ref({
  href: "#reviews",
  average: 4,
  totalCount: 117,
});
const route = useRoute();
const router = useRouter();
const id = route.params.id;
const map = ref();
const mapContainer = ref();

const listingData = ref({
  id: "",
  name: "",
  price: 0,
  description: "",
  images: [],
  user_id: "",
  user_name: "Item Owner",
  joined_since: "",
  avatar: "",
});

onMounted(() => {
  fetchListingData().then(() => {
    nextTick(() => {
      if (mapContainer.value) {
        map.value = L.map(mapContainer.value).setView([51.505, -0.09], 13);
        L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
          maxZoom: 19,
          attribution:
            '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map.value);
      }
    });
  });

  const today = new Date();
  const tomorrow = new Date();
  tomorrow.setDate(today.getDate() + 1);

  rentalStart.value = today.toISOString().slice(0, 10);
  rentalEnd.value = tomorrow.toISOString().slice(0, 10);

  calculateDays();
});

const extractImages = (data) => {
  const ImageArrays = ["images"];

  for (const prop of ImageArrays) {
    if (Array.isArray(data[prop]) && data[prop].length > 0) {
      return data[prop].map((img, index) => ({
        src:
          typeof img === "object" ? img.url || img.src || img.path || img : img,
        alt: `${data.Name || data.name || "Product"} image ${index + 1}`,
      }));
    }
  }

  const SingleImages = ["images"];

  for (const prop of SingleImages) {
    if (typeof data[prop] === "string" && data[prop]) {
      return [
        {
          src: data[prop],
          alt: `${data.Name || data.name || "Product"} image`,
        },
      ];
    }
  }

  return [];
};

const galleryLayout = computed(() => {
  const imageCount = listingData.value.images.length;
  if (imageCount === 0) return "none";
  if (imageCount === 1) return "single";
  return "multiple";
});

const formattedPrice = computed(() => {
  return `${listingData.value.price} TND`;
});

// Calculate total days between dates
const calculateDays = () => {
  if (!rentalStart.value || !rentalEnd.value) {
    totalDays.value = 1;
    return;
  }

  const start = new Date(rentalStart.value);
  const end = new Date(rentalEnd.value);

  if (isNaN(start.getTime()) || isNaN(end.getTime())) {
    totalDays.value = 1;
    return;
  }

  const diffInTime = end.getTime() - start.getTime();
  const diffInDays = Math.ceil(diffInTime / (1000 * 3600 * 24));

  totalDays.value = Math.max(1, diffInDays);
};

watch([rentalStart, rentalEnd], () => {
  calculateDays();
});

const subtotal = computed(() => {
  return listingData.value.price * totalDays.value;
});

const serviceFee = 10;
const total = computed(() => {
  return subtotal.value + serviceFee;
});

const handleReserveClick = () => {
  console.log("Reservation requested for listing:", listingData.value.id);
  console.log("Rental period:", rentalStart.value, "to", rentalEnd.value);
  console.log("Total days:", totalDays.value);
  console.log("Total cost:", total.value, "TND");
  // router.push(`/checkout/${listingData.value.id}`);
};

const nextImage = () => {
  if (activeImageIndex.value < listingData.value.images.length - 1) {
    activeImageIndex.value++;
  } else {
    activeImageIndex.value = 0;
  }
};

const prevImage = () => {
  if (activeImageIndex.value > 0) {
    activeImageIndex.value--;
  } else {
    activeImageIndex.value = listingData.value.images.length - 1;
  }
};

const toggleGallery = () => {
  showAllImages.value = !showAllImages.value;
};

const setActiveImage = (index) => {
  activeImageIndex.value = index;
};

const fetchListingData = async () => {
  try {
    isLoading.value = true;
    const response = await api.get(`/api/public-listings/${id}`);

    if (response?.data?.data) {
      const data = response.data.data;
      const processedImages = extractImages(data);

      if (processedImages.length === 0) {
        processedImages.push({
          src: "/images/fallback-image.jpg",
          alt: "Product image placeholder",
        });
      }

      listingData.value = {
        id: data.id || data.Id || "",
        name: data.Name || data.name || "",
        price: data.Price || data.price || 0,
        description: data.Description || data.description || "",
        images: processedImages,
        user_id: data.User_id || data.user_id || "",
        user_name: data.user_name || "Item Owner",
        joined_since: data.Joined_since || data.joined_since || "",
        avatar: data.avatar || "",
      };
    } else {
      throw new Error("Failed to load product details");
    }
  } catch (err) {
    error.value = `Error loading product details: ${err.message}`;
    console.error("Error fetching listing data:", err);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <DefaultLayout>
    <div class="bg-white">
      <!-- Loading state -->
      <div v-if="isLoading" class="py-12 text-center">
        <div class="animate-pulse flex flex-col items-center">
          <div class="h-8 w-8 bg-indigo-200 rounded-full mb-4"></div>
          <p class="text-gray-500">Loading product details...</p>
        </div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="py-12 text-center">
        <p class="text-red-500">{{ error }}</p>
        <button
          @click="router.push('/home')"
          class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
        >
          Return to listings
        </button>
      </div>

      <!-- Content when loaded successfully -->
      <div v-else class="pt-6">
        <!-- Breadcrumb navigation -->
        <nav aria-label="Breadcrumb">
          <ol
            role="list"
            class="mx-auto flex max-w-2xl items-center space-x-2 px-4 sm:px-6 lg:max-w-7xl lg:px-8"
          >
            <li class="text-sm">
              <router-link
                to="/home"
                class="font-medium text-gray-500 hover:text-gray-600"
              >
                Listings
              </router-link>
            </li>
            <li class="text-sm">
              <span class="mx-2 text-gray-400">/</span>
              <span class="font-medium text-gray-900">{{
                listingData.name
              }}</span>
            </li>
          </ol>
        </nav>

        <!-- Product Title (Airbnb style - title appears before gallery) -->
        <div
          class="mx-auto max-w-2xl px-4 sm:px-6 lg:max-w-7xl lg:px-8 pt-4 pb-2"
        >
          <h1
            class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl"
          >
            {{ listingData.name }}
          </h1>

          <!-- Rating and Host info in the same row (Airbnb style) -->
          <div class="flex items-center justify-between mt-2">
            <div class="flex items-center">
              <div class="flex items-center">
                <StarIcon class="h-4 w-4 text-indigo-500" aria-hidden="true" />
                <span class="ml-1 text-sm text-gray-700">{{
                  reviews.average
                }}</span>
              </div>
              <span class="mx-2 text-gray-400">·</span>
              <a
                :href="reviews.href"
                class="text-sm font-medium text-gray-700 hover:underline"
              >
                {{ reviews.totalCount }} reviews
              </a>
              <span class="mx-2 text-gray-400">·</span>
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">
                  {{ listingData.user_name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Airbnb-Style Image Gallery -->
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:max-w-7xl lg:px-8 mt-4">
          <!-- Regular gallery view -->
          <div v-if="!showAllImages" class="relative">
            <!-- Gallery Grid Layout (Airbnb style) -->
            <div class="grid grid-cols-4 gap-2 rounded-xl overflow-hidden h-96">
              <!-- Main large image -->
              <div class="col-span-2 row-span-2 relative">
                <img
                  :src="listingData.images[0].src"
                  :alt="listingData.images[0].alt"
                  class="h-full w-full object-cover object-center"
                  loading="eager"
                />
              </div>

              <!-- Secondary images -->
              <div
                v-for="(image, index) in listingData.images.slice(1, 5)"
                :key="index"
                class="overflow-hidden"
              >
                <img
                  :src="image.src"
                  :alt="image.alt"
                  class="h-full w-full object-cover object-center"
                  loading="lazy"
                />
              </div>

              <!-- Show all photos button -->
              <button
                @click="toggleGallery"
                class="absolute bottom-4 right-4 bg-white px-4 py-2 rounded-lg text-sm font-medium text-gray-900 shadow-md hover:bg-gray-100 transition-colors duration-200 flex items-center"
              >
                <span>Show all photos</span>
              </button>
            </div>
          </div>

          <!-- Fullscreen gallery modal -->
          <div
            v-if="showAllImages"
            class="fixed inset-0 bg-black z-50 flex flex-col"
          >
            <!-- Gallery header -->
            <div class="p-4 flex justify-between items-center">
              <button
                @click="toggleGallery"
                class="text-white font-medium flex items-center hover:text-gray-300"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 mr-1"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
                Close
              </button>
              <div class="text-white">
                {{ activeImageIndex + 1 }} / {{ listingData.images.length }}
              </div>
            </div>

            <!-- Main image slide -->
            <div
              class="flex-grow flex items-center justify-center p-4 relative"
            >
              <img
                :src="listingData.images[activeImageIndex].src"
                :alt="listingData.images[activeImageIndex].alt"
                class="max-h-full max-w-full object-contain"
              />

              <!-- Navigation buttons -->
              <button
                @click="prevImage"
                class="absolute left-6 p-2 rounded-full bg-white/30 hover:bg-white/50 text-black"
                aria-label="Previous image"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                  />
                </svg>
              </button>
              <button
                @click="nextImage"
                class="absolute right-6 p-2 rounded-full bg-white/30 hover:bg-white/50 text-black"
                aria-label="Next image"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                  />
                </svg>
              </button>
            </div>

            <!-- Thumbnail navigation -->
            <div class="p-4 overflow-x-auto">
              <div class="flex space-x-2">
                <button
                  v-for="(image, index) in listingData.images"
                  :key="index"
                  @click="setActiveImage(index)"
                  class="flex-shrink-0 w-16 h-16 overflow-hidden rounded"
                  :class="activeImageIndex === index ? 'ring-2 ring-white' : ''"
                >
                  <img
                    :src="image.src"
                    :alt="image.alt"
                    class="h-full w-full object-cover object-center"
                  />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Product info section -->
        <div
          class="mx-auto max-w-2xl px-4 pt-10 pb-16 sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-3 lg:gap-x-8 lg:px-8 lg:pt-16 lg:pb-24"
        >
          <!-- Left column: Description and details -->
          <div class="lg:col-span-2 lg:pr-8">
            <!-- Host information (Airbnb style) -->
            <div class="flex items-center border-b border-gray-200 pb-6">
              <div class="relative flex-shrink-0">
                <div
                  v-if="listingData.avatar"
                  class="h-14 w-14 rounded-full overflow-hidden"
                >
                  <img
                    :src="listingData.avatar"
                    alt="Host"
                    class="h-full w-full object-cover"
                  />
                </div>
                <UserCircleIcon v-else class="h-14 w-14 text-gray-400" />
              </div>
              <div class="ml-4">
                <h2 class="text-lg font-medium text-gray-900">
                  Item provided by {{ listingData.user_name }}
                </h2>
                <p class="text-sm text-gray-500">
                  Host since
                  {{ listingData.joined_since || "Information not available" }}
                </p>
              </div>
            </div>

            <!-- Property highlights (Airbnb style) -->
            <div class="py-6 border-b border-gray-200">
              <div class="grid grid-cols-2 gap-6">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    />
                  </svg>
                  <span class="ml-3 text-gray-600">Complete item</span>
                </div>
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span class="ml-3 text-gray-600"
                    >Instant booking available</span
                  >
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="py-6">
              <h3 class="text-lg font-medium text-gray-900">About this item</h3>
              <div class="mt-4 space-y-6">
                <p class="text-base text-gray-700">
                  {{ listingData.description }}
                </p>
              </div>
            </div>
          </div>

          <!-- Right column: Booking panel -->
          <div class="mt-10 lg:mt-0">
            <div
              class="sticky top-6 rounded-xl border border-gray-200 shadow-lg overflow-hidden bg-white p-6"
            >
              <!-- Price display -->
              <div class="flex items-baseline">
                <span class="text-2xl font-bold tracking-tight text-gray-900">{{
                  formattedPrice
                }}</span>
                <span class="ml-1 text-lg text-gray-700">/ day</span>
              </div>

              <!-- Reviews summary -->
              <div class="mt-2 flex items-center">
                <div class="flex items-center">
                  <StarIcon
                    v-for="rating in [0, 1, 2, 3, 4]"
                    :key="rating"
                    :class="[
                      reviews.average > rating
                        ? 'text-indigo-500'
                        : 'text-gray-200',
                      'h-4 w-4 shrink-0',
                    ]"
                    aria-hidden="true"
                  />
                </div>
                <span class="ml-1 text-sm text-gray-700"
                  >{{ reviews.totalCount }} reviews</span
                >
              </div>

              <!-- Booking form -->
              <form class="mt-6" @submit.prevent="handleReserveClick">
                <!-- Date pickers -->
                <div class="border border-gray-300 rounded-lg overflow-hidden">
                  <div class="grid grid-cols-2 divide-x divide-gray-300">
                    <div class="p-3">
                      <label class="block text-xs text-gray-700 font-medium"
                        >RENTAL START</label
                      >
                      <input
                        v-model="rentalStart"
                        type="date"
                        class="w-full text-sm border-none p-0 mt-1 focus:ring-0"
                      />
                    </div>
                    <div class="p-3">
                      <label class="block text-xs text-gray-700 font-medium"
                        >RENTAL END</label
                      >
                      <input
                        v-model="rentalEnd"
                        type="date"
                        class="w-full text-sm border-none p-0 mt-1 focus:ring-0"
                      />
                    </div>
                  </div>
                </div>

                <!-- Reserve button -->
                <button
                  type="submit"
                  class="mt-6 w-full px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
                >
                  Reserve
                </button>
              </form>

              <!-- Price breakdown -->
              <div class="mt-6">
                <div class="flex justify-between py-2">
                  <span class="text-gray-600"
                    >{{ formattedPrice }} x {{ totalDays }}
                    {{ totalDays === 1 ? "day" : "days" }}</span
                  >
                  <span>{{ subtotal }} TND</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="text-gray-600">Service fee</span>
                  <span>{{ serviceFee }} TND</span>
                </div>
                <div
                  class="flex justify-between py-2 font-semibold border-t border-gray-200 mt-2 pt-2"
                >
                  <span>Total</span>
                  <span>{{ total }} TND</span>
                </div>
              </div>
            </div>
          </div>
          <!-- map container -->
          <div
            ref="mapContainer"
            class="border border-gray-300 rounded-lg overflow-hidden h-96 lg:col-span-3 mt-8"
          ></div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
