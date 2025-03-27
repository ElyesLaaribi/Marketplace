<script setup>
import { defineProps, computed, ref, defineEmits, onMounted } from "vue";
import {
  Listbox,
  ListboxButton,
  ListboxLabel,
  ListboxOption,
  ListboxOptions,
} from "@headlessui/vue";
import { ChevronUpDownIcon } from "@heroicons/vue/16/solid";
import { CheckIcon } from "@heroicons/vue/20/solid";
import SearchForm from "./SearchForm.vue";
import api from "../axios";
const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["listingDeleted", "listingEdited"]);

const isOpen = ref(false);
const isEditMode = ref(false);
const searchFilter = ref("");
const isDeleting = ref(false);
const loading = ref(false);
const errors = ref({});
const imagePreview = ref(null);
const categories = ref([]);

const data = ref({
  id: "",
  name: "",
  price: "",
  image: null,
  description: "",
});

const imageFile = ref(null);

const fetchCategories = async () => {
  try {
    const response = await api.get("/api/categories");
    if (response.data.data && Array.isArray(response.data.data)) {
      categories.value = response.data.data;
      console.log("Categories loaded successfully:", categories.value);

      if (categories.value.length > 0) {
        data.value.category_id = categories.value[0].id;
      }
    } else {
      console.error("Invalid categories data structure:", response.data);
      alert("Unexpected data format when fetching categories.");
    }
  } catch (error) {
    console.error("Error fetching categories:", error);
    if (error.response) {
      console.error("Error details:", error.response.data);
      alert(`Failed to fetch categories: ${error.response.data.message}`);
    } else {
      alert("Network error fetching categories. Please try again.");
    }
  }
};

onMounted(fetchCategories);

const filteredItems = computed(() => {
  if (searchFilter.value !== "") {
    return props.items.filter(
      (item) =>
        String(item.id)
          .toLowerCase()
          .includes(searchFilter.value.toLowerCase()) ||
        item.name.toLowerCase().includes(searchFilter.value.toLowerCase())
    );
  }
  return props.items;
});

const handleSearch = (search) => {
  searchFilter.value = search;
};

const handleImageChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    imageFile.value = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const deleteListing = async (id) => {
  try {
    isDeleting.value = true;
    await api.delete(`/api/listings/${id}`);
    emit("listingDeleted", id);
    alert("Listing deleted successfully");
    window.location.reload();
  } catch (error) {
    console.error("Error deleting listing:", error);
    alert("Failed to delete listing. Please try again.");
  } finally {
    isDeleting.value = false;
  }
};

const editCategory = (item) => {
  data.value = { ...item, category_id: item.category_id };
  imagePreview.value = item.image;
  isEditMode.value = true;
  isOpen.value = true;
};

