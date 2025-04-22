<script setup>
import DefaultLayout from "../../components/DefaultLayout.vue";
import { ref, watch, onMounted } from "vue";
import {
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  PencilIcon,
  UserCircleIcon,
  ShieldCheckIcon,
} from "@heroicons/vue/24/outline";
import api from "../../axios.js";

const userData = ref({
  name: "",
  email: "",
  phone: "",
  country: "",
  city: "",
  cin: "",
});

const editUserData = ref({
  name: "",
  email: "",
  phone: "",
  country: "",
  city: "",
  cin: "",
});

const isEditMode = ref(false);
const activeTab = ref("profile"); // 'profile' or 'password'

const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const isLoading = ref(false);

const passwordMessage = ref(null);
const passwordMessageType = ref("success");
const profileMessage = ref(null);
const profileMessageType = ref("success");

const passwordsMatch = ref(true);
const passwordStrength = ref({
  value: 0,
  message: "Enter a new password",
  color: "bg-gray-200",
});

onMounted(async () => {
  try {
    isLoading.value = true;
    const response = await api.get("/api/profile");
    if (response && response.data && response.data.data) {
      userData.value = {
        name: response.data.data.Name || "",
        email: response.data.data.Email || "",
        phone: response.data.data.phone || "",
        country: response.data.data.country || "",
        city: response.data.data.city || "",
        cin: response.data.data.cin || "",
      };
    } else {
      profileMessage.value =
        "Failed to load profile data: Invalid response format";
      profileMessageType.value = "error";
    }
  } catch (error) {
    profileMessage.value = "Failed to load profile information";
    profileMessageType.value = "error";
  } finally {
    isLoading.value = false;
  }
});

watch(newPassword, (value) => {
  if (!value) {
    passwordStrength.value = {
      value: 0,
      message: "Enter a new password",
      color: "bg-gray-200",
    };
    return;
  }

  let strength = 0;
  if (value.length >= 8) strength += 25;
  if (/[A-Z]/.test(value)) strength += 25;
  if (/[0-9]/.test(value)) strength += 25;
  if (/[^A-Za-z0-9]/.test(value)) strength += 25;

  if (strength <= 25) {
    passwordStrength.value = {
      value: strength,
      message: "Weak password",
      color: "bg-red-500",
    };
  } else if (strength <= 75) {
    passwordStrength.value = {
      value: strength,
      message: "Medium password",
      color: "bg-yellow-500",
    };
  } else {
    passwordStrength.value = {
      value: strength,
      message: "Strong password",
      color: "bg-green-500",
    };
  }

  if (confirmPassword.value) {
    passwordsMatch.value = confirmPassword.value === value;
  }
});

watch(confirmPassword, (value) => {
  if (value || newPassword.value) {
    passwordsMatch.value = value === newPassword.value;
  }
});

const enableEditMode = () => {
  editUserData.value = { ...userData.value };
  isEditMode.value = true;
};

const cancelEdit = () => {
  isEditMode.value = false;
  profileMessage.value = null;
};

const handlePasswordUpdate = async (event) => {
  event.preventDefault();

  if (
    !passwordsMatch.value ||
    passwordStrength.value.value < 50 ||
    !currentPassword.value
  ) {
    passwordMessage.value = "Please fix the errors before submitting";
    passwordMessageType.value = "error";
    return;
  }

  try {
    const response = await api.post("/api/change-password", {
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });
    passwordMessage.value = "Password updated successfully";
    passwordMessageType.value = "success";

    currentPassword.value = "";
    newPassword.value = "";
    confirmPassword.value = "";
  } catch (error) {
    passwordMessage.value =
      error.response?.data?.message || "Error updating password";
    passwordMessageType.value = "error";
  }
};

const handleProfileUpdate = async (event) => {
  event.preventDefault();

  try {
    const response = await api.post("/api/update-profile", {
      name: editUserData.value.name,
      email: editUserData.value.email,
      phone: editUserData.value.phone,
      country: editUserData.value.country,
      city: editUserData.value.city,
      cin: editUserData.value.cin,
    });

    userData.value = { ...editUserData.value };
    isEditMode.value = false;
    profileMessage.value = "Profile information updated successfully";
    profileMessageType.value = "success";
  } catch (error) {
    profileMessage.value =
      error.response?.data?.message || "Error updating profile";
    profileMessageType.value = "error";
  }
};

