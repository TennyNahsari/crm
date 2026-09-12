<template>
  <div>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
      <h1 class="text-2xl lg:text-3xl font-bold text-navy font-sans tracking-tight mb-4 lg:mb-0">{{ $t('customers.title') }}</h1>
      <div class="flex flex-col sm:flex-row gap-2.5">
        <button
          @click="exportToExcel"
          :disabled="exportLoading"
          class="btn btn-gold inline-flex items-center justify-center shadow-sm"
        >
          <svg v-if="!exportLoading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <svg v-else class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ exportLoading ? $t('customers.exporting') : $t('customers.exportExcel') }}
        </button>
        <router-link
          to="/customers/create"
          class="btn btn-primary inline-flex items-center justify-center shadow-sm"
        >
          <svg class="w-4 h-4 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ $t('customers.addCustomer') }}
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <input
          v-model="filters.search"
          @input="handleSearch"
          type="text"
          :placeholder="$t('customers.searchPlaceholder')"
          class="input"
        />

        <select v-model="filters.area_id" @change="applyFilters" class="input">
          <option :value="null">{{ $t('customers.allAreas') }}</option>
          <option v-for="area in areas" :key="area.id" :value="area.id">
            {{ area.name }}
          </option>
        </select>

        <select v-model="filters.lead_status_id" @change="applyFilters" class="input">
          <option :value="null">{{ $t('customers.allStatuses') }}</option>
          <option v-for="status in statuses" :key="status.id" :value="status.id">
            {{ status.name }}
          </option>
        </select>

        <select v-model="filters.source" @change="applyFilters" class="input">
          <option :value="null">{{ $t('customers.allSources') }}</option>
          <option value="inbound">{{ $t('customers.inbound') }}</option>
          <option value="outbound">{{ $t('customers.outbound') }}</option>
        </select>
      </div>

      <!-- Sort Controls -->
      <div class="mt-3 flex items-center gap-3">
        <select v-model="filters.sort_by" @change="handleSortChange" class="input w-auto">
          <option value="next_action_date">{{ $t('customers.sortByNextAction') }}</option>
          <option value="last_interaction_date">{{ $t('customers.sortByLastInteraction') }}</option>
        </select>

        <button
          @click="toggleSortOrder"
          class="btn btn-secondary inline-flex items-center text-sm px-3 py-2"
        >
          <svg v-if="filters.sort_order === 'asc'" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
          </svg>
          <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
          </svg>
          {{ filters.sort_order === 'asc' ? $t('customers.sortAscending') : $t('customers.sortDescending') }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      <p class="mt-2 text-gray-600">{{ $t('customers.loading') }}</p>
    </div>

    <!-- Customer List -->
    <div v-else class="space-y-4">
      <!-- Mobile View -->
      <div class="lg:hidden space-y-4">
        <div
          v-for="customer in customers"
          :key="customer.id"
          @click="goToDetail(customer.id)"
          class="card cursor-pointer hover:shadow-lg transition-shadow"
        >
          <div class="flex justify-between items-start mb-2">
            <div>
              <h3 class="font-semibold text-gray-900">{{ customer.company }}</h3>
              <p v-if="customer.phone" class="text-xs text-gray-600">📞 {{ customer.phone }}</p>
              <p v-if="customer.is_individual" class="text-xs text-gray-500">{{ $t('customers.individualCustomer') }}</p>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <div @click.stop class="inline-block">
                <select
                  :value="customer.lead_status_id"
                  @change="quickUpdateLeadStatus(customer, $event.target.value)"
                  class="text-xs font-bold py-1 px-2.5 rounded-full border cursor-pointer transition-all outline-none"
                  :style="getStatusStyle(customer.lead_status)"
                >
                  <option
                    v-for="st in statuses"
                    :key="st.id"
                    :value="st.id"
                    class="bg-white text-slate-800 font-normal"
                  >
                    ● {{ st.name }}
                  </option>
                </select>
              </div>
              <span
                class="badge text-xs px-2 py-1"
                :class="{
                  'bg-green-100 text-green-800': customer.source === 'inbound',
                  'bg-blue-100 text-blue-800': customer.source === 'outbound',
                }"
              >
                {{ customer.source }}
              </span>
            </div>
          </div>

          <div class="space-y-1 text-sm text-gray-600">
            <p v-if="customer.email">
              <span class="font-medium">{{ $t('customerDetail.email') }}:</span> {{ customer.email }}
            </p>
            <p v-if="customer.area">
              <span class="font-medium">{{ $t('customers.area') }}:</span> {{ customer.area.name }}
            </p>
            <div v-if="customer.contacts && customer.contacts.length > 0">
              <p class="font-medium">{{ $t('customers.pic') }}:</p>
              <p class="ml-2">{{ customer.contacts.find(c => c.is_primary)?.name || customer.contacts[0].name }}</p>
              <p v-if="(customer.contacts.find(c => c.is_primary) || customer.contacts[0]).email" class="ml-2 text-xs">
                ✉️ {{ (customer.contacts.find(c => c.is_primary) || customer.contacts[0]).email }}
              </p>
              <p v-if="(customer.contacts.find(c => c.is_primary) || customer.contacts[0]).whatsapp" class="ml-2 text-xs">
                💬 {{ (customer.contacts.find(c => c.is_primary) || customer.contacts[0]).whatsapp }}
              </p>
            </div>
            <p v-if="customer.next_action_date" class="text-orange-600 font-medium">
              {{ $t('customers.next') }}: {{ formatDate(customer.next_action_date) }}
            </p>
            <div v-if="customer.interactions && customer.interactions.length > 0" class="pt-2 border-t mt-2">
              <p class="font-medium text-gray-700">{{ $t('customers.lastInteraction') }}:</p>
              <p class="text-xs text-gray-600">{{ formatDateTime(customer.interactions[0].interaction_at) }}</p>
              <p class="text-xs text-gray-500 italic truncate">{{ customer.interactions[0].summary || customer.interactions[0].content || '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Desktop View -->
      <div class="hidden lg:block table-container">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-1/4">
                {{ $t('customers.company') }}
              </th>
              <th class="w-24">
                {{ $t('customers.area') }}
              </th>
              <th class="w-32">
                {{ $t('customers.status') }}
              </th>
              <th class="w-20">
                {{ $t('customers.source') }}
              </th>
              <th class="w-1/5">
                {{ $t('customers.nextAction') }}
              </th>
              <th class="w-1/4">
                {{ $t('customers.lastInteraction') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="customer in customers"
              :key="customer.id"
              @click="goToDetail(customer.id)"
              class="cursor-pointer"
            >
              <td>
                <div class="text-sm font-semibold text-slate-900 truncate max-w-xs">{{ customer.company }}</div>
                <div class="text-xs text-slate-500 truncate max-w-xs">{{ customer.email }}</div>
                <div v-if="customer.phone" class="text-xs text-slate-500">📞 {{ customer.phone }}</div>
              </td>
              <td class="text-sm text-slate-800">
                <div class="truncate max-w-24">{{ customer.area?.name || '-' }}</div>
              </td>
              <td @click.stop class="py-3 px-2">
                <select
                  :value="customer.lead_status_id"
                  @change="quickUpdateLeadStatus(customer, $event.target.value)"
                  class="text-xs font-bold py-1 px-2.5 rounded-full border cursor-pointer transition-all outline-none"
                  :style="getStatusStyle(customer.lead_status)"
                >
                  <option
                    v-for="st in statuses"
                    :key="st.id"
                    :value="st.id"
                    class="bg-white text-slate-800 font-normal"
                  >
                    ● {{ st.name }}
                  </option>
                </select>
              </td>
              <td>
                <span
                  class="badge text-xs px-2.5 py-0.5 rounded-full whitespace-nowrap font-medium"
                  :class="{
                    'badge-emerald': customer.source === 'inbound',
                    'badge-sky': customer.source === 'outbound',
                  }"
                >
                  {{ customer.source }}
                </span>
              </td>
              <td class="text-sm">
                <div v-if="customer.next_action_date">
                  <div class="text-slate-900 text-xs font-bold">{{ formatDate(customer.next_action_date) }}</div>
                  <div class="text-slate-500 text-xs truncate max-w-xs">{{ customer.next_action_plan }}</div>
                </div>
                <span v-else class="text-slate-400">-</span>
              </td>
              <td class="text-sm">
                <div v-if="customer.interactions && customer.interactions.length > 0">
                  <div class="text-slate-900 text-xs font-medium">
                    {{ formatDateTime(customer.interactions[0].interaction_at) }}
                  </div>
                  <div class="text-slate-500 text-xs truncate max-w-xs">
                    {{ customer.interactions[0].summary || customer.interactions[0].content || '-' }}
                  </div>
                </div>
                <span v-else class="text-slate-400">{{ $t('customers.noHistory') }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center items-center space-x-2 mt-6">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-secondary disabled:opacity-50"
        >
          {{ $t('customers.previous') }}
        </button>
        <span class="text-sm text-gray-600">
          {{ $t('customers.pageOf', { current: pagination.current_page, total: pagination.last_page }) }}
        </span>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-secondary disabled:opacity-50"
        >
          {{ $t('customers.nextPage') }}
        </button>
      </div>

      <!-- No data -->
      <div v-if="!loading && customers.length === 0" class="card text-center py-12">
        <p class="text-gray-500">{{ $t('customers.noCustomers') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCustomerStore } from '@/stores/customer'
import { useAreaStore } from '@/stores/area'
import { useLeadStatusStore } from '@/stores/leadStatus'
import api from '@/api/axios'

const router = useRouter()
const route = useRoute()
const customerStore = useCustomerStore()
const areaStore = useAreaStore()
const leadStatusStore = useLeadStatusStore()

const customers = computed(() => customerStore.customers)
const pagination = computed(() => customerStore.pagination)
const loading = computed(() => customerStore.loading)

const areas = computed(() => areaStore.areas)
const statuses = computed(() => leadStatusStore.statuses)

const exportLoading = ref(false)

const filters = ref({
  search: '',
  area_id: null,
  lead_status_id: null,
  source: null,
  sort_by: 'next_action_date',
  sort_order: 'asc',
})

let searchTimeout = null

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = async () => {
  customerStore.setFilters(filters.value)
  await customerStore.fetchCustomers()
}

const handleSortChange = async () => {
  await applyFilters()
}

const toggleSortOrder = async () => {
  filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc'
  await applyFilters()
}

const changePage = async (page) => {
  await customerStore.fetchCustomers(page)
}

const goToDetail = (id) => {
  router.push(`/customers/${id}`)
}

const quickUpdateLeadStatus = async (customer, newStatusId) => {
  const targetId = parseInt(newStatusId)
  if (customer.lead_status_id === targetId) return
  
  try {
    await api.put(`/customers/${customer.id}`, {
      lead_status_id: targetId
    })
    const foundStatus = statuses.value.find(s => s.id === targetId)
    customer.lead_status_id = targetId
    if (foundStatus) {
      customer.lead_status = foundStatus
    }
  } catch (err) {
    alert('Gagal memperbarui status lead pelanggan')
  }
}

const getStatusStyle = (status) => {
  if (!status || !status.color) return { backgroundColor: '#f1f5f9', color: '#475569', borderColor: '#cbd5e1' }
  return {
    backgroundColor: status.color + '18',
    color: status.color,
    borderColor: status.color + '60'
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const exportToExcel = async () => {
  try {
    exportLoading.value = true
    
    // Build query params from current filters
    const params = new URLSearchParams()
    
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.area_id) params.append('area_id', filters.value.area_id)
    if (filters.value.lead_status_id) params.append('lead_status_id', filters.value.lead_status_id)
    if (filters.value.source) params.append('source', filters.value.source)
    if (filters.value.next_action_status) params.append('next_action_status', filters.value.next_action_status)
    
    // Request file with blob response type
    const response = await api.get(`/customers/export?${params.toString()}`, {
      responseType: 'blob'
    })
    
    // Create blob link and trigger download
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    
    // Extract filename from Content-Disposition header or use default
    const contentDisposition = response.headers['content-disposition']
    let filename = 'customers_export.xlsx'
    
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?(.+)"?/)
      if (filenameMatch && filenameMatch.length > 1) {
        filename = filenameMatch[1]
      }
    }
    
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    
    // Cleanup
    link.remove()
    window.URL.revokeObjectURL(url)
    
    console.log('Export completed successfully')
  } catch (error) {
    console.error('Error exporting customers:', error)
    alert('Failed to export customers. Please try again.')
  } finally {
    exportLoading.value = false
  }
}

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(async () => {
  try {
    // Load areas and statuses first
    await Promise.all([
      areaStore.fetchAreas(),
      leadStatusStore.fetchStatuses(),
    ])

    // Apply filters from URL query params
    if (route.query.lead_status_id) {
      filters.value.lead_status_id = parseInt(route.query.lead_status_id)
    }
    if (route.query.area_id) {
      filters.value.area_id = parseInt(route.query.area_id)
    }
    if (route.query.source) {
      filters.value.source = route.query.source
    }
    if (route.query.next_action_status) {
      filters.value.next_action_status = route.query.next_action_status
    }

    // Apply filters and fetch customers
    customerStore.setFilters(filters.value)
    await customerStore.fetchCustomers()
  } catch (error) {
    console.error('Error loading customers:', error)
  }
})
</script>
