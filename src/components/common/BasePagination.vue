<template>
  <div class="flex justify-between items-center text-sm text-gray-500 pt-2">
    <div>
      Showing {{ rangeStart }} to {{ rangeEnd }} of {{ meta.total }} entries
    </div>

    <div v-if="meta.last_page > 1" class="flex items-center gap-1">
      <button
        class="p-2 border rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white"
        :disabled="meta.current_page === 1 || loading"
        @click="$emit('change-page', meta.current_page - 1)"
      >
        &lt;
      </button>

      <button
        v-for="page in pageNumbers"
        :key="page"
        class="w-9 h-9 flex items-center justify-center rounded-lg font-medium"
        :class="page === meta.current_page
          ? 'bg-blue-600 text-white'
          : 'border hover:bg-gray-50 text-gray-700'"
        :disabled="loading"
        @click="$emit('change-page', page)"
      >
        {{ page }}
      </button>

      <button
        class="p-2 border rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white"
        :disabled="meta.current_page === meta.last_page || loading"
        @click="$emit('change-page', meta.current_page + 1)"
      >
        &gt;
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: { type: Object, required: true },
  rangeStart: { type: Number, required: true },
  rangeEnd: { type: Number, required: true },
  loading: { type: Boolean, default: false },
  windowSize: { type: Number, default: 5 },
})

defineEmits(['change-page'])

const pageNumbers = computed(() => {
  const total = props.meta.last_page
  const current = props.meta.current_page
  let start = Math.max(1, current - Math.floor(props.windowSize / 2))
  let end = Math.min(total, start + props.windowSize - 1)
  start = Math.max(1, end - props.windowSize + 1)

  const pages = []
  for (let p = start; p <= end; p++) pages.push(p)
  return pages
})
</script>