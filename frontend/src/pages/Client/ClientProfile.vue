<script setup>
import DefaultLayout from "../../components/DefaultLayout.vue";
import { ref, watch, onMounted } from "vue";
import {
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  PencilIcon,
} from "@heroicons/vue/24/outline";
import api from "../../axios.js"; // Assuming this is your configured axios instance

// --- State Management ---

// Data for displaying the profile
const userData = ref({
  name: "",
  email: "",
  phone: "",
  country: "",
  city: "",
  cin: "",
});

// Data for the profile edit form
const editUserData = ref({
  name: "",
  email: "",
  phone: "",
  country: "",
  city: "",
  cin: "",
});

// State for profile editing mode
const isEditMode = ref(false);
const isSavingProfile = ref(false); // Loading state for profile update

// State for password change form
const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const isSavingPassword = ref(false); // Loading state for password update

// General loading state for initial data fetch
const isLoading = ref(false);

// Messages and errors
const profileMessage = ref(null);
const profileMessageType = ref("success"); // 'success' or 'error'
const profileErrors = ref({}); // Store field-specific validation errors for profile

const passwordMessage = ref(null);
const passwordMessageType = ref("success"); // 'success' or 'error'
const passwordErrors = ref({}); // Store field-specific validation errors for password

// Password validation state
const passwordsMatch = ref(true);
const passwordStrength = ref({
  value: 0,
  message: "Enter a new password",
  color: "bg-gray-200",
});

// Password strength requirements check
const passwordRequirements = ref({
  minLength: false,
  hasUppercase: false,
  hasNumber: false,
  hasSpecialChar: false,
});

// --- Lifecycle Hooks ---

onMounted(async () => {
  fetchUserData();
});

// --- Watchers ---

watch(newPassword, (value) => {
  // Calculate password strength and check requirements
  let strength = 0;
  const requirements = {
    minLength: value.length >= 8,
    hasUppercase: /[A-Z]/.test(value),
    hasNumber: /[0-9]/.test(value),
    hasSpecialChar: /[^A-Za-z0-9]/.test(value),
  };

  if (requirements.minLength) strength += 25;
  if (requirements.hasUppercase) strength += 25;
  if (requirements.hasNumber) strength += 25;
  if (requirements.hasSpecialChar) strength += 25;

  passwordRequirements.value = requirements;

  if (strength <= 25 && value.length > 0) {
    passwordStrength.value = {
      value: strength,
      message: "Weak",
      color: "bg-red-500",
    };
  } else if (strength <= 75 && value.length > 0) {
    passwordStrength.value = {
      value: strength,
      message: "Medium",
      color: "bg-yellow-500",
    };
  } else if (strength > 75) {
    passwordStrength.value = {
      value: strength,
      message: "Strong",
      color: "bg-green-500",
    };
  } else {
    passwordStrength.value = {
      value: 0,
      message: "Enter a new password",
      color: "bg-gray-200",
    };
  }

  // Check if passwords match
  if (confirmPassword.value) {
    passwordsMatch.value = confirmPassword.value === value;
  } else {
    passwordsMatch.value = true; // Assume match if confirm is empty
  }

  // Clear password-related messages/errors when typing
  passwordMessage.value = null;
  passwordErrors.value = {};
});

watch(confirmPassword, (value) => {
  // Check if passwords match
  passwordsMatch.value = value === newPassword.value;

  // Clear password-related messages/errors when typing
  passwordMessage.value = null;
  passwordErrors.value = {};
});

// Clear profile messages/errors when starting to edit
watch(isEditMode, (isEditing) => {
  if (isEditing) {
    profileMessage.value = null;
    profileErrors.value = {};
  }
});

// --- Methods ---

