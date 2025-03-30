<script setup>
import { ref } from "vue";
import { Dialog, DialogPanel } from "@headlessui/vue";
import {
  Bars3Icon,
  XMarkIcon,
  UserCircleIcon,
  HeartIcon,
} from "@heroicons/vue/24/outline";
import { RouterLink } from "vue-router";

const navigation = [
  { name: "Browse Items", href: "#browse" },
  { name: "How It Works", href: "#how-it-works" },
  { name: "About Us", href: "#about" },
  { name: "Testimonials", href: "#testimonials" },
];

const categories = [
  {
    name: "Electronics",
    icon: "📱",
    href: "#electronics",
    description:
      "Cameras, projectors, speakers, and more for your next event or project.",
    bgColor: "bg-blue-50",
  },
  {
    name: "Tools",
    icon: "🔨",
    href: "#tools",
    description:
      "Professional-grade tools for home improvement, construction, and DIY projects.",
    bgColor: "bg-amber-50",
  },
  {
    name: "Outdoor",
    icon: "🏕️",
    href: "#outdoor",
    description:
      "Camping gear, bikes, kayaks, and everything you need for outdoor adventures.",
    bgColor: "bg-green-50",
  },
  {
    name: "Party",
    icon: "🎉",
    href: "#party",
    description:
      "Tables, chairs, decorations, and equipment to make your event unforgettable.",
    bgColor: "bg-pink-50",
  },
];

const featuredItems = [
  {
    id: 1,
    name: "DSLR Camera",
    price: "$25/day",
    image:
      "https://tailwindui.com/plus-assets/img/ecommerce-images/category-page-04-image-card-01.jpg",
    rating: 4.8,
  },
  {
    id: 2,
    name: "Mountain Bike",
    price: "$15/day",
    image:
      "https://tailwindui.com/plus-assets/img/ecommerce-images/category-page-04-image-card-02.jpg",
    rating: 4.9,
  },
  {
    id: 3,
    name: "Tent (4-Person)",
    price: "$20/day",
    image:
      "https://tailwindui.com/plus-assets/img/ecommerce-images/category-page-04-image-card-03.jpg",
    rating: 4.7,
  },
];

const testimonials = [
  {
    id: 1,
    text: "This platform saved me so much money when I needed equipment for my weekend project!",
    author: "Jamie L.",
    role: "DIY Enthusiast",
  },
  {
    id: 2,
    text: "I love being able to rent out my tools when I'm not using them. Extra income with minimal effort.",
    author: "Sam K.",
    role: "Tool Owner",
  },
];

const mobileMenuOpen = ref(false);
</script>

