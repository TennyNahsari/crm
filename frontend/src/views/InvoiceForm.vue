<template>
  <div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl lg:text-3xl font-bold text-navy font-sans tracking-tight">
          {{ isEdit ? $t('invoices.form.editTitle') : $t('invoices.form.createTitle') }}
        </h1>
        <p class="text-xs lg:text-sm text-slate-500 mt-1">
          {{ isEdit ? $t('invoices.form.editSubtitle') : $t('invoices.form.createSubtitle') }}
        </p>
      </div>

      <router-link to="/invoices" class="btn btn-secondary inline-flex items-center text-sm">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        {{ $t('invoices.form.backToList') }}
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="pageLoading" class="text-center py-16 card">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
      <p class="mt-3 text-sm text-slate-500">{{ $t('invoices.form.loadingForm') }}</p>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="submitForm" class="space-y-6">
      <!-- Section 1: Information Basic -->
      <div class="card p-6">
        <h3 class="text-base font-bold text-navy mb-4 border-b border-stone/60 pb-3 flex items-center gap-2">
          <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          {{ $t('invoices.form.sectionCustomer') }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Customer Selection -->
          <div class="md:col-span-2">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              {{ $t('invoices.form.customerLabel') }} <span class="text-rose-500">*</span>
            </label>
            <select v-model="form.customer_id" required class="input">
              <option value="" disabled>{{ $t('invoices.form.selectCustomer') }}</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">
                {{ c.company || (c.contacts && c.contacts.length ? c.contacts[0].name : 'Pelanggan #' + c.id) }} {{ c.area ? ' (' + c.area.name + ')' : '' }}
              </option>
            </select>
          </div>

          <!-- Invoice Number -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              {{ $t('invoices.form.invoiceNumberLabel') }}
            </label>
            <input
              v-model="form.invoice_number"
              type="text"
              :placeholder="$t('invoices.form.invoiceNumberPlaceholder')"
              class="input text-sm"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              {{ $t('invoices.form.statusLabel') }} <span class="text-rose-500">*</span>
            </label>
            <select v-model="form.status" required class="input">
              <option value="draft">{{ $t('invoices.form.statusOptions.draft') }}</option>
              <option value="sent">{{ $t('invoices.form.statusOptions.sent') }}</option>
              <option value="paid">{{ $t('invoices.form.statusOptions.paid') }}</option>
              <option value="cancelled">{{ $t('invoices.form.statusOptions.cancelled') }}</option>
            </select>
          </div>

          <!-- Invoice Date -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              {{ $t('invoices.form.invoiceDateLabel') }} <span class="text-rose-500">*</span>
            </label>
            <input v-model="form.invoice_date" type="date" required class="input" />
          </div>

          <!-- Due Date -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              {{ $t('invoices.form.dueDateLabel') }}
            </label>
            <input v-model="form.due_date" type="date" class="input" />
          </div>
        </div>
      </div>

      <!-- Section 2: Items / Line Items Table -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4 border-b border-stone/60 pb-3">
          <h3 class="text-base font-bold text-navy flex items-center gap-2">
            <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            {{ $t('invoices.form.sectionItems') }}
          </h3>
          <button
            type="button"
            @click="addItem"
            class="btn btn-secondary text-xs px-3 py-1.5 inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-1 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ $t('invoices.form.addItem') }}
          </button>
        </div>

        <div class="space-y-4">
          <div
            v-for="(item, idx) in form.items"
            :key="idx"
            class="p-4 bg-stone-light/50 rounded-xl border border-stone/60 flex flex-col md:flex-row gap-3 items-start md:items-center"
          >
            <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-12 gap-3">
              <!-- Item Name -->
              <div class="sm:col-span-5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                  {{ $t('invoices.form.itemNameLabel') }} <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="item.item_name"
                  type="text"
                  required
                  :placeholder="$t('invoices.form.itemNamePlaceholder')"
                  class="input text-sm"
                />
              </div>

              <!-- Quantity -->
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                  {{ $t('invoices.form.qtyLabel') }} <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model.number="item.quantity"
                  type="number"
                  min="1"
                  required
                  class="input text-sm text-center"
                />
              </div>

              <!-- Unit Price -->
              <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                  {{ $t('invoices.form.unitPriceLabel') }} <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model.number="item.unit_price"
                  type="number"
                  min="0"
                  required
                  class="input text-sm text-right"
                />
              </div>

              <!-- Total Price Line -->
              <div class="sm:col-span-2 flex flex-col justify-center">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                  {{ $t('invoices.form.subtotalLabel') }}
                </label>
                <span class="text-sm font-bold text-navy text-right py-2 px-1 block">
                  {{ formatCurrency(item.quantity * item.unit_price) }}
                </span>
              </div>
            </div>

            <!-- Remove Item Button -->
            <button
              type="button"
              @click="removeItem(idx)"
              :disabled="form.items.length === 1"
              class="text-rose-500 hover:text-rose-700 p-2 rounded-lg hover:bg-rose-50 transition-colors disabled:opacity-30 mt-2 md:mt-0"
              :title="$t('invoices.form.removeRow')"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Section 3: Financial Calculations & Notes -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Notes -->
        <div class="card p-6">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
            {{ $t('invoices.form.notesLabel') }}
          </label>
          <textarea
            v-model="form.notes"
            rows="5"
            :placeholder="$t('invoices.form.notesPlaceholder')"
            class="input text-sm"
          ></textarea>
        </div>

        <!-- Totals Summary -->
        <div class="card p-6 bg-white space-y-3">
          <div class="flex justify-between items-center text-sm py-1">
            <span class="text-slate-600 font-medium">{{ $t('invoices.form.itemsSubtotal') }}</span>
            <span class="font-bold text-slate-900">{{ formatCurrency(calculatedSubtotal) }}</span>
          </div>

          <div class="flex justify-between items-center text-sm py-1 border-t border-stone/60">
            <label class="text-slate-600 font-medium flex items-center">
              {{ $t('invoices.form.taxLabel') }}
            </label>
            <div class="w-36">
              <input
                v-model.number="form.tax"
                type="number"
                min="0"
                :placeholder="$t('invoices.form.taxPlaceholder')"
                class="input text-right text-xs py-1"
              />
            </div>
          </div>

          <div class="flex justify-between items-center text-sm py-1 border-t border-stone/60">
            <label class="text-slate-600 font-medium flex items-center">
              {{ $t('invoices.form.discountLabel') }}
            </label>
            <div class="w-36">
              <input
                v-model.number="form.discount"
                type="number"
                min="0"
                :placeholder="$t('invoices.form.discountPlaceholder')"
                class="input text-right text-xs py-1"
              />
            </div>
          </div>

          <div class="flex justify-between items-center text-base py-3 border-t-2 border-navy text-navy font-bold">
            <span>{{ $t('invoices.form.grandTotal') }}</span>
            <span class="text-xl text-gold">{{ formatCurrency(calculatedTotal) }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3 pt-4">
        <router-link to="/invoices" class="btn btn-secondary px-6">
          {{ $t('invoices.form.cancel') }}
        </router-link>
        <button
          type="submit"
          :disabled="submitting"
          class="btn btn-primary px-8 shadow-md hover:shadow-lg inline-flex items-center"
        >
          <svg v-if="submitting" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ submitting ? $t('invoices.form.saving') : (isEdit ? $t('invoices.form.updateInvoice') : $t('invoices.form.saveInvoice')) }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getInvoice, createInvoice, updateInvoice } from '@/api/invoices'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const isEdit = computed(() => !!route.params.id)
