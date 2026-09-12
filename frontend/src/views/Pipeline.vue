<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl lg:text-3xl font-bold text-navy font-sans tracking-tight">
          {{ $t('pipeline.title') || 'Deal Pipeline (Kanban Board)' }}
        </h1>
        <p class="text-xs lg:text-sm text-slate-500 mt-1">
          {{ $t('pipeline.subtitle') || 'Kelola dan geser posisi prospek pelanggan berdasarkan tahapan penjualan' }}
        </p>
      </div>

      <div class="mt-4 lg:mt-0 flex flex-wrap gap-2.5 items-center">
        <router-link to="/customers" class="btn btn-secondary inline-flex items-center text-sm shadow-sm">
          <svg class="w-4 h-4 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
          Tampilan Tabel Pelanggan
        </router-link>
        <router-link to="/customers/create" class="btn btn-primary inline-flex items-center text-sm shadow-md">
          <svg class="w-4 h-4 mr-1.5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Lead Baru
        </router-link>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="card mb-6 p-4 flex-shrink-0">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Search -->
        <div class="relative">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Cari prospek atau perusahaan..."
            class="input pl-9 text-xs"
          />
          <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- Area Filter -->
        <select v-model="filters.area_id" class="input text-xs">
          <option :value="null">Semua Area</option>
          <option v-for="area in areas" :key="area.id" :value="area.id">
            {{ area.name }}
          </option>
        </select>

        <!-- Sales Filter -->
        <select v-model="filters.sales_id" class="input text-xs">
          <option :value="null">Semua Sales</option>
          <option v-for="s in salesUsers" :key="s.id" :value="s.id">
            {{ s.name }}
          </option>
        </select>

        <!-- Reset Button -->
        <button @click="resetFilters" class="btn btn-secondary text-xs py-2 inline-flex items-center justify-center">
          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reset Filter
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20 bg-white rounded-2xl border border-stone/80 shadow-sm flex-1">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
      <p class="mt-3 text-sm text-slate-500 font-medium">Memuat papan Kanban Pipeline...</p>
    </div>

    <!-- Kanban Columns Container -->
    <div v-else class="flex-1 overflow-x-auto pb-4 custom-scrollbar">
      <div class="flex gap-4 min-w-max items-start">
        <!-- Loop Lead Status Columns -->
        <div
          v-for="status in leadStatuses"
          :key="status.id"
          @dragover.prevent="onDragOver(status.id)"
          @dragleave="onDragLeave(status.id)"
          @drop="onDrop($event, status.id)"
          class="w-80 bg-stone-light/60 rounded-2xl border border-stone/80 p-3 flex flex-col max-h-[75vh] transition-all"
          :class="{
            'ring-2 ring-gold/70 bg-amber-50/40 shadow-md': draggedOverStatusId === status.id
          }"
        >
          <!-- Column Header -->
          <div class="flex items-center justify-between p-2 mb-2 border-b border-stone/60">
            <div class="flex items-center gap-2">
              <span class="w-3 h-3 rounded-full shadow-xs" :style="{ backgroundColor: status.color || '#64748b' }"></span>
              <h3 class="font-bold text-navy text-sm font-sans tracking-tight">
                {{ status.name }}
              </h3>
              <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-white text-slate-600 border border-stone/80">
                {{ getCustomersForStatus(status.id).length }}
              </span>
            </div>
          </div>

          <!-- Total Value per Column -->
          <div class="px-2 mb-3 flex items-center justify-between text-[11px] text-slate-500 font-semibold bg-white/60 p-2 rounded-xl border border-stone/40">
            <span>Estimasi / Total:</span>
            <span class="text-navy font-bold font-mono text-xs">
              {{ formatCurrency(getColumnTotal(status.id)) }}
            </span>
          </div>

          <!-- Cards Scroll Container -->
          <div class="flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar min-h-[150px]">
            <!-- Empty column indicator -->
            <div
              v-if="getCustomersForStatus(status.id).length === 0"
              class="h-32 rounded-xl border-2 border-dashed border-stone/60 flex flex-col items-center justify-center text-slate-400 text-xs p-4 text-center"
            >
              <svg class="w-6 h-6 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <span>Tarik kartu ke sini</span>
            </div>

            <!-- Customer Cards -->
            <div
              v-for="c in getCustomersForStatus(status.id)"
              :key="c.id"
              draggable="true"
              @dragstart="onDragStart($event, c)"
              @dragend="onDragEnd"
              class="bg-white p-4 rounded-xl border border-stone/80 shadow-xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing group relative"
            >
              <!-- Card Top Header -->
              <div class="flex justify-between items-start mb-2 gap-2">
                <router-link
                  :to="`/customers/${c.id}`"
                  class="font-bold text-navy text-sm hover:text-gold transition-colors block leading-tight truncate"
                >
                  {{ c.company || (c.contacts && c.contacts.length ? c.contacts[0].name : 'Pelanggan #' + c.id) }}
                </router-link>

                <!-- Stage Move Quick Dropdown -->
                <div class="relative flex-shrink-0">
                  <select
                    :value="c.lead_status_id"
                    @change="moveCustomerStage(c.id, parseInt($event.target.value))"
                    class="opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity bg-stone-light text-[10px] py-0.5 px-1.5 rounded border border-stone text-slate-700 cursor-pointer"
                    title="Pindah Tahap"
                  >
                    <option v-for="st in leadStatuses" :key="st.id" :value="st.id">
                      ➜ {{ st.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Meta info (Area & PIC) -->
              <div class="flex flex-wrap gap-1.5 mb-2.5">
                <span v-if="c.area" class="px-2 py-0.5 rounded-md bg-stone-light text-slate-600 text-[10px] font-semibold border border-stone/60">
                  📍 {{ c.area.name }}
                </span>
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider"
                  :class="c.source === 'inbound' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700'"
                >
                  {{ c.source || 'outbound' }}
                </span>
              </div>

              <!-- Primary Contact & WhatsApp -->
              <div v-if="getPrimaryContact(c)" class="text-xs text-slate-600 mb-2.5 flex items-center justify-between bg-stone-light/40 p-2 rounded-lg">
                <div class="truncate mr-2">
                  <span class="font-medium text-slate-800 block truncate">{{ getPrimaryContact(c).name }}</span>
                  <span class="text-[10px] text-slate-400 block" v-if="getPrimaryContact(c).position">{{ getPrimaryContact(c).position }}</span>
                </div>
                <a
                  v-if="getPrimaryContact(c).whatsapp || c.phone"
                  :href="`https://wa.me/${cleanPhone(getPrimaryContact(c).whatsapp || c.phone)}`"
                  target="_blank"
                  @click.stop
                  class="text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 p-1.5 rounded-lg border border-emerald-200 transition-colors flex-shrink-0"
                  title="Chat WhatsApp"
                >
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.199 4.38 4.442-1.163z"/>
                  </svg>
                </a>
              </div>

              <!-- Next Action info -->
              <div v-if="c.next_action_date" class="pt-2 border-t border-stone/60 text-[11px] space-y-1">
                <div class="flex items-center justify-between text-slate-500 font-medium">
                  <span class="flex items-center">
                    <span class="w-2 h-2 rounded-full mr-1.5" :class="getPriorityDot(c.next_action_priority)"></span>
                    {{ formatDate(c.next_action_date) }}
                  </span>
                  <span
                    class="uppercase text-[9px] font-bold px-1.5 rounded"
                    :class="c.next_action_status === 'done' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                  >
                    {{ c.next_action_status === 'done' ? 'Done' : 'Pending' }}
                  </span>
                </div>
                <p v-if="c.next_action_plan" class="text-slate-700 font-semibold truncate">
                  {{ c.next_action_plan }}
                </p>
              </div>

              <!-- Footer Sales Person -->
              <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-stone/40">
                <span class="truncate">Sales: {{ c.assigned_sales ? c.assigned_sales.name : 'Unassigned' }}</span>
                <span class="font-bold text-slate-700 font-mono" v-if="getCustomerTotalInvoiced(c)">
                  {{ formatCurrency(getCustomerTotalInvoiced(c)) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/api/axios'

const loading = ref(true)
const leadStatuses = ref([])
const customers = ref([])
const areas = ref([])
const salesUsers = ref([])
const draggedCustomer = ref(null)
const draggedOverStatusId = ref(null)

const filters = reactive({
  search: '',
  area_id: null,
  sales_id: null,
})

const fetchPipelineData = async () => {
  loading.value = true
  try {
    const [resStatuses, resCustomers, resAreas, resSales] = await Promise.all([
      api.get('/lead-statuses'),
      api.get('/customers?per_page=500'),
      api.get('/areas'),
      api.get('/users?role=sales')
    ])

    leadStatuses.value = (resStatuses.data || []).sort((a, b) => a.order - b.order)
    customers.value = resCustomers.data.data || resCustomers.data || []
    areas.value = resAreas.data || []
    salesUsers.value = resSales.data || []
  } catch (err) {
    console.error('Failed to load pipeline data:', err)
  } finally {
    loading.value = false
  }
}

const filteredCustomers = computed(() => {
  return customers.value.filter((c) => {
    // Search
    if (filters.search) {
      const q = filters.search.toLowerCase()
      const compMatch = c.company?.toLowerCase().includes(q)
      const nameMatch = c.name?.toLowerCase().includes(q)
      const contactMatch = c.contacts && c.contacts.some((ct) => ct.name?.toLowerCase().includes(q))
      if (!compMatch && !nameMatch && !contactMatch) return false
    }
    // Area
    if (filters.area_id && c.area_id !== filters.area_id) return false
    // Sales
    if (filters.sales_id && c.assigned_sales_id !== filters.sales_id) return false

    return true
  })
})

const getCustomersForStatus = (statusId) => {
  return filteredCustomers.value.filter((c) => c.lead_status_id === statusId)
}

const getColumnTotal = (statusId) => {
  const list = getCustomersForStatus(statusId)
  return list.reduce((sum, c) => sum + getCustomerTotalInvoiced(c), 0)
}

const getCustomerTotalInvoiced = (c) => {
  if (!c.invoices || !c.invoices.length) return 0
  return c.invoices.reduce((sum, inv) => sum + (parseFloat(inv.total) || 0), 0)
}

const getPrimaryContact = (c) => {
  if (!c.contacts || !c.contacts.length) return null
  return c.contacts.find((ct) => ct.is_primary) || c.contacts[0]
}

const moveCustomerStage = async (customerId, newStatusId) => {
  try {
    await api.put(`/customers/${customerId}`, {
      lead_status_id: newStatusId,
    })
    const cust = customers.value.find((c) => c.id === customerId)
    if (cust) {
      cust.lead_status_id = newStatusId
    }
  } catch (err) {
    alert('Gagal memindahkan status lead customer')
  }
}

// Drag and Drop Handlers
const onDragStart = (evt, customerObj) => {
  draggedCustomer.value = customerObj
  evt.dataTransfer.effectAllowed = 'move'
  evt.dataTransfer.setData('text/plain', customerObj.id)
}

const onDragEnd = () => {
  draggedCustomer.value = null
  draggedOverStatusId.value = null
}

const onDragOver = (statusId) => {
  draggedOverStatusId.value = statusId
}

const onDragLeave = (statusId) => {
  if (draggedOverStatusId.value === statusId) {
    draggedOverStatusId.value = null
  }
}

const onDrop = async (evt, newStatusId) => {
  draggedOverStatusId.value = null
  if (!draggedCustomer.value) return
  if (draggedCustomer.value.lead_status_id === newStatusId) return

  const targetId = draggedCustomer.value.id
  await moveCustomerStage(targetId, newStatusId)
}

const resetFilters = () => {
  filters.search = ''
  filters.area_id = null
  filters.sales_id = null
}

const cleanPhone = (phone) => {
  if (!phone) return ''
  return phone.replace(/[^0-9]/g, '')
}

const formatCurrency = (val) => {
  if (!val) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short'
  })
}

const getPriorityDot = (priority) => {
  switch (priority) {
    case 'high':
      return 'bg-rose-500'
    case 'medium':
      return 'bg-amber-500'
    case 'low':
      return 'bg-emerald-500'
    default:
      return 'bg-slate-400'
  }
}

onMounted(() => {
  fetchPipelineData()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 8px;
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.18);
  border-radius: 4px;
}
</style>
