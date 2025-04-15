<script setup>
import GuestLayout from "../../components/GuestLayout.vue";
import { ref, computed } from "vue";
import api from "../../axios.js";
import router from "../../router.js";
import { useRoute } from "vue-router";
import { useToast } from "vue-toast-notification";
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/24/outline";
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
  role: "lessor",
});

const errorMessage = ref("");
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordInputType = computed(() => {
  return showPassword.value ? "text" : "password";
});

const confirmPasswordInputType = computed(() => {
  return showConfirmPassword.value ? "text" : "password";
});

const togglePasswordVisibility = (field) => {
  if (field === "password") {
    showPassword.value = !showPassword.value;
  } else {
    showConfirmPassword.value = !showConfirmPassword.value;
  }
};
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

function hasError(field) {
  return errors.value[field] && errors.value[field].length > 0;
}
</script>

<template>
  <div class="flex flex-col md:flex-row h-screen w-screen overflow-hidden">
    <!-- Blue left side panel with reversed primary colors -->
    <div
      class="relative w-full md:w-1/2 flex flex-col items-center justify-center text-white overflow-hidden bg-gradient-to-br from-[#135CA5] to-[#28BBDD] py-12 md:py-0"
    >
      <!-- Content: Logo + Headings -->
      <div class="company-logo mb-6 z-10">
        <span
          class="text-xl md:text-2xl font-bold rounded-full bg-white/20 px-4 py-2 flex items-center shadow-glow"
        >
          <svg
            class="w-5 h-5 md:w-6 md:h-6 mr-2"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 9a2 2 0 100-4 2 2 0 000 4z"
              clip-rule="evenodd"
            />
          </svg>

          RentEase
        </span>
      </div>

      <div class="text-center z-10 max-w-xs px-4 md:px-0">
        <h2 class="text-2xl md:text-3xl font-bold mb-3">Join our community</h2>
      </div>

      <!-- Background container for grid + waves -->
      <div class="absolute inset-0 z-0">
        <!-- Grid overlay -->
        <div class="grid-background"></div>

        <!-- Circles or subtle decorative dots (optional) -->
        <div class="circle-decor circle-1"></div>
        <div class="circle-decor circle-2"></div>

        <!-- Wave effect at bottom (SVG) -->
        <svg
          class="absolute bottom-0 left-0 w-full h-32"
          viewBox="0 0 1440 320"
          preserveAspectRatio="none"
        >
          <path
            fill="rgba(255, 255, 255, 0.08)"
            d="M0,224L60,213.3C120,203,240,181,360,165.3C480,149,600,139,720,154.7C840,171,960,213,1080,208C1200,203,1320,149,1380,122.7L1440,96L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"
          ></path>
        </svg>
      </div>
    </div>

    <!-- White right side panel -->
    <div
      class="bg-white w-full md:w-1/2 flex flex-col overflow-y-auto py-6 px-4 md:py-0 md:px-0"
    >
      <!-- Added a top margin to move the form lower -->
      <div class="max-w-md mx-auto w-full px-4 md:px-8 mt-12">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-4">
          <nav class="-mb-px flex space-x-8 justify-center" aria-label="Tabs">
            <router-link
              :to="{ name: 'Signup' }"
              :class="[
                route.name === 'Signup'
                  ? 'border-[#135CA5] text-[#135CA5]'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-base transition-colors',
              ]"
            >
              Customer
            </router-link>
            <router-link
              :to="{ name: 'Signuplessor' }"
              :class="[
                route.name === 'Signuplessor'
                  ? 'border-[#135CA5] text-[#135CA5]'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-base transition-colors',
              ]"
            >
              Lessor
            </router-link>
          </nav>
        </div>

        <h2 class="text-2xl font-bold text-[#135CA5] mb-3">Sign Up</h2>
        <p class="text-gray-500 text-sm mb-6">
          Create your RentEase account to get started with your rental journey.
        </p>

        <div v-if="errorMessage" class="mb-4">
          <div
            class="bg-red-50 border border-red-200 rounded-md p-4 text-sm text-red-600"
          >
            <div class="flex">
              <div class="flex-shrink-0">
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

        <form @submit.prevent="submit" class="space-y-4 pb-6" novalidate>
          <!-- Name -->
          <div>
            <div class="relative">
              <input
                type="text"
                name="name"
                id="name"
                v-model="data.name"
                placeholder="Full Name"
                class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                :class="{ 'border-red-300': errors.name.length }"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="data.name"
              ></div>
            </div>
            <p v-if="errors.name.length" class="mt-1 text-sm text-red-600">
              {{ errors.name[0] }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <div class="relative">
              <input
                type="email"
                name="email"
                id="email"
                autocomplete="email"
                v-model="data.email"
                placeholder="Email ID"
                class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                :class="{ 'border-red-300': errors.email.length }"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="data.email"
              ></div>
            </div>
            <p v-if="errors.email.length" class="mt-1 text-sm text-red-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Country and City -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Country -->
            <div>
              <div class="relative">
                <select
                  id="country"
                  name="country"
                  v-model="data.country"
                  class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                  :class="{ 'border-red-300': errors.country.length }"
                >
                  <option value="" disabled selected>Select Country</option>
                  <option>Tunisia</option>
                  <option>Canada</option>
                  <option>Mexico</option>
                  <option>United Kingdom</option>
                  <option>France</option>
                  <option>Germany</option>
                  <option>Japan</option>
                </select>
                <div
                  class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                  v-if="data.country"
                ></div>
              </div>
              <p v-if="errors.country.length" class="mt-1 text-sm text-red-600">
                {{ errors.country[0] }}
              </p>
            </div>

            <!-- City -->
            <div>
              <div class="relative">
                <input
                  type="text"
                  name="city"
                  id="city"
                  v-model="data.city"
                  placeholder="City"
                  class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                  :class="{ 'border-red-300': errors.city.length }"
                />
                <div
                  class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                  v-if="data.city"
                ></div>
              </div>
              <p v-if="errors.city.length" class="mt-1 text-sm text-red-600">
                {{ errors.city[0] }}
              </p>
            </div>
          </div>

          <!-- ID and Phone -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- ID card number -->
            <div>
              <div class="relative">
                <input
                  type="number"
                  name="cin"
                  id="cin"
                  v-model="data.cin"
                  placeholder="ID Card Number"
                  class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                  :class="{ 'border-red-300': errors.cin.length }"
                />
                <div
                  class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                  v-if="data.cin"
                ></div>
              </div>
              <p v-if="errors.cin.length" class="mt-1 text-sm text-red-600">
                {{ errors.cin[0] }}
              </p>
            </div>

            <!-- Phone -->
            <div>
              <div class="relative">
                <input
                  type="tel"
                  name="phone"
                  id="phone"
                  v-model="data.phone"
                  placeholder="Phone Number"
                  class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                  :class="{ 'border-red-300': errors.phone.length }"
                />
                <div
                  class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                  v-if="data.phone"
                ></div>
              </div>
              <p v-if="errors.phone.length" class="mt-1 text-sm text-red-600">
                {{ errors.phone[0] }}
              </p>
            </div>
          </div>

          <!-- Password -->
          <div>
            <div class="relative">
              <input
                :type="passwordInputType"
                name="password"
                id="password"
                autocomplete="new-password"
                v-model="data.password"
                placeholder="Password"
                class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
                :class="{ 'border-red-300': errors.password.length }"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="data.password"
              ></div>
              <button
                type="button"
                @click="togglePasswordVisibility('password')"
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

          <!-- Confirm Password -->
          <div>
            <div class="relative">
              <input
                :type="confirmPasswordInputType"
                name="password_confirmation"
                id="password_confirmation"
                autocomplete="new-password"
                v-model="data.password_confirmation"
                placeholder="Confirm Password"
                class="w-full p-2 border-2 border-gray-200 rounded-md bg-gray-50 focus:bg-white focus:outline-none focus:border-[#135CA5]"
              />
              <div
                class="absolute left-0 top-0 bottom-0 w-2 bg-[#135CA5] rounded-l-md"
                v-if="data.password_confirmation"
              ></div>
              <button
                type="button"
                @click="togglePasswordVisibility('confirm')"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
              >
                <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                <EyeSlashIcon v-else class="h-5 w-5" />
              </button>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-[#135CA5] hover:bg-[#28BBDD] focus:outline-none transition-colors duration-200 uppercase tracking-wide"
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
              <span v-if="loading">Creating account...</span>
              <span v-else>Create Account</span>
            </button>
          </div>
        </form>

        <p class="mt-4 mb-6 text-center text-sm text-gray-600">
          Already have an account?
          <router-link
            :to="{ name: 'Login' }"
            class="font-semibold text-[#135CA5] hover:text-[#28BBDD]"
          >
            Sign In
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Enhanced blue panel styling */
.shadow-glow {
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
}

/* Grid background */
.grid-background {
  position: absolute;
  width: 100%;
  height: 100%;
  background-image: linear-gradient(
      to right,
      rgba(255, 255, 255, 0.08) 1px,
      transparent 1px
    ),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
  background-size: 40px 40px;
  opacity: 0.8;
}

/* Optional circle decorations */
.circle-decor {
  position: absolute;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.06);
}

.circle-1 {
  width: 100px;
  height: 100px;
  top: 20%;
  left: 10%;
}

.circle-2 {
  width: 140px;
  height: 140px;
  bottom: 25%;
  right: 10%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .circle-decor {
    display: none;
  }
}
</style>