const pageLoading = ref(true)
const submitting = ref(false)
const customers = ref([])

const form = reactive({
  customer_id: '',
  invoice_number: '',
  invoice_date: new Date().toISOString().substr(0, 10),
  due_date: '',
  status: 'draft',
  tax: 0,
  discount: 0,
  notes: '',
  items: [
    { item_name: '', description: '', quantity: 1, unit_price: 0 }
  ]
})

const calculatedSubtotal = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + (item.quantity || 0) * (item.unit_price || 0)
  }, 0)
})

const calculatedTotal = computed(() => {
  const sub = calculatedSubtotal.value
  const tax = parseFloat(form.tax) || 0
  const disc = parseFloat(form.discount) || 0
  return Math.max(0, sub + tax - disc)
})

const addItem = () => {
  form.items.push({ item_name: '', description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (idx) => {
  if (form.items.length > 1) {
    form.items.splice(idx, 1)
  }
}

const loadCustomers = async () => {
  try {
    const res = await api.get('/customers?per_page=500')
    customers.value = res.data.data || res.data
  } catch (err) {
    console.error('Failed to load customers:', err)
  }
}

const loadInvoiceData = async () => {
  if (!isEdit.value) return
  try {
    const res = await getInvoice(route.params.id)
    const inv = res.data
    form.customer_id = inv.customer_id
    form.invoice_number = inv.invoice_number
    form.invoice_date = inv.invoice_date ? inv.invoice_date.substr(0, 10) : ''
    form.due_date = inv.due_date ? inv.due_date.substr(0, 10) : ''
    form.status = inv.status
    form.tax = parseFloat(inv.tax) || 0
    form.discount = parseFloat(inv.discount) || 0
    form.notes = inv.notes || ''
    if (inv.items && inv.items.length > 0) {
      form.items = inv.items.map(item => ({
        item_name: item.item_name,
        description: item.description || '',
        quantity: item.quantity,
        unit_price: parseFloat(item.unit_price)
      }))
    }
  } catch (err) {
    alert(t('invoices.form.loadInvoiceError'))
    router.push('/invoices')
  }
}

const submitForm = async () => {
  if (!form.customer_id) {
    alert(t('invoices.form.selectCustomerAlert'))
    return
  }

  submitting.value = true
  try {
    const payload = {
      customer_id: form.customer_id,
      invoice_number: form.invoice_number || null,
      invoice_date: form.invoice_date,
      due_date: form.due_date || null,
      status: form.status,
      tax: form.tax,
      discount: form.discount,
      notes: form.notes,
      items: form.items
    }

    if (isEdit.value) {
      await updateInvoice(route.params.id, payload)
    } else {
      await createInvoice(payload)
    }

    router.push('/invoices')
  } catch (err) {
    console.error('Submit error:', err)
    const msg = err.response?.data?.error || err.response?.data?.message || t('invoices.form.saveInvoiceError')
    alert(msg)
  } finally {
    submitting.value = false
  }
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

onMounted(async () => {
  await loadCustomers()
  if (route.query.customer_id) {
    form.customer_id = parseInt(route.query.customer_id)
  }
  await loadInvoiceData()
  pageLoading.value = false
})
</script>
