<script setup>
import { ref } from "vue";
import GuestLayout from "../../components/GuestLayout.vue";
import router from "../../router.js";
import api from "../../axios.js";

const data = ref({
  email: '',
  password: ''
})

const error = ref('')

const errorMessage = ref('')

function submit() {
  api.get('/sanctum/csrf-cookie').then(() => {
    api.post('/api/login', data.value)
      .then(response => {
        localStorage.setItem('auth_token', response.data.token);
        api.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
        const role = response.data.user.role; 

        if (role === 'client') {
          router.push({ name: 'Home' }); 
        } else if (role === 'lessor') {
          router.push({ name: 'LessorHome' });
        }
      })
      .catch(error => {
        console.log(error.response);
        errorMessage.value = error.response.data.message;
      });
  });
}

</script>

<template>
    <GuestLayout>
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Welcome back client</h2>
        <div v-if="errorMessage" class="y-2 px-3 mt-4 rounded text-white bg-red-400">{{ errorMessage }}</div>

    <div class="mt-4 sm:mx-auto sm:w-full sm:max-w-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
          <div class="mt-2">
            <input type="email" name="email" id="email" autocomplete="email" required="" v-model="data.email" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
            <div class="text-sm">
              <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
            </div>
          </div>
          <div class="mt-2">
            <input type="password" name="password" id="password" autocomplete="current-password" required="" v-model="data.password" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
          </div>
        </div>

        <div>
          <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign in</button>
        </div>
      </form>

      
      <p class="mt-4 text-center text-sm/6 text-gray-500">
        Not a member?
        {{ ' ' }}
      <router-link :to="{name: 'Signup'}" class="font-semibold text-indigo-600 hover:text-indigo-500">Click to create a new account</router-link>
      </p>
    </div>
    </GuestLayout>
</template>


<style scoped>

</style>
