<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../axios";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
const $toast = useToast();

const route = useRoute();
const router = useRouter();

const email = ref("");
const token = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const message = ref("");
const error = ref("");
const loading = ref(false);
const showPassword = ref(false);

onMounted(() => {
  const storedEmail = localStorage.getItem("resetEmail");
  if (storedEmail) {
    email.value = storedEmail;
  } else {
    email.value = route.query.email || "";
  }
});

const passwordsMatch = computed(() => {
  return !confirmPassword.value || newPassword.value === confirmPassword.value;
});

const submitResetPassword = async () => {
  loading.value = true;
  message.value = "";
  error.value = "";

  if (!email.value) {
    error.value = "Email address is required";
    loading.value = false;
    return;
  }

  if (newPassword.value !== confirmPassword.value) {
    error.value = "Passwords do not match!";
    loading.value = false;
    return;
  }

  try {
    const response = await api.post("/api/reset", {
      email: email.value,
      token: token.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });

    message.value = response.data.message || "Password reset successful!";
    $toast.success(message.value);

    localStorage.removeItem("resetEmail");

    setTimeout(() => {
      router.push({ name: "Login" });
    }, 2000);
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to reset password.";
    $toast.error(error.value);
  } finally {
    loading.value = false;
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
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Enter the code sent to your email and choose a new password.
        </p>
      </div>

      <div class="mt-8 bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="submitResetPassword">
          <!-- Code Input -->
          <div>
            <label for="code" class="block text-sm font-medium text-gray-700">
              Verification Code
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input
                id="code"
                type="text"
                v-model="token"
                required
                placeholder="Enter the 4-digit code"
                class="block w-full p-3 md:p-4 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="code"
              ></div>
            </div>
          </div>

          <!-- New Password -->
          <div>
            <label
              for="password"
              class="block text-sm font-medium text-gray-700"
            >
              New Password
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input
                id="password"
                type="password"
                v-model="newPassword"
                required
                placeholder="Enter new password"
                class="block w-full p-3 md:p-4 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="password"
              ></div>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label
              for="confirmPassword"
              class="block text-sm font-medium text-gray-700"
            >
              Confirm Password
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input
                id="confirmPassword"
                type="password"
                v-model="confirmPassword"
                required
                placeholder="Confirm new password"
                class="block w-full p-3 md:p-4 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="confirmPassword"
              ></div>
            </div>
          </div>

          <!-- Feedback Messages -->
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

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="loading"
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
              <span v-if="loading">Resetting...</span>
              <span v-else>Reset Password</span>
            </button>
          </div>
        </form>

        <!-- Back to login -->
        <div class="mt-6 text-center">
          <router-link
            :to="{ name: 'Login' }"
            class="text-sm font-medium text-[#135CA5] hover:text-[#28BBDD]"
          >
            Back to login
          </router-link>
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
