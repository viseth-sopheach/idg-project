<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
        <p class="text-sm text-gray-500">Manage your orders</p>
      </div>
      <BaseButton variant="primary" @click="router.push('/orders/create')">
        <span class="text-lg leading-none">+</span> New Order
      </BaseButton>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
      <div class="flex gap-3">
        <div class="relative max-w-md flex-1">
          <input
            v-model="search"
            type="text"
            placeholder="Search by order no or customer..."
            class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            @input="onFilterChange"
          />
        </div>
        <select
          v-model="statusFilter"
          @change="onFilterChange"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All statuses</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-400 text-sm">Loading orders...</div>
      <div v-else-if="errorMessage" class="text-center py-10 text-red-500 text-sm">{{ errorMessage }}</div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200">
            <tr>
              <th class="py-3 px-4">Order No</th>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Items</th>
              <th class="py-3 px-4">Total</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50/50">
              <td class="py-4 px-4 font-semibold text-gray-800">{{ order.order_no }}</td>
              <td class="py-4 px-4 text-gray-700">{{ order.customer?.name ?? '—' }}</td>
              <td class="py-4 px-4 text-gray-500">{{ order.order_date }}</td>
              <td class="py-4 px-4 text-gray-500">{{ order.items_count ?? '—' }}</td>
              <td class="py-4 px-4 font-semibold">${{ Number(order.total_amount).toFixed(2) }}</td>
              <td class="py-4 px-4">
                <OrderStatusSelect
                  :order="order"
                  :status="order.status"
                  :update-fn="updateOrderStatus"
                />
              </td>
              <td class="py-4 px-4 text-center">
                <router-link :to="`/orders/${order.id}`" class="text-blue-600 text-xs font-medium hover:underline">
                  View
                </router-link>
              </td>
            </tr>
            <tr v-if="orders.length === 0">
              <td colspan="7" class="py-10 text-center text-gray-400">No orders found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <BasePagination :meta="meta" :range-start="rangeStart" :range-end="rangeEnd" :loading="loading" @change-page="goToPage" />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '../../components/common/BaseButton.vue'
import BasePagination from '../../components/common/BasePagination.vue'
import OrderStatusSelect from '../../components/orders/OrderStatusSelect.vue'
import { useOrders } from '../../composables/useOrders'

const router = useRouter()
const {
  search, statusFilter, orders, loading, errorMessage, meta,
  rangeStart, rangeEnd, fetchOrders, goToPage, onFilterChange, updateOrderStatus,
} = useOrders()

onMounted(() => fetchOrders(1))
</script>