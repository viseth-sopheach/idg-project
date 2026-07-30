<template>
  <div class="space-y-6">
    <div class="flex justify-between items-start">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Product</h1>
        <p class="text-sm text-gray-400 mt-0.5">
          Dashboard &gt; Products &gt;
          <span class="text-gray-600">Create Product</span>
        </p>
      </div>
      <div class="flex gap-3">
        <BaseButton variant="secondary">Cancel</BaseButton>
        <BaseButton variant="primary" @click="saveProduct"
          >Save Product</BaseButton
        >
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
      <h2 class="text-base font-bold text-gray-900">Basic Information</h2>
      <div class="grid grid-cols-3 gap-6">
        <BaseInput
          v-model="form.name"
          label="Product Name"
          placeholder="Enter product name"
          required
        />
        <BaseInput
          v-model="form.sku"
          label="SKU"
          placeholder="Enter SKU"
          required
        />
        <BaseSelect
          v-model="form.status"
          label="Status"
          required
          :options="[
            { value: 'Active', label: 'Active' },
            { value: 'Inactive', label: 'Inactive' },
          ]"
        />
      </div>

      <div class="grid grid-cols-3 gap-6 pt-2">
        <BaseInput
          v-model.number="form.quantity"
          label="Quantity (Qty)"
          type="number"
          placeholder="Enter quantity"
          required
        />
        <div class="col-span-2">
          <label class="block text-xs font-semibold text-gray-700 mb-1"
            >Description</label
          >
          <textarea
            v-model="form.description"
            maxlength="500"
            placeholder="Enter product description"
            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none h-20 resize-none"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
      <h2 class="text-base font-bold text-gray-900">Pricing Information</h2>
      <div class="grid grid-cols-3 gap-6">
        <BaseInput
          v-model.number="form.cost_price"
          label="Cost Price"
          type="number"
          prefix="$"
          required
        />
        <BaseInput
          v-model.number="form.price"
          label="Price"
          type="number"
          prefix="$"
          required
        />
        <BaseSelect
          v-model="form.discount_type"
          label="Discount Type"
          :options="[
            { value: 'none', label: 'No Discount' },
            { value: 'fixed', label: 'Dollar' },
            { value: 'percentage', label: 'Percentage (%)' },
          ]"
        />
      </div>

      <div class="grid grid-cols-3 gap-6 pt-2">
        <BaseInput
          v-model.number="form.discount_value"
          label="Discount Value"
          type="number"
        />
        <BaseInput
          :model-value="calculatedSellingPrice.toFixed(2)"
          label="Selling Price"
          prefix="$"
          disabled
        />
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
  name: "",
  sku: "",
  status: "Active",
  quantity: null,
  description: "",
  cost_price: 0,
  price: 0,
  discount_type: "none",
  discount_value: 0,
});

const calculatedSellingPrice = computed(() => {
  const basePrice = form.value.price || 0;
  const disc = form.value.discount_value || 0;
  if (form.value.discount_type === "percentage")
    return basePrice - basePrice * (disc / 100);
  if (form.value.discount_type === "fixed")
    return Math.max(0, basePrice - disc);
  return basePrice;
});

const saveProduct = async () => {
  try {
    await api.post("/products", {
      name: form.value.name,
      sku: form.value.sku,
      qty: form.value.quantity,
      description: form.value.description || null,
      cost_price: form.value.cost_price,
      price: form.value.price,
      discount_type: form.value.discount_type,
      discount_value: form.value.discount_value,
      selling_price: calculatedSellingPrice.value,
      status: form.value.status.toLowerCase(),
    });
    alert("Product created successfully!");
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      alert(Object.values(err.response.data.errors).flat().join(" "));
    } else {
      alert(err.response?.data?.message || "Failed to create product.");
    }
    console.error(err);
  }
};
</script>
