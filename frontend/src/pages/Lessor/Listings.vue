<script setup>
import { onMounted, ref } from "vue";
import LessorLayout from "../../components/LessorLayout.vue";
import ListingsDataTable from "../../components/ListingsDataTable.vue";
import api from "../../axios";

const items = ref([]);

const handleListingDeleted = (listingId) => {
  items.value = items.value.filter((item) => item.id !== listingId);
};

onMounted(async () => {
  const response = await api.get("/api/listings");
  items.value = response.data.data;
});
</script>

<template>
  <LessorLayout>
    <div class="p-8 bg-gray-100 min-h-screen">
      <ListingsDataTable
        :items="items"
        @listingDeleted="handleListingDeleted"
      />
    </div>
  </LessorLayout>
</template>
