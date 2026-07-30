<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
      <p class="text-sm text-gray-500">Sales performance and top products</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1"
            >From</label
          >
          <input
            v-model="dateFrom"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1"
            >To</label
          >
          <input
            v-model="dateTo"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <BaseButton variant="primary" @click="fetchReports" :disabled="loading">
          {{ loading ? "Loading..." : "Apply" }}
        </BaseButton>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-400 text-sm">
        Loading reports...
      </div>
      <div
        v-else-if="errorMessage"
        class="text-center py-10 text-red-500 text-sm"
      >
        {{ errorMessage }}
      </div>

      <template v-else>
        <div class="grid grid-cols-3 gap-4">
          <div class="border border-gray-200 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500">Total Orders</p>
            <p class="text-xl font-bold text-gray-900 mt-1">
              {{ salesSummary.total_orders }}
            </p>
          </div>
          <div class="border border-gray-200 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500">Total Revenue</p>
            <p class="text-xl font-bold text-gray-900 mt-1">
              ${{ salesSummary.total_revenue.toFixed(2) }}
            </p>
          </div>
          <div class="border border-gray-200 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500">Total Paid</p>
            <p class="text-xl font-bold text-gray-900 mt-1">
              ${{ salesSummary.total_paid.toFixed(2) }}
            </p>
          </div>
        </div>

        <div>
          <h2 class="text-sm font-bold text-gray-900 mb-3">Revenue by Date</h2>
          <table class="w-full text-left text-sm">
            <thead
              class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200"
            >
              <tr>
                <th class="py-2 px-3">Date</th>
                <th class="py-2 px-3">Orders</th>
                <th class="py-2 px-3">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="row in salesSummary.by_date" :key="row.date">
                <td class="py-2 px-3">{{ row.date }}</td>
                <td class="py-2 px-3">{{ row.orders_count }}</td>
                <td class="py-2 px-3 font-semibold">
                  ${{ row.revenue.toFixed(2) }}
                </td>
              </tr>
              <tr v-if="!salesSummary.by_date.length">
                <td colspan="3" class="py-8 text-center text-gray-400">
                  No sales in this range.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div>
          <h2 class="text-sm font-bold text-gray-900 mb-3">Top Products</h2>
          <table class="w-full text-left text-sm">
            <thead
              class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200"
            >
              <tr>
                <th class="py-2 px-3">Product</th>
                <th class="py-2 px-3">SKU</th>
                <th class="py-2 px-3">Qty Sold</th>
                <th class="py-2 px-3">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in topProducts" :key="p.product_id">
                <td class="py-2 px-3 font-medium">{{ p.name }}</td>
                <td class="py-2 px-3 font-mono text-gray-500">{{ p.sku }}</td>
                <td class="py-2 px-3">{{ p.qty_sold }}</td>
                <td class="py-2 px-3 font-semibold">
                  ${{ p.revenue.toFixed(2) }}
                </td>
              </tr>
              <tr v-if="!topProducts.length">
                <td colspan="4" class="py-8 text-center text-gray-400">
                  No product sales in this range.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import BaseButton from "../../components/common/BaseButton.vue";
import { useReports } from "../../composables/useReports";

const {
  loading,
  errorMessage,
  dateFrom,
  dateTo,
  salesSummary,
  topProducts,
  fetchReports,
} = useReports();

onMounted(fetchReports);
</script>
