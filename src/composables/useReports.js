import { ref } from "vue";
import api from "../services/api";

export function useReports() {
  const loading = ref(false);
  const errorMessage = ref("");

  const dateFrom = ref("");
  const dateTo = ref("");

  const salesSummary = ref({
    total_orders: 0,
    total_revenue: 0,
    total_paid: 0,
    by_date: [],
  });

  const topProducts = ref([]);

  const fetchReports = async () => {
    loading.value = true;
    errorMessage.value = "";
    try {
      const params = {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
      };

      const [salesRes, topRes] = await Promise.all([
        api.get("/reports/sales", { params }),
        api.get("/reports/top-products", { params: { ...params, limit: 10 } }),
      ]);

      salesSummary.value = salesRes.data?.data ?? salesSummary.value;
      topProducts.value = topRes.data?.data ?? [];
    } catch (err) {
      errorMessage.value = "Failed to load reports.";
      console.error(err);
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    errorMessage,
    dateFrom,
    dateTo,
    salesSummary,
    topProducts,
    fetchReports,
  };
}
