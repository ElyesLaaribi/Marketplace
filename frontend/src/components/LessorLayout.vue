<script setup>
import { Bars3Icon, XMarkIcon, BellIcon } from '@heroicons/vue/24/solid';  
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'; 
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import api from '../axios.js';
import router from '../router.js';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import useLessorStore from '../store/lessor.js';
import NavItem from './NavItem.vue';

const searchQuery = ref('');
const userStore = useLessorStore();
const user = computed(() => userStore.user);
const sidebarOpen = ref(false);
const isMobile = ref(false);

const menuItems = ref([
  { name: 'Dashboard', link: '#', icon: 'fa-solid fa-chart-line' }, 
  { name: 'Listings', link: '#', icon: 'fa-regular fa-folder-open' },
  { name: 'Add listing', link: '#', icon: 'fa-regular fa-circle-up' },
  { name: 'DMs', link: '#', icon: 'fa-regular fa-comments' },
  { name: 'Reservations', link: '#', icon: 'fa-regular fa-rectangle-list' },
]);

function logout() {
  api.post('/api/logout')
    .then(() => {
      userStore.user = null;
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

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value;
}

function checkMobile() {
  isMobile.value = window.innerWidth < 1024; 
  if (isMobile.value) {
    sidebarOpen.value = false;
  } else {
    sidebarOpen.value = true;
  }
}

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<template>
  <div class="flex h-screen overflow-hidden">
    <aside 
      :class="[
        'flex flex-col w-64 h-screen px-4 py-8 overflow-y-auto bg-white border-r dark:bg-gray-900 dark:border-gray-700 transition-transform duration-300 ease-in-out z-20',
        isMobile ? 'fixed' : 'static',
        isMobile && !sidebarOpen ? '-translate-x-full' : 'translate-x-0'
      ]"
    >
      <a href="#" class="flex items-center">
        <img class="w-auto h-6 sm:h-7" src="https://merakiui.com/images/logo.svg" alt="Logo" />
      </a>

      <div class="relative mt-6">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
          <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none">
            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </span>
        <input
          type="text"
          v-model="searchQuery"
          class="w-full py-2 pl-10 pr-4 text-gray-700 bg-white border rounded-md dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600 focus:border-blue-400 dark:focus:border-blue-300 focus:ring-blue-300 focus:ring-opacity-40 focus:outline-none focus:ring"
          placeholder="Search"
        />
      </div>

      <div class="flex flex-col justify-between flex-1 mt-6">
        <nav>
          <NavItem v-for="item in menuItems" :key="item.name" :icon="item.icon" :name="item.name" :link="item.link" />
        </nav>
      </div>
    </aside>

    <div :class="['flex flex-col flex-1 overflow-x-hidden', isMobile ? 'w-full' : '']">
      <Disclosure as="nav" class="bg-gray-800" v-slot="{ open }">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
          <div class="relative flex h-16 items-center justify-between">
            <div class="absolute inset-y-0 left-0 flex items-center lg:hidden">
              <button 
                @click="toggleSidebar" 
                class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:ring-2 focus:ring-white focus:outline-none focus:ring-inset"
              >
                <span class="absolute -inset-0.5" />
                <span class="sr-only">Toggle sidebar</span>
                <Bars3Icon class="block size-6" aria-hidden="true" />
              </button>
            </div>
            
            <div class="flex flex-1 items-center justify-center lg:justify-start">
              
            </div>
            
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
              <button type="button" class="relative rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800 focus:outline-none">
                <span class="absolute -inset-1.5" />
                <span class="sr-only">View notifications</span>
                <BellIcon class="size-6" aria-hidden="true" />
              </button>

              <Menu as="div" class="relative ml-3">
                <div>
                  <MenuButton class="relative flex rounded-full bg-gray-800 text-sm focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800 focus:outline-none">
                    <span class="absolute -inset-1.5" />
                    <span class="sr-only">Open user menu</span>
                    <img class="size-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" />
                    <span v-if="user" class="mt-1 text-white ml-3 hidden sm:block">{{user.name}}</span>
                  </MenuButton>
                </div>
                <transition 
                  enter-active-class="transition ease-out duration-100" 
                  enter-from-class="transform opacity-0 scale-95" 
                  enter-to-class="transform opacity-100 scale-100" 
                  leave-active-class="transition ease-in duration-75" 
                  leave-from-class="transform opacity-100 scale-100" 
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 ring-1 shadow-lg ring-black/5 focus:outline-none">
                    <MenuItem v-slot="{ active }">
                      <a href="#" :class="[active ? 'bg-gray-100 outline-hidden' : '', 'block px-4 py-2 text-sm text-gray-700']">Your Profile</a>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <a href="#" :class="[active ? 'bg-gray-100 outline-hidden' : '', 'block px-4 py-2 text-sm text-gray-700']">Settings</a>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <a href="#" @click="logout" :class="[active ? 'bg-gray-100 outline-hidden' : '', 'block px-4 py-2 text-sm text-gray-700']">Sign out</a>
                    </MenuItem>
                  </MenuItems>
                </transition>
              </Menu>
            </div>
          </div>
        </div>
      </Disclosure>

      <!-- Page Header -->
      

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-4">
        <slot></slot>
      </main>
    </div>
  </div>

  <div 
    v-if="isMobile && sidebarOpen" 
    @click="toggleSidebar" 
    class="fixed inset-0 bg-black bg-opacity-50 z-10"
  ></div>
</template>

<style scoped>

</style>