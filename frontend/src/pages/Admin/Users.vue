<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../../components/AdminLayout.vue';
import DataTable from '../../components/DataTable.vue';
import api from '../../axios';

const items = ref([]);

const handleUserDeleted = (userId) => {
    items.value = items.value.filter(item => item.id !== userId);
};

onMounted(async () => {
    const response = await api.get("/api/users"); 
    items.value = response.data.data;
});


</script>

<template>
    <AdminLayout>
        <div class="p-8 bg-gray-100 min-h-screen">
            <DataTable :items="items" @userDeleted="handleUserDeleted" />
        </div>
    </AdminLayout>
</template>
