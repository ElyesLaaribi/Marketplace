<script setup>
import api from "../axios.js";
import { useRouter } from "vue-router";
import { computed, ref } from "vue";
import { useAdminStore } from "../store/admin.js";

const router = useRouter();

const adminStore = useAdminStore();
const admin = computed(() => adminStore.admin);

const isExpanded = ref(localStorage.getItem("is_expanded") === "true");

const toggleMenu = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem("is_expanded", isExpanded.value);
};

const menuItems = [
  { name: "Dashboard", icon: "home", route: "/AdminHome" },
  { name: "Admins", icon: "fas fa-user-shield", route: "/admins-list" },
  { name: "Users", icon: "fas fa-users", route: "/users" },
  { name: "Categories", icon: "fas fa-tags", route: "/category" },
];

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
</script>

<template>
  <div class="flex">
    <!-- Sidebar Section -->
    <aside
      class="fixed flex flex-col justify-between left-0 top-0 h-screen w-64 px-5 py-8 overflow-y-auto bg-white border-r dark:bg-gray-900 dark:border-gray-700"
    >
      <div class="text-lg text-white">Admin dashboard</div>

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
                :src="
                  admin?.avatar ||
                  'https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=634&h=634&q=80'
                "
                alt="avatar"
              />
              <span
                class="text-sm font-medium text-gray-700 dark:text-gray-200"
              >
                {{ admin?.name || "admin example" }}
              </span>
            </a>

            <a
              href="#"
              @click="logout"
              class="text-gray-500 transition-colors duration-200 rotate-180 dark:text-red-400 rtl:rotate-0 hover:text-red-500 dark:hover:text-red-400"
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
            </a>
          </div>
        </div>
      </div>
    </aside>
    <!-- Main Content Area (Dynamic Content via Slot) -->
    <main
      class="flex-1 p-6 ml-64 h-screen overflow-y-auto bg-white main-content"
    >
      <slot></slot>
    </main>
  </div>
</template>
<style scoped>
/* Only target .bg-gray-100 within the main content area */
:deep(main .bg-gray-100) {
  background-color: white !important;
}
</style>
