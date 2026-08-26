<template>
  <div
    class="bg-white p-8 rounded-xl border border-gray-200 shadow-md w-full max-w-md space-y-6"
  >
    <div class="text-center">
      <h1 class="text-2xl font-bold text-gray-900">
        <span class="text-blue-600">POS</span> SYSTEM
      </h1>
      <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <BaseInput
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="example@gmail.com"
        required
        :disabled="loading"
      />
      <BaseInput
        v-model="form.password"
        label="Password"
        type="password"
        placeholder="••••••••"
        required
        :disabled="loading"
      />

      <BaseButton
        type="submit"
        variant="primary"
        class="w-full"
        :disabled="loading"
      >
        <svg
          v-if="loading"
          class="w-4 h-4 animate-spin"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
          />
        </svg>
        {{ loading ? "Signing in..." : "Sign In" }}
      </BaseButton>
      <div class="text-right -mt-2">
        <router-link
          to="/forgot-password"
          class="text-xs font-medium text-blue-600 hover:underline"
        >
          Forgot password?
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import BaseInput from "../../components/common/BaseInput.vue";
import BaseButton from "../../components/common/BaseButton.vue";

const router = useRouter();
const authStore = useAuthStore();

const form = ref({ email: "", password: "" });
const loading = ref(false);

const handleLogin = async () => {
  if (loading.value) return;
  loading.value = true;
  try {
    await authStore.login(form.value);
    router.push("/dashboard");
  } catch (error) {
    alert("Login failed. Please check your credentials.");
  } finally {
    loading.value = false;
  }
};
</script>
