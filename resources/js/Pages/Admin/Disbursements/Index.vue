<template>
  <AppLayout title="Pencairan Anggaran">
    <div class="page-header">
      <div>
        <h2 class="page-title">Pencairan Anggaran</h2>
        <p class="text-sm text-gray-400 mt-0.5">Kelola alokasi pencairan dana kegiatan</p>
      </div>
      <Link :href="route('admin.disbursements.create')" class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Buat Pencairan</span>
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3">
        <SearchInput v-model="searchQuery" placeholder="Cari nama pencairan..." class="flex-1" />
        <select v-model="typeFilter" class="form-input sm:w-44">
          <option value="">Semua Tujuan</option>
          <option value="activity">Kegiatan</option>
          <option value="operational">Operasional</option>
        </select>
        <button @click="applyFilter" class="btn-primary">Cari</button>
        <button v-if="hasFilter" @click="clearFilter" class="btn-secondary">Reset</button>
      </div>
    </div>

    <div class="table-container animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Nama Kegiatan</Th>
              <Th>PIC</Th>
              <Th class="text-right">Dana</Th>
              <Th>Realisasi</Th>
              <Th>Periode</Th>
              <Th>Status</Th>
              <Th>Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="disbursements.data.length">
              <tr v-for="(d, i) in disbursements.data" :key="d.id"
                  :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">

                <Td>
                  <div class="flex items-center space-x-2">
                    <Badge :color="d.purpose_value === 'activity' ? 'blue' : 'teal'" class="shrink-0">
                      {{ d.purpose }}
                    </Badge>
                    <p class="font-medium text-gray-800 truncate max-w-48">{{ d.name }}</p>
                  </div>
                  <p v-if="d.delete_reason" class="text-xs text-amber-600 mt-0.5 flex items-center space-x-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>Ada transaksi</span>
                  </p>
                </Td>

                <Td class="text-sm text-gray-600">{{ d.pic_name }}</Td>

                <Td class="text-right">
                  <p class="font-medium text-gray-800">{{ fmt(d.amount) }}</p>
                  <p class="text-xs text-gray-400">Sisa: {{ fmt(d.remaining_funds) }}</p>
                </Td>

                <!-- Realization bar — safeNum() prevents toFixed() on null/string -->
                <Td>
                  <div class="w-24">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                      <span>{{ safeNum(d.realization_pct).toFixed(1) }}%</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                      <div :class="['h-1.5 rounded-full transition-all', safeNum(d.realization_pct) > 90 ? 'bg-red-500' : 'bg-green-500']"
                           :style="`width: ${Math.min(100, safeNum(d.realization_pct)).toFixed(1)}%`" />
                    </div>
                  </div>
                </Td>

                <Td class="text-xs text-gray-500">
                  <p>{{ d.start_date }}</p>
                  <p>{{ d.end_date }}</p>
                </Td>

                <Td>
                  <div class="flex items-center space-x-1.5">
                    <Badge :color="statusColor(d.status)" :dot="true">{{ d.status_label }}</Badge>
                    <span v-if="d.status === 'active'" class="text-xs text-green-600">
                      {{ d.days_remaining }}h
                    </span>
                  </div>
                </Td>

                <Td>
                  <div class="flex items-center space-x-1">
                    <!-- View -->
                    <Link :href="route('admin.disbursements.show', d.id)"
                          class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                          title="Detail">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <!-- Edit -->
                    <Link :href="route('admin.disbursements.edit', d.id)"
                          class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                          title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <!-- Delete — disabled with tooltip when has transactions -->
                    <div class="relative group">
                      <button v-if="d.can_delete"
                              @click="confirmDelete(d)"
                              class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                              title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                      <span v-else
                            class="p-1.5 text-gray-200 cursor-not-allowed rounded-lg inline-flex"
                            title="Tidak dapat dihapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </span>
                      <div v-if="!d.can_delete && d.delete_reason"
                           class="absolute right-0 top-8 z-10 w-64 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg
                                  invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-opacity pointer-events-none">
                        {{ d.delete_reason }}
                        <div class="absolute -top-1.5 right-2 w-3 h-3 bg-gray-900 rotate-45" />
                      </div>
                    </div>
                  </div>
                </Td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="7" class="py-2">
                <EmptyState title="Belum ada pencairan" description="Buat pencairan pertama dengan tombol di atas." />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="disbursements.links" />
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 animate-fade-in-up">
        <h3 class="font-bold text-lg text-gray-900 mb-2">Hapus Pencairan</h3>
        <p class="text-sm text-gray-600 mb-5">
          Yakin ingin menghapus <strong>{{ deleteTarget.name }}</strong>?
          Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex gap-3">
          <Link :href="route('admin.disbursements.destroy', deleteTarget.id)" method="delete" as="button"
                class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium text-sm transition text-center">
            Ya, Hapus
          </Link>
          <button @click="deleteTarget = null" class="flex-1 btn-secondary">Batal</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Th from '@/Components/Th.vue'
import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import SearchInput from '@/Components/SearchInput.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ disbursements: Object, filters: Object })
const { formatRp: fmt } = useCurrency()

const searchQuery  = ref(props.filters?.search || '')
const typeFilter   = ref(props.filters?.type   || '')
const deleteTarget = ref(null)
const hasFilter    = computed(() => searchQuery.value || typeFilter.value)

// WHY safeNum: Laravel decimal casts produce strings. Number() coerces safely.
function safeNum(val) { return Number(val ?? 0) }

function applyFilter() {
  router.get(
    route('admin.disbursements.index'),
    { search: searchQuery.value, type: typeFilter.value },
    { preserveState: true, replace: true }
  )
}
function clearFilter() {
  searchQuery.value = ''
  typeFilter.value  = ''
  router.get(route('admin.disbursements.index'))
}
function confirmDelete(d) { deleteTarget.value = d }

function statusColor(status) {
  return { active: 'green', grace: 'amber', upcoming: 'blue', expired: 'gray' }[status] || 'gray'
}
</script>