<script setup>
import { onMounted, ref, computed, watch } from "vue";
import DefaultLayout from "../../components/DefaultLayout.vue";
import api from "../../axios";
import CategoryBar from "../../components/CategoryBar.vue";
import SearchForm from "../../components/SearchForm.vue";

const products = ref([]);
const selectedCategory = ref(null);
const isLoading = ref(true);
const error = ref(null);
const searchQuery = ref("");
const priceRange = ref({
  min: 0,
  max: 1000,
  current: [0, 1000],
});
const distanceFilter = ref({
  enabled: false,
  max: 1000,
  current: 50,
});
const clientLat = ref(null);
const clientLng = ref(null);

const getLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        clientLat.value = position.coords.latitude;
        clientLng.value = position.coords.longitude;
        fetchProducts({ lat: clientLat.value, lng: clientLng.value });
      },
      (err) => {
        console.log("Location error:", err);
        fetchProducts();
      }
    );
  } else {
    console.error("Geolocation is not supported by this browser.");
  }
};

const calculateDistance = (lat1, lon1, lat2, lon2) => {
  if (!lat1 || !lon1 || !lat2 || !lon2) return null;

  const latFrom = parseFloat(lat1);
  const lonFrom = parseFloat(lon1);
  const latTo = parseFloat(lat2);
  const lonTo = parseFloat(lon2);

  if (isNaN(latFrom) || isNaN(lonFrom) || isNaN(latTo) || isNaN(lonTo)) {
    return null;
  }

  const R = 6371;
  const dLat = ((latTo - latFrom) * Math.PI) / 180;
  const dLon = ((lonTo - lonFrom) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((latFrom * Math.PI) / 180) *
      Math.cos((latTo * Math.PI) / 180) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  const distance = R * c;

  return distance;
};

const fetchProducts = async (filters = {}) => {
  try {
    isLoading.value = true;
    error.value = null;

    const params = {};

    if (filters.category) {
      params.category = filters.category;
    }

    if (distanceFilter.value.enabled && clientLat.value && clientLng.value) {
      params.lat = clientLat.value;
      params.lng = clientLng.value;
      params.radius = distanceFilter.value.current;
    } else if (filters.lat && filters.lng) {
      params.lat = filters.lat;
      params.lng = filters.lng;
    }

    const response = await api.get("/api/public-listings", { params });
    const allProducts = response.data.data.map((product) => {
      let distance = null;
      if (clientLat.value && clientLng.value && product.lat && product.lng) {
        distance = calculateDistance(
          clientLat.value,
          clientLng.value,
          product.lat,
          product.lng
        );
      }

      return {
        ...product,
        imageSrc: product.images?.[0] || "/images/fallback-image.jpg",
        distance: distance,
        formattedPrice: product.price
          ? parseFloat(product.price).toFixed(2)
          : null,
      };
    });

    if (allProducts.length > 0) {
      const prices = allProducts
        .map((p) => parseFloat(p.price))
        .filter((p) => !isNaN(p));

      if (prices.length > 0) {
        priceRange.value.min = Math.floor(Math.min(...prices));
        priceRange.value.max = Math.ceil(Math.max(...prices));

        if (!filters.category) {
          priceRange.value.current = [
            priceRange.value.min,
            priceRange.value.max,
          ];
        }
      }
    }

    if (filters.category) {
      products.value = allProducts.filter(
        (product) => product.cat_title === filters.category
      );
    } else {
      products.value = allProducts;
    }

    console.log(`Loaded ${products.value.length} products`);
    console.log(
      `Products with distance: ${
        products.value.filter((p) => p.distance !== null).length
      }`
    );
  } catch (err) {
    error.value = "Failed to load products. Please try again later.";
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

watch(selectedCategory, (newCategory) => {
  const filters = {};
  if (newCategory) {
    filters.category = newCategory;
  }
  fetchProducts(filters);
});

watch(
  [clientLat, clientLng, () => distanceFilter.value.enabled],
  () => {
    if (distanceFilter.value.enabled) {
      fetchProducts();
    }
  },
  { immediate: true }
);

onMounted(() => {
  getLocation();
  fetchProducts();
});

const filteredProducts = computed(() => {
  if (!products.value || !products.value.length) return [];

  return products.value.filter((product) => {
    if (!product) return false;
    const matchesSearch =
      !searchQuery.value ||
      [product.name, product.description].some((field) =>
        field
          ? field.toLowerCase().includes(searchQuery.value.toLowerCase())
          : false
      );
    const price = parseFloat(product.price);
    const matchesPrice =
      !isNaN(price) &&
      price >= priceRange.value.current[0] &&
      price <= priceRange.value.current[1];
    const matchesDistance =
      !distanceFilter.value.enabled ||
      product.distance === null ||
      product.distance <= distanceFilter.value.current;

    return matchesSearch && matchesPrice && matchesDistance;
  });
});

const statusMessage = computed(() => {
  if (isLoading.value) return "Loading products...";
  if (error.value) return `Error: ${error.value}`;
  if (products.value.length === 0) {
    return "No products available at this time.";
  }

  if (filteredProducts.value.length === 0) {
    const hasActiveFilters =
      selectedCategory.value !== null ||
      searchQuery.value.trim() !== "" ||
      priceRange.value.current[0] > priceRange.value.min ||
      priceRange.value.current[1] < priceRange.value.max ||
      distanceFilter.value.enabled;

    return hasActiveFilters
      ? "No products match your criteria. Try adjusting your filters."
      : "No products available at this time.";
  }

  return null;
});

const handleCategorySelect = (category) => {
  selectedCategory.value = category;
};

const handleSearch = (query) => {
  searchQuery.value = query;
};

const handlePriceChange = (values) => {
  priceRange.value.current = values;
};

const toggleDistanceFilter = () => {
  distanceFilter.value.enabled = !distanceFilter.value.enabled;
};

const handleDistanceChange = (value) => {
  distanceFilter.value.current = value;
};

const resetFilters = () => {
  selectedCategory.value = null;
  searchQuery.value = "";
  if (products.value.length > 0) {
    priceRange.value.current = [priceRange.value.min, priceRange.value.max];
  }
  distanceFilter.value = {
    enabled: false,
    max: 100,
    current: 50,
  };
  fetchProducts();
};

const debugFilters = () => {
  console.log("Filter status:", {
    hasProducts: products.value.length > 0,
    hasFilteredProducts: filteredProducts.value.length > 0,
    categorySelected: selectedCategory.value !== null,
    categoryValue: selectedCategory.value,

    hasSearchQuery: searchQuery.value.trim() !== "",
    searchQueryValue: searchQuery.value,

    priceMinChanged: priceRange.value.current[0] > priceRange.value.min,
    priceMaxChanged: priceRange.value.current[1] < priceRange.value.max,
    priceValues: {
      current: priceRange.value.current,
      min: priceRange.value.min,
      max: priceRange.value.max,
    },

    distanceEnabled: distanceFilter.value.enabled,
    distanceValue: distanceFilter.value.current,
  });
};

watch(
  [
    () => filteredProducts.value.length,
    selectedCategory,
    searchQuery,
    () => priceRange.value.current,
    () => distanceFilter.value.enabled,
  ],
  debugFilters
);

onMounted(() => {
  setTimeout(debugFilters, 1000);
});
</script>

<template>
  <DefaultLayout>
    <!-- Hero section with search -->
    <header class="bg-gradient-to-r from-indigo-600 to-blue-500 text-white">
      <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold mb-6">Find What You Need</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
          Rent quality items at affordable daily rates
        </p>
        <div class="max-w-xl mx-auto">
          <SearchForm @search="handleSearch" :initialQuery="searchQuery" />
        </div>
      </div>
    </header>

    <!-- Main content area -->
    <main class="bg-gray-50">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <!-- Category selection - enhanced with more rounded appearance -->
        <div
          class="mb-8 sticky top-0 z-10 bg-gray-50 py-3 px-4 rounded-xl shadow-md"
        >
          <CategoryBar
            @category-selected="handleCategorySelect"
            :selectedCategory="selectedCategory"
          />

          <!-- Price range filter -->
          <div class="mt-6 px-2">
            <h3 class="text-md font-medium text-gray-700 mb-3">
              Price Range (TND)
            </h3>
            <div class="flex items-center gap-4">
              <input
                type="range"
                :min="priceRange.min"
                :max="priceRange.max"
                v-model.number="priceRange.current[0]"
                @input="
                  handlePriceChange([
                    priceRange.current[0],
                    priceRange.current[1],
                  ])
                "
                class="w-full accent-indigo-600"
              />
              <span class="text-sm text-gray-600">to</span>
              <input
                type="range"
                :min="priceRange.min"
                :max="priceRange.max"
                v-model.number="priceRange.current[1]"
                @input="
                  handlePriceChange([
                    priceRange.current[0],
                    priceRange.current[1],
                  ])
                "
                class="w-full accent-indigo-600"
              />
            </div>
            <div class="flex justify-between mt-2 text-sm text-gray-600">
              <span>{{ priceRange.current[0] }} TND</span>
              <span>{{ priceRange.current[1] }} TND</span>
            </div>
          </div>

          <div class="mt-6 px-2">
            <div class="flex items-center justify-between">
              <h3 class="text-md font-medium text-gray-700">Distance Filter</h3>
              <label class="inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="distanceFilter.enabled"
                  class="sr-only peer"
                />
                <div
                  class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"
                ></div>
              </label>
            </div>

            <div
              v-if="distanceFilter.enabled"
              class="mt-3"
              :class="{ 'opacity-50': !clientLat || !clientLng }"
            >
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-600">Max distance:</span>
                  <input
                    type="number"
                    min="1"
                    :max="distanceFilter.max"
                    v-model.number="distanceFilter.current"
                    @input="handleDistanceChange(distanceFilter.current)"
                    class="w-20 px-2 py-1 border rounded-md text-sm"
                    :disabled="!clientLat || !clientLng"
                  />
                  <span class="text-sm text-gray-600">km</span>
                </div>
                <span
                  v-if="!clientLat || !clientLng"
                  class="text-xs text-red-500"
                  >Location required</span
                >
              </div>
              <input
                type="range"
                min="1"
                :max="distanceFilter.max"
                v-model.number="distanceFilter.current"
                @input="handleDistanceChange(distanceFilter.current)"
                class="w-full accent-indigo-600"
                :disabled="!clientLat || !clientLng"
              />
              <div class="flex justify-between mt-1 text-sm text-gray-600">
                <span>1 km</span>
                <span>{{ distanceFilter.max }} km</span>
              </div>
            </div>
          </div>

          <!-- Active filters display -->
          <div
            v-if="
              selectedCategory ||
              searchQuery ||
              priceRange.current[0] > priceRange.min ||
              priceRange.current[1] < priceRange.max ||
              distanceFilter.enabled
            "
            class="mt-4 flex flex-wrap items-center gap-2 text-sm"
          >
            <span class="text-gray-600 mr-2">Active filters:</span>

            <div
              v-if="selectedCategory"
              class="rounded-full bg-indigo-100 px-3 py-1 flex items-center"
            >
              <span class="text-indigo-800"
                >Category: {{ selectedCategory }}</span
              >
              <button
                @click="selectedCategory = null"
                class="ml-2 text-indigo-800 hover:text-indigo-600"
              >
                &times;
              </button>
            </div>

            <div
              v-if="searchQuery"
              class="rounded-full bg-indigo-100 px-3 py-1 flex items-center"
            >
              <span class="text-indigo-800">Search: "{{ searchQuery }}"</span>
              <button
                @click="searchQuery = ''"
                class="ml-2 text-indigo-800 hover:text-indigo-600"
              >
                &times;
              </button>
            </div>

            <div
              v-if="
                priceRange.current[0] > priceRange.min ||
                priceRange.current[1] < priceRange.max
              "
              class="rounded-full bg-indigo-100 px-3 py-1 flex items-center"
            >
              <span class="text-indigo-800"
                >Price: {{ priceRange.current[0] }} -
                {{ priceRange.current[1] }} TND</span
              >
              <button
                @click="priceRange.current = [priceRange.min, priceRange.max]"
                class="ml-2 text-indigo-800 hover:text-indigo-600"
              >
                &times;
              </button>
            </div>

            <div
              v-if="distanceFilter.enabled"
              class="rounded-full bg-indigo-100 px-3 py-1 flex items-center"
            >
              <span class="text-indigo-800"
                >Distance: ≤ {{ distanceFilter.current }} km</span
              >
              <button
                @click="distanceFilter.enabled = false"
                class="ml-2 text-indigo-800 hover:text-indigo-600"
              >
                &times;
              </button>
            </div>

            <button
              @click="resetFilters"
              class="ml-2 text-indigo-600 hover:text-indigo-800 font-medium text-sm"
            >
              Clear all
            </button>
          </div>
        </div>

        <!-- Products section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">
              {{ selectedCategory ? selectedCategory : "Today's picks" }}
              <span
                class="text-gray-500 text-lg font-normal ml-2"
                v-if="filteredProducts.length"
              >
                ({{ filteredProducts.length }} items)
              </span>
            </h2>
          </div>

          <!-- Loading or error states -->
          <div v-if="statusMessage" class="py-12 text-center">
            <div
              v-if="isLoading"
              class="animate-pulse flex flex-col items-center"
            >
              <div class="h-8 w-8 bg-indigo-200 rounded-full mb-4"></div>
              <p class="text-gray-500">{{ statusMessage }}</p>
            </div>
            <p v-else class="text-gray-500">{{ statusMessage }}</p>
          </div>

          <!-- Product grid -->
          <div
            v-else
            class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
          >
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              class="group relative bg-white rounded-lg overflow-hidden transition-all duration-300 hover:shadow-lg"
            >
              <a :href="`/product/${product.id}`" class="block">
                <div class="aspect-square bg-gray-200 overflow-hidden">
                  <img
                    :src="product.imageSrc"
                    :alt="product.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                </div>

                <div class="p-4">
                  <div class="flex justify-between">
                    <h3 class="text-lg font-medium text-gray-900 line-clamp-1">
                      {{ product.name }}
                    </h3>
                  </div>

                  <p v-if="product.category" class="mt-1 text-sm text-gray-500">
                    {{ product.category }}
                  </p>

                  <p class="mt-2 text-lg font-semibold text-indigo-600">
                    {{ product.formattedPrice ?? product.price }} TND<span
                      class="text-sm font-normal text-gray-500"
                      >/day</span
                    >
                  </p>

                  <!-- Display distance if available -->
                  <p
                    v-if="product.distance !== null"
                    class="mt-1 text-sm text-gray-500"
                  >
                    {{ Math.round(product.distance * 10) / 10 }} km away
                  </p>

                  <div class="mt-4 flex justify-between items-center">
                    <span
                      v-if="product.availability"
                      class="text-sm font-medium text-green-600"
                    >
                      Available
                    </span>
                    <span
                      class="text-sm text-indigo-600 font-medium flex items-center"
                    >
                      View details
                      <span class="ml-1">→</span>
                    </span>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </main>
  </DefaultLayout>
</template>
