<script setup>

import axiosClient from '../axios.js';
import router from '../router.js';
import { computed } from 'vue';
import useLessorStore from '../store/lessor.js';

const userStore = useLessorStore();
const user = computed(() => userStore.user);

function logout() {
  axiosClient.post('/lessor/logout').then(() => {
    userStore.user = null;
    router.push({ name: 'Lesslogin' }).then(() => {
      window.location.reload();
    });
  });

}
</script>

<template>
  <div>
    <div class="w-screen h-screen">
       <!-- side bar -->
      <div class="w-[200px] h-full bg-gray-200">
        Side bar
      </div>

     </div>
    <!-- <h1>Lessor Home</h1>
    <button @click="logout" class="p-15">
            Logout
    </button> -->
  <slot>
  </slot>
</div>
</template>

<style scoped>

</style>
