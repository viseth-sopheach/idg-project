<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500">
          Overview of your store's performance
        </p>
      </div>
      <div class="flex gap-3">
        <BaseButton variant="secondary" @click="router.push('/customers')"
          >New Customer</BaseButton
        >
        <BaseButton variant="secondary" @click="router.push('/products/create')"
          >New Product</BaseButton
        >
        <BaseButton variant="primary" @click="router.push('/orders/create')"
          >New Order</BaseButton
        >
      </div>
    </div>

    <div v-if="loading" class="text-center py-10 text-gray-400 text-sm">
      Loading dashboard...
    </div>
    <div
      v-else-if="errorMessage"
      class="text-center py-10 text-red-500 text-sm"
    >
      {{ errorMessage }}
    </div>

    <template v-else>
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <StatCard
          label="Total Revenue"
          :value="currency(summary.total_revenue)"
        />
        <StatCard
          label="Today's Sales"
          :value="currency(summary.todays_sales)"
        />
        <StatCard label="Total Orders" :value="summary.total_orders" />
        <StatCard label="Total Products" :value="summary.total_products" />
        <StatCard label="Total Customers" :value="summary.total_customers" />
      </div>

      <div class="grid grid-cols-12 gap-6">
        <div
          class="col-span-12 lg:col-span-7 bg-white rounded-xl border border-gray-200 p-4 sm:p-6"
        >
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-base font-bold text-gray-900">Recent Orders</h2>
            <router-link
              to="/orders"
              class="text-xs font-medium text-blue-600 hover:underline shrink-0"
              >View all</router-link
            >
          </div>

          <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
            <table class="w-full min-w-[520px] text-sm text-left">
              <thead class="border-b text-gray-500 font-semibold">
                <tr>
                  <th class="py-2 pr-2 whitespace-nowrap">Order No</th>
                  <th class="py-2 pr-2 whitespace-nowrap">Customer</th>
                  <th class="py-2 pr-2 whitespace-nowrap">Status</th>
                  <th class="py-2 text-right whitespace-nowrap">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="order in summary.recent_orders" :key="order.id">
                  <td
                    class="py-3 pr-2 font-medium text-gray-900 whitespace-nowrap"
                  >
                    {{ order.order_no }}
                  </td>
                  <td class="py-3 pr-2 text-gray-600 max-w-[140px] truncate">
                    {{ order.customer?.name ?? "—" }}
                  </td>
                  <td class="py-3 pr-2 whitespace-nowrap">
                    <OrderStatusSelect
                      :order="order"
                      :status="order.status"
                      :update-fn="updateStatusOnDashboard"
                    />
                  </td>
                  <td class="py-3 text-right font-semibold whitespace-nowrap">
                    {{ currency(order.total_amount) }}
                  </td>
                </tr>
                <tr v-if="!summary.recent_orders?.length">
                  <td colspan="4" class="py-8 text-center text-gray-400">
                    No orders yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div
          class="col-span-12 lg:col-span-5 bg-white rounded-xl border border-gray-200 p-6"
        >
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-base font-bold text-gray-900">Low Stock</h2>
            <span
              class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded-md"
            >
              {{ summary.low_stock_count }} item(s)
            </span>
          </div>
          <ul class="divide-y divide-gray-100">
            <li
              v-for="p in summary.low_stock_products"
              :key="p.id"
              class="py-3 flex justify-between items-center"
            >
              <div>
                <p class="font-medium text-gray-900 text-sm">{{ p.name }}</p>
                <p class="text-xs text-gray-500 font-mono">{{ p.sku }}</p>
              </div>
              <span class="text-sm font-bold text-red-600"
                >{{ p.qty }} left</span
              >
            </li>
            <li
              v-if="!summary.low_stock_products?.length"
              class="py-8 text-center text-gray-400 text-sm"
            >
              All products are well stocked.
            </li>
          </ul>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, h } from "vue";
import { useRouter } from "vue-router";
import { useDashboard } from "../../composables/useDashboard";
import BaseButton from "../../components/common/BaseButton.vue";
import OrderStatusSelect from "../../components/orders/OrderStatusSelect.vue";
import { useOrders } from "../../composables/useOrders";

const { updateOrderStatus: updateStatusOnDashboard } = useOrders();
const router = useRouter();
const { loading, errorMessage, summary, fetchSummary } = useDashboard();

const currency = (val) => `$${Number(val ?? 0).toFixed(2)}`;

const statusClass = (status) =>
  ({
    pending: "bg-amber-100 text-amber-700",
    completed: "bg-green-100 text-green-700",
    cancelled: "bg-red-100 text-red-600",
  })[status] ?? "bg-gray-100 text-gray-600";

const StatCard = {
  props: ["label", "value"],
  render() {
    return h(
      "div",
      { class: "bg-white rounded-xl border border-gray-200 p-5" },
      [
        h("p", { class: "text-xs font-medium text-gray-500" }, this.label),
        h(
          "p",
          { class: "text-xl font-bold text-gray-900 mt-1" },
          String(this.value),
        ),
      ],
    );
  },
};

onMounted(fetchSummary);
</script>
