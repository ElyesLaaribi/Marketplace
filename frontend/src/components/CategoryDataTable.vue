<script setup>
import { defineProps, computed, ref, defineEmits } from "vue";
import SearchForm from "./SearchForm.vue";
import api from "../axios";

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["categoryDeleted"]);

const isOpen = ref(false);
const searchFilter = ref("");
const isDeleting = ref(false);
const loading = ref(false);
const errors = ref({ cat_title: [] });

const data = ref({
  id: "",
  cat_title: "",
  created_at: "",
});

const filteredItems = computed(() => {
  if (searchFilter.value !== "") {
    return props.items.filter(
      (item) =>
        String(item.id)
          .toLowerCase()
          .includes(searchFilter.value.toLowerCase()) ||
        item.cat_title.toLowerCase().includes(searchFilter.value.toLowerCase())
    );
  }
  return props.items;
});

const handleSearch = (search) => {
  searchFilter.value = search;
};

const deleteCategory = async (id) => {
  try {
    isDeleting.value = true;
    await api.delete(`/api/categories/${id}`);
    emit("categoryDeleted", id);
    alert("Category deleted successfully");
  } catch (error) {
    console.error("Error deleting category:", error);
    alert("Failed to delete category. Please try again.");
  } finally {
    isDeleting.value = false;
  }
};

const submit = async () => {
  loading.value = true;
  errors.value = { cat_title: [] };
  try {
    await api.get("/sanctum/csrf-cookie");
    console.log("Sending payload:", data.value);
    const response = await api.post("/api/categories", data.value);
    console.log("Response from server:", response);
    alert("Category added successfully!");
    isOpen.value = false;
  } catch (error) {
    console.log("Error response:", error.response);
    if (error.response) {
      if (error.response.status === 422) {
        errors.value = error.response.data.errors;
        console.error("Validation errors:", errors.value);
      } else if (error.response.status === 401) {
        console.error("Unauthorized - Possible Sanctum issue");
      } else {
        console.error("Other error:", error.response.data);
      }
    } else {
      console.error("Request failed without a response", error);
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="bg-white relative rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center justify-between p-4">
      <SearchForm @search="handleSearch" />
      <button
        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition"
        @click="isOpen = true"
      >
        Add category
      </button>
    </div>

    <teleport to="body">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm"
      >
        <div class="bg-gray-100 rounded-lg shadow-lg max-w-3xl w-full p-6">
          <form @submit.prevent="submit">
            <div class="space-y-6">
              <div>
                <h2 class="text-lg font-semibold text-gray-900">
                  Add new category
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                  This will create a new category.
                </p>
              </div>

              <div>
                <div
                  class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6"
                >
                  <div>
                    <label
                      for="cat_title"
                      class="block text-sm font-medium text-gray-900"
                      >Category title</label
                    >
                    <input
                      type="text"
                      v-model="data.cat_title"
                      id="cat_title"
                      name="cat_title"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
                    <p
                      v-if="errors.cat_title"
                      class="text-red-500 text-xs mt-1"
                    >
                      {{ errors.cat_title[0] }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-x-4">
              <button
                type="button"
                class="text-sm font-semibold text-gray-900"
                @click="isOpen = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600"
              >
                Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>

    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-100">
        <tr>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            ID
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Category title
          </th>

          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Created at
          </th>

          <th
            scope="col"
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Delete Category
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr
          v-for="item in filteredItems"
          :key="item.id"
          class="hover:bg-gray-50"
        >
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-semibold text-gray-900">{{ item.id }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">
              {{ item.cat_title }}
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">
              {{ item.created_at }}
            </div>
          </td>

          <td class="px-6 py-4 whitespace-nowrap text-center">
            <button
              @click="deleteCategory(item.id)"
              class="bg-red-500 text-white p-1 rounded-full hover:bg-red-900 transition"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="3 6 5 6 21 6"></polyline>
                <path
                  d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                ></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
              </svg>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
