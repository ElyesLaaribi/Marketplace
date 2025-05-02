<script setup>
import { defineProps, computed, ref, defineEmits } from "vue";
import SearchForm from "./SearchForm.vue";
import api from "../axios";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
const $toast = useToast();

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["userDeleted"]);

const isOpen = ref(false);
const searchFilter = ref("");
const isDeleting = ref(false);
const loading = ref(false);
const errors = ref({ name: [], email: [], password: [] });

const data = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role: "admin",
});

const getRoleBadgeClass = (role) => {
  switch (role.toLowerCase()) {
    case "admin":
      return "bg-blue-100 text-blue-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const filteredItems = computed(() => {
  if (searchFilter.value !== "") {
    return props.items.filter(
      (item) =>
        String(item.id).toLowerCase().includes(searchFilter.value) ||
        item.Name.includes(searchFilter.value) ||
        item.Email.includes(searchFilter.value)
    );
  }
  return props.items;
});

const handleSearch = (search) => {
  searchFilter.value = search;
};

const deleteUser = async (id) => {
  try {
    isDeleting.value = true;
    await api.delete(`/api/admins/${id}`);
    emit("userDeleted", id);
    $toast.success("Admin deleted successfully!");
  } catch (error) {
    console.error("Error deleting user:", error);
    $toast.error("Error deleting user");
  } finally {
    isDeleting.value = false;
  }
};

const submit = async () => {
  loading.value = true;
  errors.value = { name: [], email: [], password: [] };
  try {
    await api.get("/sanctum/csrf-cookie");
    console.log("Sending payload:", data.value);
    const response = await api.post("/api/add-admin", data.value);
    console.log("Response from server:", response);
    alert("Admin account added successfully!");
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
  <div
    class="bg-white relative rounded-lg shadow-sm overflow-hidden border border-gray-300"
  >
    <div class="flex items-center justify-between p-4">
      <SearchForm @search="handleSearch" />
      <button
        class="rounded-md bg-[#135CA5] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#28BBDD] transition mr-4"
        @click="isOpen = true"
      >
        Add admin
      </button>
    </div>

    <teleport to="body">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div class="bg-white rounded-lg shadow-lg max-w-3xl w-full p-6">
          <form @submit.prevent="submit">
            <div class="space-y-6">
              <div>
                <h2 class="text-lg font-semibold text-gray-900">
                  Add new admin
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                  This will create a new admin account.
                </p>
              </div>

              <div>
                <h2 class="text-lg font-semibold text-gray-900">
                  Personal Information
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                  Use a permanent address where you can receive mail.
                </p>
                <div
                  class="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6"
                >
                  <div>
                    <label
                      for="first-name"
                      class="block text-sm font-medium text-gray-900"
                      >Name</label
                    >
                    <input
                      type="text"
                      v-model="data.name"
                      id="first-name"
                      name="first-name"
                      autocomplete="given-name"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                      {{ errors.name[0] }}
                    </p>
                  </div>
                  <div>
                    <label
                      for="email"
                      class="block text-sm font-medium text-gray-900"
                      >Email address</label
                    >
                    <input
                      type="email"
                      v-model="data.email"
                      id="email"
                      name="email"
                      autocomplete="email"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">
                      {{ errors.email[0] }}
                    </p>
                  </div>
                  <div>
                    <label
                      for="password"
                      class="block text-sm font-medium text-gray-900"
                      >Password</label
                    >
                    <input
                      type="password"
                      v-model="data.password"
                      id="password"
                      name="password"
                      autocomplete="new-password"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
                    <p v-if="errors.password" class="text-red-500 text-xs mt-1">
                      {{ errors.password[0] }}
                    </p>
                  </div>
                  <div>
                    <label
                      for="password-confirmation"
                      class="block text-sm font-medium text-gray-900"
                      >Password confirmation</label
                    >
                    <input
                      type="password"
                      v-model="data.password_confirmation"
                      id="password-confirmation"
                      name="password-confirmation"
                      autocomplete="new-password"
                      class="bg-white mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 sm:text-sm"
                    />
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
                class="rounded-md bg-[#135CA5] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#28BBDD] focus:outline-none focus:ring-2 focus:ring-indigo-600"
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
            Name
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Email
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            User since
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Role
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Delete User
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
            <div class="text-sm font-medium text-gray-900">{{ item.Name }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ item.Email }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ item["Admin since"] }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span
              class="px-3 py-1 inline-flex text-xs leading-5 font-medium rounded-full"
              :class="getRoleBadgeClass(item.Role)"
            >
              {{ item.Role }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <button
              @click="deleteUser(item.id)"
              c
              class="text-red-600 hover:text-red-900"
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
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
