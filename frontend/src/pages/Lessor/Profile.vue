<script setup>
import LessorLayout from "../../components/LessorLayout.vue";
import { ref, watch, onMounted } from "vue";
import {
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  PencilIcon,
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
    console.log("API Response:", response);
    if (response && response.data && response.data.data) {
      userData.value = {
        name: response.data.data.Name || "",
        email: response.data.data.Email || "",
        phone: response.data.data.phone || "",
        country: response.data.data.country || "",
        city: response.data.data.city || "",
        cin: response.data.data.cin || "",
      };
      console.log("User data loaded:", userData.value);
    } else {
      console.error("API response does not contain expected data:", response);
      profileMessage.value =
        "Failed to load profile data: Invalid response format";
      profileMessageType.value = "error";
    }
  } catch (error) {
    console.error("Error fetching user data:", error);
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

    console.log("Password updated successfully", response.data);
  } catch (error) {
    passwordMessage.value =
      error.response?.data?.message || "Error updating password";
    passwordMessageType.value = "error";
    console.error(
      "Error updating password",
      error.response ? error.response.data : error
    );
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
    console.log("Profile updated successfully", response.data);
  } catch (error) {
    profileMessage.value =
      error.response?.data?.message || "Error updating profile";
    profileMessageType.value = "error";
    console.error(
      "Error updating profile",
      error.response ? error.response.data : error
    );
  }
};
</script>