// Fetch user data from API
async function fetchUserData() {
  try {
    isLoading.value = true;
    profileMessage.value = null; // Clear previous messages
    const response = await api.get("/api/profile");
    console.log("API Response:", response);
    if (response && response.data && response.data.data) {
      // Map API response keys to your data structure (lowercase vs Uppercase)
      userData.value = {
        name: response.data.data.Name || "",
        email: response.data.data.Email || "",
        phone: response.data.data.phone || "",
        country: response.data.data.country || "",
        city: response.data.data.city || "",
        cin: response.data.data.cin || "",
      };
      console.log("User data loaded:", userData.value);
      // Consider setting a success message for clarity on initial load if needed
      // profileMessage.value = "Profile data loaded.";
      // profileMessageType.value = 'success';
    } else {
      console.error("API response does not contain expected data:", response);
      profileMessage.value =
        "Failed to load profile data: Invalid response format";
      profileMessageType.value = "error";
    }
  } catch (error) {
    console.error("Error fetching user data:", error);
    profileMessage.value =
      error.response?.data?.message || "Failed to load profile information";
    profileMessageType.value = "error";
  } finally {
    isLoading.value = false;
  }
}

// Enable edit mode for personal information
const enableEditMode = () => {
  editUserData.value = { ...userData.value }; // Copy current data to edit form
  isEditMode.value = true;
  // profileMessage.value = null; // Clear messages when entering edit mode (also handled by watcher)
  // profileErrors.value = {}; // Clear errors when entering edit mode (also handled by watcher)
};

// Cancel edit mode for personal information
const cancelEdit = () => {
  isEditMode.value = false;
  profileMessage.value = null; // Clear messages on cancel
  profileErrors.value = {}; // Clear errors on cancel
};

// Handle profile information update
const handleProfileUpdate = async (event) => {
  event.preventDefault();
  profileMessage.value = null; // Clear previous messages
  profileErrors.value = {}; // Clear previous errors

  try {
    isSavingProfile.value = true;
    const response = await api.post("/api/update-profile", editUserData.value);

    // Assuming a successful response contains the updated data or a success indicator
    if (response && response.data && response.data.data) {
      userData.value = { ...editUserData.value }; // Update displayed data
      isEditMode.value = false; // Exit edit mode
      profileMessage.value = "Profile information updated successfully";
      profileMessageType.value = "success";
      // Optional: Auto-clear success message after a delay
      setTimeout(() => (profileMessage.value = null), 5000);
      console.log("Profile updated successfully", response.data);
    } else {
      // Handle unexpected success response format
      profileMessage.value =
        "Profile updated, but response format was unexpected.";
      profileMessageType.value = "success"; // Still treat as success based on status
      console.warn("Profile updated, but unexpected response data:", response);
    }
  } catch (error) {
    console.error(
      "Error updating profile",
      error.response ? error.response.data : error
    );
    profileMessageType.value = "error";

    if (error.response && error.response.data && error.response.data.errors) {
      // Backend returned validation errors
      profileErrors.value = error.response.data.errors;
      profileMessage.value =
        error.response.data.message || "Please correct the errors below.";
    } else {
      // Other API errors
      profileMessage.value =
        error.response?.data?.message || "Error updating profile information.";
    }
  } finally {
    isSavingProfile.value = false;
  }
};

