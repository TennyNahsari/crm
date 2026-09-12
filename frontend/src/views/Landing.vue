<template>
  <div class="min-h-screen bg-[#F8F6F2] font-sans text-slate-800 flex flex-col selection:bg-gold selection:text-white">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-navy/95 backdrop-blur-md border-b border-navy-light text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="p-2 bg-white/10 rounded-xl group-hover:bg-white/20 transition-all">
            <img src="/logo.png" alt="FlowCRM Logo" class="h-8 w-auto" />
          </div>
          <div>
            <span class="font-serif font-bold italic text-2xl tracking-wide text-white block leading-none">FlowCRM</span>
            <span class="text-[10px] font-sans text-gold uppercase tracking-widest block mt-0.5">Management Portal</span>
          </div>
        </router-link>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-white/80">
          <a href="#customers-db" class="hover:text-gold transition-colors">{{ $t('landing.nav.customersDb') }}</a>
          <a href="#comm-history" class="hover:text-gold transition-colors">{{ $t('landing.nav.commHistory') }}</a>
          <a href="#ai-prediction" class="hover:text-gold transition-colors">{{ $t('landing.nav.aiPrediction') }}</a>
          <a href="#broadcast-email" class="hover:text-gold transition-colors">{{ $t('landing.nav.broadcastEmail') }}</a>
          <a href="#invoices" class="hover:text-gold transition-colors">{{ $t('landing.nav.invoices') }}</a>
          <a href="#calendar" class="hover:text-gold transition-colors">{{ $t('landing.nav.calendar') }}</a>
        </nav>

        <!-- Right Controls: Language Switcher & Auth CTA -->
        <div class="hidden md:flex items-center space-x-4">
          <!-- Language Switcher -->
          <div class="flex items-center bg-white/10 p-1 rounded-lg border border-white/20">
            <button
              @click="changeLanguage('en')"
              class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
              :class="currentLocale === 'en' ? 'bg-gold text-white shadow-sm' : 'text-white/70 hover:text-white'"
            >
              EN
            </button>
            <button
              @click="changeLanguage('id')"
              class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
              :class="currentLocale === 'id' ? 'bg-gold text-white shadow-sm' : 'text-white/70 hover:text-white'"
            >
              ID
            </button>
          </div>

          <router-link
            v-if="isAuthenticated"
            to="/dashboard"
            class="btn bg-gold hover:bg-gold-dark text-white font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all hover:scale-[1.02]"
          >
            {{ $t('landing.goToDashboard') }} ➔
          </router-link>
          <router-link
            v-else
            to="/login"
            class="btn bg-white/10 hover:bg-white/20 text-white font-semibold px-5 py-2.5 rounded-xl border border-white/20 transition-all hover:scale-[1.02]"
          >
            {{ $t('landing.signIn') }}
          </router-link>
        </div>

        <!-- Mobile Menu Toggle -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden text-white/80 hover:text-white p-2"
          aria-label="Toggle Navigation"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Navigation Drawer -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-navy border-b border-navy-light px-4 pt-2 pb-6 space-y-3">
        <a @click="mobileMenuOpen = false" href="#customers-db" class="block text-sm text-white/80 hover:text-gold py-2 border-b border-white/5">{{ $t('landing.nav.customersDb') }}</a>
        <a @click="mobileMenuOpen = false" href="#comm-history" class="block text-sm text-white/80 hover:text-gold py-2 border-b border-white/5">{{ $t('landing.nav.commHistory') }}</a>
        <a @click="mobileMenuOpen = false" href="#ai-prediction" class="block text-sm text-white/80 hover:text-gold py-2 border-b border-white/5">{{ $t('landing.nav.aiPrediction') }}</a>
        <a @click="mobileMenuOpen = false" href="#broadcast-email" class="block text-sm text-white/80 hover:text-gold py-2 border-b border-white/5">{{ $t('landing.nav.broadcastEmail') }}</a>
        <a @click="mobileMenuOpen = false" href="#invoices" class="block text-sm text-white/80 hover:text-gold py-2 border-b border-white/5">{{ $t('landing.nav.invoices') }}</a>
        <a @click="mobileMenuOpen = false" href="#calendar" class="block text-sm text-white/80 hover:text-gold py-2 border-b border-white/5">{{ $t('landing.nav.calendar') }}</a>

        <div class="pt-3 flex items-center justify-between">
          <div class="flex items-center bg-white/10 p-1 rounded-lg border border-white/20">
            <button
              @click="changeLanguage('en')"
              class="px-3 py-1 text-xs font-semibold rounded-md transition-all"
              :class="currentLocale === 'en' ? 'bg-gold text-white shadow-sm' : 'text-white/70'"
            >
              EN
            </button>
            <button
              @click="changeLanguage('id')"
              class="px-3 py-1 text-xs font-semibold rounded-md transition-all"
              :class="currentLocale === 'id' ? 'bg-gold text-white shadow-sm' : 'text-white/70'"
            >
              ID
            </button>
          </div>

          <router-link
            v-if="isAuthenticated"
            to="/dashboard"
            class="btn bg-gold text-white font-semibold text-xs px-4 py-2 rounded-lg"
          >
            {{ $t('landing.goToDashboard') }}
          </router-link>
          <router-link
            v-else
            to="/login"
            class="btn bg-white/20 text-white font-semibold text-xs px-4 py-2 rounded-lg"
          >
            {{ $t('landing.signIn') }}
          </router-link>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-navy via-navy-dark to-[#132A46] text-white pt-16 pb-24 lg:pt-24 lg:pb-36 overflow-hidden">
      <!-- Glow background circles -->
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-gold/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute top-1/2 -right-24 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <!-- AI Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-gold/30 text-gold text-xs font-semibold uppercase tracking-wider mb-8 backdrop-blur-sm animate-pulse">
          <span class="w-2 h-2 rounded-full bg-gold"></span>
          {{ $t('landing.badge') }}
        </div>

        <!-- Headline -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold font-serif tracking-tight text-white max-w-4xl mx-auto leading-tight">
          {{ $t('landing.heroTitle') }}
        </h1>

        <!-- Subtitle -->
        <p class="mt-6 text-base sm:text-lg lg:text-xl text-slate-300 max-w-3xl mx-auto font-sans leading-relaxed">
          {{ $t('landing.heroSubtitle') }}
        </p>

        <!-- CTA Action Buttons -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <router-link
            v-if="isAuthenticated"
            to="/dashboard"
            class="w-full sm:w-auto btn bg-gold hover:bg-gold-dark text-white font-bold text-base px-8 py-4 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:-translate-y-0.5 inline-flex items-center justify-center"
          >
            {{ $t('landing.goToDashboard') }} ➔
          </router-link>
          <router-link
            v-else
            to="/login"
            class="w-full sm:w-auto btn bg-gold hover:bg-gold-dark text-white font-bold text-base px-8 py-4 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:-translate-y-0.5 inline-flex items-center justify-center"
          >
            {{ $t('landing.getStarted') }} ➔
          </router-link>

          <a
            href="#features"
            class="w-full sm:w-auto btn bg-white/10 hover:bg-white/20 text-white font-semibold text-base px-8 py-4 rounded-xl border border-white/20 transition-all inline-flex items-center justify-center"
          >
            {{ $t('landing.learnMore') }}
          </a>
        </div>

        <!-- App Stats Counter Bar -->
        <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4 max-w-5xl mx-auto">
          <div class="p-6 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 text-center">
            <span class="text-3xl lg:text-4xl font-extrabold text-gold font-serif block">1,200+</span>
            <span class="text-xs text-slate-300 mt-1 block font-medium">{{ $t('landing.stats.customers') }}</span>
          </div>

          <div class="p-6 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 text-center">
            <span class="text-3xl lg:text-4xl font-extrabold text-emerald-400 font-serif block">94.8%</span>
            <span class="text-xs text-slate-300 mt-1 block font-medium">{{ $t('landing.stats.accuracy') }}</span>
          </div>

          <div class="p-6 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 text-center">
            <span class="text-3xl lg:text-4xl font-extrabold text-sky-400 font-serif block">5,000+</span>
            <span class="text-xs text-slate-300 mt-1 block font-medium">{{ $t('landing.stats.invoices') }}</span>
          </div>

          <div class="p-6 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 text-center">
            <span class="text-3xl lg:text-4xl font-extrabold text-amber-400 font-serif block">99.2%</span>
            <span class="text-xs text-slate-300 mt-1 block font-medium">{{ $t('landing.stats.deliveryRate') }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Feature Grid Section -->
    <section id="features" class="py-20 lg:py-28 bg-[#F8F6F2]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <span class="text-xs font-bold text-gold uppercase tracking-widest block mb-2">{{ $t('landing.nav.features') }}</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-navy font-serif tracking-tight">
            Designed for Modern Sales & Customer Teams
          </h2>
          <p class="text-slate-600 text-sm sm:text-base mt-3">
            Streamline your customer acquisition pipeline, automate communications, and make data-driven decisions.
          </p>
        </div>

        <!-- Feature 1: Customer Database 360° & Pipeline -->
        <div id="customers-db" class="mb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center bg-white p-8 lg:p-12 rounded-3xl border border-stone/80 shadow-card">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center font-bold mb-6">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="text-2xl lg:text-3xl font-bold text-navy font-serif">{{ $t('landing.sections.customerDbTitle') }}</h3>
            <p class="text-slate-600 text-sm lg:text-base mt-3 leading-relaxed">
              {{ $t('landing.sections.customerDbDesc') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-700 font-medium">
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.customerDbFeature1') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.customerDbFeature2') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.customerDbFeature3') }}
              </li>
            </ul>
          </div>
          <div class="bg-gradient-to-br from-slate-900 to-navy p-6 rounded-2xl text-white shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
              <span class="text-xs font-bold text-sky-400 uppercase tracking-wider">360° Customer Profiles</span>
              <span class="text-[11px] bg-blue-500/20 text-blue-300 px-2.5 py-0.5 rounded-full font-semibold">Active Directory</span>
            </div>
            <div class="space-y-3 text-xs">
              <div class="p-3.5 bg-white/10 rounded-xl space-y-2">
                <div class="flex justify-between items-start">
                  <div>
                    <span class="font-bold text-sm block text-white">PT Sinar Mas Utama</span>
                    <span class="text-slate-300 text-[11px]">PIC: Budi Santoso (Director)</span>
                  </div>
                  <span class="bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded text-[10px]">Hot Lead</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-300 border-t border-white/10 pt-2">
                  <span>Area: Jakarta Selatan</span>
                  <span>Sales: Andi Pratama</span>
                  <span class="text-gold font-semibold">Score: 94%</span>
                </div>
              </div>
              <div class="p-3 bg-white/10 rounded-xl space-y-2">
                <div class="flex justify-between items-center text-[11px]">
                  <span class="font-semibold">Deal Pipeline Stages</span>
                  <span class="text-emerald-400">Kanban Board Sync</span>
                </div>
                <div class="grid grid-cols-4 gap-1.5 text-[10px] text-center font-medium">
                  <div class="bg-blue-500/20 text-blue-300 p-1.5 rounded">Lead (14)</div>
                  <div class="bg-amber-500/20 text-amber-300 p-1.5 rounded">Proposal (6)</div>
                  <div class="bg-purple-500/20 text-purple-300 p-1.5 rounded">Nego (3)</div>
                  <div class="bg-emerald-500/20 text-emerald-300 p-1.5 rounded">Won (24)</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Feature 2: Communication History & Timeline -->
        <div id="comm-history" class="mb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center bg-white p-8 lg:p-12 rounded-3xl border border-stone/80 shadow-card">
          <div class="order-2 lg:order-1 bg-gradient-to-br from-indigo-950 via-navy to-slate-900 p-6 rounded-2xl text-white shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
              <span class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Interaction Stream Timeline</span>
              <span class="text-[11px] bg-indigo-500/20 text-indigo-200 px-2.5 py-0.5 rounded-full font-semibold">Realtime Log</span>
            </div>
            <div class="space-y-3 text-xs">
              <div class="p-3 bg-white/10 rounded-xl flex items-start gap-3">
                <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✉</div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center">
                    <span class="font-bold text-white text-[11px]">Outbound Email Sent</span>
                    <span class="text-[10px] text-slate-400">10m ago</span>
                  </div>
                  <p class="text-slate-300 text-[11px] truncate">Re: Quotation Proposal Q4 License Agreement</p>
                </div>
              </div>
              <div class="p-3 bg-white/10 rounded-xl flex items-start gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">💬</div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center">
                    <span class="font-bold text-white text-[11px]">WhatsApp Follow-up Log</span>
                    <span class="text-[10px] text-slate-400">2h ago</span>
                  </div>
                  <p class="text-slate-300 text-[11px] truncate">Client confirmed requirement approval for 50 user seats.</p>
                </div>
              </div>
              <div class="p-3 bg-white/10 rounded-xl flex items-start gap-3">
                <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">📅</div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-center">
                    <span class="font-bold text-white text-[11px]">Online Demo Meeting</span>
                    <span class="text-[10px] text-slate-400">Yesterday</span>
                  </div>
                  <p class="text-slate-300 text-[11px] truncate">Product demonstration completed with IT Manager & VP.</p>
                </div>
              </div>
            </div>
          </div>

          <div class="order-1 lg:order-2">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold mb-6">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <h3 class="text-2xl lg:text-3xl font-bold text-navy font-serif">{{ $t('landing.sections.commHistoryTitle') }}</h3>
            <p class="text-slate-600 text-sm lg:text-base mt-3 leading-relaxed">
              {{ $t('landing.sections.commHistoryDesc') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-700 font-medium">
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.commHistoryFeature1') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.commHistoryFeature2') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.commHistoryFeature3') }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Feature 3: AI Prediction -->
        <div id="ai-prediction" class="mb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center bg-white p-8 lg:p-12 rounded-3xl border border-stone/80 shadow-card">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-gold/10 text-gold flex items-center justify-center font-bold mb-6">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-2xl lg:text-3xl font-bold text-navy font-serif">{{ $t('landing.sections.aiTitle') }}</h3>
            <p class="text-slate-600 text-sm lg:text-base mt-3 leading-relaxed">
              {{ $t('landing.sections.aiDesc') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-700 font-medium">
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.aiFeature1') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.aiFeature2') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.aiFeature3') }}
              </li>
            </ul>
          </div>
          <div class="bg-gradient-to-br from-navy to-navy-dark p-6 rounded-2xl text-white shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
              <span class="text-xs font-bold text-gold uppercase tracking-wider">AI Potential Model</span>
              <span class="text-[11px] bg-emerald-500/20 text-emerald-300 px-2.5 py-0.5 rounded-full font-semibold">Model Trained ✓</span>
            </div>
            <div class="space-y-3 text-xs">
              <div class="p-3 bg-white/10 rounded-xl flex justify-between items-center">
                <div>
                  <span class="font-bold block text-sm">PT Maju Sejahtera</span>
                  <span class="text-slate-300">Score Rank #1</span>
                </div>
                <span class="text-lg font-bold text-gold">98.4 %</span>
              </div>
              <div class="p-3 bg-white/10 rounded-xl flex justify-between items-center">
                <div>
                  <span class="font-bold block text-sm">CV Ecogreen Utama</span>
                  <span class="text-slate-300">Score Rank #2</span>
                </div>
                <span class="text-lg font-bold text-emerald-400">92.1 %</span>
              </div>
              <div class="p-3 bg-white/10 rounded-xl flex justify-between items-center">
                <div>
                  <span class="font-bold block text-sm">PT Teknologi Nusantara</span>
                  <span class="text-slate-300">Score Rank #3</span>
                </div>
                <span class="text-lg font-bold text-sky-400">87.5 %</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Feature 2: Broadcast Email Engine -->
        <div id="broadcast-email" class="mb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center bg-white p-8 lg:p-12 rounded-3xl border border-stone/80 shadow-card">
          <div class="order-2 lg:order-1 bg-gradient-to-br from-sky-900 to-navy p-6 rounded-2xl text-white shadow-xl">
            <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
              <span class="text-xs font-bold text-sky-300 uppercase tracking-wider">Broadcast Queue & IMAP Sync</span>
              <span class="text-[11px] bg-sky-500/20 text-sky-200 px-2.5 py-0.5 rounded-full font-semibold">SMTP Ready</span>
            </div>
            <div class="space-y-3 text-xs">
              <div class="p-3 bg-white/10 rounded-xl space-y-1">
                <span class="text-gold font-bold block">Subject: Special Offer Q4 Software License</span>
                <span class="text-slate-300 block">Filtered: All Companies in Area Jakarta Pusat</span>
                <span class="text-[10px] text-emerald-400 block font-semibold">Status: Sent (48/48 emails)</span>
              </div>
              <div class="p-3 bg-white/10 rounded-xl flex justify-between items-center">
                <span>IMAP Inbound Sync</span>
                <span class="text-emerald-300 font-semibold">Auto-Synced 2 mins ago</span>
              </div>
            </div>
          </div>

          <div class="order-1 lg:order-2">
            <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-600 flex items-center justify-center font-bold mb-6">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-2xl lg:text-3xl font-bold text-navy font-serif">{{ $t('landing.sections.emailTitle') }}</h3>
            <p class="text-slate-600 text-sm lg:text-base mt-3 leading-relaxed">
              {{ $t('landing.sections.emailDesc') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-700 font-medium">
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.emailFeature1') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.emailFeature2') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.emailFeature3') }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Feature 3 & 4 Grid: Invoices & Calendar -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Invoices -->
          <div id="invoices" class="bg-white p-8 rounded-3xl border border-stone/80 shadow-card">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold mb-6">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-navy font-serif">{{ $t('landing.sections.invoiceTitle') }}</h3>
            <p class="text-slate-600 text-sm mt-3 leading-relaxed">
              {{ $t('landing.sections.invoiceDesc') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-700 font-medium">
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.invoiceFeature1') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.invoiceFeature2') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.invoiceFeature3') }}
              </li>
            </ul>
          </div>

          <!-- Calendar -->
          <div id="calendar" class="bg-white p-8 rounded-3xl border border-stone/80 shadow-card">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold mb-6">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-navy font-serif">{{ $t('landing.sections.calendarTitle') }}</h3>
            <p class="text-slate-600 text-sm mt-3 leading-relaxed">
              {{ $t('landing.sections.calendarDesc') }}
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-700 font-medium">
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.calendarFeature1') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.calendarFeature2') }}
              </li>
              <li class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>
                {{ $t('landing.sections.calendarFeature3') }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Call To Action Banner -->
    <section class="py-16 lg:py-24 bg-gradient-to-r from-navy via-navy-dark to-navy text-white text-center relative overflow-hidden">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <h2 class="text-3xl sm:text-4xl font-bold font-serif">{{ $t('landing.cta.title') }}</h2>
        <p class="mt-4 text-slate-300 text-base sm:text-lg max-w-2xl mx-auto">
          {{ $t('landing.cta.subtitle') }}
        </p>
        <div class="mt-8">
          <router-link
            v-if="isAuthenticated"
            to="/dashboard"
            class="btn bg-gold hover:bg-gold-dark text-white font-bold text-base px-9 py-4 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:scale-105 inline-flex items-center gap-2"
          >
            {{ $t('landing.cta.button') }} ➔
          </router-link>
          <router-link
            v-else
            to="/login"
            class="btn bg-gold hover:bg-gold-dark text-white font-bold text-base px-9 py-4 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:scale-105 inline-flex items-center gap-2"
          >
            {{ $t('landing.cta.button') }} ➔
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-navy-dark border-t border-white/10 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-3">
          <img src="/logo.png" alt="Logo" class="h-8 w-auto" />
          <div>
            <span class="font-serif font-bold italic text-xl text-white">FlowCRM</span>
            <p class="text-xs text-white/50">{{ $t('landing.footer.tagline') }}</p>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs text-white/70">
          <a href="#customers-db" class="hover:text-gold transition-colors">{{ $t('landing.nav.customersDb') }}</a>
          <a href="#comm-history" class="hover:text-gold transition-colors">{{ $t('landing.nav.commHistory') }}</a>
          <a href="#ai-prediction" class="hover:text-gold transition-colors">{{ $t('landing.nav.aiPrediction') }}</a>
          <a href="#broadcast-email" class="hover:text-gold transition-colors">{{ $t('landing.nav.broadcastEmail') }}</a>
          <a href="#invoices" class="hover:text-gold transition-colors">{{ $t('landing.nav.invoices') }}</a>
          <a href="#calendar" class="hover:text-gold transition-colors">{{ $t('landing.nav.calendar') }}</a>
        </div>

        <div class="text-xs text-white/50">
          {{ $t('landing.footer.copyright') }}
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const { locale } = useI18n()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const currentLocale = computed(() => locale.value)

const changeLanguage = (lang) => {
  locale.value = lang
  localStorage.setItem('locale', lang)
}
</script>
