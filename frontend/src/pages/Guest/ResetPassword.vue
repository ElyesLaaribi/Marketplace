<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../axios";

const route = useRoute();
const router = useRouter();

const email = ref(localStorage.getItem("resetEmail") || "");
const token = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const message = ref("");
const error = ref("");
const loading = ref(false);
const showPassword = ref(false);
const passwordStrength = ref(0);

onMounted(() => {
  if (!email.value) {
    router.push({ name: "ForgotPassword" });
  }
});

const hasMinLength = computed(() => newPassword.value.length >= 8);
const hasUppercase = computed(() => /[A-Z]/.test(newPassword.value));
const hasLowercase = computed(() => /[a-z]/.test(newPassword.value));
const hasNumber = computed(() => /[0-9]/.test(newPassword.value));
const hasSpecial = computed(() => /[^A-Za-z0-9]/.test(newPassword.value));

const calculatePasswordStrength = () => {
  let score = 0;
  if (hasMinLength.value) score++;
  if (hasUppercase.value) score++;
  if (hasLowercase.value) score++;
  if (hasNumber.value) score++;
  if (hasSpecial.value) score++;

  passwordStrength.value = Math.min(4, score);
};

const strengthColor = computed(() => {
  const colors = [
    "bg-red-500",
    "bg-orange-500",
    "bg-yellow-500",
    "bg-lime-500",
    "bg-green-500",
  ];
  return colors[passwordStrength.value];
});

const strengthLabel = computed(() => {
  const labels = ["Very Weak", "Weak", "Fair", "Good", "Strong"];
  return labels[passwordStrength.value];
});

const handlePasswordChange = () => {
  calculatePasswordStrength();
};

const passwordsMatch = computed(() => {
  return !confirmPassword.value || newPassword.value === confirmPassword.value;
});

const submitResetPassword = async () => {
  loading.value = true;
  message.value = "";
  error.value = "";

  if (newPassword.value !== confirmPassword.value) {
    error.value = "Passwords do not match!";
    loading.value = false;
    return;
  }

  if (passwordStrength.value < 2) {
    error.value = "Please choose a stronger password";
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
  <div
    class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-12 w-12 text-indigo-600"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
          />
        </svg>
      </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Reset Your Password
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Enter your security code and create a new password
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="submitResetPassword">
          <div v-if="email" class="rounded-md bg-indigo-50 p-4">
            <div class="flex">
              <div class="ml-3 flex-1">
                <p class="text-sm text-indigo-700">
                  Resetting password for <strong>{{ email }}</strong>
                </p>
              </div>
            </div>
          </div>

          <div>
            <label for="token" class="block text-sm font-medium text-gray-700">
              Security Code
            </label>
            <div class="mt-1">
              <input
                type="text"
                v-model="token"
                id="token"
                autocomplete="off"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter the code from your email"
              />
            </div>
          </div>

          <div>
            <label
              for="newPassword"
              class="block text-sm font-medium text-gray-700"
            >
              New Password
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="newPassword"
                @input="handlePasswordChange"
                id="newPassword"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter new password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500"
              >
                <svg
                  v-if="showPassword"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
              </button>
            </div>

            <!-- Password strength indicator -->
            <div class="mt-2" v-if="newPassword">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-gray-500"
                  >Password strength: {{ strengthLabel }}</span
                >
              </div>
              <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div
                  class="h-1.5 rounded-full transition-all duration-300"
                  :class="strengthColor"
                  :style="{ width: passwordStrength.value * 25 + '%' }"
                ></div>
              </div>

              <!-- Password requirements -->
              <div class="mt-2 grid grid-cols-2 gap-1 text-xs">
                <div
                  class="flex items-center"
                  :class="hasMinLength ? 'text-green-600' : 'text-gray-500'"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 mr-1"
                    :class="hasMinLength ? 'text-green-600' : 'text-gray-400'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  At least 8 characters
                </div>
                <div
                  class="flex items-center"
                  :class="hasUppercase ? 'text-green-600' : 'text-gray-500'"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 mr-1"
                    :class="hasUppercase ? 'text-green-600' : 'text-gray-400'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Uppercase letter
                </div>
                <div
                  class="flex items-center"
                  :class="hasLowercase ? 'text-green-600' : 'text-gray-500'"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 mr-1"
                    :class="hasLowercase ? 'text-green-600' : 'text-gray-400'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Lowercase letter
                </div>
                <div
                  class="flex items-center"
                  :class="hasNumber ? 'text-green-600' : 'text-gray-500'"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 mr-1"
                    :class="hasNumber ? 'text-green-600' : 'text-gray-400'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Number
                </div>
                <div
                  class="flex items-center"
                  :class="hasSpecial ? 'text-green-600' : 'text-gray-500'"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 mr-1"
                    :class="hasSpecial ? 'text-green-600' : 'text-gray-400'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Special character
                </div>
              </div>
            </div>
          </div>

          <div>
            <label
              for="confirmPassword"
              class="block text-sm font-medium text-gray-700"
            >
              Confirm Password
            </label>
            <div class="mt-1">
              <input
                type="password"
                v-model="confirmPassword"
                id="confirmPassword"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    confirmPassword && !passwordsMatch,
                }"
                placeholder="Confirm your password"
              />
            </div>
            <p
              class="mt-1 text-sm text-red-600"
              v-if="confirmPassword && !passwordsMatch"
            >
              Passwords do not match
            </p>
          </div>

          <div v-if="message" class="p-3 rounded-md bg-green-50">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-green-400"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ message }}</p>
              </div>
            </div>
          </div>

          <div v-if="error" class="p-3 rounded-md bg-red-50">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-red-400"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading || (confirmPassword && !passwordsMatch)"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="loading"
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
              <span v-if="loading">Resetting Password...</span>
              <span v-else>Reset Password</span>
            </button>
          </div>

          <div class="flex items-center justify-center">
            <div class="text-sm">
              <router-link
                :to="{ name: 'Login' }"
                class="font-medium text-indigo-600 hover:text-indigo-500"
              >
                Return to login
              </router-link>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
