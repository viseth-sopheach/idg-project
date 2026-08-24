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
      <div class="flex flex-col items-end gap-1">
        <div class="flex gap-3">
          <BaseButton variant="secondary" @click="router.push('/orders')"
            >Cancel</BaseButton
          >
          <BaseButton variant="primary" @click="saveOrder" :disabled="saving">
            {{ saving ? "Saving..." : "Save Order" }}
          </BaseButton>
        </div>
        <span v-if="saveError" class="text-xs text-red-500">{{
          saveError
        }}</span>
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
              :class="[
                'w-full border rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500',
                customerInvalid ? 'border-red-400' : 'border-gray-300',
              ]"
            >
              <option value="" disabled selected>Select customer</option>
              <option v-for="c in customerOptions" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <button
              type="button"
              class="px-3 py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 font-bold"
            >
              +
            </button>
          </div>
          <p v-if="customerInvalid" class="text-xs text-red-500 mt-1">
            Please select a customer.
          </p>
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
        class="col-span-12 lg:col-span-6 bg-white rounded-xl border border-gray-200 p-4 sm:p-5 space-y-4"
      >
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-blue-600">Add Products</h3>
          <span class="text-[11px] text-gray-400">
            {{ productsMeta.total }} total
          </span>
        </div>

        <!-- Search + filter -->
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
          <div class="relative w-full">
            <svg
              class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <input
              v-model="productSearch"
              type="text"
              placeholder="Search by product name or SKU ..."
              class="w-full h-9 border border-gray-300 rounded-lg pl-8 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="relative w-full sm:w-auto sm:shrink-0">
            <select
              class="w-full sm:w-auto h-9 border border-gray-300 rounded-lg pl-3 pr-8 text-xs bg-white appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option>All Categories</option>
            </select>
            <svg
              class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
              />
            </svg>
          </div>
        </div>

        <!-- Table: scrolls horizontally, but Action column stays pinned -->
        <div class="-mx-4 sm:mx-0 overflow-x-auto">
          <table class="w-full min-w-[460px] text-xs text-left">
            <thead class="border-b bg-gray-50 text-gray-500 font-semibold">
              <tr>
                <th class="p-2 pl-4 sm:pl-2 whitespace-nowrap">Product</th>
                <th class="p-2 whitespace-nowrap">SKU</th>
                <th class="p-2 whitespace-nowrap text-right">Price</th>
                <th class="p-2 whitespace-nowrap text-center">Stock</th>
                <th
                  class="p-2 pr-4 sm:pr-2 text-center whitespace-nowrap sticky right-0 bg-gray-50 shadow-[-6px_0_6px_-6px_rgba(0,0,0,0.1)]"
                >
                  Action
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="product in filteredProducts"
                :key="product.id"
                class="hover:bg-gray-50/60 group"
              >
                <td class="p-2 pl-4 sm:pl-2 font-medium">
                  <div class="flex items-center gap-2 min-w-0">
                    <img
                      :src="product.image"
                      :alt="product.name"
                      class="w-8 h-8 rounded-md object-cover border border-gray-200 shrink-0"
                    />
                    <span class="truncate max-w-[110px] sm:max-w-[140px]">
                      {{ product.name }}
                    </span>
                  </div>
                </td>
                <td class="p-2 font-mono text-gray-500 whitespace-nowrap">
                  {{ product.sku }}
                </td>
                <td
                  class="p-2 text-right font-semibold text-gray-900 whitespace-nowrap"
                >
                  ${{ product.price.toFixed(2) }}
                </td>
                <td class="p-2 text-center">
                  <span
                    :class="[
                      'inline-flex items-center justify-center min-w-[28px] px-1.5 py-0.5 rounded-md text-[11px] font-semibold',
                      product.stock <= 5
                        ? 'bg-red-50 text-red-600'
                        : 'text-gray-600',
                    ]"
                  >
                    {{ product.stock }}
                  </span>
                </td>
                <!-- Sticky action cell: always visible, follows row bg on hover -->
                <td
                  class="p-2 pr-4 sm:pr-2 text-center sticky right-0 bg-white group-hover:bg-gray-50 shadow-[-6px_0_6px_-6px_rgba(0,0,0,0.1)] transition-colors"
                >
                  <button
                    @click="addToCart(product)"
                    :disabled="product.stock === 0"
                    class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md font-medium hover:bg-blue-100 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-blue-50 whitespace-nowrap"
                  >
                    Add
                  </button>
                </td>
              </tr>

              <tr v-if="filteredProducts.length === 0">
                <td colspan="5" class="py-10 text-center text-gray-400">
                  <div class="flex flex-col items-center gap-2">
                    <svg
                      class="w-8 h-8 text-gray-300"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                      />
                    </svg>
                    <span class="text-xs">No products found.</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer -->
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-[11px] text-gray-500 pt-1"
        >
          <div>
            Showing 1 to {{ filteredProducts.length }} of
            {{ productsMeta.total }} products
          </div>
          <div
            v-if="productsMeta.last_page > 1"
            class="flex items-center gap-1 flex-wrap"
          >
            <button
              v-for="page in productsMeta.last_page"
              :key="page"
              @click="goToProductsPage(page)"
              :class="[
                'w-6 h-6 flex items-center justify-center rounded-md font-medium transition',
                page === productsMeta.current_page
                  ? 'bg-blue-600 text-white'
                  : 'hover:bg-gray-100 text-gray-600',
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>

      <div class="col-span-12 lg:col-span-6 space-y-4">
        <!-- Order Items -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide">
              Order Items
            </h3>
            <span
              class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full"
            >
              {{ cart.length }} item{{ cart.length === 1 ? "" : "s" }}
            </span>
          </div>

          <!-- Empty state -->
          <div
            v-if="cart.length === 0"
            class="flex flex-col items-center justify-center gap-2 text-gray-400 py-12"
          >
            <svg
              class="w-10 h-10"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M3 3h2l.4 2M7 13h10l3.6-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m-10 0a2 2 0 100 4 2 2 0 000-4zm10 0a2 2 0 100 4 2 2 0 000-4z"
              />
            </svg>
            <span class="text-xs font-medium">No items added yet</span>
            <span class="text-xs">Add products from the list on the left</span>
          </div>

          <!-- Table (scrolls horizontally instead of breaking layout) -->
          <div v-else class="-mx-4 sm:mx-0 overflow-x-auto">
            <table class="w-full min-w-[420px] text-xs text-left">
              <thead class="border-b bg-gray-50 text-gray-500 font-semibold">
                <tr>
                  <th class="p-2 pl-4 sm:pl-2 whitespace-nowrap">#</th>
                  <th class="p-2 whitespace-nowrap">Product</th>
                  <th class="p-2 whitespace-nowrap text-right">Price</th>
                  <th class="p-2 whitespace-nowrap text-center">Qty</th>
                  <th class="p-2 whitespace-nowrap text-right">Total</th>
                  <th class="p-2 pr-4 sm:pr-2"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="(item, i) in cart"
                  :key="item.id"
                  class="hover:bg-gray-50/60"
                >
                  <td class="p-2 pl-4 sm:pl-2 text-gray-500">{{ i + 1 }}</td>
                  <td
                    class="p-2 font-medium text-gray-800 max-w-[140px] truncate"
                  >
                    {{ item.name }}
                  </td>
                  <td class="p-2 text-right text-gray-600 whitespace-nowrap">
                    ${{ item.price.toFixed(2) }}
                  </td>
                  <td class="p-2">
                    <input
                      type="number"
                      min="1"
                      v-model.number="item.qty"
                      class="w-14 h-8 border border-gray-300 rounded-md text-center text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </td>
                  <td
                    class="p-2 text-right font-semibold text-gray-900 whitespace-nowrap"
                  >
                    ${{ (item.price * item.qty).toFixed(2) }}
                  </td>
                  <td class="p-2 pr-4 sm:pr-2 text-center">
                    <button
                      @click="removeFromCart(i)"
                      class="w-7 h-7 inline-flex items-center justify-center rounded-md text-red-500 hover:bg-red-50 font-bold transition"
                      aria-label="Remove item"
                    >
                      ×
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Order Summary -->
        <div
          class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 space-y-4"
        >
          <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide">
            Order Summary
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <!-- Totals -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-gray-500">Sub Total</span>
                <span class="font-semibold text-gray-800"
                  >${{ subTotal.toFixed(2) }}</span
                >
              </div>

              <div class="flex items-center justify-between gap-3">
                <span class="text-gray-500 shrink-0">Discount</span>
                <div class="relative">
                  <span
                    class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400"
                    >$</span
                  >
                  <input
                    type="number"
                    min="0"
                    v-model.number="discountAmount"
                    class="w-24 h-8 border border-gray-300 rounded-md pl-5 pr-2 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div class="flex items-center justify-between gap-3">
                <span class="text-gray-500 shrink-0">Delivery</span>
                <div class="relative">
                  <span
                    class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400"
                    >$</span
                  >
                  <input
                    type="number"
                    min="0"
                    v-model.number="deliveryFee"
                    class="w-24 h-8 border border-gray-300 rounded-md pl-5 pr-2 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div
                class="flex items-center justify-between pt-3 border-t border-gray-100"
              >
                <span class="font-semibold text-gray-900">Total Amount</span>
                <span class="text-sm font-bold text-gray-900"
                  >${{ totalAmount.toFixed(2) }}</span
                >
              </div>
            </div>

            <!-- Payment -->
            <div
              class="space-y-3 sm:border-l sm:pl-4 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100"
            >
              <div>
                <label class="block font-semibold text-gray-700 mb-1"
                  >Total Paid</label
                >
                <div class="relative">
                  <span
                    class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400"
                    >$</span
                  >
                  <input
                    type="number"
                    min="0"
                    v-model.number="totalPaid"
                    class="w-full h-9 border border-gray-300 rounded-md pl-6 pr-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div
                class="flex items-center justify-between sm:flex-col sm:items-start pt-1"
              >
                <span class="font-semibold text-gray-700">Payment Due</span>
                <span
                  :class="[
                    'text-base font-bold',
                    paymentDue > 0 ? 'text-red-600' : 'text-green-600',
                  ]"
                >
                  ${{ paymentDue.toFixed(2) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Note -->
        <div
          class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 space-y-2"
        >
          <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide">
            Order Note
          </h3>
          <textarea
            v-model="form.note"
            maxlength="300"
            placeholder="Enter order note (optional) ..."
            class="w-full border border-gray-300 rounded-lg p-3 text-xs focus:ring-2 focus:ring-blue-500 outline-none h-20 resize-none"
          ></textarea>
          <div class="text-right text-[11px] text-gray-400">
            {{ form.note.length }}/300
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../services/api";
import BaseInput from "../../components/common/BaseInput.vue";
import BaseSelect from "../../components/common/BaseSelect.vue";
import BaseButton from "../../components/common/BaseButton.vue";

const router = useRouter();

const form = ref({
  customer_id: "",
  order_date: new Date().toISOString().slice(0, 10),
  status: "Pending",
  note: "",
});

const customerOptions = ref([]);
const productsList = ref([]);
const productsMeta = ref({ total: 0, current_page: 1, last_page: 1 });
const loadingCustomers = ref(false);
const loadingProducts = ref(false);
const saving = ref(false);
const saveError = ref("");
const customerInvalid = ref(false);

const fetchCustomers = async () => {
  loadingCustomers.value = true;
  try {
    const res = await api.get("/customers", { params: { per_page: 100 } });
    customerOptions.value = res.data?.data ?? [];
  } catch (err) {
    console.error(err);
  } finally {
    loadingCustomers.value = false;
  }
};

const fetchProducts = async (page = 1) => {
  loadingProducts.value = true;
  try {
    const res = await api.get("/products", { params: { per_page: 7, page } });
    productsList.value = (res.data?.data ?? []).map((p) => ({
      id: p.id,
      name: p.name,
      sku: p.sku,
      price: p.selling_price ?? p.price,
      stock: p.qty,
      image: `https://placehold.co/64x64/1f2937/ffffff?text=${encodeURIComponent(
        (p.name || "P").charAt(0),
      )}`,
    }));
    productsMeta.value = res.data?.meta ?? {
      total: productsList.value.length,
      current_page: 1,
      last_page: 1,
    };
  } catch (err) {
    console.error(err);
  } finally {
    loadingProducts.value = false;
  }
};

const goToProductsPage = (page) => {
  if (page === productsMeta.value.current_page) return;
  fetchProducts(page);
};

onMounted(() => {
  fetchCustomers();
  fetchProducts();
});

const productSearch = ref("");
const filteredProducts = computed(() => {
  const term = productSearch.value.trim().toLowerCase();
  if (!term) return productsList.value;
  return productsList.value.filter(
    (p) =>
      p.name.toLowerCase().includes(term) || p.sku.toLowerCase().includes(term),
  );
});

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
  saveError.value = "";
  customerInvalid.value = false;

  const token = localStorage.getItem("auth_token");
  if (!token) {
    saveError.value = "You must be logged in to create an order.";
    alert(saveError.value);
    return;
  }

  if (!form.value.customer_id) {
    customerInvalid.value = true;
    saveError.value = "Please select a customer before saving the order.";
    alert("Please select a customer before saving the order.");
    return;
  }

  if (cart.value.length === 0) {
    saveError.value = "Add at least one product to the order.";
    alert(saveError.value);
    return;
  }

  saving.value = true;
  try {
    const payload = {
      customer_id: Number(form.value.customer_id),
      order_date: form.value.order_date,
      discount_amount: Number(discountAmount.value) || 0,
      delivery_fee: Number(deliveryFee.value) || 0,
      total_paid: Number(totalPaid.value) || 0,
      note: form.value.note || null,
      items: cart.value.map((item) => ({
        product_id: Number(item.id),
        qty: Number(item.qty),
      })),
    };

    await api.post("/orders", payload);

    alert("Order created successfully!");

    cart.value = [];
    discountAmount.value = 0;
    deliveryFee.value = 0;
    totalPaid.value = 0;
    form.value.customer_id = "";
    form.value.note = "";
    fetchProducts(productsMeta.value.current_page);
  } catch (err) {
    const status = err.response?.status;
    const data = err.response?.data;

    if (status === 401) {
      saveError.value = "Your session has expired. Please log in again.";
    } else if (status === 422 && data?.errors) {
      saveError.value = Object.values(data.errors).flat().join(" ");
    } else {
      saveError.value = data?.message || "Failed to create the order.";
    }
    alert(saveError.value);
    console.error(err);
  } finally {
    saving.value = false;
  }
};
</script>
