<script setup>
import { ref } from "vue";
import GuestLayout from "../../components/GuestLayout.vue";
import router from "../../router.js";
import api from "../../axios.js"; 
import { useAdminStore } from "../../store/admin"; 

const data = ref({
  email: "",
  password: ""
});

const errors = ref({
  email: [],
  password: []
});

const errorMessage = ref("");

const loading = ref(false);

async function submit() {
  errors.value = { email: [], password: [] }; 
  errorMessage.value = "";  
  loading.value = true;

  try {
    await api.get("/sanctum/csrf-cookie");

    const response = await api.post("/api/admin/login", data.value);

    const token = response.data.token;

    localStorage.setItem("auth_token", token);
    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

    useAdminStore().setToken(token);

    router.push({ name: "AdminHome" });
  } catch (error) {
    if (error.response) {
      console.error("Error response:", error.response);

      if (error.response.status === 422 && error.response.data.errors) {
        errors.value = error.response.data.errors || { email: [], password: [] };
      } else if (error.response.status === 401) {
        errorMessage.value = "Invalid credentials, please try again."; 
      } else {
        errorMessage.value = error.response.data.message || "Login failed. Please try again.";
      }
    } else {
      errorMessage.value = "Invalid credentials, please try again."; 
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <GuestLayout>
    <h2 class="mt-10 text-center text-2xl font-bold tracking-tight text-gray-900">
      Admin
    </h2>

    <div v-if="errorMessage" class="mt-4 text-center text-sm text-red-600">
      {{ errorMessage }}
    </div>

    <div class="mt-4 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-900">
            Email address
          </label>
          <div class="mt-2">
            <input
              type="email"
              name="email"
              id="email"
              autocomplete="email"
              v-model="data.email"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 border border-gray-300 placeholder-gray-400 focus:outline-indigo-600"
            />
          </div>
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-900">
              Password
            </label>
            
          </div>
          <div class="mt-2">
            <input
              type="password"
              name="password"
              id="password"
              autocomplete="current-password"
              v-model="data.password"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 border border-gray-300 placeholder-gray-400 focus:outline-indigo-600"
            />
          </div>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
        </div>
        <div>
          <button
            type="submit"
            :disabled="loading"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 text-white" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign in</span>
          </button>
        </div>
      </form>
    </div>
  </GuestLayout>
</template>


<style scoped>

</style>
