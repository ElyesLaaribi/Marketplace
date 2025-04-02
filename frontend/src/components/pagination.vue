<!-- PaginationComponent.vue -->
<script setup>
import { computed } from "vue";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/vue/20/solid";

const props = defineProps({
  totalItems: {
    type: Number,
    required: true,
  },

  currentPage: {
    type: Number,
    required: true,
  },

  itemsPerPage: {
    type: Number,
    required: true,
  },

  perPageOptions: {
    type: Array,
    default: () => [8, 12, 24, 48],
  },

  showPerPageSelector: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["page-change", "per-page-change"]);

const totalPages = computed(() =>
  Math.ceil(props.totalItems / props.itemsPerPage)
);

const pages = computed(() => {
  const result = [];
  const maxVisiblePages = 5;

  if (totalPages.value <= maxVisiblePages) {
    for (let i = 1; i <= totalPages.value; i++) {
      result.push(i);
    }
  } else {
    result.push(1);

    let startPage = Math.max(2, props.currentPage - 1);
    let endPage = Math.min(totalPages.value - 1, props.currentPage + 1);

    if (props.currentPage <= 2) {
      endPage = 4;
    } else if (props.currentPage >= totalPages.value - 1) {
      startPage = totalPages.value - 3;
    }

    if (startPage > 2) {
      result.push("...");
    }

    for (let i = startPage; i <= endPage; i++) {
      result.push(i);
    }

    if (endPage < totalPages.value - 1) {
      result.push("...");
    }

    result.push(totalPages.value);
  }

  return result;
});

const startItem = computed(() => {
  return props.totalItems === 0
    ? 0
    : (props.currentPage - 1) * props.itemsPerPage + 1;
});

const endItem = computed(() => {
  return Math.min(props.currentPage * props.itemsPerPage, props.totalItems);
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== props.currentPage) {
    emit("page-change", page);
  }
};

const changeItemsPerPage = (newValue) => {
  emit("per-page-change", Number(newValue));
};
</script>

<template>
  <div
    v-if="totalPages > 1 || (totalItems > 0 && showPerPageSelector)"
    class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4"
  >
    <!-- Mobile pagination -->
    <div class="flex flex-1 justify-between sm:hidden">
      <button
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage === 1"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Previous
      </button>
      <button
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Next
      </button>
    </div>

    <!-- Desktop pagination -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Showing
          {{ " " }}
          <span class="font-medium">{{ startItem }}</span>
          {{ " " }}
          to
          {{ " " }}
          <span class="font-medium">{{ endItem }}</span>
          {{ " " }}
          of
          {{ " " }}
          <span class="font-medium">{{ totalItems }}</span>
          {{ " " }}
          results
        </p>
      </div>
      <div class="flex items-center">
        <!-- Items per page selector -->
        <div v-if="showPerPageSelector" class="mr-6">
          <label for="itemsPerPage" class="text-sm text-gray-600 mr-2"
            >Items per page:</label
          >
          <select
            id="itemsPerPage"
            :value="itemsPerPage"
            @change="changeItemsPerPage($event.target.value)"
            class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option
              v-for="option in perPageOptions"
              :key="option"
              :value="option"
            >
              {{ option }}
            </option>
          </select>
        </div>

        <!-- Pagination controls -->
        <nav
          class="isolate inline-flex -space-x-px rounded-md shadow-xs"
          aria-label="Pagination"
        >
          <!-- Previous page button -->
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="sr-only">Previous</span>
            <ChevronLeftIcon class="size-5" aria-hidden="true" />
          </button>

          <!-- Page numbers -->
          <template v-for="(page, index) in pages" :key="index">
            <!-- Ellipsis -->
            <span
              v-if="page === '...'"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0"
              >...</span
            >

            <!-- Page number -->
            <button
              v-else
              @click="goToPage(page)"
              :class="[
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus:outline-offset-0',
                currentPage === page
                  ? 'z-10 bg-indigo-600 text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                  : 'text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50',
              ]"
            >
              {{ page }}
            </button>
          </template>

          <!-- Next page button -->
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="sr-only">Next</span>
            <ChevronRightIcon class="size-5" aria-hidden="true" />
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>