// Handle password update
const handlePasswordUpdate = async (event) => {
  event.preventDefault();
  passwordMessage.value = null; // Clear previous messages
  passwordErrors.value = {}; // Clear previous errors

  // Client-side validation check
  if (!currentPassword.value) {
    passwordMessage.value = "Please enter your current password.";
    passwordMessageType.value = "error";
    return;
  }
  if (passwordStrength.value.value < 50) {
    passwordMessage.value =
      "New password is too weak. Please meet the requirements.";
    passwordMessageType.value = "error";
    return;
  }
  if (!passwordsMatch.value) {
    passwordMessage.value = "New passwords do not match.";
    passwordMessageType.value = "error";
    return;
  }

  try {
    isSavingPassword.value = true;
    const response = await api.post("/api/change-password", {
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });

    // Assuming a successful response indicates success
    passwordMessage.value =
      response.data.message || "Password updated successfully"; // Use backend message if available
    passwordMessageType.value = "success";

    // Clear fields on success
    currentPassword.value = "";
    newPassword.value = "";
    confirmPassword.value = "";

    // Reset password strength/match indicators
    passwordStrength.value = {
      value: 0,
      message: "Enter a new password",
      color: "bg-gray-200",
    };
    passwordsMatch.value = true;
    passwordRequirements.value = {
      minLength: false,
      hasUppercase: false,
      hasNumber: false,
      hasSpecialChar: false,
    };

    // Optional: Auto-clear success message after a delay
    setTimeout(() => (passwordMessage.value = null), 5000);

    console.log("Password updated successfully", response.data);
  } catch (error) {
    console.error(
      "Error updating password",
      error.response ? error.response.data : error
    );
    passwordMessageType.value = "error";

    if (error.response && error.response.data) {
      // Handle specific backend error messages (e.g., wrong current password)
      if (error.response.data.errors) {
        // Backend returned validation errors (e.g., password format errors)
        passwordErrors.value = error.response.data.errors;
        passwordMessage.value =
          error.response.data.message || "Please correct the errors below.";
      } else {
        // Generic error message from backend
        passwordMessage.value =
          error.response.data.message || "Error updating password.";
      }
    } else {
      // Network or other errors
      passwordMessage.value = "An unexpected error occurred.";
    }
  } finally {
    isSavingPassword.value = false;
  }
};

// --- Computed Properties (Optional but can clean up template logic) ---
// No complex computed needed here, watchers handle the dynamic validation state well.
</script>

