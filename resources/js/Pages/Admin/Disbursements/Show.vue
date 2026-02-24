<template>
  <AppLayout :title="disbursement.name">
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
      <Link :href="route('admin.disbursements.index')" class="hover:text-green-700">Pencairan</Link>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
      <span class="text-gray-800 font-medium truncate">{{ disbursement.name }}</span>
    </nav>

    <!-- Header Card -->
    <div class="card p-6 mb-6 animate-fade-in-up">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
          <div class="flex items-center space-x-2 mb-1">
            <Badge :color="disbursement.purpose_value === 'activity' ? 'blue' : 'teal'">
              {{ disbursement.purpose }}
            </Badge>
            <!-- 4-phase badge -->
            <Badge :color="statusBadgeColor" :dot="true">{{ disbursement.status_label }}</Badge>
          </div>
          <h2 class="text-2xl font-bold text-green-900">{{ disbursement.name }}</h2>
          <p class="text-sm text-gray-500 mt-1">
            Ketua: <strong>{{ disbursement.chairperson }}</strong>
            &nbsp;·&nbsp; PIC: <strong>{{ disbursement.pic_name }}</strong>
            &nbsp;·&nbsp; {{ disbursement.start_date }} – {{ disbursement.end_date }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
          <Link :href="route('admin.disbursements.edit', disbursement.id)"
                class="btn-secondary text-sm py-1.5 px-3 flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Edit</span>
          </Link>
          <!--
            PDF report route: pic.disbursements.report
            This is still inside role:pic middleware so only PIC can open it here.
            Admin can view the page but the report link is only meaningful if they
            are also a PIC. The policy on the controller enforces this.
          -->
          <a :href="route('pic.disbursements.report', disbursement.id)" target="_blank"
             class="btn-primary text-sm py-1.5 px-3 flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak PDF</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
      <div class="card p-4 animate-fade-in-up">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Dana Pencairan</p>
        <p class="text-xl font-bold text-gray-800 mt-1">{{ fmt(disbursement.amount) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ disbursement.allocation_name }}</p>
      </div>
      <div class="card p-4 animate-fade-in-up" style="animation-delay:80ms">
        <p class="text-xs text-red-500 uppercase tracking-wide font-medium">Total Pengeluaran</p>
        <p class="text-xl font-bold text-red-600 mt-1">{{ fmt(disbursement.total_expense) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ disbursement.realization_pct?.toFixed(1) }}% terealisasi</p>
      </div>
      <div class="card p-4 animate-fade-in-up" style="animation-delay:160ms">
        <p class="text-xs text-teal-500 uppercase tracking-wide font-medium">Total Pemasukan</p>
        <p class="text-xl font-bold text-teal-600 mt-1">{{ fmt(disbursement.total_income) }}</p>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-xl p-4 animate-fade-in-up" style="animation-delay:240ms">
        <p class="text-xs text-green-600 uppercase tracking-wide font-medium">Sisa Dana</p>
        <p class="text-xl font-bold text-green-800 mt-1">{{ fmt(disbursement.remaining_funds) }}</p>
        <p v-if="disbursement.status === 'active'" class="text-xs text-green-500 mt-0.5">
          {{ disbursement.days_remaining }} hari tersisa
        </p>
      </div>
    </div>

    <!-- Realization Bar -->
    <div class="card p-5 mb-6 animate-fade-in-up" style="animation-delay:300ms">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-semibold text-green-800">Progres Realisasi</h3>
        <span :class="['text-sm font-bold',
          disbursement.realization_pct >= 90 ? 'text-red-600' :
          disbursement.realization_pct >= 70 ? 'text-amber-600' : 'text-green-700']">
          {{ disbursement.realization_pct?.toFixed(1) }}%
        </span>
      </div>
      <ProgressBar :percentage="disbursement.realization_pct" size="lg" :show-label="false" />
      <div class="flex justify-between text-xs text-gray-400 mt-2">
        <span>Rp 0</span>
        <span>{{ fmt(disbursement.amount) }}</span>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="card animate-fade-in-up" style="animation-delay:360ms">
      <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-green-800">Daftar Transaksi ({{ disbursement.transactions?.length || 0 }})</h3>
      </div>
      <div v-if="disbursement.transactions?.length" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Tanggal</Th>
              <Th>Tipe</Th>
              <Th>Keterangan</Th>
              <Th class="text-right">Jumlah</Th>
              <Th>Dibuat Oleh</Th>
              <Th>Bukti</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <tr v-for="(t, i) in disbursement.transactions" :key="t.id"
                :class="['hover:bg-green-50/40 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
              <Td class="whitespace-nowrap text-xs">{{ t.transaction_date }}</Td>
              <Td>
                <Badge :color="t.type_value === 'expense' ? 'red' : 'teal'">{{ t.type }}</Badge>
              </Td>
              <Td class="max-w-xs"><p class="truncate">{{ t.description }}</p></Td>
              <Td class="text-right font-semibold whitespace-nowrap">
                <span :class="t.type_value === 'expense' ? 'text-red-600' : 'text-teal-600'">
                  {{ t.type_value === 'expense' ? '−' : '+' }}{{ fmt(t.amount) }}
                </span>
              </Td>
              <Td class="text-xs text-gray-500">{{ t.created_by_name }}</Td>
              <Td>
                <!--
                  FIXED: was 'pic.transactions.proof.download' (inside role:pic middleware — 403 for admin).
                  Corrected to 'transactions.proof.download' (shared route, policy-authorised).
                -->
                <a v-if="t.has_proof"
                   :href="route('transactions.proof.download', t.id)"
                   target="_blank"
                   class="inline-flex items-center space-x-1 text-blue-600 hover:text-blue-800 text-xs font-medium transition">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                  </svg>
                  <span class="truncate max-w-24">{{ t.proof_name }}</span>
                </a>
                <span v-else class="text-gray-300 text-xs">—</span>
              </Td>
            </tr>
          </tbody>
        </table>
      </div>
      <EmptyState v-else title="Belum ada transaksi" description="Belum ada transaksi yang dicatat untuk pencairan ini." />
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import Th from '@/Components/Th.vue'
import Td from '@/Components/Td.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ disbursement: Object })
const { formatRp: fmt } = useCurrency()

const statusBadgeColor = computed(() => ({
  upcoming: 'blue',
  active:   'green',
  grace:    'amber',
  expired:  'gray',
})[props.disbursement.status] ?? 'gray')
</script>