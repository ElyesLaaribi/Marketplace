<template>
  <div class="map-input-container">
    <div class="relative mb-2">
      <input
        v-model="address"
        type="text"
        class="block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 text-sm"
        placeholder="Enter address"
      />
      <button
        @click="getUserLocation"
        type="button"
        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-indigo-600 text-white p-1 rounded hover:bg-indigo-700"
        title="Use my current location"
      >
        <svg
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
            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
          />
        </svg>
      </button>
    </div>

    <div
      v-if="loading"
      class="flex justify-center items-center py-2 text-indigo-600"
    >
      <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
          fill="none"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
      Loading location...
    </div>

    <div v-if="locationFound" class="mt-2 text-sm bg-gray-100 p-2 rounded-md">
      <strong>Selected location:</strong> {{ address }}
      <div class="text-xs text-gray-600 mt-1">
        Latitude: {{ latitude }}, Longitude: {{ longitude }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

// Define props for the component
const props = defineProps({
  initialAddress: {
    type: String,
    default: "",
  },
  initialLatitude: {
    type: Number,
    default: null,
  },
  initialLongitude: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(["update:location"]);

// Component state
const address = ref(props.initialAddress || "");
const latitude = ref(props.initialLatitude || null);
const longitude = ref(props.initialLongitude || null);
const loading = ref(false);
const locationFound = computed(
  () => latitude.value !== null && longitude.value !== null
);

// Reverse geocoding function (coordinates to address)
const reverseGeocode = async (lat, lng) => {
  try {
    loading.value = true;
    const response = await axios.get(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
      {
        headers: {
          "User-Agent": "YourAppName/1.0",
          "Accept-Language": "en",
        },
      }
    );
    if (response.data && response.data.display_name) {
      address.value = response.data.display_name;
    }
  } catch (error) {
    console.error("Error during reverse geocoding:", error);
  } finally {
    loading.value = false;
  }
};

// Forward geocoding function (address to coordinates)
const searchAddress = async () => {
  if (!address.value.trim()) return;

  try {
    loading.value = true;
    const encodedAddress = encodeURIComponent(address.value);
    const response = await axios.get(
      `https://nominatim.openstreetmap.org/search?format=json&q=${encodedAddress}&limit=1`,
      {
        headers: {
          "User-Agent": "YourAppName/1.0",
          "Accept-Language": "en",
        },
      }
    );

    if (response.data && response.data.length > 0) {
      const result = response.data[0];
      latitude.value = parseFloat(result.lat);
      longitude.value = parseFloat(result.lon);
      updateLocation();
    }
  } catch (error) {
    console.error("Error during address search:", error);
  } finally {
    loading.value = false;
  }
};

// Get user's current location
const getUserLocation = () => {
  if (navigator.geolocation) {
    loading.value = true;
    navigator.geolocation.getCurrentPosition(
      async (position) => {
        latitude.value = position.coords.latitude;
        longitude.value = position.coords.longitude;

        await reverseGeocode(latitude.value, longitude.value);
        updateLocation();
        loading.value = false;
      },
      (error) => {
        console.error("Error getting current location:", error);
        loading.value = false;
        alert(
          "Unable to retrieve your location. Please enter your address manually."
        );
      }
    );
  } else {
    alert(
      "Geolocation is not supported by your browser. Please enter your address manually."
    );
  }
};

// Emit location update to parent component
const updateLocation = () => {
  if (latitude.value && longitude.value) {
    emit("update:location", {
      address: address.value || "",
      latitude: latitude.value,
      longitude: longitude.value,
    });
  }
};

// Initialize component
onMounted(() => {
  // If we have initial coordinates, update the state
  if (props.initialLatitude && props.initialLongitude) {
    latitude.value = props.initialLatitude;
    longitude.value = props.initialLongitude;

    if (!props.initialAddress) {
      // If we don't have an address but have coordinates, reverse geocode
      reverseGeocode(latitude.value, longitude.value);
    }
  }
  // If we have an initial address but no coordinates, search for it
  else if (
    props.initialAddress &&
    !props.initialLatitude &&
    !props.initialLongitude
  ) {
    searchAddress();
  }
});

// Watch for address changes to trigger search
let searchTimeout = null;
watch(address, (newAddress) => {
  clearTimeout(searchTimeout);
  if (newAddress && newAddress.trim()) {
    searchTimeout = setTimeout(() => {
      searchAddress();
    }, 800); // Debounce the search
  }
});
</script>
