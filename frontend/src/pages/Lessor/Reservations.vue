<script setup>
import LessorLayout from "../../components/LessorLayout.vue";
import ReservationDataTable from "../../components/ReservationDataTable.vue";
</script>

<template>
  <LessorLayout>
    <div class="p-8 bg-gray-100 min-h-screen">
      <ReservationDataTable :items="items" />
    </div>
  </LessorLayout>
</template>

<style scoped></style>
