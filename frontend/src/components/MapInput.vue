<template>
  <div class="location-input-container">
    <div class="form-group mb-2">
      <input
        v-model="address"
        class="block w-full border border-gray-300 rounded-md px-3 py-2 placeholder-gray-400 focus:border-indigo-600 focus:ring-indigo-600 text-sm"
        placeholder="Enter address"
        @input="debouncedSearch"
      />
    </div>

    <div
      v-if="selectedLocation"
      class="mt-2 text-sm bg-gray-100 p-2 rounded-md"
    >
      <strong>Selected location:</strong> {{ selectedLocation.address }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

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
  apiKey: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["update:location"]);

const address = ref(props.initialAddress || "");
const selectedLocation = ref(null);
const googleMapsLoaded = ref(false);
let searchTimeout = null;

if (props.initialLatitude && props.initialLongitude) {
  selectedLocation.value = {
    address: props.initialAddress,
    latitude: props.initialLatitude,
    longitude: props.initialLongitude,
  };
}

const searchAddress = async () => {
  if (!address.value || !googleMapsLoaded.value) return;

  try {
    const geocoder = new google.maps.Geocoder();
    const response = await new Promise((resolve, reject) => {
      geocoder.geocode({ address: address.value }, (results, status) => {
        if (status === "OK" && results[0]) {
          resolve(results);
        } else {
          reject(status);
        }
      });
    });

    const results = response;
    if (results[0]) {
      const location = results[0].geometry.location;

      selectedLocation.value = {
        address: results[0].formatted_address,
        latitude: location.lat(),
        longitude: location.lng(),
      };

      emit("update:location", selectedLocation.value);
    }
  } catch (error) {
    console.error("Geocoding failed:", error);
  }
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(searchAddress, 500);
};

const loadGoogleMapsScript = () => {
  return new Promise((resolve, reject) => {
    if (window.google && window.google.maps) {
      googleMapsLoaded.value = true;
      return resolve();
    }

    const script = document.createElement("script");
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMaps`;
    script.async = true;
    script.defer = true;

    window.initGoogleMaps = () => {
      googleMapsLoaded.value = true;
      resolve();
    };

    script.onerror = () => {
      reject(new Error("Google Maps failed to load"));
    };

    document.head.appendChild(script);
  });
};

onMounted(async () => {
  try {
    await loadGoogleMapsScript();
    if (address.value) {
      searchAddress();
    }
  } catch (error) {
    console.error("Error loading Google Maps:", error);
  }
});

watch(
  () => props.initialAddress,
  (newVal) => {
    if (newVal !== address.value) {
      address.value = newVal;
      if (address.value && googleMapsLoaded.value) searchAddress();
    }
  }
);
</script>
