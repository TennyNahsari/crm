<template>
  <div v-if="totalPages > 1" class="flex flex-wrap items-center justify-center gap-1.5 sm:gap-2 my-4">
    <!-- Previous Button -->
    <button
      type="button"
      @click="onPageChange(currentPage - 1)"
      :disabled="currentPage <= 1"
      class="px-2.5 sm:px-3.5 py-1.5 text-xs sm:text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-1 cursor-pointer"
      :aria-label="prevText"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      <span>{{ prevText }}</span>
    </button>

    <!-- Page Numbers & Ellipses -->
    <template v-for="(item, index) in visiblePages" :key="index">
      <span
        v-if="item === '...'"
        class="px-2 py-1 text-xs sm:text-sm font-semibold text-slate-400 dark:text-slate-500 select-none"
      >
        ...
      </span>
      <button
        v-else
        type="button"
        @click="onPageChange(item)"
        :class="[
          'px-2.5 sm:px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all shadow-sm cursor-pointer',
          item === currentPage
            ? 'bg-navy text-white shadow-md font-bold border border-navy'
            : 'border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700'
        ]"
      >
        {{ item }}
      </button>
    </template>

    <!-- Next Button -->
    <button
      type="button"
      @click="onPageChange(currentPage + 1)"
      :disabled="currentPage >= totalPages"
      class="px-2.5 sm:px-3.5 py-1.5 text-xs sm:text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-1 cursor-pointer"
      :aria-label="nextText"
    >
      <span>{{ nextText }}</span>
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
    default: 1
  },
  totalPages: {
    type: Number,
    required: true,
    default: 1
  },
  prevText: {
    type: String,
    default: 'Prev'
  },
  nextText: {
    type: String,
    default: 'Next'
  }
})

const emit = defineEmits(['change', 'update:currentPage'])

const visiblePages = computed(() => {
  const total = Number(props.totalPages) || 1
  const current = Number(props.currentPage) || 1

  if (total <= 6) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const pages = []
  const initialPages = [1, 2]
  const finalPages = [total - 1, total]

  const middlePages = []
  for (let p = current - 1; p <= current + 1; p++) {
    if (p > 2 && p < total - 1) {
      middlePages.push(p)
    }
  }

  pages.push(...initialPages)

  if (middlePages.length > 0) {
    if (middlePages[0] > 3) {
      pages.push('...')
    } else if (middlePages[0] === 3) {
      pages.push(3)
    }
    pages.push(...middlePages)
    if (middlePages[middlePages.length - 1] < total - 2) {
      pages.push('...')
    } else if (middlePages[middlePages.length - 1] === total - 2) {
      pages.push(total - 2)
    }
  } else {
    if (current <= 2) {
      if (current === 2 && 3 < total - 1) {
        pages.push(3)
        if (4 < total - 1) pages.push('...')
      } else {
        pages.push('...')
      }
    } else if (current >= total - 1) {
      if (current === total - 1 && total - 2 > 2) {
        if (total - 3 > 2) pages.push('...')
        pages.push(total - 2)
      } else {
        pages.push('...')
      }
    }
  }

  pages.push(...finalPages)

  const result = []
  pages.forEach(p => {
    if (p === '...' || !result.includes(p)) {
      result.push(p)
    }
  })

  return result
})

const onPageChange = (page) => {
  if (typeof page !== 'number') return
  if (page < 1 || page > props.totalPages || page === props.currentPage) return
  emit('change', page)
  emit('update:currentPage', page)
}
</script>
