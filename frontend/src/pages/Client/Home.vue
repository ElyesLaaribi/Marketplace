<script setup>
import { onMounted, ref, computed } from "vue";
import DefaultLayout from "../../components/DefaultLayout.vue";
import api from "../../axios";
import CategoryBar from "../../components/CategoryBar.vue";

const products = ref([]);
const selectedCategory = ref(null);

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value;

  return products.value.filter(
    (product) => product.category === selectedCategory.value
  );
});

onMounted(() => {
  api.get("/api/public-listings").then((response) => {
    console.log(response.data);
    products.value = response.data.data.map((product) => {
      return {
        ...product,
        imageSrc:
          product.images && product.images.length
            ? product.images[0]
            : "fallback-image.jpg",
      };
    });
  });
});

const handleCategorySelect = (category) => {
  selectedCategory.value = category;
};
</script>

<template>
  <DefaultLayout>
    <header class="bg-gray-50 shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
          Today's picks
        </h1>
      </div>
    </header>
    <div class="mx-auto max-w-7xl">
      <CategoryBar @category-selected="handleCategorySelect" />
    </div>
    <div class="bg-white">
      <div
        class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8"
      >
        <h2 class="sr-only">Products</h2>

        <div
          class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8"
        >
          <a
            v-for="product in products"
            :key="product.id"
            :href="product.href"
            class="group"
          >
            <img
              :src="product.imageSrc"
              class="aspect-square w-full rounded-lg bg-gray-200 object-cover group-hover:opacity-75 xl:aspect-7/8"
            />
            <h3 class="mt-4 text-lg font-medium text-gray-900">
              {{ product.name }}
            </h3>
            <p class="mt-1 text-sm font-medium text-gray-700">
              {{ product.price }} TND / day
            </p>
          </a>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
