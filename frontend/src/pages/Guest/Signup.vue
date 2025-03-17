<script setup>
import GuestLayout from "../../components/GuestLayout.vue";
import { ref } from "vue";
import api from "../../axios.js";
import router from "../../router.js";
import { useRoute } from "vue-router";

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
          console.error("Unauthorized - Possible Sanctum issue");
        } else {
          console.error("Other error:", error.response.data);
        }
      } else {
        console.error("Request failed without a response", error);
      }
    })
    .finally(() => {
      loading.value = false;
    });
}
</script>

<template>
  <GuestLayout>
    <div class="border-b border-gray-200 mb-8">
      <nav class="-mb-px flex space-x-8 justify-center" aria-label="Tabs">
        <router-link
          :to="{ name: 'Signup' }"
          :class="
            route.name === 'Signup'
              ? 'border-indigo-500 text-indigo-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          "
          class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
        >
          Customer
        </router-link>
        <router-link
          :to="{ name: 'Signuplessor' }"
          :class="
            route.name === 'Signuplessor'
              ? 'border-indigo-500 text-indigo-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          "
          class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
        >
          Lessor
        </router-link>
      </nav>
    </div>
    <h2
      class="mt-1 text-center text-2xl/9 font-bold tracking-tight text-gray-900"
    >
      Create new client account
    </h2>

    <div class="mt-1 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label for="name" class="block text-sm/6 font-medium text-gray-900"
            >Full name</label
          >
          <div class="mt-1">
            <input
              type="text"
              name="name"
              id="name"
              v-model="data.name"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
          <p class="mt-1 text-sm text-red-600">
            {{ errors.name ? errors.name[0] : "" }}
          </p>
        </div>
        <div>
          <label for="email" class="block text-sm/6 font-medium text-gray-900"
            >Email address</label
          >
          <div class="mt-1">
            <input
              type="email"
              name="email"
              id="email"
              autocomplete="email"
              v-model="data.email"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
          <p class="mt-1 text-sm text-red-600">
            {{ errors.email ? errors.email[0] : "" }}
          </p>
        </div>
        <!-- new attributes -->
        <div>
          <label for="country" class="block text-sm/6 font-medium text-gray-900"
            >Country</label
          >
          <div class="mt-1">
            <input
              type="text"
              name="country"
              id="country"
              autocomplete="country"
              v-model="data.country"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
          <p class="mt-1 text-sm text-red-600">
            {{ errors.country ? errors.country[0] : "" }}
          </p>
        </div>
        <div>
          <label for="city" class="block text-sm/6 font-medium text-gray-900"
            >City</label
          >
          <div class="mt-1">
            <input
              type="text"
              name="city"
              id="city"
              autocomplete="city"
              v-model="data.city"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
          <p class="mt-1 text-sm text-red-600">
            {{ errors.city ? errors.city[0] : "" }}
          </p>
        </div>
        <div>
          <label for="id" class="block text-sm/6 font-medium text-gray-900"
            >ID card number</label
          >
          <div class="mt-1">
            <input
              type="number"
              name="id"
              id="id"
              autocomplete="off"
              v-model="data.cin"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
          <p class="mt-1 text-sm text-red-600">
            {{ errors.cin ? errors.cin[0] : "" }}
          </p>
        </div>
        <div>
          <label for="tel" class="block text-sm/6 font-medium text-gray-900"
            >Phone number</label
          >
          <div class="mt-2">
            <input
              type="tel"
              name="tel"
              id="tel"
              autocomplete="tel"
              v-model="data.phone"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
          <p class="mt-1 text-sm text-red-600">
            {{ errors.phone ? errors.phone[0] : "" }}
          </p>
        </div>
        <!-- end -->
        <div>
          <div class="flex items-center justify-between">
            <label
              for="password"
              class="block text-sm/6 font-medium text-gray-900"
              >New password</label
            >
          </div>
          <div class="mt-1">
            <input
              type="password"
              name="password"
              id="password"
              v-model="data.password"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
            <p class="mt-1 text-sm text-red-600">
              {{ errors.password ? errors.password[0] : "" }}
            </p>
          </div>
        </div>
        <div>
          <div class="flex items-center justify-between">
            <label
              for="password_confirmation"
              class="block text-sm/6 font-medium text-gray-900"
              >Password confirmation</label
            >
          </div>
          <div class="mt-1">
            <input
              type="password"
              name="password"
              id="password_confirmation"
              v-model="data.password_confirmation"
              class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
            />
          </div>
        </div>
        <div>
          <button
            type="submit"
            :disabled="loading"
            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
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
            <span v-if="loading">Creating...</span>
            <span v-else>Create an account</span>
          </button>
        </div>
      </form>
      <p class="mt-5 text-center text-sm/6 text-gray-500">
        Already have an account?
        {{ " " }}
        <router-link
          :to="{ name: 'Login' }"
          class="font-semibold text-indigo-600 hover:text-indigo-500"
          >Login in from here</router-link
        >
      </p>
    </div>
  </GuestLayout>
</template>

<style scoped></style>
