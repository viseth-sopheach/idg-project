import { ref, computed } from 'vue'
import api from '../services/api'

export function useCustomers() {
  const search = ref('')
  const customers = ref([])
  const loading = ref(false)
  const errorMessage = ref('')

  const meta = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
  })

  let searchDebounce = null

  const fetchCustomers = async (page = 1) => {
    loading.value = true
    errorMessage.value = ''
    try {
      const res = await api.get('/customers', {
        params: {
          search: search.value || undefined,
          page,
          per_page: meta.value.per_page,
        },
      })
      customers.value = res.data?.data ?? []
      meta.value = {
        current_page: res.data?.meta?.current_page ?? 1,
        per_page: res.data?.meta?.per_page ?? 15,
        total: res.data?.meta?.total ?? customers.value.length,
        last_page: res.data?.meta?.last_page ?? 1,
      }
    } catch (err) {
      errorMessage.value = 'Failed to load customers.'
      console.error(err)
    } finally {
      loading.value = false
    }
  }

  const goToPage = (page) => {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) return
    fetchCustomers(page)
  }

  const onSearchInput = () => {
    clearTimeout(searchDebounce)
    searchDebounce = setTimeout(() => {
      fetchCustomers(1)
    }, 350)
  }

  const createCustomer = async (payload) => {
    await api.post('/customers', payload)
    await fetchCustomers(1)
  }

  const updateCustomer = async (id, payload) => {
    await api.put(`/customers/${id}`, payload)
    await fetchCustomers(meta.value.current_page)
  }

  const deleteCustomer = async (customer) => {
    await api.delete(`/customers/${customer.id}`)
    const nextPage = customers.value.length === 1 && meta.value.current_page > 1
      ? meta.value.current_page - 1
      : meta.value.current_page
    await fetchCustomers(nextPage)
  }

  const rangeStart = computed(() => {
    if (meta.value.total === 0) return 0
    return (meta.value.current_page - 1) * meta.value.per_page + 1
  })

  const rangeEnd = computed(() => {
    return Math.min(meta.value.current_page * meta.value.per_page, meta.value.total)
  })

  return {
    search,
    customers,
    loading,
    errorMessage,
    meta,
    rangeStart,
    rangeEnd,
    fetchCustomers,
    goToPage,
    onSearchInput,
    createCustomer,
    updateCustomer,
    deleteCustomer,
  }
}