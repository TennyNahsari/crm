<template>
  <div class="max-w-4xl mx-auto">
    <!-- Action Bar (Hidden during printing) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
      <router-link to="/invoices" class="btn btn-secondary inline-flex items-center text-sm">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        {{ $t('invoices.detailView.backToList') }}
      </router-link>

      <div class="flex flex-wrap gap-2.5">
        <!-- Print Button -->
        <button @click="triggerPrint" class="btn btn-secondary inline-flex items-center text-sm shadow-sm">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          {{ $t('invoices.detailView.printPdf') }}
        </button>

        <!-- Quick Status Change: Mark as Paid -->
        <button
          v-if="invoice && invoice.status !== 'paid'"
          @click="markAsPaid"
          :disabled="updatingStatus"
          class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold inline-flex items-center shadow-sm"
        >
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          {{ $t('invoices.detailView.markPaid') }}
        </button>

        <!-- Edit Button -->
        <router-link
          v-if="invoice"
          :to="`/invoices/${invoice.id}/edit`"
          class="btn btn-gold text-sm font-semibold inline-flex items-center shadow-sm"
        >
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          {{ $t('invoices.detailView.edit') }}
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20 card">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
      <p class="mt-3 text-sm text-slate-500">{{ $t('invoices.detailView.loading') }}</p>
    </div>

    <!-- Printable Invoice Card -->
    <div v-else-if="invoice" class="bg-white rounded-2xl border border-stone/80 shadow-card p-8 sm:p-12 print:shadow-none print:border-none print:p-0">
      <!-- Invoice Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start border-b border-stone/80 pb-8">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <img src="/logo.png" alt="Logo" class="h-10 w-auto" />
            <span class="font-serif font-bold italic text-2xl text-navy">FlowCRM</span>
          </div>
          <p class="text-xs text-slate-500">Customer Relationship & Billing Management</p>
        </div>

        <div class="mt-6 sm:mt-0 text-left sm:text-right">
          <h1 class="text-3xl font-extrabold text-navy tracking-tight font-serif">
            {{ invoice.status === 'draft' ? $t('invoices.detailView.titleQuotation') : (invoice.status === 'sent' ? $t('invoices.detailView.titleProforma') : $t('invoices.detailView.titleInvoice')) }}
          </h1>
          <p class="text-xs text-slate-500 font-medium uppercase tracking-widest mt-0.5">
            {{ invoice.status === 'draft' ? $t('invoices.detailView.subtitleQuotation') : (invoice.status === 'sent' ? $t('invoices.detailView.subtitleProforma') : $t('invoices.detailView.subtitleInvoice')) }}
          </p>
          <span class="text-base font-bold text-gold font-mono mt-1 block">{{ invoice.invoice_number }}</span>
          
          <div class="mt-3">
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
              :class="getStatusBadgeClass(invoice.status)"
            >
              {{ getStatusLabel(invoice.status) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Customer & Invoice Info Details Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 my-8 text-sm">
        <!-- Billed To -->
        <div>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">{{ $t('invoices.detailView.billedTo') }}</span>
          <div v-if="invoice.customer" class="space-y-1">
            <h3 class="font-bold text-slate-900 text-base">
              {{ invoice.customer.company || (invoice.customer.contacts && invoice.customer.contacts.length ? invoice.customer.contacts[0].name : 'Pelanggan #' + invoice.customer.id) }}
            </h3>
            <p v-if="!invoice.customer.is_individual && invoice.customer.name" class="text-slate-600">
              {{ $t('invoices.detailView.attn') }} {{ invoice.customer.name }}
            </p>
            <p v-if="invoice.customer.email" class="text-slate-600">{{ invoice.customer.email }}</p>
            <p v-if="invoice.customer.phone" class="text-slate-600">{{ invoice.customer.phone }}</p>
            <p v-if="invoice.customer.address" class="text-slate-600 whitespace-pre-line">{{ invoice.customer.address }}</p>
          </div>
        </div>

        <!-- Invoice Meta -->
        <div class="sm:text-right space-y-2">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">{{ $t('invoices.detailView.issuedDate') }}</span>
            <span class="font-semibold text-slate-800">{{ formatDate(invoice.invoice_date) }}</span>
          </div>
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">{{ $t('invoices.detailView.dueDate') }}</span>
            <span class="font-semibold" :class="isOverdue(invoice) ? 'text-rose-600 font-bold' : 'text-slate-800'">
              {{ invoice.due_date ? formatDate(invoice.due_date) : '-' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="my-8 overflow-hidden rounded-xl border border-stone/80">
        <table class="w-full text-left border-collapse text-sm">
          <thead>
            <tr class="bg-stone-light border-b border-stone/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              <th class="py-3.5 px-4">{{ $t('invoices.detailView.itemDescription') }}</th>
              <th class="py-3.5 px-4 text-center">{{ $t('invoices.detailView.qty') }}</th>
              <th class="py-3.5 px-4 text-right">{{ $t('invoices.detailView.unitPrice') }}</th>
              <th class="py-3.5 px-4 text-right">{{ $t('invoices.detailView.total') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone/60">
            <tr v-for="item in invoice.items" :key="item.id">
              <td class="py-4 px-4 font-medium text-slate-900">
                {{ item.item_name }}
                <p v-if="item.description" class="text-xs text-slate-500 mt-0.5">{{ item.description }}</p>
              </td>
              <td class="py-4 px-4 text-center text-slate-700 font-semibold">{{ item.quantity }}</td>
              <td class="py-4 px-4 text-right text-slate-700 font-mono">{{ formatCurrency(item.unit_price) }}</td>
              <td class="py-4 px-4 text-right font-bold text-slate-900 font-mono">{{ formatCurrency(item.total_price) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Financial Totals Summary -->
      <div class="flex flex-col sm:flex-row justify-between items-start gap-8 my-8">
        <!-- Notes -->
        <div class="flex-1 text-xs text-slate-500 space-y-1">
          <span class="font-bold text-slate-700 uppercase tracking-wider block mb-1">{{ $t('invoices.detailView.notes') }}</span>
          <p class="whitespace-pre-line leading-relaxed">{{ invoice.notes || $t('invoices.detailView.defaultNotes') }}</p>
        </div>

        <!-- Calculations -->
        <div class="w-full sm:w-72 space-y-2 text-sm bg-stone-light/40 p-4 rounded-xl border border-stone/60">
          <div class="flex justify-between text-slate-600">
            <span>{{ $t('invoices.detailView.subtotal') }}</span>
            <span class="font-semibold text-slate-900 font-mono">{{ formatCurrency(invoice.subtotal) }}</span>
          </div>
          <div v-if="parseFloat(invoice.tax) > 0" class="flex justify-between text-slate-600">
            <span>{{ $t('invoices.detailView.tax') }}</span>
            <span class="font-semibold text-slate-900 font-mono">+ {{ formatCurrency(invoice.tax) }}</span>
          </div>
          <div v-if="parseFloat(invoice.discount) > 0" class="flex justify-between text-rose-600 font-medium">
            <span>{{ $t('invoices.detailView.discount') }}</span>
            <span class="font-semibold font-mono">- {{ formatCurrency(invoice.discount) }}</span>
          </div>
          <div class="flex justify-between pt-3 border-t-2 border-navy font-bold text-base text-navy">
            <span>{{ $t('invoices.detailView.totalAmount') }}</span>
            <span class="text-gold font-mono text-lg">{{ formatCurrency(invoice.total) }}</span>
          </div>
        </div>
      </div>

      <!-- Signature Footer (Visible on print) -->
      <div class="hidden print:flex justify-between items-end pt-16 text-xs text-slate-600">
        <div>
          <p class="font-semibold text-slate-800">{{ $t('invoices.detailView.paymentMethod') }}</p>
          <p>{{ $t('invoices.detailView.bankTransfer') }}</p>
          <p>{{ $t('invoices.detailView.billingDept') }}</p>
        </div>
        <div class="text-center w-48 border-t border-slate-300 pt-2">
          <p class="font-bold text-slate-800">{{ $t('invoices.detailView.authorizedSignature') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getInvoice, updateInvoice } from '@/api/invoices'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const loading = ref(true)
const updatingStatus = ref(false)
const invoice = ref(null)

const fetchInvoiceDetail = async () => {
  loading.value = true
  try {
    const res = await getInvoice(route.params.id)
    invoice.value = res.data
  } catch (err) {
    alert(t('invoices.detailView.notFoundAlert'))
    router.push('/invoices')
  } finally {
    loading.value = false
  }
}

const markAsPaid = async () => {
  if (!invoice.value) return
  updatingStatus.value = true
  try {
    await updateInvoice(invoice.value.id, { status: 'paid' })
    invoice.value.status = 'paid'
  } catch (err) {
    alert(t('invoices.detailView.updateStatusError'))
  } finally {
    updatingStatus.value = false
  }
}

const triggerPrint = () => {
  window.print()
}

const formatCurrency = (val) => {
  if (val === undefined || val === null) return 'Rp 0'
  const isEn = locale.value === 'en'
  return new Intl.NumberFormat(isEn ? 'en-US' : 'id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  const loc = locale.value === 'en' ? 'en-US' : 'id-ID'
  return d.toLocaleDateString(loc, {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const isOverdue = (inv) => {
  if (inv.status === 'paid' || !inv.due_date) return false
  return new Date(inv.due_date) < new Date()
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'paid':
      return 'bg-emerald-100 text-emerald-800'
    case 'sent':
      return 'bg-amber-100 text-amber-800'
    case 'draft':
      return 'bg-slate-200 text-slate-700'
    case 'cancelled':
      return 'bg-rose-100 text-rose-800'
    default:
      return 'bg-slate-200 text-slate-700'
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
  fetchInvoiceDetail()
})
</script>

<style scoped>
@media print {
  body {
    background-color: white !important;
  }
}
</style>
