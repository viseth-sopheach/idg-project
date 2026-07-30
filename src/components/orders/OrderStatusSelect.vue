<template>
  <div class="relative inline-block">
    <select
      :value="status"
      :disabled="saving"
      @change="onChange"
      :class="[
        badgeClass(status),
        'appearance-none px-2 py-0.5 pr-6 text-xs font-semibold rounded-md capitalize border-0 cursor-pointer disabled:opacity-60 disabled:cursor-wait focus:outline-none focus:ring-2 focus:ring-blue-500',
      ]"
    >
      <option v-for="opt in STATUSES" :key="opt" :value="opt">{{ opt }}</option>
    </select>
    <p v-if="error" class="absolute top-full left-0 mt-1 text-[11px] text-red-500 whitespace-nowrap">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const STATUSES = ['pending', 'completed', 'cancelled']

const props = defineProps({
  order: { type: Object, required: true },
  status: { type: String, required: true },
  updateFn: { type: Function, required: true }, // (order, newStatus) => Promise
})

const emit = defineEmits(['updated'])

const saving = ref(false)
const error = ref('')

const badgeClass = (status) => ({
  pending: 'bg-amber-100 text-amber-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-600',
}[status] ?? 'bg-gray-100 text-gray-600')

const onChange = async (event) => {
  const newStatus = event.target.value
  if (newStatus === props.status) return

  const confirmed = window.confirm(
    newStatus === 'cancelled'
      ? `Cancel order ${props.order.order_no}? This will restock its items.`
      : `Change order ${props.order.order_no} status to "${newStatus}"?`
  )
  if (!confirmed) {
    event.target.value = props.status
    return
  }

  saving.value = true
  error.value = ''
  try {
    const updated = await props.updateFn(props.order, newStatus)
    emit('updated', updated)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update status.'
    event.target.value = props.status
    console.error(err)
  } finally {
    saving.value = false
  }
}
</script>