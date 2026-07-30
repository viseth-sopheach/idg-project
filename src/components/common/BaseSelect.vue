<template>
  <div>
    <label v-if="label" class="block text-xs font-semibold text-gray-700 mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <select
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
    >
      <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
      <option
        v-for="opt in options"
        :key="opt.value ?? opt.id"
        :value="opt.value ?? opt.id"
      >
        {{ opt.label ?? opt.name }}
      </option>
    </select>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  placeholder: String,
  required: Boolean,
  options: { type: Array, default: () => [] }
})

defineEmits(['update:modelValue'])
</script>