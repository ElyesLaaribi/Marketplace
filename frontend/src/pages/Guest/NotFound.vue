<script setup>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "../../store/user";
import useLessorStore from "../../store/lessor";
import { useAdminStore } from "../../store/admin";

const router = useRouter();
const userStore = useUserStore();
const lessorStore = useLessorStore();
const adminStore = useAdminStore(); 

onMounted(async () => {
  console.log("User Store:", userStore.user);
  console.log("Lessor Store:", lessorStore.lessor);

  if (userStore.token) {
    await userStore.fetchUser(); 
  }

  if (lessorStore.token) {
    await lessorStore.fetchLessor(); 
  }

  if (adminStore.token) {
    await adminStore.fetchUser(); 
  }
});

const homeRoute = computed(() => {
  if (userStore.user && userStore.user.role === "client") {
    return "/home";
  } else if (lessorStore.lessor) {
    return "/lessorhome";
  } else if (adminStore.admin && adminStore.admin.role === "admin") {
    return "/admin-home";
  } else {
    return "/"; 
  }
});

const goToHome = () => {
  console.log("Redirecting to:", homeRoute.value);
  router.push(homeRoute.value);
};
</script>

<template>
  <main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
    <div class="text-center">
      <p class="text-base font-semibold text-indigo-600">404</p>
      <h1 class="mt-4 text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl">
        Page not found
      </h1>
      <p class="mt-6 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">
        Sorry, we couldn’t find the page you’re looking for.
      </p>
      <div class="mt-10 flex items-center justify-center gap-x-6">
        <button
          @click="goToHome"
          class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-600"
        >
          Go back home
        </button>
        <a href="#" class="text-sm font-semibold text-gray-900">Contact support <span aria-hidden="true">&rarr;</span></a>
      </div>
    </div>
  </main>
</template>

<style scoped>
</style>
