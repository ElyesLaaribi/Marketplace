<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../axios";

const route = useRoute();
const router = useRouter();

const email = ref(route.query.email || "");
const token = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const message = ref("");
const error = ref("");

const loading = ref(false);

const submitResetPassword = async () => {
  loading.value = true;
  message.value = "";
  error.value = "";

  if (newPassword.value !== confirmPassword.value) {
    error.value = "Passwords do not match!";
    return;
  }

  try {
    const response = await api.post("/api/reset", {
      email: email.value,
      token: token.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });

    message.value = response.data.message;

    setTimeout(() => {
      router.push({ name: "Login" });
    }, 2000);
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to reset password.";
  } finally {
    loading.value = false;
    }
};
</script>

<template>
  <div class="mt-30 flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <h2 class="text-center text-2xl font-bold tracking-tight text-gray-900">
        Reset Your Password
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6" @submit.prevent="submitResetPassword">
        <div>
          <label for="token" class="block text-sm font-medium text-gray-900">
            Enter security code
          </label>
          <div class="mt-2">
            <input
              type="text"
              v-model="token"
              id="token"
              required
              class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600"
            />
          </div>
        </div>

        <div>
          <label for="newPassword" class="block text-sm font-medium text-gray-900">
            New Password
          </label>
          <div class="mt-2">
            <input
              type="password"
              v-model="newPassword"
              id="newPassword"
              required
              class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600"
            />
          </div>
        </div>

        <div>
          <label for="confirmPassword" class="block text-sm font-medium text-gray-900">
            Confirm Password
          </label>
          <div class="mt-2">
            <input
              type="password"
              v-model="confirmPassword"
              id="confirmPassword"
              required
              class="block w-full rounded-md px-3 py-1.5 text-base text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600"
            />
          </div>
        </div>

        <div v-if="message" class="text-green-600">{{ message }}</div>
        <div v-if="error" class="text-red-600">{{ error }}</div>

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
            <span v-if="loading">Submitting...</span>
            <span v-else>Submit</span>
          </button>
        </div>
        <p class="mt-5 text-center text-sm/6 text-gray-500">
            Remember your account? 
            {{ ' ' }}
          <router-link :to="{name: 'Login'}" class="font-semibold text-indigo-600 hover:text-indigo-500">Login</router-link>
            </p>
      </form>
    </div>
  </div>
</template>
