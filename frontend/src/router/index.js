import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: () => import('@/views/Landing.vue'),
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '',
    component: () => import('@/layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard.vue'),
      },
      {
        path: '/customers',
        name: 'Customers',
        component: () => import('@/views/Customers.vue'),
        alias: '/dashboard/customers',
      },
      {
        path: '/customers/create',
        name: 'CustomerCreate',
        component: () => import('@/views/CustomerForm.vue'),
      },
      {
        path: '/customers/:id/edit',
        name: 'CustomerEdit',
        component: () => import('@/views/CustomerForm.vue'),
      },
      {
        path: '/customers/:id',
        name: 'CustomerDetail',
        component: () => import('@/views/CustomerDetail.vue'),
      },
      {
        path: '/areas',
        name: 'Areas',
        component: () => import('@/views/Areas.vue'),
        alias: '/dashboard/areas',
      },
      {
        path: '/areas/create',
        name: 'AreaCreate',
        component: () => import('@/views/AreaForm.vue'),
      },
      {
        path: '/areas/:id/edit',
        name: 'AreaEdit',
        component: () => import('@/views/AreaForm.vue'),
      },
      {
        path: '/sales',
        name: 'Sales',
        component: () => import('@/views/Sales.vue'),
        alias: '/dashboard/sales',
      },
      {
        path: '/sales/create',
        name: 'SalesCreate',
        component: () => import('@/views/SalesForm.vue'),
      },
      {
        path: '/sales/:id/edit',
        name: 'SalesEdit',
        component: () => import('@/views/SalesForm.vue'),
      },
      {
        path: '/settings',
        name: 'Settings',
        component: () => import('@/views/Settings.vue'),
        alias: '/dashboard/settings',
      },
      {
        path: '/broadcast-email',
        name: 'BroadcastEmail',
        component: () => import('@/views/BroadcastEmail.vue'),
        alias: '/dashboard/broadcast-email',
      },
      {
        path: '/broadcast-email/drafts',
        name: 'BroadcastEmailDrafts',
        component: () => import('@/views/BroadcastEmailDrafts.vue'),
      },
      {
        path: '/broadcast-email/history',
        name: 'BroadcastEmailHistory',
        component: () => import('@/views/BroadcastEmailHistory.vue'),
      },
      {
        path: '/invoices',
        name: 'Invoices',
        component: () => import('@/views/Invoices.vue'),
        alias: '/dashboard/invoices',
      },
      {
        path: '/invoices/create',
        name: 'InvoiceCreate',
        component: () => import('@/views/InvoiceForm.vue'),
      },
      {
        path: '/invoices/:id',
        name: 'InvoiceDetail',
        component: () => import('@/views/InvoiceDetail.vue'),
      },
      {
        path: '/invoices/:id/edit',
        name: 'InvoiceEdit',
        component: () => import('@/views/InvoiceForm.vue'),
      },
      {
        path: '/calendar',
        name: 'Calendar',
        component: () => import('@/views/Calendar.vue'),
        alias: '/dashboard/calendar',
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isGuestOnly = to.matched.some(record => record.meta.guest)

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (isGuestOnly && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

router.onError((error, to) => {
  if (
    error.message?.includes('Failed to fetch dynamically imported module') ||
    error.message?.includes('Importing a module script failed')
  ) {
    if (to?.fullPath) {
      window.location.href = to.fullPath
    } else {
      window.location.reload()
    }
  }
})

export default router
