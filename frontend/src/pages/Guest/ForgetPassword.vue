<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../../axios";


const email = ref("");
const message = ref("");
const error = ref("");
const router = useRouter();
const loading = ref(false);

const submitForgotPassword = async () => {
  loading.value = true;
  message.value = "";
  error.value = "";

  try {
    const response = await api.post("api/forgot", { email: email.value });

    message.value = response.data.message;

    // Redirect to reset password page with email as query parameter
    setTimeout(() => {
      router.push({ name: "ResetPassword", query: { email: email.value } });
    }, 1500); // Delay for message display
  } catch (err) {
    error.value = err.response?.data?.message || "Email not found.";
  }finally {
    loading.value = false;
    }
};
</script>

<template>
  <div class="mt-50 flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <h2 class="text-center text-2xl font-bold tracking-tight text-gray-900">
        Enter your email address
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form class="space-y-6" @submit.prevent="submitForgotPassword">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-900">
            Email address
          </label>
          <div class="mt-2">
            <input
              type="email"
              v-model="email"
              id="email"
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
            <span v-if="loading">Resetting...</span>
            <span v-else>Reset password</span>
          </button>
          <p class="mt-5 text-center text-sm/6 text-gray-500">
            Remember your account? 
            {{ ' ' }}
          <router-link :to="{name: 'Login'}" class="font-semibold text-indigo-600 hover:text-indigo-500">Login</router-link>
            </p>
        </div>
      </form>
    </div>
  </div>
</template>
