<template>
  <div class="min-h-screen bg-gray-50 flex">

    <!-- ── Sidebar Overlay (mobile) ── -->
    <Transition name="sidebar-overlay">
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
      />
    </Transition>

    <!-- ── Sidebar ── -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 flex flex-col',
        'bg-gradient-to-b from-green-900 via-green-800 to-green-700 text-white',
        'shadow-2xl transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0 lg:static lg:inset-0',
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-5 border-b border-green-600/50 shrink-0">
        <Link :href="route('dashboard')" class="flex items-center space-x-2.5 group">
          <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
              <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div>
            <div class="text-sm font-bold tracking-wide leading-none">SIMRAN</div>
            <div class="text-xs text-green-300 leading-none mt-0.5">UNISYA</div>
          </div>
        </Link>
        <button @click="sidebarOpen = false" class="lg:hidden text-green-200 hover:text-white transition p-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <!-- Dashboard (all roles) -->
        <NavItem :href="route('dashboard')" label="Dashboard">
          <template #icon>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </template>
        </NavItem>

        <!-- ── SUPERADMIN ── -->
        <template v-if="user.role_value === 'superadmin'">
          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Anggaran</p>
          </div>
          <NavItem :href="route('admin.budget-allocations.index')" label="Alokasi Anggaran">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </template>
          </NavItem>
          <NavItem :href="route('admin.disbursements.index')" label="Pencairan Anggaran">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
              </svg>
            </template>
          </NavItem>

          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Proposal</p>
          </div>
          <NavItem :href="route('admin.proposals.index')" label="Manajemen Proposal">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </template>
          </NavItem>

          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Sistem</p>
          </div>
          <NavItem :href="route('admin.users.index')" label="Pengguna">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </template>
          </NavItem>
          <NavItem :href="route('admin.reports.index')" label="Laporan Global">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </template>
          </NavItem>
          <NavItem :href="route('admin.settings.index')" label="Pengaturan">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </template>
          </NavItem>
        </template>

        <!-- ── PIC ── -->
        <template v-if="user.role_value === 'pic'">
          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Kegiatan Saya</p>
          </div>
          <NavItem :href="route('pic.disbursements.index')" label="Pencairan Saya">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </template>
          </NavItem>
        </template>

        <!-- ── AUDITOR ── -->
        <template v-if="user.role_value === 'auditor'">
          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Audit</p>
          </div>
          <NavItem :href="route('auditor.budget-allocations.index')" label="Alokasi Anggaran">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </template>
          </NavItem>
          <NavItem :href="route('auditor.disbursements.index')" label="Pencairan">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </template>
          </NavItem>
          <NavItem :href="route('auditor.reports.index')" label="Laporan">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </template>
          </NavItem>
        </template>

        <!-- ── APPLICANT (NEW) ── -->
        <template v-if="user.role_value === 'applicant'">
          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Proposal Saya</p>
          </div>
          <NavItem :href="route('applicant.proposals.index')" label="Daftar Proposal">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </template>
          </NavItem>
          <NavItem :href="route('applicant.proposals.create')" label="Ajukan Proposal">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </template>
          </NavItem>
        </template>

        <!-- ── APPROVER (NEW) ── -->
        <template v-if="user.role_value === 'approver'">
          <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-green-300 uppercase tracking-wider">Tugas Saya</p>
          </div>
          <NavItem :href="route('approver.proposals.index')" label="Proposal Masuk">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </template>
          </NavItem>
        </template>

      </nav>

      <!-- Sidebar Footer (Profile) -->
      <div class="shrink-0 p-3 border-t border-green-600/50">
        <Link :href="route('profile.edit')" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-white/10 transition group">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-sm font-bold shrink-0 shadow">
            {{ user.initials }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ user.name }}</p>
            <p class="text-xs text-green-300 truncate">{{ user.role }}</p>
          </div>
          <svg class="w-4 h-4 text-green-300 group-hover:text-white transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </Link>
      </div>
    </aside>

    <!-- ── Main Content ── -->
    <div class="flex-1 flex flex-col min-w-0">

      <!-- Topbar -->
      <header class="bg-white border-b border-gray-200 h-16 flex items-center px-4 sm:px-6 sticky top-0 z-30 shadow-sm">
        <button @click="sidebarOpen = true" class="lg:hidden mr-3 p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <div class="flex-1">
          <h1 class="text-base sm:text-lg font-semibold text-gray-800 truncate">{{ title }}</h1>
        </div>

        <div class="flex items-center space-x-2">
          <!-- Alert bell -->
          <div v-if="alerts.length > 0" class="relative">
            <button @click="showAlerts = !showAlerts"
                    class="relative p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold animate-pulse">
                {{ alerts.length }}
              </span>
            </button>
            <Transition name="slide-down">
              <div v-if="showAlerts"
                   v-click-outside="() => showAlerts = false"
                   class="absolute right-0 top-12 w-72 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden animate-slide-down">
                <div class="bg-amber-50 px-4 py-2.5 border-b border-amber-100">
                  <p class="text-sm font-semibold text-amber-800">⚠ Peringatan Anggaran Rendah</p>
                </div>
                <div class="max-h-64 overflow-y-auto">
                  <div v-for="a in alerts" :key="a.id"
                       class="px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ a.name }}</p>
                    <p class="text-xs text-red-600 mt-0.5">Sisa: Rp {{ fmt(a.remaining) }}</p>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Role badge -->
          <span :class="['hidden sm:inline px-2.5 py-1 rounded-full text-xs font-semibold', roleBadgeClass]">
            {{ user.role }}
          </span>

          <!-- Logout -->
          <Link :href="route('logout')" method="post" as="button"
                class="p-2 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 transition"
                title="Keluar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </Link>
        </div>
      </header>

      <!-- Flash Messages -->
      <Transition name="slide-down">
        <div v-if="$page.props.flash?.success"
             class="mx-4 sm:mx-6 mt-4 p-3.5 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center space-x-2 animate-slide-down">
          <svg class="w-4 h-4 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm font-medium">{{ $page.props.flash.success }}</span>
        </div>
      </Transition>
      <Transition name="slide-down">
        <div v-if="$page.props.flash?.error"
             class="mx-4 sm:mx-6 mt-4 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center space-x-2">
          <svg class="w-4 h-4 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm font-medium">{{ $page.props.flash.error }}</span>
        </div>
      </Transition>

      <!-- Page Content -->
      <main class="flex-1 p-4 sm:p-6 overflow-auto">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="px-6 py-3 border-t border-gray-100 bg-white">
        <p class="text-xs text-gray-400 text-center">
          SIMRAN UNISYA &copy; {{ new Date().getFullYear() }} &mdash; Universitas Islam Syarifuddin
        </p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import NavItem from '@/Components/NavItem.vue'
import { useCurrency } from '@/Composables/useCurrency'

defineProps({ title: { type: String, default: 'Dashboard' } })

const sidebarOpen = ref(false)
const showAlerts  = ref(false)
const page        = usePage()
const user        = computed(() => page.props.auth.user)

// HandleInertiaRequests shares this key as `low_budget_alerts`
// Kept backward-compat: also fall back to old `alerts` key if present
const alerts = computed(() => page.props.low_budget_alerts || page.props.alerts || [])

const { formatRp: fmt } = useCurrency()

const roleBadgeClass = computed(() => {
  const map = {
    superadmin: 'bg-purple-100 text-purple-800',
    pic:        'bg-blue-100 text-blue-800',
    auditor:    'bg-amber-100 text-amber-800',
    applicant:  'bg-teal-100 text-teal-800',   // NEW
    approver:   'bg-indigo-100 text-indigo-800', // NEW
  }
  return map[user.value?.role_value] || 'bg-gray-100 text-gray-800'
})

// v-click-outside directive (unchanged from original)
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutsideHandler = (event) => {
      if (!el.contains(event.target)) binding.value(event)
    }
    document.addEventListener('click', el._clickOutsideHandler)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutsideHandler)
  },
}
</script>