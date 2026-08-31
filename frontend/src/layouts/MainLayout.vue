<template>
  <div class="flex h-screen bg-[#F8F6F2]">
    <!-- Desktop Sidebar -->
    <aside
      class="w-64 bg-navy text-white shadow-xl hidden lg:flex lg:flex-col justify-between z-20"
      :class="{ 'hidden': !sidebarOpen }"
    >
      <div>
        <div class="p-6 border-b border-navy-light flex flex-col items-center justify-center">
          <div class="flex items-center justify-center mb-2 p-2 bg-white/10 rounded-xl backdrop-blur-sm">
            <img src="/logo.png" alt="Logo" class="h-10 w-auto" />
          </div>
          <h1 class="font-serif font-bold italic text-2xl text-white tracking-wide mt-1">FlowCRM</h1>
          <span class="text-[11px] font-sans text-gold uppercase tracking-widest mt-0.5">Management Portal</span>
        </div>

        <nav class="mt-6 px-3 space-y-1 font-sans">
          <router-link
            to="/"
            class="flex items-center px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
            active-class="bg-gold text-white font-semibold shadow-md"
          >
            <svg class="w-5 h-5 mr-3 text-gold" :class="{ 'text-white': route.path === '/' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            {{ $t('sidebar.dashboard') }}
          </router-link>

          <router-link
            to="/customers"
            class="flex items-center px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
            active-class="bg-gold text-white font-semibold shadow-md"
          >
            <svg class="w-5 h-5 mr-3 text-gold" :class="{ 'text-white': route.path.startsWith('/customers') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ $t('sidebar.customers') }}
          </router-link>
          
          <!-- Broadcast Email Dropdown -->
          <div>
            <button
              @click="broadcastOpen = !broadcastOpen"
              class="flex items-center justify-between w-full px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
              :class="{ 'bg-white/10 text-white': broadcastOpen }"
            >
              <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ $t('sidebar.broadcastEmail') }}
              </div>
              <svg
                class="w-4 h-4 transition-transform text-white/60"
                :class="{ 'rotate-180': broadcastOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div v-show="broadcastOpen" class="mt-1 pl-4 space-y-1">
              <router-link
                to="/broadcast-email"
                exact
                class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                active-class="bg-gold/80 text-white font-medium"
              >
                {{ $t('sidebar.sendBroadcast') }}
              </router-link>
              <router-link
                to="/broadcast-email/drafts"
                class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                active-class="bg-gold/80 text-white font-medium"
              >
                {{ $t('sidebar.drafts') }}
              </router-link>
              <router-link
                to="/broadcast-email/history"
                class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                active-class="bg-gold/80 text-white font-medium"
              >
                {{ $t('sidebar.history') }}
              </router-link>
            </div>
          </div>
          
          <!-- Settings Dropdown -->
          <div>
            <button
              @click="settingsOpen = !settingsOpen"
              class="flex items-center justify-between w-full px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
              :class="{ 'bg-white/10 text-white': settingsOpen }"
            >
              <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $t('sidebar.settings') }}
              </div>
              <svg class="w-4 h-4 transition-transform text-white/60" :class="{ 'rotate-180': settingsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div v-show="settingsOpen" class="mt-1 pl-4 space-y-1">
              <router-link
                v-if="user?.role === 'admin'"
                to="/areas"
                class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                active-class="bg-gold/80 text-white font-medium"
              >
                <svg class="w-3.5 h-3.5 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $t('sidebar.areas') }}
              </router-link>
              <router-link
                v-if="user?.role === 'admin'"
                to="/sales"
                class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                active-class="bg-gold/80 text-white font-medium"
              >
                <svg class="w-3.5 h-3.5 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ $t('sidebar.sales') }}
              </router-link>
              <router-link
                to="/settings"
                class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                active-class="bg-gold/80 text-white font-medium"
              >
                <svg class="w-3.5 h-3.5 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                {{ $t('sidebar.emailConfig') }}
              </router-link>
            </div>
          </div>
        </nav>
      </div>

      <!-- Footer / Version Info -->
      <div class="p-4 border-t border-white/10 text-center">
        <p class="text-[11px] text-white/50">FlowCRM v1.0 &copy; 2026</p>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Navbar / Header -->
      <header class="bg-white shadow-card border-b border-stone/60 z-10">
        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-4">
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden text-navy hover:text-gold transition-colors p-1"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <h1 class="text-2xl font-bold text-navy font-sans tracking-tight lg:block hidden">
              {{ currentPageTitle }}
            </h1>
          </div>

          <div class="flex items-center space-x-6">
            <!-- Language Switcher -->
            <div class="flex items-center bg-stone-light p-1 rounded-lg border border-stone">
              <button
                @click="changeLanguage('en')"
                class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
                :class="currentLocale === 'en' ? 'bg-navy text-white shadow-sm' : 'text-slate-600 hover:text-navy'"
              >
                EN
              </button>
              <button
                @click="changeLanguage('id')"
                class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all"
                :class="currentLocale === 'id' ? 'bg-navy text-white shadow-sm' : 'text-slate-600 hover:text-navy'"
              >
                ID
              </button>
            </div>
            
            <!-- Profile Section -->
            <div class="flex items-center space-x-3 pl-4 border-l border-stone">
              <div class="w-9 h-9 rounded-full bg-navy text-gold flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-gold/30">
                {{ user?.name ? user.name.charAt(0).toUpperCase() : 'A' }}
              </div>
              <div class="hidden sm:block text-left">
                <span class="block text-sm font-semibold text-slate-800 leading-none">{{ user?.name || 'Administrator' }}</span>
                <span class="text-[11px] font-medium text-gold uppercase tracking-wider leading-tight">{{ user?.role || 'Admin' }}</span>
              </div>
              <button
                @click="handleLogout"
                class="ml-2 text-xs font-medium text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg border border-rose-200 transition-colors flex items-center gap-1"
                title="Logout"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                {{ $t('header.logout') }}
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Mobile Sidebar Drawer -->
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 bg-navy/60 backdrop-blur-sm z-40 lg:hidden"
        @click="sidebarOpen = false"
      >
        <aside class="w-64 bg-navy text-white h-full shadow-2xl flex flex-col justify-between" @click.stop>
          <div>
            <div class="p-6 border-b border-navy-light flex flex-col items-center justify-center">
              <div class="flex items-center justify-center mb-2 p-2 bg-white/10 rounded-xl">
                <img src="/logo.png" alt="Logo" class="h-10 w-auto" />
              </div>
              <h1 class="font-serif font-bold italic text-2xl text-white tracking-wide">FlowCRM</h1>
            </div>
            <nav class="mt-6 px-3 space-y-1">
              <router-link
                to="/"
                class="flex items-center px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
                active-class="bg-gold text-white font-semibold"
                @click="sidebarOpen = false"
              >
                <svg class="w-5 h-5 mr-3 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ $t('sidebar.dashboard') }}
              </router-link>
              <router-link
                to="/customers"
                class="flex items-center px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
                active-class="bg-gold text-white font-semibold"
                @click="sidebarOpen = false"
              >
                <svg class="w-5 h-5 mr-3 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ $t('sidebar.customers') }}
              </router-link>
              
              <!-- Broadcast Email Dropdown Mobile -->
              <div>
                <button
                  @click="broadcastOpenMobile = !broadcastOpenMobile"
                  class="flex items-center justify-between w-full px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
                  :class="{ 'bg-white/10 text-white': broadcastOpenMobile }"
                >
                  <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ $t('sidebar.broadcastEmail') }}
                  </div>
                  <svg
                    class="w-4 h-4 transition-transform text-white/60"
                    :class="{ 'rotate-180': broadcastOpenMobile }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                
                <div v-show="broadcastOpenMobile" class="mt-1 pl-4 space-y-1">
                  <router-link
                    to="/broadcast-email"
                    class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                    active-class="bg-gold/80 text-white font-medium"
                    @click="sidebarOpen = false"
                  >
                    {{ $t('sidebar.sendBroadcast') }}
                  </router-link>
                  <router-link
                    to="/broadcast-email/drafts"
                    class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                    active-class="bg-gold/80 text-white font-medium"
                    @click="sidebarOpen = false"
                  >
                    {{ $t('sidebar.drafts') }}
                  </router-link>
                  <router-link
                    to="/broadcast-email/history"
                    class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                    active-class="bg-gold/80 text-white font-medium"
                    @click="sidebarOpen = false"
                  >
                    {{ $t('sidebar.history') }}
                  </router-link>
                </div>
              </div>
              
              <!-- Settings Dropdown Mobile -->
              <div>
                <button
                  @click="settingsOpenMobile = !settingsOpenMobile"
                  class="flex items-center justify-between w-full px-4 py-3 text-white/80 hover:bg-white/10 hover:text-white rounded-lg transition-all text-sm font-medium"
                  :class="{ 'bg-white/10 text-white': settingsOpenMobile }"
                >
                  <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $t('sidebar.settings') }}
                  </div>
                  <svg class="w-4 h-4 transition-transform text-white/60" :class="{ 'rotate-180': settingsOpenMobile }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                
                <div v-show="settingsOpenMobile" class="mt-1 pl-4 space-y-1">
                  <router-link
                    v-if="user?.role === 'admin'"
                    to="/areas"
                    class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                    active-class="bg-gold/80 text-white font-medium"
                    @click="sidebarOpen = false"
                  >
                    <svg class="w-3.5 h-3.5 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $t('sidebar.areas') }}
                  </router-link>
                  <router-link
                    v-if="user?.role === 'admin'"
                    to="/sales"
                    class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                    active-class="bg-gold/80 text-white font-medium"
                    @click="sidebarOpen = false"
                  >
                    <svg class="w-3.5 h-3.5 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ $t('sidebar.sales') }}
                  </router-link>
                  <router-link
                    to="/settings"
                    class="flex items-center px-4 py-2 text-xs text-white/70 hover:bg-white/10 hover:text-white rounded-md transition-colors"
                    active-class="bg-gold/80 text-white font-medium"
                    @click="sidebarOpen = false"
                  >
                    <svg class="w-3.5 h-3.5 mr-2 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    {{ $t('sidebar.emailConfig') }}
                  </router-link>
                </div>
              </div>
            </nav>
          </div>

          <div class="p-4 border-t border-white/10 text-center">
            <p class="text-[11px] text-white/50">FlowCRM v1.0 &copy; 2026</p>
          </div>
        </aside>
      </div>

      <!-- Main Page View Container -->
      <main class="flex-1 overflow-y-auto bg-[#F8F6F2] p-4 sm:p-6 lg:p-8">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { locale } = useI18n()

const sidebarOpen = ref(false)
const settingsOpen = ref(false)
const broadcastOpen = ref(false)
const settingsOpenMobile = ref(false)
const broadcastOpenMobile = ref(false)

const user = computed(() => authStore.user)
const currentLocale = computed(() => locale.value)

const currentPageTitle = computed(() => {
  return route.name || 'FlowCRM'
})

const changeLanguage = (lang) => {
  locale.value = lang
  localStorage.setItem('locale', lang)
}

const handleLogout = async () => {
  try {
    await authStore.logout()
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    router.push('/login')
  }
}
</script>