<template>
  <div class="bg-white">
    <header class="absolute inset-x-0 top-0 z-50">
      <nav
        class="flex items-center justify-between p-6 lg:px-8"
        aria-label="Global"
      >
        <div class="flex lg:flex-1">
          <a href="#" class="-m-1.5 p-1.5 flex items-center">
            <span class="sr-only">RentEase</span>
            <img
              class="h-10 w-auto"
              src="https://tailwindui.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
              alt=""
            />
            <span class="ml-2 text-xl font-bold text-indigo-600">RentEase</span>
          </a>
        </div>
        <div class="flex lg:hidden">
          <button
            type="button"
            class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
            @click="mobileMenuOpen = true"
          >
            <span class="sr-only">Open main menu</span>
            <Bars3Icon class="size-6" aria-hidden="true" />
          </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
          <a
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="text-sm/6 font-semibold text-gray-900 hover:text-indigo-600 transition-colors"
            >{{ item.name }}</a
          >
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:gap-x-6">
          <router-link
            :to="{ name: 'Login' }"
            class="text-sm/6 font-semibold text-gray-900 hover:text-indigo-600 transition-colors"
            >Log in <span aria-hidden="true">&rarr;</span></router-link
          >
        </div>
      </nav>
      <Dialog
        class="lg:hidden"
        @close="mobileMenuOpen = false"
        :open="mobileMenuOpen"
      >
        <div class="fixed inset-0 z-50 bg-white" />
        <DialogPanel
          class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10"
        >
          <div class="flex items-center justify-between">
            <a href="#" class="-m-1.5 p-1.5 flex items-center">
              <span class="sr-only">RentEase</span>
              <img
                class="h-8 w-auto"
                src="https://tailwindui.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600"
                alt=""
              />
              <span class="ml-2 text-lg font-bold text-indigo-600"
                >RentEase</span
              >
            </a>
            <button
              type="button"
              class="-m-2.5 rounded-md p-2.5 text-gray-700"
              @click="mobileMenuOpen = false"
            >
              <span class="sr-only">Close menu</span>
              <XMarkIcon class="size-6" aria-hidden="true" />
            </button>
          </div>
          <div class="mt-6 flow-root">
            <div class="-my-6 divide-y divide-gray-500/10">
              <div class="space-y-2 py-6">
                <a
                  v-for="item in navigation"
                  :key="item.name"
                  :href="item.href"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50"
                  >{{ item.name }}</a
                >
              </div>
              <div class="py-6">
                <router-link
                  :to="{ name: 'Login' }"
                  class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50"
                  >Log in</router-link
                >
                <router-link
                  :to="{ name: 'Signup' }"
                  class="-mx-3 mt-2 block rounded-lg bg-indigo-600 px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-indigo-500"
                  >Sign up</router-link
                >
              </div>
            </div>
          </div>
        </DialogPanel>
      </Dialog>
    </header>

    <!-- Hero Section with Background Color Instead of Image -->
    <div class="relative isolate">
      <!-- Solid background color instead of image -->
      <div class="absolute inset-0 -z-10"></div>

      <div class="relative px-6 pt-2 lg:px-8">
        <div
          class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
          aria-hidden="true"
        >
          <div
            class="relative left-[calc(50%-11rem)] aspect-1155/678 w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
          />
        </div>
        <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
          <div class="text-center">
            <h1
              class="text-5xl font-bold tracking-tight text-balance text-gray-900 sm:text-7xl"
            >
              Rent with ease, find with confidence
            </h1>
            <p
              class="mt-8 text-lg font-medium text-pretty text-gray-600 sm:text-xl/8"
            >
              Your peer-to-peer rental marketplace - simple, secure, and
              hassle-free. Save money and reduce waste by renting what you need.
            </p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
              <router-link
                :to="{ name: 'Signup' }"
                class="rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                >Get started</router-link
              >
              <a
                href="#how-it-works"
                class="text-base font-semibold text-gray-600 hover:text-indigo-600 transition-colors"
                >Learn more <span aria-hidden="true">→</span></a
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Category Section -->
    <div id="browse" class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2
            class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl"
          >
            Browse by category
          </h2>
          <p class="mt-4 text-lg text-gray-600">
            Find exactly what you need from our extensive collection of rental
            items.
          </p>
        </div>

        <!-- Improved category cards with better visuals -->
        <div class="mx-auto mt-16 max-w-7xl">
          <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div
              v-for="category in categories"
              :key="category.name"
              :class="[
                category.bgColor,
                'group relative overflow-hidden rounded-lg shadow transition-all duration-300 hover:shadow-lg transform hover:-translate-y-1',
              ]"
            >
              <div
                class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent opacity-60"
              ></div>
              <div class="relative p-8">
                <div class="text-4xl mb-4">{{ category.icon }}</div>
                <h3
                  class="text-xl font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors"
                >
                  {{ category.name }}
                </h3>
                <p class="mt-2 text-gray-600">
                  {{ category.description }}
                </p>
                <div class="mt-6">
                  <a
                    :href="category.href"
                    class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                  >
                    Explore {{ category.name }}
                    <span class="ml-1">→</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Items Section -->
    <div class="bg-gray-50 py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2
            class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl"
          >
            Featured items
          </h2>
          <p class="mt-4 text-lg text-gray-600">
            Our most popular rental items this week.
          </p>
        </div>

        <div
          class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3"
        >
          <article
            v-for="item in featuredItems"
            :key="item.id"
            class="flex flex-col items-start bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow"
          >
            <div class="h-56 w-full relative">
              <img
                :src="item.image"
                alt=""
                class="h-full w-full object-cover"
              />
              <button
                class="absolute top-2 right-2 p-1.5 rounded-full bg-white/80 hover:bg-white text-gray-600 hover:text-red-500 transition-colors"
              >
                <HeartIcon class="h-5 w-5" />
              </button>
            </div>
            <div class="max-w-xl p-5">
              <div class="flex items-center gap-x-4 text-xs mb-3">
                <div class="flex items-center gap-1">
                  <span class="text-yellow-400">★★★★★</span>
                  <span class="text-gray-500 font-medium">{{
                    item.rating
                  }}</span>
                </div>
                <span class="font-medium text-indigo-600">{{
                  item.price
                }}</span>
              </div>
              <div class="group">
                <h3
                  class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600"
                >
                  <a href="#">{{ item.name }}</a>
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                  Available now in your area. Includes free delivery for rentals
                  over 3 days.
                </p>
              </div>
              <div class="mt-4">
                <a
                  href="#"
                  class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                >
                  View details →
                </a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>

    <!-- How It Works Section -->
    <div id="how-it-works" class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2
            class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl"
          >
            How it works
          </h2>
          <p class="mt-4 text-lg text-gray-600">
            Renting has never been easier. Follow these simple steps to get
            started.
          </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
          <dl
            class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3"
          >
            <div class="flex flex-col items-center">
              <div
                class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 text-white text-2xl font-bold"
              >
                1
              </div>
              <dt class="mt-6 text-lg font-semibold text-gray-900">
                Browse & Search
              </dt>
              <dd class="mt-2 text-base text-center text-gray-600">
                Find what you need from thousands of items available in your
                area. Filter by category, price, and availability.
              </dd>
            </div>
            <div class="flex flex-col items-center">
              <div
                class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 text-white text-2xl font-bold"
              >
                2
              </div>
              <dt class="mt-6 text-lg font-semibold text-gray-900">
                Book & Pay
              </dt>
              <dd class="mt-2 text-base text-center text-gray-600">
                Reserve your items securely through our platform. All payments
                are protected with our satisfaction guarantee.
              </dd>
            </div>
            <div class="flex flex-col items-center">
              <div
                class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 text-white text-2xl font-bold"
              >
                3
              </div>
              <dt class="mt-6 text-lg font-semibold text-gray-900">
                Pick Up & Return
              </dt>
              <dd class="mt-2 text-base text-center text-gray-600">
                Arrange pickup or delivery with the owner. Return the item in
                the same condition when you're done.
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Testimonials Section -->
    <div id="testimonials" class="bg-gray-50 py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2
            class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl"
          >
            What our users say
          </h2>
          <p class="mt-4 text-lg text-gray-600">
            Don't just take our word for it - hear from our happy users.
          </p>
        </div>
        <div
          class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-2"
        >
          <div
            v-for="testimonial in testimonials"
            :key="testimonial.id"
            class="flex flex-col justify-between bg-white p-8 shadow-sm rounded-xl hover:shadow-md transition-shadow"
          >
            <blockquote class="text-xl italic text-gray-800">
              "{{ testimonial.text }}"
            </blockquote>
            <div class="mt-6 flex items-center">
              <div class="flex-shrink-0">
                <UserCircleIcon class="h-12 w-12 text-gray-400" />
              </div>
              <div class="ml-4">
                <div class="font-medium text-gray-900">
                  {{ testimonial.author }}
                </div>
                <div class="text-gray-500">{{ testimonial.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-indigo-600">
      <div class="px-6 py-24 sm:px-6 sm:py-32 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Ready to start renting?
          </h2>
          <p class="mx-auto mt-6 max-w-xl text-lg text-indigo-100">
            Join thousands of users saving money and reducing waste by renting
            items instead of buying them.
          </p>
          <div class="mt-10 flex items-center justify-center gap-x-6">
            <router-link
              :to="{ name: 'Signup' }"
              class="rounded-md bg-white px-5 py-3 text-base font-semibold text-indigo-600 shadow-sm hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-colors"
              >Sign up now</router-link
            >
            <a
              href="#browse"
              class="text-base font-semibold text-white hover:text-indigo-100 transition-colors"
              >Browse items <span aria-hidden="true">→</span></a
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900">
      <div
        class="mx-auto max-w-7xl px-6 py-12 md:flex md:items-center md:justify-between lg:px-8"
      >
        <div class="flex justify-center space-x-6 md:order-2">
          <a href="#" class="text-gray-400 hover:text-gray-300">
            <span class="sr-only">Facebook</span>
            <svg
              class="h-6 w-6"
              fill="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                clip-rule="evenodd"
              />
            </svg>
          </a>
          <a href="#" class="text-gray-400 hover:text-gray-300">
            <span class="sr-only">Instagram</span>
            <svg
              class="h-6 w-6"
              fill="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                clip-rule="evenodd"
              />
            </svg>
          </a>
          <a href="#" class="text-gray-400 hover:text-gray-300">
            <span class="sr-only">Twitter</span>
            <svg
              class="h-6 w-6"
              fill="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path
                d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"
              />
            </svg>
          </a>
        </div>
        <div class="mt-8 md:order-1 md:mt-0">
          <p class="text-center text-sm text-gray-400">
            &copy; 2025 RentEase, Inc. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Background styles */
.bg-white {
  background-color: #ffffff;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.bg-gray-900 {
  background-color: #111827;
}

.bg-indigo-600 {
  background-color: #4f46e5;
}

.bg-indigo-700 {
  background-color: #4338ca;
}

/* Background opacity and overlay styles */
.bg-white\/80 {
  background-color: rgb(255 255 255 / 0.8);
}

.bg-black {
  background-color: #000000;
}

.opacity-50 {
  opacity: 0.5;
}

.opacity-60 {
  opacity: 0.6;
}

/* Background styles */
.bg-white {
  background-color: #ffffff;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.bg-gray-900 {
  background-color: #111827;
}

.bg-indigo-600 {
  background-color: #4f46e5;
}

.bg-indigo-700 {
  background-color: #4338ca;
}

/* Background opacity and overlay styles */
.bg-white\/80 {
  background-color: rgb(255 255 255 / 0.8);
}

.bg-black {
  background-color: #000000;
}

.opacity-50 {
  opacity: 0.5;
}

.opacity-60 {
  opacity: 0.6;
}
</style>
