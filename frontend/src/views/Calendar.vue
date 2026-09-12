<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
      <div>
        <h1 class="text-2xl lg:text-3xl font-bold text-navy font-sans tracking-tight">
          {{ $t('calendar.title') }}
        </h1>
        <p class="text-xs lg:text-sm text-slate-500 mt-1">
          {{ $t('calendar.subtitle') }}
        </p>
      </div>

      <!-- Navigation & Controls -->
      <div class="mt-4 lg:mt-0 flex flex-wrap gap-2.5 items-center">
        <!-- Month Navigation -->
        <div class="inline-flex items-center bg-white p-1 rounded-xl border border-stone/80 shadow-sm">
          <button @click="prevMonth" class="p-1.5 text-slate-600 hover:text-navy rounded-lg hover:bg-stone-light">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="px-4 font-bold text-navy text-sm min-w-[140px] text-center">
            {{ currentMonthName }} {{ currentYear }}
          </span>
          <button @click="nextMonth" class="p-1.5 text-slate-600 hover:text-navy rounded-lg hover:bg-stone-light">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <button @click="goToToday" class="btn btn-secondary text-xs px-3 py-2 font-semibold">
          {{ $t('calendar.today') }}
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="card mb-6 p-4">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-3 items-center w-full sm:w-auto">
          <!-- Priority Filter -->
          <div class="flex items-center gap-1.5">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $t('calendar.priority') }}</span>
            <select v-model="filters.priority" @change="fetchEvents" class="input py-1 text-xs w-auto">
              <option value="all">{{ $t('calendar.allPriorities') }}</option>
              <option value="high">{{ $t('calendar.priorityHigh') }}</option>
              <option value="medium">{{ $t('calendar.priorityMedium') }}</option>
              <option value="low">{{ $t('calendar.priorityLow') }}</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="flex items-center gap-1.5">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $t('calendar.statusLabel') }}</span>
            <select v-model="filters.status" @change="fetchEvents" class="input py-1 text-xs w-auto">
              <option value="all">{{ $t('calendar.allStatuses') }}</option>
              <option value="pending">{{ $t('calendar.statusPending') }}</option>
              <option value="done">{{ $t('calendar.statusDone') }}</option>
            </select>
          </div>
        </div>

        <!-- Event Legend -->
        <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> {{ $t('calendar.legendHigh') }}
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> {{ $t('calendar.legendMedium') }}
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> {{ $t('calendar.legendLow') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20 card">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
      <p class="mt-3 text-sm text-slate-500">{{ $t('calendar.loading') }}</p>
    </div>

    <!-- Calendar Grid -->
    <div v-else class="bg-white rounded-2xl border border-stone/80 shadow-card overflow-hidden">
      <!-- Days of the week header -->
      <div class="grid grid-cols-7 bg-stone-light border-b border-stone/60 text-center py-3 text-xs font-bold text-slate-600 uppercase tracking-wider">
        <div class="text-rose-600">{{ $t('calendar.days.sun') }}</div>
        <div>{{ $t('calendar.days.mon') }}</div>
        <div>{{ $t('calendar.days.tue') }}</div>
        <div>{{ $t('calendar.days.wed') }}</div>
        <div>{{ $t('calendar.days.thu') }}</div>
        <div>{{ $t('calendar.days.fri') }}</div>
        <div class="text-navy">{{ $t('calendar.days.sat') }}</div>
      </div>

      <!-- Days Grid -->
      <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-stone/60">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          class="min-h-[120px] p-2 transition-colors relative flex flex-col justify-start"
          :class="{
            'bg-stone-light/40 text-slate-400': !day.isCurrentMonth,
            'bg-amber-50/40 ring-1 ring-gold/40': day.isToday,
            'hover:bg-amber-50/20': day.isCurrentMonth
          }"
        >
          <!-- Date Number -->
          <div class="flex items-center justify-between mb-1.5">
            <span
              class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
              :class="{
                'bg-navy text-gold shadow-sm': day.isToday,
                'text-slate-800': day.isCurrentMonth && !day.isToday,
                'text-slate-400': !day.isCurrentMonth
              }"
            >
              {{ day.dateNumber }}
            </span>
            <span v-if="day.events.length > 0" class="text-[10px] font-bold text-slate-400">
              {{ $t('calendar.actionsCount', { count: day.events.length }) }}
            </span>
          </div>

          <!-- Events List for Day -->
          <div class="space-y-1 overflow-y-auto max-h-[90px] pr-0.5 custom-scrollbar">
            <div
              v-for="ev in day.events"
              :key="ev.id"
              @click.stop="openEventModal(ev)"
              class="p-1.5 rounded-lg text-xs font-medium cursor-pointer shadow-2xs transition-all hover:scale-[1.02] border leading-tight"
              :class="getEventBadgeClass(ev)"
            >
              <div class="flex items-center justify-between gap-1">
                <span class="font-bold truncate" :class="{ 'line-through opacity-60': ev.status === 'done' }">
                  {{ ev.customer_name }}
                </span>
                <span class="text-[9px] uppercase px-1 rounded font-mono font-bold" :class="ev.status === 'done' ? 'bg-emerald-200 text-emerald-800' : 'bg-white/80 text-slate-700'">
                  {{ ev.status === 'done' ? '✓' : '' }}
                </span>
              </div>
              <p class="text-[10px] opacity-90 truncate mt-0.5" :class="{ 'line-through opacity-60': ev.status === 'done' }">
                {{ ev.title }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Event Quick Detail Modal -->
    <div v-if="selectedEvent" class="fixed inset-0 bg-navy/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl border border-stone/80 shadow-2xl max-w-lg w-full p-6 animate-fadeIn">
        <div class="flex items-start justify-between border-b border-stone/60 pb-4 mb-4">
          <div>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-1"
              :class="getPriorityBadgeClass(selectedEvent.priority)"
            >
              {{ $t('calendar.modal.priorityBadge', { priority: selectedEvent.priority }) }}
            </span>
            <h3 class="text-xl font-bold text-navy font-sans">
              {{ selectedEvent.customer_name }}
            </h3>
            <p class="text-xs text-slate-500" v-if="selectedEvent.area_name">{{ $t('calendar.modal.area', { name: selectedEvent.area_name }) }}</p>
          </div>
          <button @click="selectedEvent = null" class="text-slate-400 hover:text-slate-600 p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-4 text-sm mb-6">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ $t('calendar.modal.actionPlan') }}</span>
            <div class="p-3 bg-stone-light/60 rounded-xl border border-stone/60 text-slate-800 font-medium">
              {{ selectedEvent.title }}
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ $t('calendar.modal.scheduleDate') }}</span>
              <span class="font-semibold text-slate-800">{{ formatDate(selectedEvent.date) }}</span>
            </div>
            <div>
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ $t('calendar.modal.status') }}</span>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold"
                :class="selectedEvent.status === 'done' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
              >
                {{ selectedEvent.status === 'done' ? $t('calendar.modal.statusDoneLabel') : $t('calendar.modal.statusPendingLabel') }}
              </span>
            </div>
          </div>

          <div v-if="selectedEvent.customer_email || selectedEvent.customer_phone" class="pt-2 border-t border-stone/60 flex gap-4">
            <a
              v-if="selectedEvent.customer_phone"
              :href="`https://wa.me/${cleanPhone(selectedEvent.customer_phone)}`"
              target="_blank"
              class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-1.5 px-3 inline-flex items-center"
            >
              {{ $t('calendar.modal.chatWa') }}
            </a>
            <a
              v-if="selectedEvent.customer_email"
              :href="`mailto:${selectedEvent.customer_email}`"
              class="btn btn-secondary text-xs font-semibold py-1.5 px-3 inline-flex items-center"
            >
              {{ $t('calendar.modal.sendEmail') }}
            </a>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="flex justify-between items-center border-t border-stone/60 pt-4">
          <router-link
            :to="`/customers/${selectedEvent.customer_id}`"
            class="text-xs font-bold text-navy hover:text-gold transition-colors inline-flex items-center"
          >
            {{ $t('calendar.modal.openCustomerDetail') }}
          </router-link>

          <div class="flex gap-2">
            <button
              @click="toggleEventStatus(selectedEvent)"
              :disabled="updatingStatus"
              class="btn text-xs font-semibold py-2 px-4 shadow-sm"
              :class="selectedEvent.status === 'done' ? 'btn-secondary' : 'btn-primary'"
            >
              {{ selectedEvent.status === 'done' ? $t('calendar.modal.markPending') : $t('calendar.modal.markDone') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { getCalendarEvents } from '@/api/calendar'
import api from '@/api/axios'

const { t, locale, tm } = useI18n()

const loading = ref(true)
const updatingStatus = ref(false)
const events = ref([])
const selectedEvent = ref(null)

const currentDate = ref(new Date())
const filters = reactive({
  priority: 'all',
  status: 'all',
})

const currentMonthName = computed(() => {
  const months = tm('calendar.months')
  if (Array.isArray(months) && months[currentDate.value.getMonth()]) {
    return months[currentDate.value.getMonth()]
  }
  return currentDate.value.toLocaleString(locale.value === 'en' ? 'en-US' : 'id-ID', { month: 'long' })
})
const currentYear = computed(() => currentDate.value.getFullYear())

const fetchEvents = async () => {
  loading.value = true
  try {
    const month = currentDate.value.getMonth() + 1
    const year = currentDate.value.getFullYear()
    const res = await getCalendarEvents({
      month,
      year,
      priority: filters.priority,
      status: filters.status,
    })
    events.value = res.data.events || []
  } catch (err) {
    console.error('Error fetching calendar events:', err)
  } finally {
    loading.value = false
  }
}

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDayOfMonth = new Date(year, month, 1)
  const lastDayOfMonth = new Date(year, month + 1, 0)

  const startingDayOfWeek = firstDayOfMonth.getDay() // 0 = Sunday
  const totalDaysInMonth = lastDayOfMonth.getDate()

  const todayStr = new Date().toISOString().substr(0, 10)
  const days = []

  // Prev month padding days
  const prevMonthLastDay = new Date(year, month, 0).getDate()
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    const dayNum = prevMonthLastDay - i
    days.push({
      dateNumber: dayNum,
      isCurrentMonth: false,
      isToday: false,
      events: [],
    })
  }

  // Current month days
  for (let d = 1; d <= totalDaysInMonth; d++) {
    const monthStr = String(month + 1).padStart(2, '0')
    const dayStr = String(d).padStart(2, '0')
    const dateFormatted = `${year}-${monthStr}-${dayStr}`

    const dayEvents = events.value.filter((ev) => ev.date === dateFormatted)

    days.push({
      dateNumber: d,
      dateFormatted,
      isCurrentMonth: true,
      isToday: dateFormatted === todayStr,
      events: dayEvents,
    })
  }

  // Next month padding days to complete 35 or 42 grid cells
  const remaining = 35 - (days.length % 35)
  if (remaining < 35 && remaining > 0) {
    for (let j = 1; j <= remaining; j++) {
      days.push({
        dateNumber: j,
        isCurrentMonth: false,
        isToday: false,
        events: [],
      })
    }
  }

  return days
})

const prevMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
  fetchEvents()
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
  fetchEvents()
}

const goToToday = () => {
  currentDate.value = new Date()
  fetchEvents()
}

const openEventModal = (ev) => {
  selectedEvent.value = ev
}

const toggleEventStatus = async (ev) => {
  updatingStatus.value = true
  try {
    const newStatus = ev.status === 'done' ? 'pending' : 'done'
    await api.post(`/customers/${ev.customer_id}/next-action`, {
      next_action_status: newStatus,
    })
    ev.status = newStatus
    fetchEvents()
  } catch (err) {
    alert(t('calendar.updateStatusError'))
  } finally {
    updatingStatus.value = false
  }
}

const cleanPhone = (phone) => {
  if (!phone) return ''
  return phone.replace(/[^0-9]/g, '')
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  const loc = locale.value === 'en' ? 'en-US' : 'id-ID'
  return d.toLocaleDateString(loc, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const getEventBadgeClass = (ev) => {
  if (ev.status === 'done') {
    return 'bg-slate-100 border-slate-200 text-slate-500'
  }
  switch (ev.priority) {
    case 'high':
      return 'bg-rose-50 border-rose-200 text-rose-800 hover:bg-rose-100'
    case 'medium':
      return 'bg-amber-50 border-amber-200 text-amber-800 hover:bg-amber-100'
    case 'low':
      return 'bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100'
    default:
      return 'bg-slate-50 border-slate-200 text-slate-800'
  }
}

const getPriorityBadgeClass = (priority) => {
  switch (priority) {
    case 'high':
      return 'bg-rose-100 text-rose-800'
    case 'medium':
      return 'bg-amber-100 text-amber-800'
    case 'low':
      return 'bg-emerald-100 text-emerald-800'
    default:
      return 'bg-slate-100 text-slate-700'
  }
}

onMounted(() => {
  fetchEvents()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 4px;
}
</style>
