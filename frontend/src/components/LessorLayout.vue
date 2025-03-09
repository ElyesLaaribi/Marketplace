<script setup>
import api from '../axios.js';
import router from '../router.js';
import { computed, ref } from 'vue';
import useLessorStore from '../store/lessor.js';

// Get the lessor (user) from the store
const lessorStore = useLessorStore();
const lessor = computed(() => lessorStore.lessor);

// State to track if the sidebar menu is expanded
const isExpanded = ref(localStorage.getItem("is_expanded") === "true");

// Toggle the sidebar menu expansion state
const toggleMenu = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem("is_expanded", isExpanded.value);
};

// Define the menu items for navigation
const menuItems = [
  { name: 'Dashboard', icon: 'home', route: '/lessorhome' },
  { name: 'Listings', icon: 'fa-regular fa-rectangle-list', route: '/listings' },
  { name: 'Add listing', icon: 'plus', route: '/add-listings' },
  { name: 'DMs', icon: 'envelope', route: '/DMS' },
  { name: 'Reservations', icon: 'clipboard-list', route: '/reservations' },
  { name: 'Reporting', icon: 'fa-solid fa-chart-line', route: '/reporting' },
  { name: 'Profile', icon: 'user', route: '/lessor-profile' },
];

// Logout function
function logout() {
  api.post('/api/logout')
    .then(() => {
      lessorStore.lessor = null; 
      localStorage.removeItem('auth_token');
      delete api.defaults.headers.common['Authorization'];
      router.push({ name: 'Login' }).then(() => {
        window.location.reload();
      });
    })
    .catch(error => {
      console.error('Logout failed:', error);
    });
}
</script>

<template>
  <div class="flex">
    <!-- Sidebar Section -->
    <aside class="flex flex-col w-64 h-screen px-5 py-8 overflow-y-auto bg-white border-r rtl:border-r-0 rtl:border-l dark:bg-gray-900 dark:border-gray-700">
      <!-- Logo -->
      <a href="#">
        <img class="w-auto h-7" src="https://merakiui.com/images/logo.svg" alt="logo" />
      </a>

      <div class="flex flex-col justify-between flex-1 mt-6">
        <!-- Navigation Menu -->
        <nav class="flex-1 -mx-3 space-y-3">
          <!-- Loop through each menu item -->
          <div v-for="item in menuItems" :key="item.name" class="relative mx-3">
            <router-link 
              :to="item.route" 
              class="flex items-center px-3 py-2 text-gray-600 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700"
              active-class="bg-gray-100 dark:bg-gray-800 text-blue-500"
            >
              <!-- Use FontAwesome icon -->
              <i :class="`fas fa-${item.icon} mr-2`"></i>
              <span class="text-sm font-medium">{{ item.name }}</span>
            </router-link>
          </div>
        </nav>

        <!-- User Profile & Logout Section -->
        <div class="mt-6">
          <div class="flex items-center justify-between mt-6">
            <a href="#" class="flex items-center gap-x-2">
              <img 
                class="object-cover rounded-full h-7 w-7" 
                :src="lessor?.avatar || 'https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=634&h=634&q=80'" 
                alt="avatar" 
              />
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ lessor?.name || 'John Doe' }}
              </span>
            </a>
            
            <a href="#" @click="logout" class="text-gray-500 transition-colors duration-200 rotate-180 dark:text-gray-400 rtl:rotate-0 hover:text-blue-500 dark:hover:text-blue-400">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area (Dynamic Content via Slot) -->
    <main class="flex-1 p-6">
      <slot></slot>
    </main>
  </div>
</template>
