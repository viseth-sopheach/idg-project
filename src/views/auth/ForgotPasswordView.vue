<template>
  <div
    class="bg-white p-8 rounded-xl border border-gray-200 shadow-md w-full max-w-md space-y-6"
  >
    <div class="text-center">
      <h1 class="text-2xl font-bold text-gray-900">Forgot Password</h1>
      <p class="text-sm text-gray-500 mt-1">
        Enter your email and we'll send you a reset link
      </p>
    </div>

    <form v-if="!sent" @submit.prevent="handleSubmit" class="space-y-4">
      <BaseInput
        v-model="email"
        label="Email"
        type="email"
        placeholder="example@gmail.com"
        required
        :disabled="loading"
      />

      <p v-if="errorMessage" class="text-sm text-red-500">{{ errorMessage }}</p>

      <BaseButton
        type="submit"
        variant="primary"
        class="w-full"
        :disabled="loading"
      >
        {{ loading ? "Sending..." : "Send Reset Link" }}
      </BaseButton>
    </form>

    <div v-else class="text-center space-y-4">
      <p class="text-sm text-green-600">
        If an account exists for <strong>{{ email }}</strong
        >, a password reset link has been sent.
      </p>
      <BaseButton variant="secondary" class="w-full" @click="sent = false">
        Send another link
      </BaseButton>
    </div>

    <p class="text-center text-sm text-gray-500">
      <router-link to="/" class="text-blue-600 font-medium hover:underline">
        Back to login
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import BaseInput from "../../components/common/BaseInput.vue";
import BaseButton from "../../components/common/BaseButton.vue";
import { useAuthStore } from "../../stores/auth";

const authStore = useAuthStore();

const email = ref("");
const loading = ref(false);
const errorMessage = ref("");
const sent = ref(false);

const handleSubmit = async () => {
  if (loading.value) return;
  loading.value = true;
  errorMessage.value = "";
  try {
    await authStore.forgotPassword(email.value);
    sent.value = true;
  } catch (err) {
    const data = err.response?.data;
    errorMessage.value = data?.errors
      ? Object.values(data.errors).flat().join(" ")
      : data?.message || "Failed to send reset link.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};
</script>
