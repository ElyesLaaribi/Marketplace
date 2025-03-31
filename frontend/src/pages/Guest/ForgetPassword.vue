<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../../axios";

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

    localStorage.setItem("resetEmail", email.value);

    setTimeout(() => {
      router.push({ name: "ResetPassword" });
    }, 2000);
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      "We couldn't find an account with that email address.";
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
    class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8"
  >
    <div class="w-full max-w-md space-y-8">
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">
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
                class="block w-full rounded-md border-gray-300 px-4 py-3 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :class="{
                  'border-red-300 focus:border-red-500 focus:ring-red-500':
                    error,
                }"
              />
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
              class="group relative flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <svg
                  v-if="loading"
                  class="animate-spin h-5 w-5 text-indigo-300"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
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
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <svg
                  v-else
                  class="h-5 w-5 text-indigo-300"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  aria-hidden="true"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 12a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                  <path
                    d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                  />
                </svg>
              </span>
              {{ loading ? "Sending reset link..." : "Reset password" }}
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
              class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
            >
              Back to login
            </router-link>
            <span class="text-gray-500">•</span>
            <router-link
              :to="{ name: 'Register' }"
              class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
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
