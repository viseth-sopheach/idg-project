<template>
  <div
    class="bg-white p-8 rounded-xl border border-gray-200 shadow-md w-full max-w-md space-y-6"
  >
    <div class="text-center">
      <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
      <p class="text-sm text-gray-500 mt-1">
        Choose a new password for your account
      </p>
    </div>

    <form v-if="!done" @submit.prevent="handleSubmit" class="space-y-4">
      <BaseInput
        v-model="form.email"
        label="Email"
        type="email"
        required
        :disabled="loading"
      />
      <BaseInput
        v-model="form.password"
        label="New Password"
        type="password"
        placeholder="At least 8 characters"
        required
        :disabled="loading"
      />
      <BaseInput
        v-model="form.password_confirmation"
        label="Confirm New Password"
        type="password"
        placeholder="Repeat new password"
        required
        :disabled="loading"
      />

      <p v-if="!token" class="text-sm text-red-500">
        This reset link is invalid or missing. Please request a new one.
      </p>
      <p v-else-if="errorMessage" class="text-sm text-red-500">
        {{ errorMessage }}
      </p>

      <BaseButton
        type="submit"
        variant="primary"
        class="w-full"
        :disabled="loading || !token"
      >
        {{ loading ? "Resetting..." : "Reset Password" }}
      </BaseButton>
    </form>

    <div v-else class="text-center space-y-4">
      <p class="text-sm text-green-600">
        Your password has been reset successfully.
      </p>
      <BaseButton variant="primary" class="w-full" @click="router.push('/')">
        Go to Login
      </BaseButton>
    </div>

    <p v-if="!done" class="text-center text-sm text-gray-500">
      <router-link to="/" class="text-blue-600 font-medium hover:underline">
        Back to login
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import BaseInput from "../../components/common/BaseInput.vue";
import BaseButton from "../../components/common/BaseButton.vue";
import { useAuthStore } from "../../stores/auth";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const token = ref(String(route.query.token || ""));

const form = reactive({
  email: String(route.query.email || ""),
  password: "",
  password_confirmation: "",
});

const loading = ref(false);
const errorMessage = ref("");
const done = ref(false);

const handleSubmit = async () => {
  if (!token.value || loading.value) return;

  if (form.password !== form.password_confirmation) {
    errorMessage.value = "Password confirmation does not match.";
    return;
  }

  loading.value = true;
  errorMessage.value = "";
  try {
    await authStore.resetPassword({
      token: token.value,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    });
    done.value = true;
  } catch (err) {
    const data = err.response?.data;
    errorMessage.value = data?.errors
      ? Object.values(data.errors).flat().join(" ")
      : data?.message || "Failed to reset password. The link may have expired.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};
</script>
