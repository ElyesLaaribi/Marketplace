<script setup>

const emit = defineEmits(['search'])

const search = (e) => {
    emit('search', e.target.value);
}
</script>

<template>
    <form class="py-3 px-4 flex items-center">
        <label class="sr-only">Search</label>
        <div class="relative w-full">
            <input type="text" @input="search" placeholder="Search" class="w-full px-4 py-2 text-gray-500 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-transparent">
        </div>
    </form>
</template>