// Function to get avatar text from name
const getAvatarText = (name) => {
  if (!name) return "U";
  const nameParts = name.split(" ");
  if (nameParts.length >= 2) {
    return `${nameParts[0][0]}${nameParts[1][0]}`.toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

// Function to get background color based on name
const getAvatarColor = (name) => {
  if (!name) return "bg-blue-600";
  const colors = [
    "bg-blue-600",
    "bg-emerald-600",
    "bg-violet-600",
    "bg-amber-600",
    "bg-rose-600",
    "bg-indigo-600",
  ];
  const hash = name
    .split("")
    .reduce((acc, char) => acc + char.charCodeAt(0), 0);
  return colors[hash % colors.length];
};
</script>

<template>
  <DefaultLayout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Page Header with Avatar -->
      <div class="mb-8">
        <div
          class="flex flex-col sm:flex-row items-center sm:items-start gap-6"
        >
          <div
            class="w-24 h-24 rounded-full flex items-center justify-center text-white text-2xl font-bold"
            :class="getAvatarColor(userData.name)"
          >
            {{ getAvatarText(userData.name) }}
          </div>
          <div class="flex flex-col items-center sm:items-start">
            <h1 class="text-2xl font-bold text-gray-900">
              {{ userData.name || "Your Profile" }}
            </h1>
            <p class="text-gray-500">{{ userData.email || "No email set" }}</p>
            <div class="flex space-x-4 mt-4">
              <button
                @click="activeTab = 'profile'"
                class="flex items-center px-4 py-2 rounded-md text-sm font-medium"
                :class="
                  activeTab === 'profile'
                    ? 'bg-[#002D4A] text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                "
              >
                <UserCircleIcon class="h-5 w-5 mr-2" />
                Profile
              </button>
              <button
                @click="activeTab = 'password'"
                class="flex items-center px-4 py-2 rounded-md text-sm font-medium"
                :class="
                  activeTab === 'password'
                    ? 'bg-[#002D4A] text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                "
              >
                <ShieldCheckIcon class="h-5 w-5 mr-2" />
                Security
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading indicator -->
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div
          class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-[#002D4A]"
        ></div>
        <span class="ml-2 text-gray-600">Loading profile information...</span>
      </div>

      <div v-else>
        <!-- Profile Tab Content -->
        <div
          v-if="activeTab === 'profile'"
          class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200"
        >
          <!-- Header -->
          <div
            class="px-6 py-5 border-b border-gray-300 flex justify-between items-center bg-white sm-shadow"
          >
            <div>
              <h2 class="text-xl font-semibold text-gray-900">
                Personal Information
              </h2>
              <p class="mt-1 text-sm text-gray-600">
                Your account details and personal information
              </p>
            </div>
            <button
              v-if="!isEditMode"
              @click="enableEditMode"
              type="button"
              class="inline-flex items-center rounded-md bg-[#002D4A] px-4 py-2 text-sm font-medium text-white hover:bg-[#036F8B] transition-colors duration-200"
            >
              <PencilIcon class="h-4 w-4 mr-2" />
              Edit
            </button>
          </div>

          <!-- Profile Message -->
          <div
            v-if="profileMessage"
            class="mx-6 mt-4 p-4 rounded-md"
            :class="
              profileMessageType === 'success'
                ? 'bg-green-50 text-green-800 border border-green-200'
                : 'bg-red-50 text-red-800 border border-red-200'
            "
          >
            <div class="flex">
              <CheckCircleIcon
                v-if="profileMessageType === 'success'"
                class="h-5 w-5 mr-2"
              />
              <ExclamationCircleIcon v-else class="h-5 w-5 mr-2" />
              <span>{{ profileMessage }}</span>
            </div>
          </div>

          <!-- Profile Content -->
          <div class="p-6">
            <!-- View Mode -->
            <div v-if="!isEditMode">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-gray-500">Full Name</h3>
                  <p class="mt-1 text-base font-medium text-gray-900">
                    {{ userData.name || "Not set" }}
                  </p>
                </div>

                <!-- Email -->
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-gray-500">
                    Email Address
                  </h3>
                  <p class="mt-1 text-base font-medium text-gray-900">
                    {{ userData.email || "Not set" }}
                  </p>
                </div>

                <!-- Phone -->
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-gray-500">
                    Phone Number
                  </h3>
                  <p class="mt-1 text-base font-medium text-gray-900">
                    {{ userData.phone || "Not set" }}
                  </p>
                </div>

                <!-- CIN -->
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-gray-500">CIN</h3>
                  <p class="mt-1 text-base font-medium text-gray-900">
                    {{ userData.cin || "Not set" }}
                  </p>
                </div>

                <!-- Country -->
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-gray-500">Country</h3>
                  <p class="mt-1 text-base font-medium text-gray-900">
                    {{ userData.country || "Not set" }}
                  </p>
                </div>

                <!-- City -->
                <div class="flex flex-col">
                  <h3 class="text-sm font-medium text-gray-500">City</h3>
                  <p class="mt-1 text-base font-medium text-gray-900">
                    {{ userData.city || "Not set" }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Edit Mode Form -->
            <form v-else @submit="handleProfileUpdate">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                  <label
                    for="name"
                    class="block text-sm font-medium text-gray-700 mb-1"
                  >
                    Full Name
                  </label>
                  <input
                    v-model="editUserData.name"
                    type="text"
                    id="name"
                    placeholder="Enter your full name"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label
                    for="email"
                    class="block text-sm font-medium text-gray-700 mb-1"
                  >
                    Email Address
                  </label>
                  <input
                    v-model="editUserData.email"
                    type="email"
                    id="email"
                    placeholder="your.email@example.com"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200"
                  />
                </div>

                <!-- Phone -->
                <div>
                  <label
                    for="phone"
                    class="block text-sm font-medium text-gray-700 mb-1"
                  >
                    Phone Number
                  </label>
                  <input
                    v-model="editUserData.phone"
                    type="tel"
                    id="phone"
                    placeholder="(+216) 00-000-000"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200"
                  />
                </div>

                <!-- CIN -->
                <div>
                  <label
                    for="cin"
                    class="block text-sm font-medium text-gray-700 mb-1"
                  >
                    CIN
                  </label>
                  <input
                    v-model="editUserData.cin"
                    type="text"
                    id="cin"
                    placeholder="Customer Identification Number"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200"
                  />
                </div>

                <!-- Country -->
                <div>
                  <label
                    for="country"
                    class="block text-sm font-medium text-gray-700 mb-1"
                  >
                    Country
                  </label>
                  <select
                    v-model="editUserData.country"
                    id="country"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200"
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

                <!-- City -->
                <div>
                  <label
                    for="city"
                    class="block text-sm font-medium text-gray-700 mb-1"
                  >
                    City
                  </label>
                  <input
                    v-model="editUserData.city"
                    type="text"
                    id="city"
                    placeholder="Enter your city"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200"
                  />
                </div>
              </div>

              <!-- Edit Mode Buttons -->
              <div class="mt-8 flex justify-end space-x-4">
                <button
                  type="button"
                  @click="cancelEdit"
                  class="px-5 py-2.5 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium transition-colors duration-200"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-5 py-2.5 rounded-lg text-white bg-[#002D4A] hover:bg-[#036F8B] font-medium transition-colors duration-200"
                >
                  Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Password Tab Content -->
        <div
          v-if="activeTab === 'password'"
          class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 sm-shadow"
        >
          <!-- Header -->
          <div class="px-6 py-5 border-b border-gray-200 bg-white">
            <h2 class="text-xl font-semibold text-gray-900">
              Security Settings
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              Update your password and secure your account
            </p>
          </div>

          <!-- Password Message -->
          <div
            v-if="passwordMessage"
            class="mx-6 mt-4 p-4 rounded-md"
            :class="
              passwordMessageType === 'success'
                ? 'bg-green-50 text-green-800 border border-green-200'
                : 'bg-red-50 text-red-800 border border-red-200'
            "
          >
            <div class="flex">
              <CheckCircleIcon
                v-if="passwordMessageType === 'success'"
                class="h-5 w-5 mr-2"
              />
              <ExclamationCircleIcon v-else class="h-5 w-5 mr-2" />
              <span>{{ passwordMessage }}</span>
            </div>
          </div>

          <!-- Password Content -->
          <form @submit="handlePasswordUpdate" class="p-6">
            <!-- Current Password -->
            <div class="mb-6">
              <label
                for="current-password"
                class="block text-sm font-medium text-gray-700 mb-1"
              >
                Current Password
              </label>
              <div class="relative">
                <input
                  :type="showCurrentPassword ? 'text' : 'password'"
                  v-model="currentPassword"
                  id="current-password"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200 pr-10"
                />
                <button
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <EyeIcon v-if="!showCurrentPassword" class="h-5 w-5" />
                  <EyeSlashIcon v-else class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- New Password -->
            <div class="mb-6">
              <label
                for="new-password"
                class="block text-sm font-medium text-gray-700 mb-1"
              >
                New Password
              </label>
              <div class="relative">
                <input
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="newPassword"
                  id="new-password"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-[#036F8B] focus:ring focus:ring-[#036F8B] focus:ring-opacity-20 transition-all duration-200 pr-10"
                />
                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <EyeIcon v-if="!showNewPassword" class="h-5 w-5" />
                  <EyeSlashIcon v-else class="h-5 w-5" />
                </button>
              </div>

              <!-- Password strength meter -->
              <div class="mt-3">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs text-gray-600">Password strength:</span>
                  <span
                    class="text-xs font-medium"
                    :class="{
                      'text-red-600': passwordStrength.value <= 25,
                      'text-yellow-600':
                        passwordStrength.value > 25 &&
                        passwordStrength.value <= 75,
                      'text-green-600': passwordStrength.value > 75,
                    }"
                  >
                    {{ passwordStrength.message }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full transition-all duration-300"
                    :class="passwordStrength.color"
                    :style="`width: ${passwordStrength.value}%`"
                  ></div>
                </div>

                <!-- Password requirements -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2">
                  <div
                    class="flex items-center text-xs"
                    :class="
                      newPassword.length >= 8
                        ? 'text-green-600'
                        : 'text-gray-500'
                    "
                  >
                    <CheckCircleIcon
                      class="h-4 w-4 mr-1.5"
                      v-if="newPassword.length >= 8"
                    />
                    <div
                      v-else
                      class="h-4 w-4 mr-1.5 rounded-full border"
                      :class="
                        newPassword ? 'border-gray-400' : 'border-gray-300'
                      "
                    ></div>
                    At least 8 characters
                  </div>

                  <div
                    class="flex items-center text-xs"
                    :class="
                      /[A-Z]/.test(newPassword)
                        ? 'text-green-600'
                        : 'text-gray-500'
                    "
                  >
                    <CheckCircleIcon
                      class="h-4 w-4 mr-1.5"
                      v-if="/[A-Z]/.test(newPassword)"
                    />
                    <div
                      v-else
                      class="h-4 w-4 mr-1.5 rounded-full border"
                      :class="
                        newPassword ? 'border-gray-400' : 'border-gray-300'
                      "
                    ></div>
                    Uppercase letter
                  </div>

                  <div
                    class="flex items-center text-xs"
                    :class="
                      /[0-9]/.test(newPassword)
                        ? 'text-green-600'
                        : 'text-gray-500'
                    "
                  >
                    <CheckCircleIcon
                      class="h-4 w-4 mr-1.5"
                      v-if="/[0-9]/.test(newPassword)"
                    />
                    <div
                      v-else
                      class="h-4 w-4 mr-1.5 rounded-full border"
                      :class="
                        newPassword ? 'border-gray-400' : 'border-gray-300'
                      "
                    ></div>
                    Number
                  </div>

                  <div
                    class="flex items-center text-xs"
                    :class="
                      /[^A-Za-z0-9]/.test(newPassword)
                        ? 'text-green-600'
                        : 'text-gray-500'
                    "
                  >
                    <CheckCircleIcon
                      class="h-4 w-4 mr-1.5"
                      v-if="/[^A-Za-z0-9]/.test(newPassword)"
                    />
                    <div
                      v-else
                      class="h-4 w-4 mr-1.5 rounded-full border"
                      :class="
                        newPassword ? 'border-gray-400' : 'border-gray-300'
                      "
                    ></div>
                    Special character
                  </div>
                </div>
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
              <label
                for="confirm-password"
                class="block text-sm font-medium text-gray-700 mb-1"
              >
                Confirm Password
              </label>
              <div class="relative">
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="confirmPassword"
                  id="confirm-password"
                  required
                  class="w-full px-4 py-2.5 rounded-lg border focus:ring focus:ring-opacity-20 transition-all duration-200 pr-10"
                  :class="
                    confirmPassword && !passwordsMatch
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-[#036F8B] focus:ring-[#036F8B]'
                  "
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                  <EyeSlashIcon v-else class="h-5 w-5" />
                </button>
              </div>
              <div class="mt-1.5 min-h-6">
                <p
                  v-if="confirmPassword && !passwordsMatch"
                  class="text-xs text-red-600 flex items-center"
                >
                  <ExclamationCircleIcon class="h-4 w-4 mr-1" />
                  Passwords don't match
                </p>
                <p
                  v-else-if="confirmPassword && passwordsMatch"
                  class="text-xs text-green-600 flex items-center"
                >
                  <CheckCircleIcon class="h-4 w-4 mr-1" />
                  Passwords match
                </p>
              </div>
            </div>

            <!-- Button Group -->
            <div class="mt-8 flex justify-end space-x-4">
              <button
                type="button"
                class="px-5 py-2.5 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium transition-colors duration-200"
                @click="
                  currentPassword = '';
                  newPassword = '';
                  confirmPassword = '';
                  passwordMessage = null;
                "
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-5 py-2.5 rounded-lg text-white font-medium transition-colors duration-200"
                :class="
                  !passwordsMatch ||
                  passwordStrength.value < 50 ||
                  !currentPassword
                    ? 'bg-[#002D4A] opacity-50 cursor-not-allowed'
                    : 'bg-[#002D4A] hover:bg-[#036F8B]'
                "
                :disabled="
                  !passwordsMatch ||
                  passwordStrength.value < 50 ||
                  !currentPassword
                "
              >
                Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
