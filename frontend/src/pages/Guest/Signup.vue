<script setup>
import GuestLayout from "../../components/GuestLayout.vue";
import { ref } from "vue";
import api from "../../axios.js";
import router from "../../router.js";
import { useRoute } from "vue-router";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
const $toast = useToast();

const route = useRoute();
const data = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  country: "",
  city: "",
  cin: "",
  phone: "",
  role: "client",
});

const errorMessage = ref("");
const loading = ref(false);
const passwordVisible = ref(false);
const confirmPasswordVisible = ref(false);

const errors = ref({
  name: [],
  email: [],
  password: [],
  country: [],
  city: [],
  cin: [],
  phone: [],
});

function submit() {
  loading.value = true;
  errors.value = {
    name: [],
    email: [],
    password: [],
    country: [],
    city: [],
    cin: [],
    phone: [],
  };
  api
    .get("/sanctum/csrf-cookie")
    .then(() => {
      return api.post("/api/register", data.value);
    })
    .then((response) => {
      console.log("Response from server:", response);

      if (response.data && response.data.token) {
        localStorage.setItem("token", response.data.token);
        api.defaults.headers.common[
          "Authorization"
        ] = `Bearer ${response.data.token}`;
        /////
        $toast.success("Account created successfully!");
        router.push({ name: "Login" });
      } else {
        console.error("Token not found in response");
      }
    })
    .catch((error) => {
      console.log("Error response:", error.response);

      if (error.response) {
        if (error.response.status === 422) {
          errors.value = error.response.data.errors;
        } else if (error.response.status === 401) {
          errorMessage.value = "Authentication failed. Please try again.";
        } else {
          errorMessage.value = "An error occurred. Please try again later.";
        }
      } else {
        errorMessage.value = "Network error. Please check your connection.";
      }
    })
    .finally(() => {
      loading.value = false;
    });
}

function togglePasswordVisibility(field) {
  if (field === "password") {
    passwordVisible.value = !passwordVisible.value;
  } else {
    confirmPasswordVisible.value = !confirmPasswordVisible.value;
  }
}

function hasError(field) {
  return errors.value[field] && errors.value[field].length > 0;
}
</script>

