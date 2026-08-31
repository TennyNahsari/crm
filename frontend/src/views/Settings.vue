<template>
  <div class="font-sans">
    <h1 class="text-2xl lg:text-3xl font-bold text-navy tracking-tight mb-6 lg:mb-8">{{ $t('settings.title') }}</h1>

    <div class="card max-w-2xl">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
        <p class="mt-2 text-slate-600">{{ $t('settings.loading') }}</p>
      </div>

      <form v-else @submit.prevent="saveSettings" class="space-y-6">
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-sky-900">
          <p class="text-sm">
            <strong>{{ $t('settings.note') }}</strong> {{ $t('settings.noteText') }}
          </p>
        </div>

        <div>
          <label class="label">
            {{ $t('settings.mailServer') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
          </label>
          <input
            v-model="form.mail_host"
            type="text"
            placeholder="smtp.gmail.com"
            class="input"
            required
          />
          <p class="text-xs text-slate-500 mt-1">{{ $t('settings.mailServerExample') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">
              {{ $t('settings.port') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
            </label>
            <input
              v-model.number="form.mail_port"
              type="number"
              placeholder="587"
              class="input"
              required
            />
            <p class="text-xs text-slate-500 mt-1">{{ $t('settings.portExample') }}</p>
          </div>

          <div>
            <label class="label">
              {{ $t('settings.encryption') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
            </label>
            <select v-model="form.mail_encryption" class="input" required>
              <option value="tls">{{ $t('settings.tls') }}</option>
              <option value="ssl">{{ $t('settings.ssl') }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="label">
            {{ $t('settings.emailUsername') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
          </label>
          <input
            v-model="form.mail_username"
            type="email"
            placeholder="your-email@gmail.com"
            class="input"
            required
          />
        </div>

        <div>
          <label class="label">
            {{ $t('settings.emailPassword') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
          </label>
          <input
            v-model="form.mail_password"
            type="password"
            placeholder="App Password"
            class="input"
            required
          />
          <p class="text-xs text-slate-500 mt-1">{{ $t('settings.emailPasswordHelp') }}</p>
        </div>

        <div>
          <label class="label">
            {{ $t('settings.fromAddress') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
          </label>
          <input
            v-model="form.mail_from_address"
            type="email"
            placeholder="your-email@gmail.com"
            class="input"
            required
          />
        </div>

        <div>
          <label class="label">
            {{ $t('settings.fromName') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
          </label>
          <input
            v-model="form.mail_from_name"
            type="text"
            placeholder="FlowCRM System"
            class="input"
            required
          />
          <p class="text-xs text-slate-500 mt-1">{{ $t('settings.fromNameHelp') }}</p>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-stone">
          <button type="submit" class="btn btn-gold shadow-sm" :disabled="loading">
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ settings ? $t('settings.updateSettings') : $t('settings.saveSettings') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEmailSettingStore } from '@/stores/emailSetting'

const { t } = useI18n()
const emailSettingStore = useEmailSettingStore()
const loading = ref(false)
const settings = ref(null)

const form = ref({
  mail_host: '',
  mail_port: 587,
  mail_username: '',
  mail_password: '',
  mail_encryption: 'tls',
  mail_from_address: '',
  mail_from_name: '',
})

const saveSettings = async () => {
  try {
    if (settings.value) {
      await emailSettingStore.updateSettings(form.value)
      alert(t('settings.updateSuccess'))
    } else {
      await emailSettingStore.saveSettings(form.value)
      alert(t('settings.saveSuccess'))
    }
    await loadSettings()
  } catch (error) {
    alert(t('settings.saveError') + ': ' + (error.response?.data?.message || error.message))
  }
}

const loadSettings = async () => {
  loading.value = true
  try {
    const data = await emailSettingStore.fetchSettings()
    if (data) {
      settings.value = data
      form.value = {
        mail_host: data.mail_host || '',
        mail_port: data.mail_port || 587,
        mail_username: data.mail_username || '',
        mail_password: data.mail_password || '',
        mail_encryption: data.mail_encryption || 'tls',
        mail_from_address: data.mail_from_address || '',
        mail_from_name: data.mail_from_name || '',
      }
    }
  } catch (error) {
    // No settings yet
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>
