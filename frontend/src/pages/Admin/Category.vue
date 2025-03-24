<script setup>
import { onMounted, ref } from "vue";
import AdminLayout from "../../components/AdminLayout.vue";
import CategoryDataTable from "../../components/CategoryDataTable.vue";
import api from "../../axios";

const items = ref([]);

const handleUserDeleted = (userId) => {
  items.value = items.value.filter((item) => item.id !== userId);
};

onMounted(async () => {
  const response = await api.get("/api/categories");
  items.value = response.data.data;
});
</script>

<template>
  <AdminLayout>
    <div class="p-8 bg-gray-100 min-h-screen">
      <CategoryDataTable :items="items" @userDeleted="handleUserDeleted" />
    </div>
  </AdminLayout>
</template>