<template>
  <GuestLayout>
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8 justify-center" aria-label="Tabs">
        <router-link
          :to="{ name: 'Signup' }"
          :class="[
            route.name === 'Signup'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
          ]"
        >
          Customer
        </router-link>
        <router-link
          :to="{ name: 'Signuplessor' }"
          :class="[
            route.name === 'Signuplessor'
              ? 'border-indigo-600 text-indigo-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
          ]"
        >
          Lessor
        </router-link>
      </nav>
    </div>

    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold tracking-tight text-gray-900">
        Create your client account
      </h2>
      <p class="mt-2 text-sm text-gray-600">
        Fill in your details to get started
      </p>
    </div>

    <!-- Global Error Message -->
    <div
      v-if="errorMessage"
      class="mb-6 p-3 rounded-md bg-red-50 border border-red-200"
    >
      <p class="text-sm text-red-600">{{ errorMessage }}</p>
    </div>

    <!-- Form -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <form @submit.prevent="submit" class="space-y-5">
        <!-- Two column layout for first row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Full name
            </label>
            <div class="mt-1 relative bg-white">
              <input
                type="text"
                name="name"
                id="name"
                v-model="data.name"
                placeholder="Enter your full name"
                :class="[
                  hasError('name')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border',
                ]"
              />
            </div>
            <p v-if="hasError('name')" class="mt-1 text-sm text-red-600">
              {{ errors.name[0] }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1 relative bg-white">
              <input
                type="email"
                name="email"
                id="email"
                autocomplete="email"
                v-model="data.email"
                placeholder="you@example.com"
                :class="[
                  hasError('email')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border',
                ]"
              />
            </div>
            <p v-if="hasError('email')" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>
        </div>

        <!-- Second row - Country and City -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Country -->
          <div>
            <label
              for="country"
              class="block text-sm font-medium text-gray-700"
            >
              Country
            </label>
            <div class="mt-1 bg-white">
              <select
                id="country"
                name="country"
                autocomplete="country-name"
                v-model="data.country"
                :class="[
                  hasError('country')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border',
                ]"
              >
                <option value="" disabled selected>Select your country</option>
                <option>Tunisia</option>
                <option>Canada</option>
                <option>Mexico</option>
                <option>United Kingdom</option>
                <option>France</option>
                <option>Germany</option>
                <option>Japan</option>
              </select>
            </div>
            <p v-if="hasError('country')" class="mt-1 text-sm text-red-600">
              {{ errors.country[0] }}
            </p>
          </div>

          <!-- City -->
          <div>
            <label for="city" class="block text-sm font-medium text-gray-700">
              City
            </label>
            <div class="mt-1 bg-white">
              <input
                type="text"
                name="city"
                id="city"
                autocomplete="address-level2"
                v-model="data.city"
                placeholder="Enter your city"
                :class="[
                  hasError('city')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border',
                ]"
              />
            </div>
            <p v-if="hasError('city')" class="mt-1 text-sm text-red-600">
              {{ errors.city[0] }}
            </p>
          </div>
        </div>

        <!-- Third row - ID and Phone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- ID card number -->
          <div>
            <label for="id" class="block text-sm font-medium text-gray-700">
              ID card number
            </label>
            <div class="mt-1 bg-white">
              <input
                type="number"
                name="id"
                id="id"
                autocomplete="off"
                v-model="data.cin"
                placeholder="Enter your ID number"
                :class="[
                  hasError('cin')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border',
                ]"
              />
            </div>
            <p v-if="hasError('cin')" class="mt-1 text-sm text-red-600">
              {{ errors.cin[0] }}
            </p>
          </div>

          <!-- Phone -->
          <div>
            <label for="tel" class="block text-sm font-medium text-gray-700">
              Phone number
            </label>
            <div class="mt-1 bg-white">
              <input
                type="tel"
                name="tel"
                id="tel"
                autocomplete="tel"
                v-model="data.phone"
                placeholder="Enter your phone number"
                :class="[
                  hasError('phone')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border',
                ]"
              />
            </div>
            <p v-if="hasError('phone')" class="mt-1 text-sm text-red-600">
              {{ errors.phone[0] }}
            </p>
          </div>
        </div>

        <!-- Fourth row - Password fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Password -->
          <div>
            <label
              for="password"
              class="block text-sm font-medium text-gray-700"
            >
              Password
            </label>
            <div class="mt-1 relative bg-white">
              <input
                :type="passwordVisible ? 'text' : 'password'"
                name="password"
                id="password"
                v-model="data.password"
                placeholder="Enter your password"
                :class="[
                  hasError('password')
                    ? 'ring-red-500 border-red-500'
                    : 'ring-gray-300 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
                  'block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border pr-10',
                ]"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500"
                @click="togglePasswordVisibility('password')"
              >
                <svg
                  v-if="passwordVisible"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 12a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                  <path
                    fill-rule="evenodd"
                    d="M10 4C5.478 4 2 7.495 2 10s3.478 6 8 6 8-3.495 8-6-3.478-6-8-6zm0 2c3.67 0 6 2.168 6 4s-2.33 4-6 4-6-2.168-6-4 2.33-4 6-4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                    clip-rule="evenodd"
                  />
                  <path
                    d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"
                  />
                </svg>
              </button>
            </div>
            <p v-if="hasError('password')" class="mt-1 text-sm text-red-600">
              {{ errors.password[0] }}
            </p>
          </div>

          <!-- Password Confirmation -->
          <div>
            <label
              for="password_confirmation"
              class="block text-sm font-medium text-gray-700"
            >
              Confirm password
            </label>
            <div class="mt-1 relative bg-white">
              <input
                :type="confirmPasswordVisible ? 'text' : 'password'"
                name="password_confirmation"
                id="password_confirmation"
                v-model="data.password_confirmation"
                placeholder="Confirm your password"
                class="block w-full rounded-md shadow-sm sm:text-sm py-2 px-3 border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 pr-10"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500"
                @click="togglePasswordVisibility('confirm')"
              >
                <svg
                  v-if="confirmPasswordVisible"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 12a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                  <path
                    fill-rule="evenodd"
                    d="M10 4C5.478 4 2 7.495 2 10s3.478 6 8 6 8-3.495 8-6-3.478-6-8-6zm0 2c3.67 0 6 2.168 6 4s-2.33 4-6 4-6-2.168-6-4 2.33-4 6-4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                    clip-rule="evenodd"
                  />
                  <path
                    d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-indigo-500"
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
            <span v-if="loading">Creating account...</span>
            <span v-else>Create account</span>
          </button>
        </div>
      </form>

      <!-- Login Link -->
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
          Already have an account?
          <router-link
            :to="{ name: 'Login' }"
            class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors"
          >
            Sign in instead
          </router-link>
        </p>
      </div>
    </div>
  </GuestLayout>
</template>
