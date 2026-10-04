<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F8F6F2] px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-xl shadow-card border border-stone/60 p-8">
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex p-3 bg-navy/5 rounded-2xl mb-3 border border-stone hover:bg-navy/10 transition-all group" title="Kembali ke Landing Page">
          <img src="/logo.png" alt="Logo" class="h-16 w-auto mx-auto group-hover:scale-105 transition-transform" />
        </router-link>
        <h1 class="font-serif font-bold italic text-3xl text-navy tracking-wide">
          <router-link to="/" class="hover:text-gold transition-colors">FlowCRM</router-link>
        </h1>
        <p class="text-slate-500 text-sm mt-1 font-sans">Customer & Lead Management System</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5 font-sans">
        <div>
          <label class="label">
            Email Address
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            class="input"
            placeholder="admin@flowcrm.test"
          />
        </div>

        <div>
          <label class="label">
            Password
          </label>
          <input
            v-model="form.password"
            type="password"
            required
            class="input"
            placeholder="••••••••"
          />
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center cursor-pointer">
            <input
              v-model="form.remember"
              type="checkbox"
              id="remember"
              class="h-4 w-4 text-gold border-stone rounded focus:ring-gold/30 accent-gold"
            />
            <span class="ml-2 text-xs lg:text-sm text-slate-600">Remember me</span>
          </label>
        </div>

        <div v-if="error" class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full btn btn-primary py-3 font-semibold shadow-md"
        >
          {{ loading ? 'Signing in...' : 'Sign In to Dashboard' }}
        </button>
      </form>

      <div class="mt-6 pt-6 border-t border-stone text-center text-xs text-slate-500 font-sans space-y-3">
        <div>
          <router-link
            to="/"
            class="inline-flex items-center gap-1.5 text-navy hover:text-gold font-semibold transition-colors py-1.5 px-3 rounded-lg bg-stone-light hover:bg-stone border border-stone"
          >
            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Landing Page</span>
          </router-link>
        </div>

        <div class="pt-2 border-t border-stone/50 space-y-1">
          <p class="font-medium text-slate-700">Default Demo Credentials:</p>
          <p><span class="text-slate-400">Email:</span> <code class="bg-stone-light px-1.5 py-0.5 rounded text-navy font-mono text-xs">admin@flowcrm.test</code></p>
          <p><span class="text-slate-400">Password:</span> <code class="bg-stone-light px-1.5 py-0.5 rounded text-navy font-mono text-xs">password</code></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    await authStore.login(form.value)
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>
