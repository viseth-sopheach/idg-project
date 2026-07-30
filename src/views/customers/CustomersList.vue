<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
        <p class="text-sm text-gray-500">Manage your customers</p>
      </div>
      <BaseButton variant="primary" @click="openCreateModal">
        <span class="text-lg leading-none">+</span> Add New Customer
      </BaseButton>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
      <div class="relative max-w-md">
        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input
          v-model="search"
          type="text"
          placeholder="Search by name, phone or email..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-400 text-sm">Loading customers...</div>
      <div v-else-if="errorMessage" class="text-center py-10 text-red-500 text-sm">{{ errorMessage }}</div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200">
            <tr>
              <th class="py-3 px-4">#</th>
              <th class="py-3 px-4">Code</th>
              <th class="py-3 px-4">Name</th>
              <th class="py-3 px-4">Phone</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(customer, index) in filteredCustomers" :key="customer.id" class="hover:bg-gray-50/50">
              <td class="py-4 px-4 font-medium text-gray-600">{{ index + 1 }}</td>
              <td class="py-4 px-4 font-semibold text-gray-800">{{ customer.code }}</td>
              <td class="py-4 px-4 font-medium text-gray-900">{{ customer.name }}</td>
              <td class="py-4 px-4 text-gray-600">{{ customer.phone }}</td>
              <td class="py-4 px-4 text-gray-600">{{ customer.email }}</td>
              <td class="py-4 px-4">
                <span :class="['px-2.5 py-1 text-xs font-semibold rounded-md inline-block capitalize', customer.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600']">
                  {{ customer.status }}
                </span>
              </td>
              <td class="py-4 px-4">
                <div class="flex items-center justify-center space-x-2">
                  <button @click="openEditModal(customer)" class="p-1.5 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  </button>
                  <button @click="confirmDelete(customer)" class="p-1.5 text-red-500 border border-red-200 rounded-lg hover:bg-red-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredCustomers.length === 0">
              <td colspan="7" class="py-10 text-center text-gray-400">No customers found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center text-sm text-gray-500 pt-2">
        <div>Showing 1 to {{ filteredCustomers.length }} of {{ customers.length }} entries</div>
        <div class="flex items-center gap-1">
          <button class="p-2 border rounded-lg hover:bg-gray-50">&lt;</button>
          <button class="px-3.5 py-1.5 bg-blue-600 text-white font-medium rounded-lg">1</button>
          <button class="p-2 border rounded-lg hover:bg-gray-50">&gt;</button>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-xl w-full max-w-md p-6 space-y-4">
        <h2 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Edit Customer' : 'Add New Customer' }}</h2>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Name</label>
            <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Phone</label>
            <input v-model="form.phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <p v-if="formError" class="text-sm text-red-500">{{ formError }}</p>

        <div class="flex justify-end gap-3 pt-2">
          <BaseButton variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton variant="primary" @click="submitForm" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save' }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import BaseButton from '../../components/common/BaseButton.vue'

const search = ref('')
const customers = ref([])
const loading = ref(false)
const errorMessage = ref('')

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({
  name: '',
  phone: '',
  email: '',
  status: 'active',
})

const fetchCustomers = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await api.get('/customers')
    customers.value = res.data?.data ?? []
  } catch (err) {
    errorMessage.value = 'Failed to load customers.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const filteredCustomers = computed(() => {
  const term = search.value.toLowerCase()
  return customers.value.filter(c =>
    (c.name ?? '').toLowerCase().includes(term) ||
    (c.code ?? '').toLowerCase().includes(term) ||
    (c.email ?? '').toLowerCase().includes(term)
  )
})

const resetForm = () => {
  form.value = { name: '', phone: '', email: '', status: 'active' }
  formError.value = ''
}

const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  resetForm()
  showModal.value = true
}

const openEditModal = (customer) => {
  isEditing.value = true
  editingId.value = customer.id
  form.value = {
    name: customer.name ?? '',
    phone: customer.phone ?? '',
    email: customer.email ?? '',
    status: customer.status ?? 'active',
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const submitForm = async () => {
  saving.value = true
  formError.value = ''
  try {
    if (isEditing.value) {
      await api.put(`/customers/${editingId.value}`, form.value)
    } else {
      await api.post('/customers', form.value)
    }
    await fetchCustomers()
    showModal.value = false
  } catch (err) {
    formError.value = err.response?.data?.message || 'Failed to save customer.'
    console.error(err)
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (customer) => {
  if (!window.confirm(`Delete customer "${customer.name}"?`)) return
  try {
    await api.delete(`/customers/${customer.id}`)
    customers.value = customers.value.filter(c => c.id !== customer.id)
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete customer.')
    console.error(err)
  }
}

onMounted(fetchCustomers)
</script>