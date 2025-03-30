<script setup>
import { ref, computed } from "vue";
import GuestLayout from "../../components/GuestLayout.vue";
import router from "../../router.js";
import api from "../../axios.js";
import { useUserStore } from "../../store/user";
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/24/outline";

const data = ref({
  email: "",
  password: "",
  rememberMe: false,
});

const errors = ref({
  email: [],
  password: [],
});

const errorMessage = ref("");
const loading = ref(false);
const showPassword = ref(false);

const passwordInputType = computed(() => {
  return showPassword.value ? "text" : "password";
});

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

async function submit() {
  errors.value = { email: [], password: [] };
  errorMessage.value = "";
  loading.value = true;

  try {
    await api.get("/sanctum/csrf-cookie");
    const response = await api.post("/api/login", data.value);
    const token = response.data.token;

    const storage = data.value.rememberMe ? localStorage : sessionStorage;
    storage.setItem("auth_token", token);

    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    useUserStore().setToken(token);

    const redirectRoute =
      response.data.user.role === "client"
        ? { name: "Home" }
        : { name: "LessorHome" };
    router.push(redirectRoute);
  } catch (error) {
    if (error.response) {
      if (error.response.status === 422) {
        errors.value = {
          email: error.response.data.errors?.email || [],
          password: error.response.data.errors?.password || [],
        };
      } else if (error.response.status === 401) {
        errors.value = {
          email: error.response.data.errors?.email || [],
          password: error.response.data.errors?.password || [],
        };
      } else {
        errorMessage.value =
          error.response.data.message || "Login failed. Please try again.";
      }
    } else if (error.request) {
      errorMessage.value =
        "No response from server. Check your internet connection.";
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <GuestLayout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Welcome back
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Sign in to access your account
      </p>
    </div>

    <div v-if="errorMessage" class="mt-4 sm:mx-auto sm:w-full sm:max-w-md">
      <div
        class="bg-red-50 border border-red-200 rounded-md p-4 text-sm text-red-600"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <!-- Warning icon -->
            <svg
              class="h-5 w-5 text-red-500"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <p>{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div
        class="bg-white py-8 px-6 shadow rounded-lg sm:px-10 border border-gray-100"
      >
        <form @submit.prevent="submit" class="space-y-6" novalidate>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input
                type="text"
                name="email"
                id="email"
                autocomplete="email"
                v-model="data.email"
                placeholder="you@example.com"
                :class="[
                  'block w-full rounded-md px-3 py-2 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm border',
                  errors.email.length
                    ? 'border-red-300 focus:ring-red-500'
                    : 'border-gray-300',
                ]"
              />
            </div>
            <p v-if="errors.email.length" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label
                for="password"
                class="block text-sm font-medium text-gray-700"
              >
                Password
              </label>
              <div class="text-sm">
                <router-link
                  :to="{ name: 'ForgetPassword' }"
                  class="font-semibold text-indigo-600 hover:text-indigo-500"
                >
                  Forgot password?
                </router-link>
              </div>
            </div>
            <div class="mt-1 relative">
              <input
                :type="passwordInputType"
                name="password"
                id="password"
                autocomplete="current-password"
                v-model="data.password"
                placeholder="Your password"
                :class="[
                  'block w-full rounded-md px-3 py-2 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-transparent sm:text-sm border pr-10',
                  errors.password.length
                    ? 'border-red-300 focus:ring-red-500'
                    : 'border-gray-300',
                ]"
              />
              <button
                type="button"
                @click="togglePasswordVisibility"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
              >
                <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                <EyeSlashIcon v-else class="h-5 w-5" />
              </button>
            </div>
            <p v-if="errors.password.length" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>

          <div class="flex items-center">
            <input
              id="remember-me"
              name="remember-me"
              type="checkbox"
              v-model="data.rememberMe"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-700">
              Remember me
            </label>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <svg
                v-if="loading"
                class="animate-spin h-5 w-5 mr-2 text-white"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8v8H4z"
                ></path>
              </svg>
              <span v-if="loading">Signing in...</span>
              <span v-else>Sign in</span>
            </button>
          </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
          Not a member?
          <router-link
            :to="{ name: 'Signup' }"
            class="font-semibold text-indigo-600 hover:text-indigo-500"
          >
            Create a new account
          </router-link>
        </p>
      </div>
    </div>
  </GuestLayout>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
