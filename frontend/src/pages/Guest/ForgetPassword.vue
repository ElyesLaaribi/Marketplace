<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../../axios";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
const $toast = useToast();

const email = ref("");
const message = ref("");
const error = ref("");
const router = useRouter();
const loading = ref(false);
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const isEmailValid = computed(() => {
  return email.value.length > 0 && emailRegex.test(email.value);
});

const submitForgotPassword = async () => {
  if (!isEmailValid.value) {
    error.value = "Please enter a valid email address";
    return;
  }

  loading.value = true;
  message.value = "";
  error.value = "";

  try {
    const response = await api.post("api/forgot", { email: email.value });
    message.value =
      response.data.message || "Password reset link sent to your email.";
    $toast.success(message.value);

    // Store email in localStorage instead of query params for better security
    localStorage.setItem("resetEmail", email.value);

    setTimeout(() => {
      router.push({ name: "ResetPassword" });
    }, 2000);
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      "We couldn't find an account with that email address.";
    $toast.error(error.value);
  } finally {
    loading.value = false;
  }
};

const handleKeydown = (event) => {
  if (event.key === "Enter" && isEmailValid.value) {
    submitForgotPassword();
  }
};
</script>

<template>
  <div
    class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <span
          class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-[#135CA5] mb-4"
        >
          <svg
            class="h-6 w-6 text-white"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 9a2 2 0 100-4 2 2 0 000 4z"
              clip-rule="evenodd"
            />
          </svg>
        </span>
        <h2 class="text-center text-3xl font-bold text-[#135CA5]">
          Forgot your password?
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter your email address and we'll send you a link to reset your
          password.
        </p>
      </div>

      <div class="mt-8 bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="submitForgotPassword">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input
                id="email"
                type="email"
                v-model="email"
                @keydown="handleKeydown"
                required
                autocomplete="email"
                placeholder="your@email.com"
                class="block w-full p-3 md:p-4 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                :class="{
                  'border-red-300': error,
                }"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="email"
              ></div>
            </div>
          </div>

          <transition name="fade">
            <p
              v-if="message"
              class="mt-2 rounded-md bg-green-50 p-3 text-sm text-green-700"
            >
              {{ message }}
            </p>
            <p
              v-else-if="error"
              class="mt-2 rounded-md bg-red-50 p-3 text-sm text-red-700"
            >
              {{ error }}
            </p>
          </transition>

          <div>
            <button
              type="submit"
              :disabled="loading || !isEmailValid"
              class="w-full flex justify-center py-3 md:py-4 px-4 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-[#135CA5] hover:bg-[#28BBDD] focus:outline-none transition-colors duration-200 uppercase tracking-wide"
              :class="{ 'opacity-70': loading }"
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
              <span v-if="loading">Sending reset link...</span>
              <span v-else>Reset Password</span>
            </button>
          </div>
        </form>

        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="bg-white px-2 text-gray-500">Or</span>
            </div>
          </div>

          <div class="mt-6 flex justify-center gap-4">
            <router-link
              :to="{ name: 'Login' }"
              class="text-sm font-medium text-[#135CA5] hover:text-[#28BBDD]"
            >
              Back to login
            </router-link>
            <span class="text-gray-500">•</span>
            <router-link
              :to="{ name: 'Register' }"
              class="text-sm font-medium text-[#135CA5] hover:text-[#28BBDD]"
            >
              Create account
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
