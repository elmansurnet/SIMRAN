<template>
  <AppLayout title="Proposal Saya">
    <div class="page-header">
      <div>
        <h2 class="page-title">Proposal Saya</h2>
        <p class="text-sm text-gray-400 mt-0.5">Riwayat pengajuan proposal kegiatan Anda</p>
      </div>
      <Link :href="route('applicant.proposals.create')" class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Ajukan Proposal</span>
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5">
      <div class="flex flex-col sm:flex-row gap-3">
        <input v-model="search" type="text" class="form-input flex-1" placeholder="Cari nama atau kode..." @keyup.enter="applyFilter" />
        <select v-model="statusFilter" class="form-input sm:w-44">
          <option value="">Semua Status</option>
          <option value="draft">Menunggu Review</option>
          <option value="forwarded">Menunggu Persetujuan</option>
          <option value="approved">Disetujui</option>
          <option value="rejected">Ditolak</option>
        </select>
        <button @click="applyFilter" class="btn-primary">Cari</button>
        <button v-if="search || statusFilter" @click="clearFilter" class="btn-secondary">Reset</button>
      </div>
    </div>

    <!-- Cards Grid -->
    <div v-if="proposals.data.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="p in proposals.data" :key="p.id"
           class="card p-5 hover:shadow-md transition-shadow cursor-pointer animate-fade-in-up"
           @click="$inertia.visit(route('applicant.proposals.show', p.id))">
        <div class="flex items-start justify-between mb-3">
          <span class="font-mono text-xs text-gray-400 bg-gray-50 px-2 py-0.5 rounded">{{ p.code }}</span>
          <Badge :color="p.status_color" :dot="true">{{ p.status_label }}</Badge>
        </div>
        <h3 class="font-semibold text-gray-800 mb-1 line-clamp-2">{{ p.name }}</h3>
        <p class="text-xs text-gray-400 mb-3">{{ p.start_date }} – {{ p.end_date }}</p>
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500">Diusulkan</span>
          <span class="font-semibold text-gray-800">{{ fmt(p.proposed_amount) }}</span>
        </div>
        <div v-if="p.approved_amount" class="flex items-center justify-between text-sm mt-1">
          <span class="text-green-600">Disetujui</span>
          <span class="font-bold text-green-700">{{ fmt(p.approved_amount) }}</span>
        </div>
        <!-- Certificate download button -->
        <div v-if="p.status === 'approved'" class="mt-3 pt-3 border-t border-gray-50">
          <a :href="route('applicant.proposals.certificate', p.id)" target="_blank"
             @click.stop
             class="w-full flex items-center justify-center space-x-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 py-1.5 rounded-lg transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Unduh Pengesahan</span>
          </a>
        </div>
      </div>
    </div>

    <EmptyState v-else title="Belum ada proposal" description="Klik 'Ajukan Proposal' untuk memulai." />
    <div class="mt-4"><Pagination :links="proposals.links" /></div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ proposals: Object, filters: Object })
const { formatRp: fmt } = useCurrency()

const search       = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')

function applyFilter() {
  router.get(route('applicant.proposals.index'), { search: search.value, status: statusFilter.value }, { preserveState: true, replace: true })
}
function clearFilter() { search.value = ''; statusFilter.value = ''; router.get(route('applicant.proposals.index')) }
</script>