<template>
  <DefaultLayout>
    <header class="bg-gray-100 shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Profile</h1>
      </div>
    </header>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div
          class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600"
        ></div>
        <span class="ml-2 text-gray-600">Loading profile information...</span>
      </div>

      <div v-else>
        <div
          class="mb-8 bg-white p-6 rounded-lg shadow-sm border border-gray-200"
        >
          <div class="pb-6">
            <div class="flex justify-between items-center">
              <div>
                <h2 class="text-xl font-semibold text-gray-900">
                  Personal Information
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                  Your account details and personal information.
                </p>
              </div>

              <button
                v-if="!isEditMode"
                @click="enableEditMode"
                type="button"
                class="inline-flex items-center rounded-md bg-[#002D4A] px-3 py-2 text-sm font-medium text-white hover:bg-[#036F8B]"
              >
                <PencilIcon class="h-4 w-4 mr-2" />
                Edit Information
              </button>
            </div>

            <div
              v-if="profileMessage"
              class="mt-4 p-3 rounded-md text-sm"
              :class="
                profileMessageType === 'success'
                  ? 'bg-green-100 text-green-800' // Lighter background for success
                  : 'bg-red-100 text-red-800' // Lighter background for error
              "
              role="alert"
            >
              <div class="flex items-center">
                <CheckCircleIcon
                  v-if="profileMessageType === 'success'"
                  class="h-5 w-5 mr-2 text-green-500"
                />
                <ExclamationCircleIcon
                  v-else
                  class="h-5 w-5 mr-2 text-red-500"
                />
                {{ profileMessage }}
              </div>
            </div>

            <div v-if="!isEditMode" class="mt-6">
              <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                <div class="sm:col-span-3">
                  <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                  <dd class="mt-1 text-sm text-gray-900 pb-2">
                    {{ userData.name || "Not set" }}
                  </dd>
                </div>

                <div class="sm:col-span-3">
                  <dt class="text-sm font-medium text-gray-500">
                    Email address
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 pb-2">
                    {{ userData.email || "Not set" }}
                  </dd>
                </div>

                <div class="sm:col-span-3">
                  <dt class="text-sm font-medium text-gray-500">
                    Phone Number
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 pb-2">
                    {{ userData.phone || "Not set" }}
                  </dd>
                </div>

                <div class="sm:col-span-3">
                  <dt class="text-sm font-medium text-gray-500">CIN</dt>
                  <dd class="mt-1 text-sm text-gray-900 pb-2">
                    {{ userData.cin || "Not set" }}
                  </dd>
                </div>

                <div class="sm:col-span-3">
                  <dt class="text-sm font-medium text-gray-500">Country</dt>
                  <dd class="mt-1 text-sm text-gray-900 pb-2">
                    {{ userData.country || "Not set" }}
                  </dd>
                </div>

                <div class="sm:col-span-3">
                  <dt class="text-sm font-medium text-gray-500">City</dt>
                  <dd class="mt-1 text-sm text-gray-900 pb-2">
                    {{ userData.city || "Not set" }}
                  </dd>
                </div>
              </dl>
            </div>

            <form v-else @submit.prevent="handleProfileUpdate" class="mt-6">
              <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <div class="sm:col-span-3">
                  <label
                    for="profile-name"
                    class="block text-sm font-medium text-gray-900"
                    >Full Name</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.name"
                      type="text"
                      name="profile-name"
                      id="profile-name"
                      autocomplete="name"
                      placeholder="Enter your full name"
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                        profileErrors.name
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                    />
                  </div>
                  <p
                    v-if="profileErrors.name"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ profileErrors.name[0] }}
                  </p>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="profile-email"
                    class="block text-sm font-medium text-gray-900"
                    >Email address</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.email"
                      id="profile-email"
                      name="profile-email"
                      type="email"
                      autocomplete="email"
                      placeholder="you@example.com"
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                        profileErrors.email
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                    />
                  </div>
                  <p
                    v-if="profileErrors.email"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ profileErrors.email[0] }}
                  </p>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="profile-phone"
                    class="block text-sm font-medium text-gray-900"
                    >Phone Number</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.phone"
                      id="profile-phone"
                      name="profile-phone"
                      type="tel"
                      autocomplete="tel"
                      placeholder="(+216) 00-000-000"
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                        profileErrors.phone
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                    />
                  </div>
                  <p
                    v-if="profileErrors.phone"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ profileErrors.phone[0] }}
                  </p>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="profile-cin"
                    class="block text-sm font-medium text-gray-900"
                    >CIN</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.cin"
                      id="profile-cin"
                      name="profile-cin"
                      type="text"
                      placeholder="Customer Identification Number"
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                        profileErrors.cin
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                    />
                  </div>
                  <p v-if="profileErrors.cin" class="mt-1 text-sm text-red-600">
                    {{ profileErrors.cin[0] }}
                  </p>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="profile-country"
                    class="block text-sm font-medium text-gray-900"
                    >Country</label
                  >
                  <div class="mt-2">
                    <select
                      v-model="editUserData.country"
                      id="profile-country"
                      name="profile-country"
                      autocomplete="country-name"
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                        profileErrors.country
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                    >
                      <option value="" disabled>Select your country</option>
                      <option value="Tunisia">Tunisia</option>
                      <option value="Canada">Canada</option>
                      <option value="Mexico">Mexico</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="France">France</option>
                      <option value="Germany">Germany</option>
                      <option value="Japan">Japan</option>
                    </select>
                  </div>
                  <p
                    v-if="profileErrors.country"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ profileErrors.country[0] }}
                  </p>
                </div>

                <div class="sm:col-span-3">
                  <label
                    for="profile-city"
                    class="block text-sm font-medium text-gray-900"
                    >City</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.city"
                      type="text"
                      name="profile-city"
                      id="profile-city"
                      autocomplete="address-level2"
                      placeholder="Enter your city"
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                        profileErrors.city
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                    />
                  </div>
                  <p
                    v-if="profileErrors.city"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ profileErrors.city[0] }}
                  </p>
                </div>
              </div>

              <div class="mt-6 flex items-center justify-end gap-x-6">
                <button
                  type="button"
                  @click="cancelEdit"
                  class="text-sm font-semibold text-gray-900 hover:text-gray-700"
                  :disabled="isSavingProfile"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="rounded-md bg-[#002D4A] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#036F8B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#036F8B]"
                  :disabled="isSavingProfile"
                  :class="{ 'opacity-50 cursor-not-allowed': isSavingProfile }"
                >
                  <span v-if="isSavingProfile" class="flex items-center"
                    ><div
                      class="animate-spin rounded-full h-4 w-4 mr-2 border-t-2 border-b-2 border-white"
                    ></div>
                    Saving...</span
                  >
                  <span v-else>Save Changes</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <form @submit.prevent="handlePasswordUpdate">
            <div class="border-b border-gray-900/10 pb-6">
              <h2 class="text-xl font-semibold text-gray-900">
                Change Password
              </h2>
              <p class="mt-1 text-sm text-gray-600">
                We recommend using a strong, unique password that you don't use
                elsewhere.
              </p>

              <div
                v-if="passwordMessage"
                class="mt-4 p-3 rounded-md text-sm"
                :class="
                  passwordMessageType === 'success'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                "
                role="alert"
              >
                <div class="flex items-center">
                  <CheckCircleIcon
                    v-if="passwordMessageType === 'success'"
                    class="h-5 w-5 mr-2 text-green-500"
                  />
                  <ExclamationCircleIcon
                    v-else
                    class="h-5 w-5 mr-2 text-red-500"
                  />
                  {{ passwordMessage }}
                </div>
              </div>

              <div class="mt-6 space-y-6">
                <div>
                  <label
                    for="current-password"
                    class="block text-sm font-medium text-gray-900"
                  >
                    Current Password <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative">
                    <input
                      :type="showCurrentPassword ? 'text' : 'password'"
                      v-model="currentPassword"
                      name="current-password"
                      id="current-password"
                      autocomplete="current-password"
                      required
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm',
                        passwordErrors.current_password
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                      @input="
                        passwordMessage = null;
                        passwordErrors.current_password = null;
                      "
                    />
                    <button
                      type="button"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                      aria-label="Toggle password visibility"
                    >
                      <EyeIcon v-if="!showCurrentPassword" class="h-5 w-5" />
                      <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                  </div>
                  <p
                    v-if="passwordErrors.current_password"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ passwordErrors.current_password[0] }}
                  </p>
                </div>

                <div>
                  <label
                    for="new-password"
                    class="block text-sm font-medium text-gray-900"
                  >
                    New Password <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative">
                    <input
                      :type="showNewPassword ? 'text' : 'password'"
                      v-model="newPassword"
                      name="new-password"
                      id="new-password"
                      autocomplete="new-password"
                      required
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm',
                        passwordErrors.password
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                      @input="
                        passwordMessage = null;
                        passwordErrors.password = null;
                      "
                    />
                    <button
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                      aria-label="Toggle password visibility"
                    >
                      <EyeIcon v-if="!showNewPassword" class="h-5 w-5" />
                      <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                  </div>

                  <div class="mt-2">
                    <div class="flex items-center justify-between mb-1">
                      <p class="text-xs text-gray-600">Password strength:</p>
                      <p
                        class="text-xs font-medium"
                        :class="{
                          'text-red-600':
                            passwordStrength.value <= 25 && newPassword,
                          'text-yellow-600':
                            passwordStrength.value > 25 &&
                            passwordStrength.value <= 75,
                          'text-green-600': passwordStrength.value > 75,
                          'text-gray-600': !newPassword,
                        }"
                      >
                        {{ passwordStrength.message }}
                      </p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                      <div
                        class="h-1.5 rounded-full transition-all duration-300 ease-in-out"
                        :class="passwordStrength.color"
                        :style="`width: ${passwordStrength.value}%`"
                      ></div>
                    </div>
                    <ul class="mt-2 text-xs text-gray-600 space-y-1">
                      <li class="flex items-center gap-x-2">
                        <span
                          :class="
                            passwordRequirements.minLength
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="passwordRequirements.minLength"
                          />
                          <span
                            class="inline-block h-4 w-4 rounded-full border border-current"
                            v-else
                          ></span>
                        </span>
                        At least 8 characters
                      </li>
                      <li class="flex items-center gap-x-2">
                        <span
                          :class="
                            passwordRequirements.hasUppercase
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="passwordRequirements.hasUppercase"
                          />
                          <span
                            class="inline-block h-4 w-4 rounded-full border border-current"
                            v-else
                          ></span>
                        </span>
                        At least one uppercase letter
                      </li>
                      <li class="flex items-center gap-x-2">
                        <span
                          :class="
                            passwordRequirements.hasNumber
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="passwordRequirements.hasNumber"
                          />
                          <span
                            class="inline-block h-4 w-4 rounded-full border border-current"
                            v-else
                          ></span>
                        </span>
                        At least one number
                      </li>
                      <li class="flex items-center gap-x-2">
                        <span
                          :class="
                            passwordRequirements.hasSpecialChar
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="passwordRequirements.hasSpecialChar"
                          />
                          <span
                            class="inline-block h-4 w-4 rounded-full border border-current"
                            v-else
                          ></span>
                        </span>
                        At least one special character
                      </li>
                    </ul>
                    <p
                      v-if="passwordErrors.password"
                      class="mt-1 text-sm text-red-600"
                    >
                      {{ passwordErrors.password[0] }}
                    </p>
                  </div>
                </div>

                <div>
                  <label
                    for="confirm-password"
                    class="block text-sm font-medium text-gray-900"
                  >
                    Confirm New Password <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative">
                    <input
                      :type="showConfirmPassword ? 'text' : 'password'"
                      v-model="confirmPassword"
                      name="confirm-password"
                      id="confirm-password"
                      autocomplete="new-password"
                      required
                      :class="[
                        'block w-full rounded-md border-0 px-3 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm',
                        (confirmPassword || newPassword) && !passwordsMatch
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300 focus:ring-indigo-600',
                      ]"
                      @input="
                        passwordMessage = null;
                        passwordErrors.password_confirmation = null;
                      "
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                      aria-label="Toggle password visibility"
                    >
                      <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                      <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                  </div>
                  <p
                    v-if="(confirmPassword || newPassword) && !passwordsMatch"
                    class="mt-1 text-sm text-red-600 flex items-center"
                  >
                    <ExclamationCircleIcon class="h-4 w-4 mr-1" />
                    Passwords don't match
                  </p>
                  <p
                    v-if="passwordErrors.password_confirmation"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ passwordErrors.password_confirmation[0] }}
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6">
              <button
                type="button"
                class="text-sm font-semibold text-gray-900 hover:text-gray-700"
                :disabled="isSavingPassword"
                @click="
                  currentPassword = '';
                  newPassword = '';
                  confirmPassword = '';
                  passwordMessage = null; // Clear messages on cancel
                  passwordErrors = {}; // Clear errors on cancel
                  // Reset password strength/match indicators
                  passwordStrength = {
                    value: 0,
                    message: 'Enter a new password',
                    color: 'bg-gray-200',
                  };
                  passwordsMatch = true;
                  passwordRequirements = {
                    minLength: false,
                    hasUppercase: false,
                    hasNumber: false,
                    hasSpecialChar: false,
                  };
                "
              >
                Cancel
              </button>
              <button
                type="submit"
                class="rounded-md bg-[#002D4A] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#036F8B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#036F8B]"
                :disabled="
                  !currentPassword || // Current password required
                  passwordStrength.value < 50 || // New password must be at least medium strength
                  !passwordsMatch || // New and confirm passwords must match
                  isSavingPassword // Prevent multiple submissions
                "
                :class="{
                  'opacity-50 cursor-not-allowed':
                    !currentPassword ||
                    passwordStrength.value < 50 ||
                    !passwordsMatch ||
                    isSavingPassword,
                }"
              >
                <span v-if="isSavingPassword" class="flex items-center"
                  ><div
                    class="animate-spin rounded-full h-4 w-4 mr-2 border-t-2 border-b-2 border-white"
                  ></div>
                  Updating...</span
                >
                <span v-else>Update Password</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
