<template>
  <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-md w-full max-w-md space-y-6">
    <div class="text-center">
      <h1 class="text-2xl font-bold text-gray-900"><span class="text-blue-600">IDG</span> POS</h1>
      <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <BaseInput v-model="form.email" label="Email" type="email" placeholder="admin@idg.com" required />
      <BaseInput v-model="form.password" label="Password" type="password" placeholder="••••••••" required />

      <BaseButton type="submit" variant="primary" class="w-full">Sign In</BaseButton>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import BaseInput from '../../components/common/BaseInput.vue'
import BaseButton from '../../components/common/BaseButton.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({ email: '', password: '' })

const handleLogin = async () => {
  try {
    await authStore.login(form.value)
    router.push('/customers')
  } catch (error) {
    alert('Login failed. Please check your credentials.')
  }
}
</script>