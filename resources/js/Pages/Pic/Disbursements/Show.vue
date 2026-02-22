<template>
  <AppLayout :title="disbursement.name">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
      <Link :href="route('pic.disbursements.index')" class="hover:text-green-700">Pencairan Saya</Link>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
      <span class="text-gray-800 font-medium truncate">{{ disbursement.name }}</span>
    </nav>

    <!-- Header -->
    <div class="card p-5 sm:p-6 mb-5 animate-fade-in-up">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
          <div class="flex flex-wrap items-center gap-2 mb-2">
            <Badge :color="disbursement.purpose_value === 'activity' ? 'blue' : 'teal'">{{ disbursement.purpose }}</Badge>
            <Badge
              :color="disbursement.status === 'active' ? 'green' : disbursement.status === 'grace' ? 'amber' : disbursement.status === 'expired' ? 'gray' : 'blue'"
              :dot="true"
            >
              {{ disbursement.status_label }}
            </Badge>
            <span v-if="disbursement.status === 'active'"
                  class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full flex items-center space-x-1">
              <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
              <span>{{ disbursement.days_remaining }} hari lagi</span>
            </span>
          </div>
          <h2 class="text-xl font-bold text-green-900">{{ disbursement.name }}</h2>
          <p class="text-sm text-gray-500 mt-1">
            Ketua: <strong>{{ disbursement.chairperson }}</strong>
            &nbsp;·&nbsp; {{ disbursement.start_date }} – {{ disbursement.end_date }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
          <Link
            v-if="disbursement.is_active"
            :href="route('pic.disbursements.transactions.create', disbursement.id)"
            class="btn-primary text-sm flex items-center space-x-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>+ Transaksi</span>
          </Link>
          <a :href="route('disbursements.report', disbursement.id)"
             target="_blank" class="btn-secondary text-sm flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak PDF</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Grace Period Banner -->
    <div v-if="disbursement.status === 'grace'"
         class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start space-x-3 animate-slide-down">
      <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <div>
        <p class="text-sm font-bold text-amber-800">Periode Persiapan Laporan</p>
        <p class="text-xs text-amber-700 mt-0.5">
          Kegiatan telah berakhir namun masih dalam masa toleransi. Anda masih bisa menambah, mengedit, dan menghapus transaksi.
        </p>
      </div>
    </div>

    <!-- Expired Banner -->
    <div v-if="disbursement.status === 'expired'"
         class="mb-5 p-4 bg-gray-50 border border-gray-200 rounded-xl flex items-center space-x-3 animate-slide-down">
      <svg class="w-5 h-5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
      </svg>
      <p class="text-sm text-gray-600">Periode kegiatan dan masa toleransi telah berakhir. Transaksi tidak dapat lagi ditambah atau diubah.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5 stagger-children">
      <div class="card p-4 animate-fade-in-up">
        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Dana Pencairan</p>
        <p class="text-lg font-bold text-gray-800 mt-1">{{ fmt(disbursement.amount) }}</p>
      </div>
      <div class="card p-4 animate-fade-in-up" style="animation-delay:80ms">
        <p class="text-xs text-red-400 uppercase tracking-wide font-medium">Pengeluaran</p>
        <p class="text-lg font-bold text-red-600 mt-1">{{ fmt(disbursement.total_expense) }}</p>
      </div>
      <div class="card p-4 animate-fade-in-up" style="animation-delay:160ms">
        <p class="text-xs text-teal-400 uppercase tracking-wide font-medium">Pemasukan</p>
        <p class="text-lg font-bold text-teal-600 mt-1">{{ fmt(disbursement.total_income) }}</p>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-xl p-4 animate-fade-in-up" style="animation-delay:240ms">
        <p class="text-xs text-green-500 uppercase tracking-wide font-medium">Sisa Dana</p>
        <p :class="['text-lg font-bold mt-1', disbursement.remaining_funds < 0 ? 'text-red-700' : 'text-green-800']">
          {{ fmt(disbursement.remaining_funds) }}
        </p>
      </div>
    </div>

    <!-- Progress -->
    <div class="card p-5 mb-5 animate-fade-in-up" style="animation-delay:300ms">
      <ProgressBar :percentage="disbursement.realization_pct" label="Progres Realisasi" :show-label="true" size="lg" />
    </div>

    <!-- Transactions Table -->
    <div class="card animate-fade-in-up" style="animation-delay:360ms">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-green-800">Daftar Transaksi ({{ disbursement.transactions?.length || 0 }})</h3>
        <Link v-if="disbursement.is_active"
              :href="route('pic.disbursements.transactions.create', disbursement.id)"
              class="btn-primary text-xs py-1.5 px-3 flex items-center space-x-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span>Tambah</span>
        </Link>
      </div>

      <div v-if="disbursement.transactions?.length" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Tanggal</Th>
              <Th>Tipe</Th>
              <Th>Keterangan</Th>
              <Th class="text-right">Jumlah</Th>
              <Th class="text-right">Saldo</Th>
              <Th>Bukti</Th>
              <Th v-if="disbursement.is_active">Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <tr v-for="(t, i) in disbursement.transactions" :key="t.id"
                :class="['hover:bg-green-50/40 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
              <Td class="whitespace-nowrap text-xs font-medium">{{ t.transaction_date }}</Td>
              <Td><Badge :color="t.type_value === 'expense' ? 'red' : 'teal'">{{ t.type }}</Badge></Td>
              <Td class="max-w-xs"><p class="truncate">{{ t.description }}</p></Td>
              <Td class="text-right font-semibold whitespace-nowrap">
                <span :class="t.type_value === 'expense' ? 'text-red-600' : 'text-teal-600'">
                  {{ t.type_value === 'expense' ? '−' : '+' }}{{ fmt(t.amount) }}
                </span>
              </Td>
              <Td class="text-right whitespace-nowrap">
                <span :class="['text-xs font-medium', t.running_balance < 0 ? 'text-red-600' : 'text-gray-500']">
                  {{ fmt(t.running_balance) }}
                </span>
              </Td>
              <Td>
                <a v-if="t.has_proof" :href="route('transactions.proof.download', t.id)" target="_blank"
                   class="inline-flex items-center space-x-1 text-blue-600 hover:text-blue-800 text-xs font-medium">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                  </svg>
                  <span class="truncate max-w-[80px]">{{ t.proof_name }}</span>
                </a>
                <span v-else class="text-gray-300 text-xs">—</span>
              </Td>

              <!-- Edit / Delete — only if this PIC created it AND period allows it -->
              <Td v-if="disbursement.is_active">
                <div v-if="t.created_by_me" class="flex items-center space-x-1">
                  <Link
                    :href="route('pic.disbursements.transactions.edit', [disbursement.id, t.id])"
                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                    title="Edit"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>
                  <button @click="handleDelete(t)"
                          class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                          title="Hapus">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
                <span v-else class="text-gray-200 text-xs">—</span>
              </Td>
            </tr>
          </tbody>
        </table>
      </div>
      <EmptyState v-else title="Belum ada transaksi" description="Klik '+ Tambah' untuk mencatat transaksi pertama." />
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import Th from '@/Components/Th.vue'; import Td from '@/Components/Td.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'
import { useAlert } from '@/Composables/useAlert'

defineProps({ disbursement: Object })
const { formatRp: fmt } = useCurrency()
const { confirmDelete } = useAlert()

async function handleDelete(tx) {
  const ok = await confirmDelete(
    `Hapus transaksi "${tx.description}"?`,
    'Transaksi yang dihapus tidak bisa dikembalikan.'
  )
  if (!ok) return

  router.delete(
    route('pic.disbursements.transactions.destroy', [tx.disbursement_id, tx.id]),
    { preserveScroll: true }
  )
}
</script>
