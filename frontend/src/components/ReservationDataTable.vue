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

const loading = ref(false);
const errors = ref({ name: [], email: [], password: [] });

const data = ref({
  id: "",
  listing_id: "",
  user_d: "",
  start_date: "",
  end_date: "",
});

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
</script>

<template>
  <div class="bg-white relative rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center justify-between p-4">
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
            listing
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            user
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            Start date
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider"
          >
            End Date
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
        </tr>
      </tbody>
    </table>
  </div>
</template>
