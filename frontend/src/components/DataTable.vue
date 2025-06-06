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
const roleFilter = ref("");
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
  let filtered = props.items;

  if (searchFilter.value !== "") {
    filtered = filtered.filter(
      (item) =>
        String(item.id).toLowerCase().includes(searchFilter.value) ||
        item.Name.includes(searchFilter.value) ||
        item.Role.includes(searchFilter.value)
    );
  }

  if (roleFilter.value !== "") {
    filtered = filtered.filter((item) => {
      if (!item.Role) return false;

      const itemRole = String(item.Role).toLowerCase();
      const filterRole = roleFilter.value.toLowerCase();

      return itemRole.includes(filterRole);
    });
  }

  return filtered;
});

const handleSearch = (search) => {
  searchFilter.value = search;
  roleFilter.value = "";
};

const filterByRole = (role) => {
  if (roleFilter.value === role) {
    roleFilter.value = "";
  } else {
    roleFilter.value = role;
  }
  searchFilter.value = "";
};

const deleteUser = async (id) => {
  if (
    !confirm(
      "Are you sure you want to delete this user? This action cannot be undone."
    )
  )
    return;

  try {
    isDeleting.value = true;
    await api.delete(`/api/users/${id}`);
    emit("userDeleted", id);
    $toast.success("User deleted successfully!");
    setTimeout(() => {
      window.location.reload();
    }, 1500);
  } catch (error) {
    console.error("Error deleting user:", error);
    $toast.error("Error deleting user");
  } finally {
    isDeleting.value = false;
  }
};

const emit = defineEmits(["userDeleted"]);
const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};
</script>

<template>
  <div
    class="bg-white relative rounded-lg shadow-sm overflow-hidden border border-gray-300"
  >
    <div class="flex items-center justify-between p-4">
      <div class="flex items-center space-x-4">
        <SearchForm @search="handleSearch" />
        <button
          @click="filterByRole('client')"
          class="px-4 py-2 rounded-md font-medium text-sm transition-colors"
          :class="
            roleFilter === 'client'
              ? 'bg-blue-500 text-white'
              : 'bg-blue-100 text-blue-600 hover:bg-blue-200'
          "
        >
          Client
        </button>
        <button
          @click="filterByRole('lessor')"
          class="px-4 py-2 rounded-md font-medium text-sm transition-colors"
          :class="
            roleFilter === 'lessor'
              ? 'bg-green-500 text-white'
              : 'bg-green-100 text-green-600 hover:bg-green-200'
          "
        >
          Lessor
        </button>
        <button
          v-if="roleFilter !== ''"
          @click="roleFilter = ''"
          class="px-4 py-2 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 transition-colors font-medium text-sm"
        >
          Clear
        </button>
      </div>
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
            <div class="text-sm text-gray-900">
              {{ formatDate(item["User since"]) }}
            </div>
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
