<template>
  <button
    :type="type"
    :class="[
      'px-5 py-2 rounded-lg text-sm font-medium transition shadow-sm flex items-center justify-center gap-2',
      variant === 'primary' ? 'bg-blue-600 hover:bg-blue-700 text-white' : '',
      variant === 'secondary' ? 'border border-gray-300 hover:bg-gray-50 bg-white text-gray-700' : '',
      variant === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white' : ''
    ]"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }
})
</script>