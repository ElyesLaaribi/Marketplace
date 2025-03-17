<script setup>
import DefaultLayout from "../../components/DefaultLayout.vue";
import { PhotoIcon, UserCircleIcon } from "@heroicons/vue/24/solid";
import { ChevronDownIcon } from "@heroicons/vue/16/solid";
</script>

<template>
  <DefaultLayout>
    <header class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Profile</h1>
      </div>
    </header>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <form>
        <div class="space-y-8">
          <!-- Public Profile Section -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-900/10 pb-6">
              <h2 class="text-xl font-semibold text-gray-900">Profile</h2>
              <p class="mt-1 text-sm text-gray-600">
                This information will be displayed publicly so be careful what
                you share.
              </p>

              <!-- Profile Photo Upload (Added feature) -->
              <div class="mt-6">
                <div class="flex items-center gap-x-3">
                  <UserCircleIcon
                    class="h-12 w-12 text-gray-300"
                    aria-hidden="true"
                  />
                  <button
                    type="button"
                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                  >
                    Change
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Personal Information Section -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="pb-6">
              <h2 class="text-xl font-semibold text-gray-900">
                Personal Information
              </h2>
              <p class="mt-1 text-sm text-gray-600">
                Use a permanent address where you can receive mail.
              </p>

              <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- First row: Name and Email -->
                <div class="sm:col-span-3">
                  <label
                    for="first-name"
                    class="block text-sm font-medium text-gray-900"
                    >Full Name</label
                  >
                  <div class="mt-2">
                    <input
                      type="text"
                      name="first-name"
                      id="first-name"
                      autocomplete="given-name"
                      placeholder="Enter your full name"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                  </div>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="email"
                    class="block text-sm font-medium text-gray-900"
                    >Email address</label
                  >
                  <div class="mt-2">
                    <input
                      id="email"
                      name="email"
                      type="email"
                      autocomplete="email"
                      placeholder="you@example.com"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                  </div>
                </div>

                <!-- Second row: Phone and CIN (Added fields) -->
                <div class="sm:col-span-3">
                  <label
                    for="phone"
                    class="block text-sm font-medium text-gray-900"
                    >Phone Number</label
                  >
                  <div class="mt-2">
                    <input
                      id="phone"
                      name="phone"
                      type="tel"
                      autocomplete="tel"
                      placeholder="(+216) 00-000-000"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                  </div>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="cin"
                    class="block text-sm font-medium text-gray-900"
                    >CIN</label
                  >
                  <div class="mt-2">
                    <input
                      id="cin"
                      name="cin"
                      type="text"
                      placeholder="Customer Identification Number"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                  </div>
                </div>

                <!-- Third row: Country and City -->
                <div class="sm:col-span-3">
                  <label
                    for="country"
                    class="block text-sm font-medium text-gray-900"
                    >Country</label
                  >
                  <div class="mt-2">
                    <select
                      id="country"
                      name="country"
                      autocomplete="country-name"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    >
                      <option>Tunisia</option>
                      <option>Canada</option>
                      <option>Mexico</option>
                      <option>United Kingdom</option>
                      <option>France</option>
                      <option>Germany</option>
                      <option>Japan</option>
                      <!-- Add more countries as needed -->
                    </select>
                  </div>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="city"
                    class="block text-sm font-medium text-gray-900"
                    >City</label
                  >
                  <div class="mt-2">
                    <input
                      type="text"
                      name="city"
                      id="city"
                      autocomplete="address-level2"
                      placeholder="Enter your city"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex items-center justify-end gap-x-6">
          <button
            type="button"
            class="text-sm font-semibold text-gray-900 hover:text-gray-700"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  </DefaultLayout>
</template>
