<script setup>
import GuestLayout from "../../components/GuestLayout.vue";
import {ref} from "vue";
import axiosClient from "../../axios.js";
import router from "../../router.js";
import { useRoute } from "vue-router";


const route = useRoute();

const data = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});


const errors = ref({
  name: [],
  email: [],
  password: [],
})


function submit() { 
  axiosClient.get('/sanctum/csrf-cookie').then(response => {
    axiosClient.post('/lessRegister', data.value) 
    .then(response => {
      router.push({name: 'LessorHome'})
    })
    .catch(error => {
      console.log(error.response.data)
      errors.value = error.response.data.errors;
    })
});
}
</script>

<template>
  <GuestLayout>
    <div class="border-b border-gray-200 mb-8">
        <nav class="-mb-px flex space-x-8 justify-center" aria-label="Tabs">
          <router-link
            :to="{ name: 'Signup' }"
            :class="route.name === 'Signup' 
              ? 'border-indigo-500 text-indigo-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
          >
            Customer
          </router-link>
          <router-link
            :to="{ name: 'Signuplessor' }"
            :class="route.name === 'Signuplessor' 
              ? 'border-indigo-500 text-indigo-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
          >
            Lessor
          </router-link>
        </nav>
      </div>
    <h2 class="mt-1 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Create new lessor account</h2>

    <div class="mt-4 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label for="name" class="block text-sm/6 font-medium text-gray-900">Full name</label>
          <div class="mt-2">
            <input type="text" name="name" id="name" v-model="data.name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
          </div>
          <p class="mt-1 text-sm text-red-600">{{errors.name ? errors.name[0] : '' }}</p>
        </div>
        <div>
          <label for="email" class="block text-sm/6 font-medium text-gray-900">Email adress </label>
          <div class="mt-2">
            <input type="email" name="email" id="email" autocomplete="email" v-model="data.email" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
          </div>
          <p class="mt-1 text-sm text-red-600">{{errors.email ? errors.email[0] : '' }}</p>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">New password</label>
            
          </div>
          <div class="mt-2">
            <input type="password" name="password" id="password" v-model="data.password" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
          </div>
          <p class="mt-1 text-sm text-red-600">{{errors.password ? errors.password[0] : '' }}</p>
        </div>
        <div>
          <div class="flex items-center justify-between">
            <label for="passwordConfirmation" class="block text-sm/6 font-medium text-gray-900">Password confirmation</label>
            
          </div>
          <div class="mt-2">
            <input type="password" name="password" id="passwordConfirmation" v-model="data.password_confirmation" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
          </div>
        </div>

        <div>
          <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Create an account</button>
        </div>
      </form>
      <p class="mt-5 text-center text-sm/6 text-gray-500">
        Alreay have an account? 
        {{ ' ' }}
      <router-link :to="{name: 'Login'}" class="font-semibold text-indigo-600 hover:text-indigo-500">Login in from here</router-link>
      </p>
    </div>        
  </GuestLayout>
</template>

<style scoped>

</style>
