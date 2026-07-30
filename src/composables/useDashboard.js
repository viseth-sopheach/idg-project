import { ref } from 'vue'
import api from '../services/api'

export function useDashboard() {
  const loading = ref(false)
  const errorMessage = ref('')
  const summary = ref({
    total_products: 0,
    total_customers: 0,
    total_orders: 0,
    total_revenue: 0,
    todays_sales: 0,
    low_stock_products: [],
    low_stock_count: 0,
    recent_orders: [],
  })

  const fetchSummary = async () => {
    loading.value = true
    errorMessage.value = ''
    try {
      const res = await api.get('/dashboard')
      summary.value = { ...summary.value, ...(res.data?.data ?? {}) }
    } catch (err) {
      errorMessage.value = 'Failed to load dashboard data.'
      console.error(err)
    } finally {
      loading.value = false
    }
  }

  return { loading, errorMessage, summary, fetchSummary }
}