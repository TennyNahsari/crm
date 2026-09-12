<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
      <div>
        <h1 class="text-2xl lg:text-3xl font-bold text-navy font-sans tracking-tight">
          {{ $t('invoices.title') }}
        </h1>
        <p class="text-xs lg:text-sm text-slate-500 mt-1">
          {{ $t('invoices.subtitle') }}
        </p>
      </div>

      <div class="mt-4 lg:mt-0 flex gap-3">
        <router-link
          to="/invoices/create"
          class="btn btn-primary inline-flex items-center justify-center shadow-md hover:shadow-lg transition-all"
        >
          <svg class="w-4 h-4 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ $t('invoices.createInvoice') }}
        </router-link>
      </div>
    </div>

    <!-- Financial Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-5 rounded-2xl border border-stone/80 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ $t('invoices.stats.totalInvoiced') }}</span>
          <span class="text-xl font-bold text-navy font-sans mt-1 block">{{ formatCurrency(stats.totalInvoiced) }}</span>
          <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $t('invoices.stats.totalCount', { count: stats.totalCount }) }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-navy/5 text-navy flex items-center justify-center font-bold">
          <svg class="w-6 h-6 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">{{ $t('invoices.stats.paidAmount') }}</span>
          <span class="text-xl font-bold text-emerald-700 font-sans mt-1 block">{{ formatCurrency(stats.paidAmount) }}</span>
          <span class="text-[11px] text-emerald-500 mt-0.5 block">{{ $t('invoices.stats.paidCount', { count: stats.paidCount }) }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-amber-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider block">{{ $t('invoices.stats.unpaidAmount') }}</span>
          <span class="text-xl font-bold text-amber-700 font-sans mt-1 block">{{ formatCurrency(stats.unpaidAmount) }}</span>
          <span class="text-[11px] text-amber-500 mt-0.5 block">{{ $t('invoices.stats.unpaidCount', { count: stats.unpaidCount }) }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ $t('invoices.stats.draftCancelled') }}</span>
          <span class="text-xl font-bold text-slate-700 font-sans mt-1 block">{{ stats.draftCount + stats.cancelledCount }}</span>
          <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $t('invoices.stats.draftCancelledCount', { draft: stats.draftCount, cancelled: stats.cancelledCount }) }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-bold">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-6 p-4">
      <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="w-full md:w-80">
          <div class="relative">
            <input
              v-model="filters.search"
              type="text"
              :placeholder="$t('invoices.filters.searchPlaceholder')"
              class="input pl-9 text-sm"
              @input="handleSearch"
            />
            <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <div class="flex flex-wrap gap-2.5 w-full md:w-auto items-center">
          <!-- Status Tabs -->
          <div class="inline-flex bg-stone-light p-1 rounded-xl border border-stone/60">
            <button
              v-for="st in statusOptions"
              :key="st.value"
              @click="setStatusFilter(st.value)"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
              :class="filters.status === st.value ? 'bg-navy text-white shadow-sm' : 'text-slate-600 hover:text-navy'"
            >
              {{ st.label }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16 bg-white rounded-2xl border border-stone/80 shadow-sm">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
      <p class="mt-3 text-sm text-slate-500 font-medium">{{ $t('invoices.loading') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="invoices.length === 0" class="text-center py-16 bg-white rounded-2xl border border-stone/80 shadow-sm px-4">
      <div class="w-16 h-16 rounded-full bg-stone-light text-slate-400 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-bold text-navy">{{ $t('invoices.emptyState.title') }}</h3>
      <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1 mb-6">
        {{ $t('invoices.emptyState.description') }}
      </p>
      <router-link to="/invoices/create" class="btn btn-primary inline-flex items-center text-sm px-5 py-2.5 shadow-md">
        <svg class="w-4 h-4 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        {{ $t('invoices.emptyState.createFirst') }}
      </router-link>
    </div>

    <!-- Desktop Table View -->
    <div v-else class="bg-white rounded-2xl border border-stone/80 shadow-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-stone-light border-b border-stone/60 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <th class="py-4 px-6">{{ $t('invoices.table.invoiceNumber') }}</th>
              <th class="py-4 px-6">{{ $t('invoices.table.customer') }}</th>
              <th class="py-4 px-6">{{ $t('invoices.table.invoiceDate') }}</th>
              <th class="py-4 px-6">{{ $t('invoices.table.dueDate') }}</th>
              <th class="py-4 px-6">{{ $t('invoices.table.total') }}</th>
              <th class="py-4 px-6">{{ $t('invoices.table.status') }}</th>
              <th class="py-4 px-6 text-right">{{ $t('invoices.table.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone/60 text-sm font-sans">
            <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-amber-50/30 transition-colors">
              <td class="py-4 px-6 font-semibold text-navy">
                <router-link :to="`/invoices/${inv.id}`" class="hover:text-gold transition-colors">
                  {{ inv.invoice_number }}
                </router-link>
              </td>
              <td class="py-4 px-6">
                <div v-if="inv.customer">
                  <router-link :to="`/customers/${inv.customer.id}`" class="font-medium text-slate-800 hover:text-gold block">
                    {{ inv.customer.company || (inv.customer.contacts && inv.customer.contacts.length ? inv.customer.contacts[0].name : 'Pelanggan #' + inv.customer.id) }}
                  </router-link>
                  <span class="text-xs text-slate-400 block" v-if="inv.customer.email">{{ inv.customer.email }}</span>
                </div>
                <span v-else class="text-slate-400 italic">{{ $t('invoices.table.noCustomer') }}</span>
              </td>
              <td class="py-4 px-6 text-slate-600 text-xs">
                {{ formatDate(inv.invoice_date) }}
              </td>
              <td class="py-4 px-6 text-slate-600 text-xs">
                <span :class="{ 'text-rose-600 font-semibold': isOverdue(inv) }">
                  {{ inv.due_date ? formatDate(inv.due_date) : '-' }}
                </span>
              </td>
              <td class="py-4 px-6 font-bold text-slate-900">
                {{ formatCurrency(inv.total) }}
              </td>
              <td class="py-4 px-6">
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                  :class="getStatusBadgeClass(inv.status)"
                >
                  <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusDotClass(inv.status)"></span>
                  {{ getStatusLabel(inv.status) }}
                </span>
              </td>
              <td class="py-4 px-6 text-right space-x-2">
                <router-link
                  :to="`/invoices/${inv.id}`"
                  class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors"
                >
                  {{ $t('invoices.table.detail') }}
                </router-link>
                <router-link
                  :to="`/invoices/${inv.id}/edit`"
                  class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition-colors"
                >
                  {{ $t('invoices.table.edit') }}
                </router-link>
                <button
                  @click="confirmDelete(inv)"
                  class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-lg transition-colors"
                >
                  {{ $t('invoices.table.delete') }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="p-4 border-t border-stone/60 flex items-center justify-between bg-stone-light/50">
        <span class="text-xs text-slate-500 font-medium">
          {{ $t('invoices.pagination.info', { current: pagination.current_page, last: pagination.last_page, total: pagination.total }) }}
        </span>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="btn btn-secondary text-xs px-3 py-1.5 disabled:opacity-50"
          >
            {{ $t('invoices.pagination.previous') }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="btn btn-secondary text-xs px-3 py-1.5 disabled:opacity-50"
          >
            {{ $t('invoices.pagination.next') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { getInvoices, deleteInvoice } from '@/api/invoices'

const { t, locale } = useI18n()
const loading = ref(true)
const invoices = ref([])
const allInvoices = ref([])

const filters = reactive({
  search: '',
  status: 'all',
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const statusOptions = computed(() => [
  { value: 'all', label: t('invoices.filters.allStatuses') },
  { value: 'draft', label: t('invoices.filters.draftQuotation') },
  { value: 'sent', label: t('invoices.filters.sentQuotation') },
  { value: 'paid', label: t('invoices.filters.paid') },
  { value: 'cancelled', label: t('invoices.filters.cancelled') },
])

const stats = computed(() => {
  let totalInvoiced = 0
  let paidAmount = 0
  let unpaidAmount = 0
  let paidCount = 0
  let unpaidCount = 0
  let draftCount = 0
  let cancelledCount = 0

  allInvoices.value.forEach((inv) => {
    const total = parseFloat(inv.total) || 0
    totalInvoiced += total
    if (inv.status === 'paid') {
      paidAmount += total
      paidCount++
    } else if (inv.status === 'sent') {
      unpaidAmount += total
      unpaidCount++
    } else if (inv.status === 'draft') {
      draftCount++
    } else if (inv.status === 'cancelled') {
      cancelledCount++
    }
  })

  return {
    totalInvoiced,
    totalCount: allInvoices.value.length,
    paidAmount,
    paidCount,
    unpaidAmount,
    unpaidCount,
    draftCount,
    cancelledCount,
  }
})

const fetchInvoices = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      per_page: pagination.per_page,
    }
    if (filters.status !== 'all') {
      params.status = filters.status
    }
    const res = await getInvoices(params)
    invoices.value = res.data.data || res.data
    pagination.current_page = res.data.current_page || 1
    pagination.last_page = res.data.last_page || 1
    pagination.total = res.data.total || invoices.value.length

    // Also fetch all for aggregate stats overview if not fetched yet
    if (allInvoices.value.length === 0) {
      const allRes = await getInvoices({ per_page: 500 })
      allInvoices.value = allRes.data.data || allRes.data
    }
  } catch (err) {
    console.error('Error fetching invoices:', err)
  } finally {
    loading.value = false
  }
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (!filters.search) {
      fetchInvoices()
      return
    }
    const q = filters.search.toLowerCase()
    invoices.value = allInvoices.value.filter((inv) => {
      const numMatch = inv.invoice_number?.toLowerCase().includes(q)
      const custMatch =
        inv.customer?.name?.toLowerCase().includes(q) ||
        inv.customer?.company?.toLowerCase().includes(q)
      return numMatch || custMatch
    })
  }, 300)
}

const setStatusFilter = (val) => {
  filters.status = val
  pagination.current_page = 1
  fetchInvoices()
}

const changePage = (page) => {
  pagination.current_page = page
  fetchInvoices()
}

const confirmDelete = async (inv) => {
  if (confirm(t('invoices.confirmDelete', { number: inv.invoice_number }))) {
    try {
      await deleteInvoice(inv.id)
      allInvoices.value = allInvoices.value.filter((i) => i.id !== inv.id)
      fetchInvoices()
    } catch (err) {
      alert(t('invoices.deleteError'))
    }
  }
}

const formatCurrency = (val) => {
  if (val === undefined || val === null) return 'Rp 0'
  const isEn = locale.value === 'en'
  return new Intl.NumberFormat(isEn ? 'en-US' : 'id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  const loc = locale.value === 'en' ? 'en-US' : 'id-ID'
  return d.toLocaleDateString(loc, {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const isOverdue = (inv) => {
  if (inv.status === 'paid' || !inv.due_date) return false
  return new Date(inv.due_date) < new Date()
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'paid':
      return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
    case 'sent':
      return 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20'
    case 'draft':
      return 'bg-slate-100 text-slate-600 ring-1 ring-slate-400/20'
    case 'cancelled':
      return 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20'
    default:
      return 'bg-slate-100 text-slate-600'
  }
}

const getStatusDotClass = (status) => {
  switch (status) {
    case 'paid':
      return 'bg-emerald-500'
    case 'sent':
      return 'bg-amber-500'
    case 'draft':
      return 'bg-slate-400'
    case 'cancelled':
      return 'bg-rose-500'
    default:
      return 'bg-slate-400'
  }
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'paid':
      return t('invoices.status.paid')
    case 'sent':
      return t('invoices.status.sent')
    case 'draft':
      return t('invoices.status.draft')
    case 'cancelled':
      return t('invoices.status.cancelled')
    default:
      return status?.toUpperCase() || 'UNKNOWN'
  }
}

onMounted(() => {
  fetchInvoices()
})
</script>
