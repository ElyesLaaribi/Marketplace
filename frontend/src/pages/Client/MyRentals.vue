<script setup>
import DefaultLayout from "../../components/DefaultLayout.vue";
import ReservationDataTable from "../../components/ReservationDataTable.vue";
</script>

<template>
  <DefaultLayout>
    <header class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
          Rental Records
        </h1>
      </div>
    </header>
    <div class="p-20 bg-white min-h-screen">
      <ReservationDataTable :items="items" />
    </div>
  </DefaultLayout>
</template>

<style scoped></style>
