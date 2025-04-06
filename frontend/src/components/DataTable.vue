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

const searchFilter = ref("");
const isDeleting = ref(false);

const getRoleBadgeClass = (role) => {
  switch (role.toLowerCase()) {
    case "client":
      return "bg-blue-100 text-blue-600";
    case "lessor":
      return "bg-green-100 text-green-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

const filteredItems = computed(() => {
  if (searchFilter.value != "") {
    return props.items.filter(
      (item) =>
        String(item.id).toLowerCase().includes(searchFilter.value) ||
        item.Name.includes(searchFilter.value) ||
        item.Role.includes(searchFilter.value)
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
    await api.delete(`/api/users/${id}`);
    emit("userDeleted", id);
    $toast.success("User deleted successfully!");
  } catch (error) {
    console.error("Error deleting user:", error);
    $toast.error("Failed to delete user. Please try again.");
  } finally {
    isDeleting.value = false;
  }
};

const emit = defineEmits(["userDeleted"]);
</script>

<template>
  <div class="bg-white relative rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center justify-between">
      <SearchForm @search="handleSearch" />
    </div>

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
            <div class="text-sm text-gray-900">{{ item["User since"] }}</div>
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
