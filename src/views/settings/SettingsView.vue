<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
      <p class="text-sm text-gray-500">Manage your account details</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="text-base font-bold text-gray-900">Profile</h2>

        <div class="space-y-4">
          <BaseInput
            v-model="profileForm.name"
            label="Name"
            placeholder="update your name"
            required
          />
          <!-- <BaseInput
            v-model="profileForm.email"
            label="Email"
            type="email"
            disabled
          /> -->
          <BaseInput
            v-model="profileForm.current_password"
            label="Confirm with Password"
            type="password"
            placeholder="Enter your password to save changes"
            required
          />
        </div>

        <p v-if="profileError" class="text-sm text-red-500">
          {{ profileError }}
        </p>
        <p v-if="profileSuccess" class="text-sm text-green-600">
          {{ profileSuccess }}
        </p>

        <div class="flex justify-end">
          <BaseButton
            variant="primary"
            @click="saveProfile"
            :disabled="profileSaving"
          >
            {{ profileSaving ? "Saving..." : "Save Profile" }}
          </BaseButton>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="text-base font-bold text-gray-900">Change Password</h2>

        <div class="space-y-4">
          <BaseInput
            v-model="passwordForm.current_password"
            label="Current Password"
            type="password"
            placeholder="••••••••"
            required
          />
          <BaseInput
            v-model="passwordForm.password"
            label="New Password"
            type="password"
            placeholder="At least 8 characters"
            required
          />
          <BaseInput
            v-model="passwordForm.password_confirmation"
            label="Confirm New Password"
            type="password"
            placeholder="Repeat new password"
            required
          />
        </div>

        <p v-if="passwordError" class="text-sm text-red-500">
          {{ passwordError }}
        </p>
        <p v-if="passwordSuccess" class="text-sm text-green-600">
          {{ passwordSuccess }}
        </p>

        <div class="flex justify-end">
          <BaseButton
            variant="primary"
            @click="savePassword"
            :disabled="passwordSaving"
          >
            {{ passwordSaving ? "Updating..." : "Update Password" }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import BaseInput from "../../components/common/BaseInput.vue";
import BaseButton from "../../components/common/BaseButton.vue";
import { useSettings } from "../../composables/useSettings";

const {
  profileForm,
  profileSaving,
  profileError,
  profileSuccess,
  passwordForm,
  passwordSaving,
  passwordError,
  passwordSuccess,
  loadProfile,
  saveProfile,
  savePassword,
} = useSettings();

onMounted(loadProfile);
</script>
