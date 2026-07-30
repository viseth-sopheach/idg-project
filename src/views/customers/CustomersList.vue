<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
        <p class="text-sm text-gray-500">Manage your customers</p>
      </div>
      <BaseButton variant="primary">
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

      <div class="overflow-x-auto">
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
                <span :class="['px-2.5 py-1 text-xs font-semibold rounded-md inline-block', customer.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600']">
                  {{ customer.status }}
                </span>
              </td>
              <td class="py-4 px-4">
                <div class="flex items-center justify-center space-x-2">
                  <button class="p-1.5 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  </button>
                  <button class="p-1.5 text-red-500 border border-red-200 rounded-lg hover:bg-red-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import BaseButton from '../../components/common/BaseButton.vue'

const search = ref('')
const customers = ref([
  { id: 1, code: 'CUS-0001', name: 'John Doe', phone: '010 123 456', email: 'john.doe@email.com', status: 'Active' },
  { id: 2, code: 'CUS-0002', name: 'Jane Smith', phone: '012 654 321', email: 'jane.smith@email.com', status: 'Active' },
  { id: 3, code: 'CUS-0003', name: 'Michael Lee', phone: '017 888 999', email: 'michael.lee@email.com', status: 'Active' },
  { id: 4, code: 'CUS-0004', name: 'Emily Johnson', phone: '016 222 111', email: 'emily.j@email.com', status: 'Inactive' },
  { id: 5, code: 'CUS-0005', name: 'David Brown', phone: '011 444 333', email: 'david.b@email.com', status: 'Active' },
  { id: 6, code: 'CUS-0006', name: 'Sokun Reach', phone: '010 555 666', email: 'sokun.r@email.com', status: 'Active' },
  { id: 7, code: 'CUS-0007', name: 'Chan Sreyleak', phone: '012 777 888', email: 'chan.s@email.com', status: 'Inactive' },
  { id: 8, code: 'CUS-0008', name: 'Penh Nary', phone: '015 999 000', email: 'penh.n@email.com', status: 'Active' },
])

const fetchCustomers = async () => {
  try {
    const res = await api.get('/customers')
    if (res.data?.data) customers.value = res.data.data
  } catch (err) {
    console.error(err)
  }
}

const filteredCustomers = computed(() => {
  return customers.value.filter(c =>
    c.name.toLowerCase().includes(search.value.toLowerCase()) ||
    c.code.toLowerCase().includes(search.value.toLowerCase()) ||
    c.email.toLowerCase().includes(search.value.toLowerCase())
  )
})

onMounted(fetchCustomers)
</script>