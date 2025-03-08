<script setup>
import { ref } from "vue";
import GuestLayout from "../../components/GuestLayout.vue";
import router from "../../router.js";
import api from "../../axios.js"; 
import { useUserStore } from "../../store/user"; 

const data = ref({
  email: "",
  password: ""
});

// Store field-specific validation errors
const errors = ref({
  email: [],
  password: []
});

// A general error message (for non-validation errors)
const errorMessage = ref("");

// Loading state to disable the button during submission
const loading = ref(false);

async function submit() {
  // Step 1: Initialize or reset errors and error messages
  errors.value = { email: [], password: [] }; // Clear previous errors
  errorMessage.value = "";  // Reset general error message
  loading.value = true;

  try {
    // Step 2: Get CSRF cookie for security
    await api.get("/sanctum/csrf-cookie");

    // Step 3: Send login request with user data
    const response = await api.post("/api/login", data.value);

    // Step 4: On successful login, store token and user data
    const token = response.data.token;
    localStorage.setItem("auth_token", token);
    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    useUserStore().setToken(token);

    // Step 5: Redirect based on user role
    const role = response.data.user.role;
    if (role === "client") {
      router.push({ name: "Home" });
    } else if (role === "lessor") {
      router.push({ name: "LessorHome" });
    }

  } catch (error) {
    if (error.response) {
      console.error("Error response:", error.response);

      // Step 6: Handle validation errors (422)
      if (error.response.status === 422 && error.response.data.errors) {
        // Ensure errors are correctly assigned
        errors.value = error.response.data.errors || { email: [], password: [] };
      }
      // Step 7: Handle unauthorized error (401)
      else if (error.response.status === 401) {
        errorMessage.value = "Invalid credentials, please try again."; // Specific message for wrong credentials
      } 
      // Step 8: Handle other errors (e.g., server issues)
      else {
        errorMessage.value = error.response.data.message || "Login failed. Please try again.";
      }
    } else {
      // Step 9: Handle network errors (e.g., no internet connection)
      errorMessage.value = "Invalid credentials, please try again."; // Catch network error
    }
  } finally {
    // Step 10: Stop loading once the request is complete
    loading.value = false;
  }
}



</script>

<template>
  <GuestLayout>
    <h2 class="mt-10 text-center text-2xl font-bold tracking-tight text-gray-900">
      Welcome back client
    </h2>

    <!-- General error message (e.g., wrong credentials) -->
    <div v-if="errorMessage" class="mt-4 text-center text-sm text-red-600">
      {{ errorMessage }}
    </div>

    <div class="mt-4 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Email Field -->
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
          <!-- Display first error for email if it exists -->
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>

        <!-- Password Field -->
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-900">
              Password
            </label>
            <div class="text-sm">
              <router-link :to="{ name: 'ForgetPassword' }" class="font-semibold text-indigo-600 hover:text-indigo-500">
                Forgot password?
              </router-link>
            </div>
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
          <!-- Display first error for password if it exists -->
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
        </div>

        <!-- Submit Button with Loading Animation -->
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

      <p class="mt-4 text-center text-sm text-gray-500">
        Not a member?
        <router-link :to="{ name: 'Signup' }" class="font-semibold text-indigo-600 hover:text-indigo-500">
          Click to create a new account
        </router-link>
      </p>
    </div>
  </GuestLayout>
</template>


<style scoped>
/* Optional: Add additional styling if needed */
</style>