const submit = async () => {
  loading.value = true;
  errors.value = {};

  try {
    await api.get("/sanctum/csrf-cookie");
    const formData = new FormData();

    formData.append("name", data.value.name);
    formData.append("price", data.value.price);
    formData.append("category_id", data.value.category_id);
    formData.append("description", data.value.description);

    if (imageFile.value) {
      formData.append("image", imageFile.value);
    }

    let response;
    if (isEditMode.value) {
      formData.append("_method", "PUT");
      response = await api.post(`/api/listings/${data.value.id}`, formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      emit("listingEdited", data.value);
      alert("Listing updated successfully!");
    } else {
      response = await api.post("/api/listings", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      alert("Listing added successfully!");
    }

    console.log("Response from server:", response);
    isOpen.value = false;

    data.value = {
      id: "",
      name: "",
      price: "",
      image: null,
      cat_title: "",
      category_id: "",
      description: "",
    };
    imageFile.value = null;
    imagePreview.value = null;
    isEditMode.value = false;
    window.location.reload();
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

const openNewListingForm = () => {
  data.value = {
    id: "",
    name: "",
    price: "",
    image: null,
    category_id: "",
    cat_title: "",
    description: "",
  };
  imageFile.value = null;
  imagePreview.value = null;
  isEditMode.value = false;
  isOpen.value = true;
};

defineExpose({
  categories,
});
</script>

<template>
  <div class="bg-white relative rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center justify-between p-4">
      <SearchForm @search="handleSearch" />
      <button
        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 transition"
        @click="openNewListingForm"
      >
        Add Listing
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
                  {{ isEditMode ? "Edit Listing" : "Add New Listing" }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                  {{
                    isEditMode
                      ? "This will update the listing."
                      : "This will create a new listing."
                  }}
                </p>
              </div>

              <div>
                <div
                  class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6"
                >
                  <div>
                    <label
                      for="name"
                      class="block text-sm font-medium text-gray-900"
                      >Name</label
                    >
                    <input
                      type="text"
                      v-model="data.name"
                      id="name"
                      name="name"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                      {{ errors.name[0] }}
                    </p>
                  </div>

                  <div>
                    <label
                      for="price"
                      class="block text-sm font-medium text-gray-900"
                      >Price</label
                    >
                    <input
                      type="number"
                      v-model="data.price"
                      id="price"
                      name="price"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
                    <p v-if="errors.price" class="text-red-500 text-xs mt-1">
                      {{ errors.price[0] }}
                    </p>
                  </div>

                  <div>
                    <Listbox
                      v-model="data.category_id"
                      as="div"
                      class="sm:col-span-2"
                    >
                      <ListboxLabel
                        class="block text-sm font-medium text-gray-900 mb-1"
                      >
                        Category
                      </ListboxLabel>
                      <div class="relative mt-2">
                        <ListboxButton
                          class="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-md ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                          <span class="block truncate">
                            {{
                              categories.find(
                                (cat) => cat.id === data.category_id
                              )?.cat_title || "Select a category"
                            }}
                          </span>
                          <span
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                          >
                            <ChevronUpDownIcon
                              class="h-5 w-5 text-gray-400"
                              aria-hidden="true"
                            />
                          </span>
                        </ListboxButton>

                        <transition
                          leave-active-class="transition ease-in duration-100"
                          leave-from-class="opacity-100"
                          leave-to-class="opacity-0"
                        >
                          <ListboxOptions
                            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                          >
                            <ListboxOption
                              v-for="category in categories"
                              :key="category.id"
                              :value="category.id"
                              v-slot="{ active, selected }"
                              as="template"
                            >
                              <li
                                :class="[
                                  active
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-gray-900',
                                  'relative cursor-default select-none py-2 pl-3 pr-9',
                                ]"
                              >
                                <span
                                  :class="[
                                    selected ? 'font-semibold' : 'font-normal',
                                    'block truncate',
                                  ]"
                                >
                                  {{ category.cat_title }}
                                </span>

                                <span
                                  v-if="selected"
                                  :class="[
                                    active ? 'text-white' : 'text-indigo-600',
                                    'absolute inset-y-0 right-0 flex items-center pr-4',
                                  ]"
                                >
                                  <CheckIcon
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                  />
                                </span>
                              </li>
                            </ListboxOption>
                          </ListboxOptions>
                        </transition>
                      </div>
                    </Listbox>
                    <p
                      v-if="errors.category_id"
                      class="text-red-500 text-xs mt-1"
                    >
                      {{ errors.category_id[0] }}
                    </p>
                  </div>

                  <div class="sm:col-span-2">
                    <label
                      for="description"
                      class="block text-sm font-medium text-gray-900"
                    >
                      Description
                    </label>
                    <textarea
                      v-model="data.description"
                      id="description"
                      name="description"
                      rows="3"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    ></textarea>
                    <p
                      v-if="errors.description"
                      class="text-red-500 text-xs mt-1"
                    >
                      {{ errors.description[0] }}
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
                {{ isEditMode ? "Update" : "Create" }}
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
            Image
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Name
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Price
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Category
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Actions
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
            <img
              :src="item.image"
              alt="Listing Image"
              class="h-16 w-16 object-cover rounded-md"
            />
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">
              {{ item.name }}
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ item.price }} TND</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">
              {{ item.cat_title }}
            </div>
          </td>

          <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="flex justify-center space-x-2">
              <button
                @click="editCategory(item)"
                class="bg-blue-500 text-white p-1 rounded-full hover:bg-blue-700 transition"
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
                  <path
                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                    transform="scale(0.8) translate(3,3)"
                  ></path>
                  <path
                    d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                    transform="scale(0.8) translate(3,3)"
                  ></path>
                </svg>
              </button>
              <button
                @click="deleteListing(item.id)"
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
                  <polyline
                    points="3 6 5 6 21 6"
                    transform="scale(0.8) translate(3,3)"
                  ></polyline>
                  <path
                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                    transform="scale(0.8) translate(3,3)"
                  ></path>
                  <line
                    x1="10"
                    y1="11"
                    x2="10"
                    y2="17"
                    transform="scale(0.8) translate(3,3)"
                  ></line>
                  <line
                    x1="14"
                    y1="11"
                    x2="14"
                    y2="17"
                    transform="scale(0.8) translate(3,3)"
                  ></line>
                </svg>
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
