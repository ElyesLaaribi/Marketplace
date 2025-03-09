<template>
    <a
      :href="link"
      class="flex items-center px-4 py-2 mt-2 text-gray-700 transition-colors duration-300 transform rounded-lg dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700"
    >
      <i :class="['w-5 h-5', icon, 'text-gray-600 dark:text-gray-300']"></i>
  
      <span class="mx-4 font-medium">{{ name }}</span>
    </a>
  </template>
  
  <script setup>
  defineProps({
    name: String,
    link: String,
    icon: String, 
  });
  </script>
  