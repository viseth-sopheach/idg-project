import { ref, computed } from 'vue'
import api from '../services/api'

export function useOrders() {
  const search = ref('')
  const statusFilter = ref('')
  const orders = ref([])
  const loading = ref(false)
  const errorMessage = ref('')
  const meta = ref({ current_page: 1, per_page: 15, total: 0, last_page: 1 })

  let searchDebounce = null

  const fetchOrders = async (page = 1) => {
    loading.value = true
    errorMessage.value = ''
    try {
      const res = await api.get('/orders', {
        params: {
          search: search.value || undefined,
          status: statusFilter.value || undefined,
          page,
          per_page: meta.value.per_page,
        },
      })
      orders.value = res.data?.data ?? []
      meta.value = {
        current_page: res.data?.meta?.current_page ?? 1,
        per_page: res.data?.meta?.per_page ?? 15,
        total: res.data?.meta?.total ?? orders.value.length,
        last_page: res.data?.meta?.last_page ?? 1,
      }
    } catch (err) {
      errorMessage.value = 'Failed to load orders.'
      console.error(err)
    } finally {
      loading.value = false
    }
  }

  const goToPage = (page) => {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) return
    fetchOrders(page)
  }

  const onFilterChange = () => {
    clearTimeout(searchDebounce)
    searchDebounce = setTimeout(() => fetchOrders(1), 350)
  }

  const updateOrderStatus = async (order, status) => {
    const res = await api.patch(`/orders/${order.id}/status`, { status })
    const updated = res.data?.data
    const target = orders.value.find((o) => o.id === order.id)
    if (target && updated) Object.assign(target, updated)
    return updated
  }

  const rangeStart = computed(() =>
    meta.value.total === 0 ? 0 : (meta.value.current_page - 1) * meta.value.per_page + 1
  )
  const rangeEnd = computed(() =>
    Math.min(meta.value.current_page * meta.value.per_page, meta.value.total)
  )

  return {
    search, statusFilter, orders, loading, errorMessage, meta,
    rangeStart, rangeEnd,
    fetchOrders, goToPage, onFilterChange, updateOrderStatus,
  }
}