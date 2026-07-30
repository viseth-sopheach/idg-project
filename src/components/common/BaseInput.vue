<template>
  <div>
    <label v-if="label" class="block text-xs font-semibold text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative">
      <span v-if="prefix" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">
        {{ prefix }}
      </span>
      <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
        :class="[
          'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition',
          prefix ? 'pl-8' : '',
          disabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'
        ]"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  type: { type: String, default: 'text' },
  placeholder: String,
  required: Boolean,
  disabled: Boolean,
  prefix: String
})

defineEmits(['update:modelValue'])
</script>