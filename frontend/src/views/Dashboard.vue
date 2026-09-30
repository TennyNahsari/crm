<template>
  <div>
    <h1 class="text-2xl lg:text-3xl font-bold text-navy font-sans tracking-tight mb-6 lg:mb-8">{{ $t('dashboard.title') }}</h1>

    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
      <p class="mt-2 text-slate-600 font-sans">{{ $t('dashboard.loading') }}</p>
    </div>

    <div v-else-if="stats" class="space-y-6 font-sans">
      <!-- AI Customer Prediction -->
      <div class="card bg-gradient-to-br from-white via-ivory to-stone-light/50 border border-gold/40 shadow-card">
        <div class="flex items-center gap-2 sm:gap-3 mb-4">
          <div class="p-2 bg-navy text-gold rounded-xl text-xl sm:text-2xl shadow-sm">🤖</div>
          <div class="flex-1 min-w-0">
            <h3 class="text-base sm:text-lg font-semibold text-navy truncate">{{ $t('dashboard.aiPrediction.title') }}</h3>
            <p class="text-xs text-slate-500 hidden sm:block">{{ $t('dashboard.aiPrediction.subtitle') }}</p>
          </div>
        </div>

        <!-- Control Buttons -->
        <div class="flex flex-col sm:flex-row gap-2.5 mb-4">
          <button
            @click="trainModel"
            :disabled="training"
            class="btn btn-primary flex items-center justify-center gap-2 w-full sm:w-auto text-sm"
          >
            <span v-if="training" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <span>{{ training ? $t('dashboard.aiPrediction.training') : '🔄 ' + $t('dashboard.aiPrediction.trainButton') }}</span>
          </button>
          <button
            @click="predict"
            :disabled="predicting || !modelInfo?.model_exists"
            class="btn btn-gold flex items-center justify-center gap-2 w-full sm:w-auto text-sm shadow-sm"
          >
            <span v-if="predicting" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <span>{{ predicting ? $t('dashboard.aiPrediction.predicting') : '🎯 ' + $t('dashboard.aiPrediction.predictButton') }}</span>
          </button>
        </div>

        <!-- Model Info -->
        <div v-if="modelInfo?.model_exists" class="text-xs sm:text-sm text-gray-600 bg-white/50 rounded p-2 sm:p-3 mb-3 overflow-x-auto">
          <div class="flex flex-wrap gap-x-3 gap-y-1">
            <span><span class="font-semibold">{{ $t('dashboard.aiPrediction.modelStatus') }}:</span> {{ $t('dashboard.aiPrediction.trained') }} ✓</span>
            <span class="hidden sm:inline">|</span>
            <span><span class="font-semibold">{{ $t('dashboard.aiPrediction.lastTrained') }}:</span> {{ formatDateTime(modelInfo.info?.trained_at) }}</span>
            <span class="hidden sm:inline">|</span>
            <span><span class="font-semibold">{{ $t('dashboard.aiPrediction.customers') }}:</span> {{ modelInfo.info?.customers_count }}</span>
          </div>
        </div>
        <div v-else-if="modelInfo !== null" class="text-xs sm:text-sm text-amber-700 bg-amber-50 rounded p-2 sm:p-3 mb-3">
          ⚠️ {{ $t('dashboard.aiPrediction.modelNotTrained') }}
        </div>

        <!-- Success Message -->
        <div v-if="trainSuccess" class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-xs sm:text-sm mb-3">
          ✓ {{ trainSuccess }}
        </div>

        <!-- Error Message -->
        <div v-if="mlError" class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-xs sm:text-sm mb-3 break-words">
          ✗ {{ mlError }}
        </div>

        <!-- Predictions Display -->
        <div v-if="predictions.length > 0" class="space-y-2">
          <div
            v-for="(pred, idx) in predictions"
            :key="pred.customer_id"
            @click="goToDetail(pred.customer_id)"
            class="flex items-center justify-between p-2 sm:p-3 bg-white rounded-lg border-2 border-blue-100 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer"
          >
            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
              <div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm lg:text-lg">
                {{ idx + 1 }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h4 class="font-semibold text-gray-900 text-xs sm:text-sm lg:text-base truncate">{{ pred.company }}</h4>
                  <span v-if="pred.area" class="badge bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded">📍 {{ pred.area }}</span>
                </div>
                <p class="text-xs text-gray-600 truncate">{{ pred.email }}</p>
                <p class="text-xs text-blue-600 mt-1 line-clamp-2">{{ pred.reason }}</p>
              </div>
            </div>
            <div class="text-right ml-2 flex-shrink-0">
              <div class="flex flex-col items-end gap-0.5">
                <!-- Percentile Score (Primary - Large) -->
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold" :class="getScoreColor(pred.percentile_score)">
                  {{ pred.percentile_score.toFixed(0) }}
                </div>
                <!-- Badge -->
                <div class="text-xs px-2 py-0.5 rounded-full font-semibold" :class="getScoreBadgeClass(pred.percentile_score)">
                  {{ getScoreBadgeLabel(pred.percentile_score) }}
                </div>
                <!-- Raw Score (Secondary - Small) -->
                <p class="text-xs text-gray-400 mt-1" :title="'Raw score: ' + pred.score.toFixed(0)">
                  {{ formatRawScore(pred.score) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!predicting && !training" class="text-center py-6 sm:py-8 text-gray-500">
          <div class="text-3xl sm:text-4xl mb-2">
            <span v-if="predictionMessage">📋</span>
            <span v-else>🎯</span>
          </div>
          <p class="text-xs sm:text-sm px-4">
            <span v-if="predictionMessage">{{ predictionMessage }}</span>
            <span v-else>{{ $t('dashboard.aiPrediction.emptyState') }}</span>
          </p>
        </div>
      </div>

      <!-- Next Action Today -->
      <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
          <h3 class="text-base lg:text-lg font-semibold text-gray-800">{{ $t('dashboard.nextActionToday') }}</h3>
          <span v-if="todayActionsPagination.total > 0" class="badge bg-green-100 text-green-800 text-xs lg:text-sm">
            {{ todayActionsPagination.total }} {{ todayActionsPagination.total === 1 ? 'customer' : 'customers' }}
          </span>
        </div>

        <div v-if="todayActionsLoading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="todayActions.length === 0" class="text-center py-8 text-gray-500">
          {{ $t('dashboard.noActionsToday') }}
        </div>

        <template v-else>
          <!-- Mobile View -->
          <div class="lg:hidden space-y-3">
          <div
            v-for="customer in todayActions"
            :key="customer.id"
            @click="goToDetail(customer.id)"
            class="bg-gray-50 p-3 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="flex-1">
                <h4 class="font-semibold text-gray-900 text-sm">{{ customer.company }}</h4>
                <p class="text-xs text-gray-600">{{ customer.email }}</p>
                <p v-if="customer.phone" class="text-xs text-gray-600">📞 {{ customer.phone }}</p>
              </div>
              <div class="flex flex-col items-end space-y-1 ml-2">
                <span
                  v-if="customer.lead_status"
                  class="badge text-xs px-2 py-1 whitespace-nowrap"
                  :style="{
                    backgroundColor: customer.lead_status.color + '20',
                    color: customer.lead_status.color,
                  }"
                >
                  {{ customer.lead_status.name }}
                </span>
                <span
                  class="badge text-xs px-2 py-1 whitespace-nowrap"
                  :class="{
                    'bg-green-100 text-green-800': customer.source === 'inbound',
                    'bg-blue-100 text-blue-800': customer.source === 'outbound',
                  }"
                >
                  {{ customer.source }}
                </span>
              </div>
            </div>
            <div class="space-y-1 text-xs text-gray-600">
              <p v-if="customer.area">
                <span class="font-medium">{{ $t('customers.area') }}:</span> {{ customer.area.name }}
              </p>
              <p v-if="customer.next_action_date" class="text-orange-600 font-medium">
                <span class="font-bold">{{ $t('customers.next') }}:</span> {{ formatDate(customer.next_action_date) }}
                <span v-if="customer.next_action_plan" class="block text-gray-600 font-normal mt-1">{{ customer.next_action_plan }}</span>
              </p>
              <div v-if="customer.interactions && customer.interactions.length > 0" class="pt-2 border-t">
                <p class="font-medium text-gray-700">{{ $t('customers.lastInteraction') }}:</p>
                <p class="text-xs text-gray-600">{{ formatDateTime(customer.interactions[0].interaction_at) }}</p>
                <p class="text-xs text-gray-500 italic">{{ customer.interactions[0].summary || customer.interactions[0].content || '-' }}</p>
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
                <th class="w-24">
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
                v-for="customer in todayActions"
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
                <td>
                  <span
                    v-if="customer.lead_status"
                    class="badge text-xs px-2.5 py-0.5 rounded-full whitespace-nowrap font-medium"
                    :style="{
                      backgroundColor: customer.lead_status.color + '20',
                      color: customer.lead_status.color,
                    }"
                  >
                    {{ customer.lead_status.name }}
                  </span>
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
        </template>

        <!-- Pagination -->
        <Pagination
          :current-page="todayActionsPagination.current_page"
          :total-pages="todayActionsPagination.last_page"
          :prev-text="$t('invoices.pagination.previous')"
          :next-text="$t('invoices.pagination.next')"
          @change="changeTodayActionsPage"
        />
      </div>

      <!-- This Week Meetings -->
      <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
          <h3 class="text-base lg:text-lg font-semibold text-gray-800">{{ $t('dashboard.thisWeekMeetings') }}</h3>
          <span v-if="weekMeetingsPagination.total > 0" class="badge bg-orange-100 text-orange-800 text-xs lg:text-sm">
            {{ weekMeetingsPagination.total }} {{ weekMeetingsPagination.total === 1 ? 'customer' : 'customers' }}
          </span>
        </div>

        <div v-if="weekMeetingsLoading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
        </div>

        <div v-else-if="weekMeetings.length === 0" class="text-center py-8 text-gray-500">
          {{ $t('dashboard.noMeetingsThisWeek') }}
        </div>

        <!-- Mobile View -->
        <div v-else class="lg:hidden space-y-3">
          <div
            v-for="customer in weekMeetings"
            :key="customer.id"
            @click="goToDetail(customer.id)"
            class="bg-gray-50 p-3 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
          >
            <div class="flex justify-between items-start mb-2">
              <div class="flex-1">
                <h4 class="font-semibold text-gray-900 text-sm">{{ customer.company }}</h4>
                <p class="text-xs text-gray-600">{{ customer.email }}</p>
                <p v-if="customer.phone" class="text-xs text-gray-600">📞 {{ customer.phone }}</p>
              </div>
              <div class="flex flex-col items-end space-y-1 ml-2">
                <span
                  v-if="customer.lead_status"
                  class="badge text-xs px-2 py-1 whitespace-nowrap"
                  :style="{
                    backgroundColor: customer.lead_status.color + '20',
                    color: customer.lead_status.color,
                  }"
                >
                  {{ customer.lead_status.name }}
                </span>
                <span
                  class="badge text-xs px-2 py-1 whitespace-nowrap"
                  :class="{
                    'bg-green-100 text-green-800': customer.source === 'inbound',
                    'bg-blue-100 text-blue-800': customer.source === 'outbound',
                  }"
                >
                  {{ customer.source }}
                </span>
              </div>
            </div>
            <div class="space-y-1 text-xs text-gray-600">
              <p v-if="customer.area">
                <span class="font-medium">{{ $t('customers.area') }}:</span> {{ customer.area.name }}
              </p>
              <p v-if="customer.next_action_date" class="text-orange-600 font-medium">
                <span class="font-bold">{{ $t('customers.next') }}:</span> {{ formatDate(customer.next_action_date) }}
                <span v-if="customer.next_action_plan" class="block text-gray-600 font-normal mt-1">{{ customer.next_action_plan }}</span>
              </p>
              <div v-if="customer.interactions && customer.interactions.length > 0" class="pt-2 border-t">
                <p class="font-medium text-gray-700">{{ $t('customers.lastInteraction') }}:</p>
                <p class="text-xs text-gray-600">{{ formatDateTime(customer.interactions[0].interaction_at) }}</p>
                <p class="text-xs text-gray-500 italic">{{ customer.interactions[0].summary || customer.interactions[0].content || '-' }}</p>
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
                <th class="w-24">
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
                v-for="customer in weekMeetings"
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
                <td>
                  <span
                    v-if="customer.lead_status"
                    class="badge text-xs px-2.5 py-0.5 rounded-full whitespace-nowrap font-medium"
                    :style="{
                      backgroundColor: customer.lead_status.color + '20',
                      color: customer.lead_status.color,
                    }"
                  >
                    {{ customer.lead_status.name }}
                  </span>
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
        <Pagination
          :current-page="weekMeetingsPagination.current_page"
          :total-pages="weekMeetingsPagination.last_page"
          :prev-text="$t('invoices.pagination.previous')"
          :next-text="$t('invoices.pagination.next')"
          @change="changeWeekMeetingsPage"
        />
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <!-- Leads by Status -->
        <div class="card">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
            <h3 class="text-base lg:text-lg font-semibold text-gray-800">{{ $t('dashboard.leadsByStatus') }}</h3>
            <span class="badge bg-red-100 text-red-800 text-xs lg:text-sm">
              🔥 {{ stats.hot_leads }} {{ $t('dashboard.hotLeads').replace('🔥 ', '') }}
            </span>
          </div>
          <div class="space-y-3">
            <div
              v-for="status in stats.leads_by_status"
              :key="status.status"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  class="w-3 h-3 rounded-full mr-3"
                  :style="{ backgroundColor: status.color }"
                ></div>
                <span class="text-sm text-gray-700">{{ status.status }}</span>
              </div>
              <span class="text-sm font-semibold text-gray-900">{{ status.total }}</span>
            </div>
          </div>
        </div>

        <!-- Customers by Area -->
        <div class="card">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
            <h3 class="text-base lg:text-lg font-semibold text-gray-800">{{ $t('dashboard.customersByArea') }}</h3>
            <span class="badge bg-blue-100 text-blue-800 text-xs lg:text-sm">
              👥 {{ stats.total_customers }} {{ $t('dashboard.totalCustomers') }}
            </span>
          </div>
          <div class="space-y-3">
            <div
              v-for="area in stats.customers_by_area"
              :key="area.area"
              class="flex items-center justify-between"
            >
              <span class="text-sm text-gray-700">{{ area.area }}</span>
              <span class="text-sm font-semibold text-gray-900">{{ area.total }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useDashboardStore } from '@/stores/dashboard'
