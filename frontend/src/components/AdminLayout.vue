<script setup>
import api from "../axios.js";
import { useRouter } from "vue-router";
import { computed, ref, onMounted } from "vue";
import { useAdminStore } from "../store/admin.js";

const router = useRouter();
const adminStore = useAdminStore();
const admin = computed(() => adminStore.admin);
const isExpanded = ref(false);
const showDropdown = ref(false);

onMounted(() => {
  const savedState = localStorage.getItem("sidebar_expanded");
  isExpanded.value = savedState === null ? true : savedState === "true";

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    const dropdownElement = document.getElementById("user-dropdown");
    const avatarElement = document.getElementById("user-avatar");
    if (
      dropdownElement &&
      avatarElement &&
      !dropdownElement.contains(e.target) &&
      !avatarElement.contains(e.target)
    ) {
      showDropdown.value = false;
    }
  });
});

const toggleMenu = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem("sidebar_expanded", isExpanded.value);
};

const toggleDropdown = (event) => {
  event.stopPropagation();
  showDropdown.value = !showDropdown.value;
};

function logout() {
  api
    .post(
      "/api/admin/logout",
      {},
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
      }
    )
    .then(() => {
      adminStore.admin = null;
      localStorage.removeItem("auth_token");
      delete api.defaults.headers.common["Authorization"];
      router.push({ name: "AdminLogin" }).then(() => {
        window.location.reload();
      });
    })
    .catch((error) => {
      console.error("Logout failed:", error);
    });
}

const menuItems = [
  {
    name: "Dashboard",
    route: "/AdminHome",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>`,
  },
  {
    name: "Admins",
    route: "/admins-list",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>`,
  },
  {
    name: "Users",
    route: "/users",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>`,
  },
  {
    name: "Categories",
    route: "/category",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
          </svg>`,
  },
];

const activeRoute = computed(() => router.currentRoute.value.path);

const currentMenuItem = computed(() => {
  return (
    menuItems.find((item) => item.route === activeRoute.value) || {
      name: "Page",
      route: activeRoute.value,
    }
  );
});
</script>

<template>
  <div class="flex h-screen bg-white dark:bg-gray-900">
    <!-- Sidebar -->
    <aside
      :class="`fixed flex flex-col h-screen overflow-y-auto bg-[#002D4A] border-r rtl:border-r-0 rtl:border-l border-gray-700 z-10 transition-all duration-300 shadow-md ${
        isExpanded ? 'w-64' : 'w-20'
      }`"
    >
      <div
        class="flex items-center justify-center p-4 border-b dark:border-gray-700"
      >
        <span v-if="isExpanded" class="text-white text-lg font-medium"
          >Admin Dashboard</span
        >
      </div>

      <div class="flex flex-col justify-between flex-1 p-4">
        <nav class="space-y-1">
          <div v-for="item in menuItems" :key="item.name" class="relative">
            <router-link
              :to="item.route"
              :class="`flex items-center px-3 py-3 text-gray-300 transition-colors duration-200 rounded-lg ${
                activeRoute === item.route
                  ? 'bg-[#036F8B]/20 text-white font-medium'
                  : 'hover:bg-[#036F8B] hover:text-white'
              }`"
            >
              <span class="flex-shrink-0" v-html="item.icon"></span>
              <span v-if="isExpanded" class="ml-3 text-sm">{{
                item.name
              }}</span>
            </router-link>
          </div>
        </nav>
      </div>
    </aside>

    <!-- Top navigation bar -->
    <div
      :class="`fixed top-0 right-0 z-20 transition-all duration-300 ${
        isExpanded ? 'left-64' : 'left-20'
      }`"
    >
      <div
        class="flex items-center justify-between h-16 bg-white border-b border-gray-200 px-6"
      >
        <!-- Left side - Breadcrumb navigation -->
        <div class="flex items-center">
          <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
              <!-- Home icon -->
              <li class="inline-flex items-center">
                <router-link
                  to="/AdminHome"
                  class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700"
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
                      d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                    />
                  </svg>
                </router-link>
              </li>

              <!-- Current page -->
              <li v-if="currentMenuItem.route !== '/AdminHome'">
                <div class="flex items-center">
                  <span class="mx-2 text-gray-400">/</span>
                  <span class="text-sm font-medium text-gray-700">{{
                    currentMenuItem.name
                  }}</span>
                </div>
              </li>
            </ol>
          </nav>
        </div>

        <!-- Right side - User dropdown -->
        <div class="flex items-center relative">
          <div
            @click="toggleDropdown"
            id="user-avatar"
            class="flex items-center cursor-pointer"
          >
            <div class="mr-3 text-right hidden sm:block">
              <p class="text-sm font-medium text-gray-700">
                {{ admin?.name || "Admin" }}
              </p>
              <p class="text-xs text-gray-500">Administrator</p>
            </div>
            <div
              class="h-10 w-10 rounded-full bg-[#002D4A] text-white flex items-center justify-center overflow-hidden"
            >
              <img
                v-if="admin?.avatar"
                :src="admin.avatar"
                class="h-full w-full object-cover"
                alt="Admin avatar"
              />
              <span v-else class="text-sm font-medium">
                {{ admin?.name ? admin.name.charAt(0) : "A" }}
              </span>
            </div>
          </div>

          <!-- Dropdown menu -->
          <div
            v-show="showDropdown"
            id="user-dropdown"
            class="absolute top-full right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-30 border border-gray-200"
          >
            <div class="px-4 py-3 border-b border-gray-100">
              <p class="text-sm font-medium text-gray-700">
                {{ admin?.name || "Admin" }}
              </p>
              <p class="text-xs text-gray-500 truncate">Administrator</p>
            </div>
            <a
              href="#"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >Settings</a
            >
            <button
              @click="logout"
              class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <main
      :class="`transition-all duration-300 ${
        isExpanded ? 'ml-64' : 'ml-20'
      } pt-16 w-full overflow-y-auto bg-white min-h-screen`"
    >
      <div class="p-6 bg-white">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<style scoped>
:deep(.bg-gray-100) {
  background-color: white !important;
}
</style>
