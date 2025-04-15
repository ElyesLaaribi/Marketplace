<script setup>
import api from "../axios.js";
import router from "../router.js";
import { computed, ref, onMounted } from "vue";
import useLessorStore from "../store/lessor.js";

const lessorStore = useLessorStore();
const lessor = computed(() => lessorStore.lessor);

const isExpanded = ref(false);

onMounted(() => {
  const savedState = localStorage.getItem("sidebar_expanded");
  isExpanded.value = savedState === null ? true : savedState === "true";
});

const toggleMenu = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem("sidebar_expanded", isExpanded.value);
};

const menuItems = [
  {
    name: "Dashboard",
    route: "/lessorhome",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>`,
  },
  {
    name: "Listings",
    route: "/listings",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
          </svg>`,
  },
  {
    name: "Reservations",
    route: "/reservations",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
          </svg>`,
  },
  {
    name: "Reporting",
    route: "/reporting",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
          </svg>`,
  },
  {
    name: "Profile",
    route: "/lessor-profile",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>`,
  },
];

const activeRoute = computed(() => router.currentRoute.value.path);

async function logout() {
  try {
    await api.post("/api/logout");
    lessorStore.lessor = null;
    localStorage.removeItem("auth_token");
    delete api.defaults.headers.common["Authorization"];
    await router.push({ name: "Login" });
    window.location.reload();
  } catch (error) {
    console.error("Logout failed:", error);
  }
}
</script>

<template>
  <div class="flex h-screen bg-white dark:bg-gray-900">
    <!-- Sidebar - collapsible -->
    <aside
      :class="`fixed flex flex-col h-screen overflow-y-auto bg-white border-r rtl:border-r-0 rtl:border-l dark:bg-gray-900 dark:border-gray-700 z-10 transition-all duration-300 shadow-md ${
        isExpanded ? 'w-64' : 'w-20'
      }`"
    >
      <!-- <div class="flex lg:flex-1">
        <img
          class="h-17 w-auto"
          src="../../assets/images/logo.png"
          alt="RentEase Logo"
        />
      </div> -->
      <div
        class="flex items-center justify-between p-4 border-b dark:border-gray-700"
      >
        <span
          v-if="isExpanded"
          class="text-lg font-semibold text-gray-800 dark:text-white"
          ><img
            src="../../src/assets/images/logo.png"
            class="h-16 w-auto"
            alt="Logo"
        /></span>

        <button
          @click="toggleMenu"
          class="p-1 rounded-md text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none"
          aria-label="Toggle sidebar"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5 transition-transform"
            :class="isExpanded ? '' : 'rotate-180'"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"
            />
          </svg>
        </button>
      </div>

      <div class="flex flex-col justify-between flex-1 p-4">
        <nav class="space-y-1">
          <div v-for="item in menuItems" :key="item.name" class="relative">
            <router-link
              :to="item.route"
              :class="`flex items-center px-3 py-3 text-gray-600 transition-colors duration-200 rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 hover:text-gray-700 ${
                activeRoute === item.route
                  ? 'bg-blue-50 dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-medium'
                  : ''
              }`"
            >
              <span class="flex-shrink-0" v-html="item.icon"></span>
              <span v-if="isExpanded" class="ml-3 text-sm">{{
                item.name
              }}</span>

              <span
                v-if="
                  isExpanded &&
                  (item.name === 'Messages' || item.name === 'Reservations')
                "
                class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-red-500 rounded-full"
              >
                3
              </span>
            </router-link>
          </div>
        </nav>

        <div class="pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <a href="#" class="flex items-center gap-x-2">
              <img
                class="object-cover rounded-full h-9 w-9 ring-2 ring-gray-200 dark:ring-gray-600"
                :src="
                  lessor?.avatar ||
                  'https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=634&h=634&q=80'
                "
                alt="avatar"
              />
              <div v-if="isExpanded" class="flex flex-col">
                <span
                  class="text-sm font-medium text-gray-700 dark:text-gray-200"
                >
                  {{ lessor?.name || "John Doe" }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  Items owner
                </span>
              </div>
            </a>

            <button
              v-if="isExpanded"
              @click="logout"
              class="p-1.5 text-gray-500 transition-colors duration-200 rounded-lg dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-red-600 dark:hover:text-red-400"
              aria-label="Logout"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
                />
              </svg>
            </button>
          </div>

          <button
            v-if="!isExpanded"
            @click="logout"
            class="mt-4 flex justify-center w-full p-2 text-gray-500 transition-colors duration-200 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-red-600 dark:hover:text-red-400 rounded-lg"
            aria-label="Logout"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="w-5 h-5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
              />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main content with dynamic margin to account for sidebar width -->
    <main
      :class="`transition-all duration-300 ${
        isExpanded ? 'ml-64' : 'ml-20'
      } w-full overflow-y-auto bg-gray-100 `"
    >
      <div class="p-6">
        <slot></slot>
      </div>
    </main>
  </div>
</template>