import axios from '@/api/axios'
import Pagination from '@/components/Pagination.vue'

const router = useRouter()
const dashboardStore = useDashboardStore()

const stats = ref(null)
const loading = ref(false)
const todayActions = computed(() => dashboardStore.todayActions)
const todayActionsLoading = computed(() => dashboardStore.todayActionsLoading)
const todayActionsPagination = computed(() => dashboardStore.todayActionsPagination)

const weekMeetings = computed(() => dashboardStore.weekMeetings)
const weekMeetingsLoading = computed(() => dashboardStore.weekMeetingsLoading)
const weekMeetingsPagination = computed(() => dashboardStore.weekMeetingsPagination)

// AI Prediction state
const training = ref(false)
const predicting = ref(false)
const predictions = ref([])
const predictionMessage = ref('') // For sales with no assigned customers
const modelInfo = ref(null)
const trainSuccess = ref('')
const mlError = ref('')

const goToDetail = (id) => {
  router.push(`/customers/${id}`)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
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

// Percentile Score Helper Functions
const getScoreColor = (percentile) => {
  if (percentile >= 90) return 'text-green-600'
  if (percentile >= 75) return 'text-blue-600'
  if (percentile >= 50) return 'text-yellow-600'
  return 'text-gray-600'
}

const getScoreBadgeClass = (percentile) => {
  if (percentile >= 90) return 'bg-green-100 text-green-800'
  if (percentile >= 75) return 'bg-blue-100 text-blue-800'
  if (percentile >= 50) return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-800'
}

const getScoreBadgeLabel = (percentile) => {
  if (percentile >= 90) return '🏆 Excellent'
  if (percentile >= 75) return '⭐ High'
  if (percentile >= 50) return '✓ Good'
  return '○ Average'
}

const formatRawScore = (score) => {
  if (score >= 1000000) {
    return (score / 1000000).toFixed(1) + 'M'
  } else if (score >= 1000) {
    return (score / 1000).toFixed(0) + 'K'
  }
  return score.toFixed(0)
}

const changeTodayActionsPage = async (page) => {
  if (page < 1 || page > todayActionsPagination.value.last_page) return
  await dashboardStore.fetchTodayActions(page)
}

const changeWeekMeetingsPage = async (page) => {
  if (page < 1 || page > weekMeetingsPagination.value.last_page) return
  await dashboardStore.fetchWeekMeetings(page)
}

// AI Prediction functions
const fetchModelInfo = async () => {
  try {
    const response = await axios.get('/ml/model-info')
    modelInfo.value = response.data
  } catch (error) {
    console.error('Error fetching model info:', error)
    modelInfo.value = { model_exists: false }
  }
}

const trainModel = async () => {
  training.value = true
  trainSuccess.value = ''
  mlError.value = ''
  
  try {
    const response = await axios.post('/ml/train')
    
    if (response.data.success) {
      trainSuccess.value = `Model berhasil di-train! (${response.data.data.customers_count} customers)`
      await fetchModelInfo()
      
      // Clear success message after 5 seconds
      setTimeout(() => {
        trainSuccess.value = ''
      }, 5000)
    } else {
      mlError.value = response.data.message || 'Gagal training model'
    }
  } catch (error) {
    console.error('Training error:', error)
    mlError.value = error.response?.data?.message || 'Error saat training model. Pastikan ML service berjalan.'
  } finally {
    training.value = false
  }
}

const predict = async () => {
  predicting.value = true
  mlError.value = ''
  predictionMessage.value = ''
  
  try {
    const response = await axios.post('/ml/predict')
    
    if (response.data.success) {
      predictions.value = response.data.data.predictions
      // Check if there's a message (e.g., "No customers assigned")
      if (response.data.data.message) {
        predictionMessage.value = response.data.data.message
      }
    } else {
      mlError.value = response.data.message || 'Gagal mendapatkan prediksi'
    }
  } catch (error) {
    console.error('Prediction error:', error)
    mlError.value = error.response?.data?.message || 'Error saat prediksi. Pastikan model sudah di-train.'
  } finally {
    predicting.value = false
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const [statsData] = await Promise.all([
      dashboardStore.fetchStats(),
      dashboardStore.fetchTodayActions(),
      dashboardStore.fetchWeekMeetings(),
      fetchModelInfo(), // Fetch model info on mount
    ])
    stats.value = statsData
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>
