<template>
  <div class="space-y-6">
    <div class="flex justify-between items-start">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Order</h1>
        <p class="text-sm text-gray-400 mt-0.5">
          Dashboard &gt; Orders &gt;
          <span class="text-gray-600">Create Order</span>
        </p>
      </div>
      <div class="flex gap-3">
        <BaseButton variant="secondary">Cancel</BaseButton>
        <BaseButton variant="primary" @click="saveOrder">Save Order</BaseButton>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
      <h2 class="text-base font-bold text-gray-900">Order Information</h2>

      <div class="grid grid-cols-4 gap-4">
        <BaseInput label="Order No" value="Auto Generate" disabled />

        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1"
            >Customer <span class="text-red-500">*</span></label
          >
          <div class="flex gap-2">
            <select
              v-model="form.customer_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500"
            >
              <option value="" disabled selected>Select customer</option>
              <option v-for="c in customerOptions" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <button
              class="px-3 py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 font-bold"
            >
              +
            </button>
          </div>
        </div>

        <BaseInput
          v-model="form.order_date"
          label="Order Date"
          type="date"
          required
        />
        <BaseSelect
          v-model="form.status"
          label="Status"
          required
          :options="[
            { value: 'Pending', label: 'Pending' },
            { value: 'Completed', label: 'Completed' },
          ]"
        />
      </div>

      <div class="grid grid-cols-5 gap-4 pt-2">
        <BaseInput
          label="Sub Total"
          :model-value="`$ ${subTotal.toFixed(2)}`"
          disabled
        />
        <BaseInput
          label="Discount Amount"
          :model-value="`$ ${discountAmount.toFixed(2)}`"
          disabled
        />
        <BaseInput
          label="Delivery Fee"
          :model-value="`$ ${deliveryFee.toFixed(2)}`"
          disabled
        />
        <BaseInput
          label="Total Amount"
          :model-value="`$ ${totalAmount.toFixed(2)}`"
          disabled
        />
        <BaseInput
          label="Total Paid"
          :model-value="`$ ${totalPaid.toFixed(2)}`"
          disabled
        />
      </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
      <div
        class="col-span-6 bg-white rounded-xl border border-gray-200 p-5 space-y-4"
      >
        <h3 class="text-sm font-bold text-blue-600">Add Products</h3>

        <div class="flex gap-3">
          <input
            type="text"
            placeholder="Search product..."
            class="w-full border rounded-lg px-3 py-1.5 text-xs"
          />
          <select class="border rounded-lg px-3 py-1.5 text-xs bg-white">
            <option>All Categories</option>
          </select>
        </div>

        <table class="w-full text-xs text-left">
          <thead class="border-b bg-gray-50 text-gray-600 font-semibold">
            <tr>
              <th class="p-2">Product</th>
              <th class="p-2">SKU</th>
              <th class="p-2">Price</th>
              <th class="p-2">Stock</th>
              <th class="p-2 text-center">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="product in productsList" :key="product.id">
              <td class="p-2 font-medium">{{ product.name }}</td>
              <td class="p-2 font-mono text-gray-500">{{ product.sku }}</td>
              <td class="p-2 font-semibold">${{ product.price.toFixed(2) }}</td>
              <td class="p-2">{{ product.stock }}</td>
              <td class="p-2 text-center">
                <button
                  @click="addToCart(product)"
                  class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md font-medium hover:bg-blue-100"
                >
                  Add
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="col-span-6 space-y-4">
        <div
          class="bg-white rounded-xl border border-gray-200 p-5 min-h-[220px]"
        >
          <h3 class="text-xs font-bold text-gray-800 mb-3">
            Order Items ({{ cart.length }})
          </h3>
          <div
            v-if="cart.length === 0"
            class="text-center py-10 text-gray-400 text-xs"
          >
            No items added yet.
          </div>
          <table v-else class="w-full text-xs text-left">
            <thead class="border-b bg-gray-50">
              <tr>
                <th class="p-2">#</th>
                <th class="p-2">Product</th>
                <th class="p-2">Price</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Total</th>
                <th class="p-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="(item, i) in cart" :key="item.id">
                <td class="p-2">{{ i + 1 }}</td>
                <td class="p-2 font-medium">{{ item.name }}</td>
                <td class="p-2">${{ item.price.toFixed(2) }}</td>
                <td class="p-2">
                  <input
                    type="number"
                    min="1"
                    v-model.number="item.qty"
                    class="w-12 border rounded text-center"
                  />
                </td>
                <td class="p-2 font-semibold">
                  ${{ (item.price * item.qty).toFixed(2) }}
                </td>
                <td class="p-2">
                  <button
                    @click="removeFromCart(i)"
                    class="text-red-500 font-bold"
                  >
                    ×
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
          <h3 class="text-xs font-bold text-gray-800">Order Summary</h3>
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="space-y-2">
              <div class="flex justify-between">
                <span>Sub Total</span
                ><span class="font-bold">${{ subTotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span>Discount</span>
                <input
                  type="number"
                  v-model.number="discountAmount"
                  class="w-16 border rounded px-1 text-right"
                />
              </div>
              <div class="flex justify-between items-center">
                <span>Delivery</span>
                <input
                  type="number"
                  v-model.number="deliveryFee"
                  class="w-16 border rounded px-1 text-right"
                />
              </div>
            </div>
            <div class="space-y-2 border-l pl-4">
              <div>
                <label class="block font-semibold">Total Paid</label>
                <input
                  type="number"
                  v-model.number="totalPaid"
                  class="w-full border rounded px-2 py-1"
                />
              </div>
              <div>
                <span class="block font-semibold">Payment Due</span>
                <span class="text-base font-bold text-red-600"
                  >${{ paymentDue.toFixed(2) }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import api from "../../services/api";
import BaseInput from "../../components/common/BaseInput.vue";
import BaseSelect from "../../components/common/BaseSelect.vue";
import BaseButton from "../../components/common/BaseButton.vue";

const form = ref({
  customer_id: "",
  order_date: "2026-07-25",
  status: "Pending",
  note: "",
});
const customerOptions = ref([
  { id: 1, name: "John Doe" },
  { id: 2, name: "Jane Smith" },
]);
const productsList = ref([
  {
    id: 101,
    name: "Black T-Shirt (M)",
    sku: "TSH-M-BLK",
    price: 15.0,
    stock: 120,
  },
  { id: 102, name: "Polo Shirt (L)", sku: "PLS-L-NVY", price: 20.0, stock: 85 },
  { id: 103, name: "Jeans (32)", sku: "JNS-32-BLU", price: 35.0, stock: 60 },
]);

const cart = ref([]);
const discountAmount = ref(0);
const deliveryFee = ref(0);
const totalPaid = ref(0);

const addToCart = (prod) => {
  const existing = cart.value.find((item) => item.id === prod.id);
  if (existing) existing.qty++;
  else cart.value.push({ ...prod, qty: 1 });
};
const removeFromCart = (i) => cart.value.splice(i, 1);

const subTotal = computed(() =>
  cart.value.reduce((sum, item) => sum + item.price * item.qty, 0),
);
const totalAmount = computed(
  () => subTotal.value - discountAmount.value + deliveryFee.value,
);
const paymentDue = computed(() =>
  Math.max(0, totalAmount.value - totalPaid.value),
);

const saveOrder = async () => {
  try {
    await api.post("/orders", {
      ...form.value,
      items: cart.value,
      total_amount: totalAmount.value,
    });
    alert("Order created successfully!");
  } catch (err) {
    console.error(err);
  }
};
</script>
