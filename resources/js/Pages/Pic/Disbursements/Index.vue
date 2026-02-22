<template>
  <AppLayout title="Pencairan Saya">
    <div class="page-header">
      <div>
        <h2 class="page-title">Pencairan Saya</h2>
        <p class="text-sm text-gray-400 mt-0.5">Daftar pencairan anggaran yang ditugaskan kepada Anda</p>
      </div>
    </div>

    <!-- Summary Banner -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
      <div class="card p-4 animate-fade-in-up">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Dana</p>
        <p class="text-lg font-bold text-gray-800 mt-1">{{ fmt(summary.total) }}</p>
      </div>
      <div class="card p-4 animate-fade-in-up" style="animation-delay:80ms">
        <p class="text-xs text-green-500 uppercase tracking-wide font-medium">Kegiatan Aktif</p>
        <p class="text-lg font-bold text-green-700 mt-1 flex items-center space-x-1.5">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
          <span>{{ summary.active_count }}</span>
        </p>
      </div>
      <div class="card p-4 animate-fade-in-up sm:block hidden" style="animation-delay:160ms">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total Kegiatan</p>
        <p class="text-lg font-bold text-gray-800 mt-1">{{ disbursements.total }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3">
        <SearchInput v-model="searchQuery" placeholder="Cari nama kegiatan..." class="flex-1" />
        <select v-model="statusFilter" class="form-input sm:w-44">
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="expired">Selesai</option>
          <option value="upcoming">Akan Datang</option>
        </select>
        <button @click="applyFilter" class="btn-primary">Cari</button>
        <button v-if="hasFilter" @click="clearFilter" class="btn-secondary">Reset</button>
      </div>
    </div>

    <!-- Cards Grid -->
    <div v-if="disbursements.data.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <Link
        v-for="(d, i) in disbursements.data"
        :key="d.id"
        :href="route('pic.disbursements.show', d.id)"
        :class="['card p-5 hover:shadow-lg transition-all duration-200 hover:-translate-y-1 group animate-fade-in-up block']"
        :style="{ animationDelay: (i * 60) + 'ms' }"
      >
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1 min-w-0 pr-2">
            <div class="flex items-center space-x-2 mb-1">
              <Badge :color="d.purpose_value === 'activity' ? 'blue' : 'teal'" class="text-xs">{{ d.purpose }}</Badge>
              <Badge :color="d.status === 'active' ? 'green' : d.status === 'expired' ? 'gray' : 'blue'" :dot="true">
                {{ d.status_label }}
              </Badge>
            </div>
            <h3 class="font-semibold text-gray-800 truncate group-hover:text-green-700 transition-colors">{{ d.name }}</h3>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>

        <!-- Meta -->
        <div class="space-y-1.5 mb-3">
          <div class="flex items-center space-x-1.5 text-xs text-gray-500">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ d.start_date }} – {{ d.end_date }}</span>
          </div>
          <div class="flex items-center space-x-1.5 text-xs text-gray-500">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
            </svg>
            <span class="truncate">{{ d.allocation_name }}</span>
          </div>
          <div v-if="d.status === 'active'" class="flex items-center space-x-1.5 text-xs text-green-600 font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
            <span>{{ d.days_remaining }} hari tersisa</span>
          </div>
        </div>

        <!-- Progress -->
        <ProgressBar :percentage="d.realization_pct" size="sm" :show-label="false" class="mb-2" />

        <!-- Financials -->
        <div class="flex justify-between items-end pt-2 border-t border-gray-50">
          <div>
            <p class="text-xs text-gray-400">Dana</p>
            <p class="text-sm font-semibold text-gray-700">{{ fmt(d.amount) }}</p>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-400">Sisa</p>
            <p :class="['text-sm font-bold', d.remaining_funds < 0 ? 'text-red-600' : 'text-green-700']">
              {{ fmt(d.remaining_funds) }}
            </p>
          </div>
        </div>

        <!-- Transaction count badge -->
        <div v-if="d.tx_count" class="mt-2 pt-2 border-t border-gray-50">
          <p class="text-xs text-gray-400">{{ d.tx_count }} transaksi · {{ d.realization_pct.toFixed(1) }}% terealisasi</p>
        </div>
      </Link>
    </div>

    <!-- Empty -->
    <div v-else class="card py-2">
      <EmptyState title="Tidak ada pencairan ditemukan" description="Belum ada pencairan yang ditugaskan kepada Anda." />
    </div>

    <Pagination :links="disbursements.links" class="mt-4" />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import SearchInput from '@/Components/SearchInput.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ disbursements: Object, filters: Object, summary: Object })
const { formatRp: fmt } = useCurrency()

const searchQuery = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')
const hasFilter = computed(() => searchQuery.value || statusFilter.value)

function applyFilter() {
  router.get(route('pic.disbursements.index'), { search: searchQuery.value, status: statusFilter.value }, { preserveState: true, replace: true })
}
function clearFilter() {
  searchQuery.value = ''; statusFilter.value = ''
  router.get(route('pic.disbursements.index'))
}
</script>
