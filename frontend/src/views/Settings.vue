<template>
  <div class="font-sans pb-12">
    <h1 class="text-2xl lg:text-3xl font-bold text-navy tracking-tight mb-6 lg:mb-8">{{ $t('settings.title') }}</h1>

    <div class="card max-w-3xl">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-navy"></div>
        <p class="mt-2 text-slate-600">{{ $t('settings.loading') }}</p>
      </div>

      <form v-else @submit.prevent="saveSettings(true)" class="space-y-8">
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-sky-900">
          <p class="text-sm">
            <strong>{{ $t('settings.note') }}</strong> {{ $t('settings.noteText') }}
          </p>
        </div>

        <!-- Section 1: SMTP Outbound Email -->
        <div>
          <h2 class="text-lg font-bold text-navy mb-4 pb-2 border-b border-stone flex items-center justify-between">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
              </svg>
              {{ $t('settings.smtpTitle') }}
            </span>

            <button
              type="button"
              @click="handleTestSmtp"
              class="btn bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 text-xs px-3 py-1.5 font-medium"
              :disabled="testingSmtp"
            >
              <svg v-if="testingSmtp" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-amber-800 inline" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ $t('settings.testSmtp') }}
            </button>
          </h2>

          <div class="space-y-4">
            <div>
              <label class="label">
                {{ $t('settings.mailServer') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
              </label>
              <input
                v-model="form.mail_host"
                type="text"
                placeholder="mail.ecogreen.id / smtp.gmail.com"
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
                  <option value="tls">TLS (Port 587)</option>
                  <option value="ssl">SSL (Port 465)</option>
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
                placeholder="admin02@ecogreen.id"
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
                placeholder="Password Email SMTP"
                class="input"
                required
              />
              <p class="text-xs text-slate-500 mt-1">{{ $t('settings.smtpPasswordHelp') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="label">
                  {{ $t('settings.fromAddress') }} <span class="text-rose-500">{{ $t('settings.required') }}</span>
                </label>
                <input
                  v-model="form.mail_from_address"
                  type="email"
                  placeholder="admin02@ecogreen.id"
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
                  placeholder="Ecogreen Sales"
                  class="input"
                  required
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Section 2: IMAP Inbound Email Auto-Sync -->
        <div class="pt-4 border-t border-stone">
          <div class="flex items-center justify-between mb-4 pb-2 border-b border-stone">
            <h2 class="text-lg font-bold text-navy flex items-center">
              <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              {{ $t('settings.imapTitle') }}
            </h2>

            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.is_imap_enabled" class="sr-only peer" />
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              <span class="ml-2 text-sm font-medium text-slate-700">{{ $t('settings.enableImap') }}</span>
            </label>
          </div>

          <div v-if="form.is_imap_enabled" class="space-y-4">
            <div>
              <label class="label">{{ $t('settings.imapHost') }}</label>
              <input
                v-model="form.imap_host"
                type="text"
                placeholder="mail.ecogreen.id / imap.gmail.com"
                class="input"
              />
              <p class="text-xs text-slate-500 mt-1">{{ $t('settings.imapHostExample') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="label">{{ $t('settings.imapPort') }}</label>
                <input
                  v-model.number="form.imap_port"
                  type="number"
                  placeholder="993"
                  class="input"
                />
              </div>

              <div>
                <label class="label">{{ $t('settings.encryption') }}</label>
                <select v-model="form.imap_encryption" class="input">
                  <option value="ssl">SSL (Port 993)</option>
                  <option value="tls">TLS (Port 143/993)</option>
                  <option value="none">{{ $t('settings.none') }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="label">{{ $t('settings.imapUsername') }}</label>
              <input
                v-model="form.imap_username"
                type="text"
                placeholder="admin02@ecogreen.id"
                class="input"
              />
            </div>

            <div>
              <label class="label">{{ $t('settings.imapPassword') }}</label>
              <input
                v-model="form.imap_password"
                type="password"
                placeholder="Password Email IMAP"
                class="input"
              />
              <p class="text-xs text-slate-500 mt-1">{{ $t('settings.imapPasswordHelp') }}</p>
            </div>

            <!-- Action buttons for IMAP Test & Sync -->
            <div class="bg-slate-50 p-4 rounded-xl flex flex-wrap items-center justify-between gap-3 border border-slate-200">
              <div class="text-xs text-slate-600">
                <span v-if="settings?.last_imap_sync_at">
                  {{ $t('settings.lastSync', { time: formatDateTime(settings.last_imap_sync_at) }) }}
                </span>
                <span v-else>{{ $t('settings.neverSynced') }}</span>
              </div>

              <div class="flex space-x-2">
                <button
                  type="button"
                  @click="handleTestImap"
                  class="btn bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs px-3 py-2"
                  :disabled="testingImap"
                >
                  <svg v-if="testingImap" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-slate-600 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ $t('settings.testImap') }}
                </button>

                <button
                  type="button"
                  @click="handleSyncImapNow"
                  class="btn bg-indigo-600 text-white hover:bg-indigo-700 text-xs px-3 py-2 shadow-sm"
                  :disabled="syncingImap"
                >
                  <svg v-if="syncingImap" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ $t('settings.syncNow') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-stone">
          <button type="submit" class="btn btn-gold shadow-sm px-6 py-2.5 font-semibold" :disabled="saving">
            <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
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

const { t, locale } = useI18n()
const emailSettingStore = useEmailSettingStore()
const loading = ref(false)
const saving = ref(false)
const testingSmtp = ref(false)
const testingImap = ref(false)
const syncingImap = ref(false)
const settings = ref(null)

const form = ref({
  mail_host: '',
  mail_port: 587,
  mail_username: '',
  mail_password: '',
  mail_encryption: 'tls',
  mail_from_address: '',
  mail_from_name: '',
  imap_host: '',
  imap_port: 993,
  imap_username: '',
  imap_password: '',
  imap_encryption: 'ssl',
  is_imap_enabled: false,
})

const saveSettings = async (showToast = true) => {
  saving.value = true
  try {
    if (settings.value) {
      await emailSettingStore.updateSettings(form.value)
      if (showToast) alert(t('settings.updateSuccess'))
    } else {
      await emailSettingStore.saveSettings(form.value)
      if (showToast) alert(t('settings.saveSuccess'))
    }
    await loadSettings()
  } catch (error) {
    if (showToast) alert(t('settings.saveError') + ': ' + (error.response?.data?.message || error.message))
  } finally {
    saving.value = false
  }
}

const handleTestSmtp = async () => {
  testingSmtp.value = true
  try {
    const res = await emailSettingStore.testSmtp(form.value)
    alert((res.message || t('settings.smtpTestSuccess')))
    await saveSettings(false)
  } catch (error) {
    alert(t('settings.smtpTestError') + (error.response?.data?.message || error.message))
  } finally {
    testingSmtp.value = false
  }
}

const handleTestImap = async () => {
  testingImap.value = true
  try {
    const res = await emailSettingStore.testImap(form.value)
    alert((res.message || t('settings.imapTestSuccess')))
    await saveSettings(false)
  } catch (error) {
    alert(t('settings.imapTestError') + (error.response?.data?.message || error.message))
  } finally {
    testingImap.value = false
  }
}

const handleSyncImapNow = async () => {
  syncingImap.value = true
  try {
    const res = await emailSettingStore.syncImap()
    alert(res.message || t('settings.syncSuccess'))
    await loadSettings()
  } catch (error) {
    alert(t('settings.syncError') + (error.response?.data?.message || error.message))
  } finally {
    syncingImap.value = false
  }
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-'
  const loc = locale.value === 'en' ? 'en-US' : 'id-ID'
  return new Date(dateStr).toLocaleString(loc, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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
        imap_host: data.imap_host || '',
        imap_port: data.imap_port || 993,
        imap_username: data.imap_username || '',
        imap_password: data.imap_password || '',
        imap_encryption: data.imap_encryption || 'ssl',
        is_imap_enabled: !!data.is_imap_enabled,
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
