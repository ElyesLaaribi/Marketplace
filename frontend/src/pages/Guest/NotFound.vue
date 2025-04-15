<script setup>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "../../store/user";
import useLessorStore from "../../store/lessor";
import { useAdminStore } from "../../store/admin";
import NotFoundSvg from "../../components/NotFoundSvg.vue";

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
    return "/AdminHome";
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
  <!-- <main
    class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8"
  >
    <div class="text-center">
      <p class="text-base font-semibold text-indigo-600">404</p>
      <h1
        class="mt-4 text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl"
      >
        Page not found
      </h1>
      <p
        class="mt-6 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8"
      >
        Sorry, we couldn’t find the page you’re looking for.
      </p>
      <div class="mt-10 flex items-center justify-center gap-x-6">
        <button
          @click="goToHome"
          class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-600"
        >
          Go back home
        </button>
        <a href="#" class="text-sm font-semibold text-gray-900"
          >Contact support <span aria-hidden="true">&rarr;</span></a
        >
      </div>
    </div>
  </main> -->

  <div class="h-screen w-screen bg-gray-100 flex items-center">
    <div
      class="container flex flex-col md:flex-row items-center justify-center px-5 text-gray-700"
    >
      <div class="max-w-md">
        <div class="text-5xl font-dark font-bold">404</div>
        <p class="text-2xl md:text-3xl font-light leading-normal">
          Sorry we couldn't find this page.
        </p>
        <p class="mb-8">
          But dont worry, you can find plenty of other things on our homepage.
        </p>

        <button
          class="px-4 inline py-2 text-sm font-medium leading-5 shadow text-white transition-colors duration-150 border border-transparent rounded-lg focus:outline-none focus:shadow-outline-blue bg-blue-600 active:bg-blue-600 hover:bg-blue-700"
          @click="goToHome"
        >
          back to homepage
        </button>
      </div>
      <div class="max-w-lg">
        <NotFoundSvg />
      </div>
    </div>
  </div>
</template>

<style scoped></style>
