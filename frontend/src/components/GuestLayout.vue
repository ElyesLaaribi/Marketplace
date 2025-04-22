<template>
  <div
    class="mt-1flex min-h-screen flex-1 flex-col justify-center px-6 py-12 lg:px-8 bg-white"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <img
        class="mx-auto h-50 w-auto"
        src="../assets/images/adminImg.jpg"
        alt="Your Company"
      />
    </div>
    <slot></slot>
  </div>
</template>
