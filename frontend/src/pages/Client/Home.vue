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

const fetchProducts = async (filters = {}) => {
  try {
    isLoading.value = true;
    error.value = null;
    const response = await api.get("/api/public-listings");

    const allProducts = response.data.data.map((product) => ({
      ...product,
      imageSrc:
        product.images && product.images.length
          ? product.images[0]
          : "/images/fallback-image.jpg",
    }));

    if (allProducts.length > 0) {
      const prices = allProducts.map((p) => p.price);
      priceRange.value.min = Math.floor(Math.min(...prices));
      priceRange.value.max = Math.ceil(Math.max(...prices));

      if (!filters.category) {
        priceRange.value.current = [priceRange.value.min, priceRange.value.max];
      }
    }

    if (filters.category) {
      products.value = allProducts.filter(
        (product) => product.cat_title === filters.category
      );
    } else {
      products.value = allProducts;
    }
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

onMounted(() => {
  fetchProducts();
});

const filteredProducts = computed(() => {
  if (!products.value.length) return [];
  let filtered = products.value;
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (product) =>
        product.name.toLowerCase().includes(query) ||
        product.description?.toLowerCase().includes(query)
    );
  }

  filtered = filtered.filter(
    (product) =>
      product.price >= priceRange.value.current[0] &&
      product.price <= priceRange.value.current[1]
  );

  return filtered;
});

const statusMessage = computed(() => {
  if (isLoading.value) return "Loading products...";
  if (error.value) return `Error: ${error.value}`;
  if (filteredProducts.value.length === 0) {
    if (
      selectedCategory.value ||
      searchQuery.value ||
      priceRange.value.current[0] > priceRange.value.min ||
      priceRange.value.current[1] < priceRange.value.max
    ) {
      return "No products match your criteria. Try adjusting your filters.";
    }
    return "No products available at this time.";
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

const resetFilters = () => {
  selectedCategory.value = null;
  searchQuery.value = "";
  if (products.value.length > 0) {
    priceRange.value.current = [priceRange.value.min, priceRange.value.max];
  }
  fetchProducts();
};
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

          <!-- Active filters display -->
          <div
            v-if="
              selectedCategory ||
              searchQuery ||
              priceRange.current[0] > priceRange.min ||
              priceRange.current[1] < priceRange.max
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