<template>
  <LessorLayout>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Loading indicator -->
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div
          class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600"
        ></div>
        <span class="ml-2 text-gray-600">Loading profile information...</span>
      </div>

      <!-- Personal Information Section -->
      <div v-else class="mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
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

              <!-- Edit Mode Toggle -->
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

            <!-- Success/Error Message -->
            <div
              v-if="profileMessage"
              class="mt-4 p-3 rounded-md text-white"
              :class="
                profileMessageType === 'success' ? 'bg-green-500' : 'bg-red-500'
              "
            >
              {{ profileMessage }}
            </div>

            <!-- View Mode -->
            <div v-if="!isEditMode" class="mt-6">
              <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                <!-- First row: Name and Email -->
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

                <!-- Second row: Phone and CIN -->
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

                <!-- Third row: Country and City -->
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

            <!-- Edit Mode Form -->
            <form v-else @submit="handleProfileUpdate" class="mt-6">
              <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- First row: Name and Email -->
                <div class="sm:col-span-3">
                  <label
                    for="first-name"
                    class="block text-sm font-medium text-gray-900"
                    >Full Name</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.name"
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
                      v-model="editUserData.email"
                      id="email"
                      name="email"
                      type="email"
                      autocomplete="email"
                      placeholder="you@example.com"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                  </div>
                </div>

                <!-- Second row: Phone and CIN -->
                <div class="sm:col-span-3">
                  <label
                    for="phone"
                    class="block text-sm font-medium text-gray-900"
                    >Phone Number</label
                  >
                  <div class="mt-2">
                    <input
                      v-model="editUserData.phone"
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
                      v-model="editUserData.cin"
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
                      v-model="editUserData.country"
                      id="country"
                      name="country"
                      autocomplete="country-name"
                      class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    >
                      <option value="" disabled>Select your country</option>
                      <option value="Tunisia">Tunisia</option>
                      <option value="Canada">Canada</option>
                      <option value="Mexico">Mexico</option>
                      <option value="United Kingdom">United Kingdom</option>
                      <option value="France">France</option>
                      <option value="Germany">Germany</option>
                      <option value="Japan">Japan</option>
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
                      v-model="editUserData.city"
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

              <!-- Edit Mode Buttons -->
              <div class="mt-6 flex items-center justify-end gap-x-6">
                <button
                  type="button"
                  @click="cancelEdit"
                  class="text-sm font-semibold text-gray-900 hover:text-gray-700"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="rounded-md bg-[#002D4A] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#036F8B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#036F8B]"
                >
                  Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Password Change Section -->
      <form @submit="handlePasswordUpdate">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="border-b border-gray-900/10 pb-6">
            <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
            <p class="mt-1 text-sm text-gray-600">
              We recommend using a strong, unique password that you don't use
              elsewhere.
            </p>
            <div
              v-if="passwordMessage"
              class="mt-4 p-3 rounded-md text-white"
              :class="
                passwordMessageType === 'success'
                  ? 'bg-green-500'
                  : 'bg-red-500'
              "
            >
              {{ passwordMessage }}
            </div>

            <div class="mt-6">
              <div class="space-y-6">
                <!-- Current Password -->
                <div>
                  <label
                    for="current-password"
                    class="block text-sm font-medium text-gray-900"
                  >
                    Current Password
                  </label>
                  <div class="mt-1 relative">
                    <input
                      :type="showCurrentPassword ? 'text' : 'password'"
                      v-model="currentPassword"
                      name="current-password"
                      id="current-password"
                      autocomplete="current-password"
                      required
                      class="block w-full rounded-md border-0 px-3 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                    <button
                      type="button"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                      <EyeIcon v-if="!showCurrentPassword" class="h-5 w-5" />
                      <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                  </div>
                </div>

                <!-- New Password -->
                <div>
                  <label
                    for="new-password"
                    class="block text-sm font-medium text-gray-900"
                  >
                    New Password
                  </label>
                  <div class="mt-1 relative">
                    <input
                      :type="showNewPassword ? 'text' : 'password'"
                      v-model="newPassword"
                      name="new-password"
                      id="new-password"
                      autocomplete="new-password"
                      required
                      class="block w-full rounded-md border-0 px-3 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                    />
                    <button
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                      <EyeIcon v-if="!showNewPassword" class="h-5 w-5" />
                      <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                  </div>

                  <!-- Password strength meter -->
                  <div class="mt-2">
                    <div class="flex items-center justify-between mb-1">
                      <p class="text-xs text-gray-600">Password strength:</p>
                      <p
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
                      </p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                      <div
                        class="h-1.5 rounded-full"
                        :class="passwordStrength.color"
                        :style="`width: ${passwordStrength.value}%`"
                      ></div>
                    </div>
                    <ul class="mt-2 text-xs text-gray-600 space-y-1">
                      <li class="flex items-center gap-x-2">
                        <span
                          :class="
                            newPassword.length >= 8
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="newPassword.length >= 8"
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
                            /[A-Z]/.test(newPassword)
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="/[A-Z]/.test(newPassword)"
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
                            /[0-9]/.test(newPassword)
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="/[0-9]/.test(newPassword)"
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
                            /[^A-Za-z0-9]/.test(newPassword)
                              ? 'text-green-600'
                              : 'text-gray-400'
                          "
                        >
                          <CheckCircleIcon
                            class="h-4 w-4 inline"
                            v-if="/[^A-Za-z0-9]/.test(newPassword)"
                          />
                          <span
                            class="inline-block h-4 w-4 rounded-full border border-current"
                            v-else
                          ></span>
                        </span>
                        At least one special character
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Confirm Password -->
                <div>
                  <label
                    for="confirm-password"
                    class="block text-sm font-medium text-gray-900"
                  >
                    Confirm Password
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
                        confirmPassword && !passwordsMatch
                          ? 'ring-red-300 focus:ring-red-500'
                          : 'ring-gray-300',
                      ]"
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                    >
                      <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                      <EyeSlashIcon v-else class="h-5 w-5" />
                    </button>
                  </div>
                  <p
                    v-if="confirmPassword && !passwordsMatch"
                    class="mt-1 text-xs text-red-600 flex items-center"
                  >
                    <ExclamationCircleIcon class="h-4 w-4 mr-1" />
                    Passwords don't match
                  </p>
                  <p
                    v-else-if="confirmPassword && passwordsMatch"
                    class="mt-1 text-xs text-green-600 flex items-center"
                  >
                    <CheckCircleIcon class="h-4 w-4 mr-1" />
                    Passwords match
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Password Update Button -->
          <div class="mt-6 flex items-center justify-end gap-x-6">
            <button
              type="button"
              class="text-sm font-semibold text-gray-900 hover:text-gray-700"
              @click="
                currentPassword = '';
                newPassword = '';
                confirmPassword = '';
              "
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-md bg-[#036F8B] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#036F8B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#036F8B]"
              :disabled="
                !passwordsMatch ||
                passwordStrength.value < 50 ||
                !currentPassword
              "
              :class="{
                'opacity-50 cursor-not-allowed':
                  !passwordsMatch ||
                  passwordStrength.value < 50 ||
                  !currentPassword,
              }"
            >
              Update Password
            </button>
          </div>
        </div>
      </form>
    </div>
  </LessorLayout>
</template>
