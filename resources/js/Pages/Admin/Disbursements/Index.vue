<template>
  <AppLayout title="Pencairan Anggaran">
    <div class="page-header">
      <div>
        <h2 class="page-title">Pencairan Anggaran</h2>
        <p class="text-sm text-gray-400 mt-0.5">Kelola pencairan anggaran kegiatan dan operasional</p>
      </div>
      <Link v-if="canManage" :href="route('admin.disbursements.create')" class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pencairan</span>
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
        <SearchInput v-model="searchQuery" placeholder="Cari nama kegiatan..." class="flex-1 min-w-48" />
        <select v-model="purposeFilter" class="form-input sm:w-44">
          <option value="">Semua Tujuan</option>
          <option value="activity">Kegiatan</option>
          <option value="operational">Operasional</option>
        </select>
        <select v-model="statusFilter" class="form-input sm:w-44">
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="expired">Selesai</option>
          <option value="upcoming">Akan Datang</option>
        </select>
        <button @click="applyFilter" class="btn-primary whitespace-nowrap">Cari</button>
        <button v-if="hasFilter" @click="clearFilter" class="btn-secondary whitespace-nowrap">Reset</button>
      </div>
    </div>

    <div class="table-container animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Nama / Tujuan</Th>
              <Th>PIC</Th>
              <Th>Alokasi</Th>
              <Th>Periode</Th>
              <Th class="text-right">Jumlah</Th>
              <Th>Realisasi</Th>
              <Th>Status</Th>
              <Th v-if="canManage">Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="disbursements.data.length">
              <tr v-for="(d, i) in disbursements.data" :key="d.id"
                  :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
                <Td>
                  <p class="font-medium text-gray-800">{{ d.name }}</p>
                  <div class="flex items-center space-x-1 mt-0.5">
                    <Badge :color="d.purpose_value === 'activity' ? 'blue' : 'teal'" class="text-xs">{{ d.purpose }}</Badge>
                    <span v-if="d.tx_count" class="text-xs text-gray-400">· {{ d.tx_count }} transaksi</span>
                  </div>
                </Td>
                <Td>{{ d.pic_name }}</Td>
                <Td class="max-w-36">
                  <p class="text-sm truncate text-gray-600">{{ d.allocation_name }}</p>
                </Td>
                <Td>
                  <p class="text-xs whitespace-nowrap">{{ d.start_date }}</p>
                  <p class="text-xs text-gray-400">{{ d.end_date }}</p>
                  <p v-if="d.status === 'active'" class="text-xs text-green-600 font-medium">{{ d.days_remaining }} hari lagi</p>
                </Td>
                <Td class="text-right">
                  <p class="font-medium text-gray-800 whitespace-nowrap">{{ fmt(d.amount) }}</p>
                  <p class="text-xs text-green-600">Sisa: {{ fmt(d.remaining_funds) }}</p>
                </Td>
                <Td class="min-w-32">
                  <ProgressBar :percentage="d.realization_pct" size="sm" :show-label="true" label="" />
                </Td>
                <Td>
                  <Badge :color="d.status === 'active' ? 'green' : d.status === 'expired' ? 'gray' : 'blue'" :dot="true">
                    {{ d.status_label }}
                  </Badge>
                </Td>
                <Td v-if="canManage">
                  <div class="flex items-center space-x-1">
                    <Link :href="route('admin.disbursements.show', d.id)"
                          class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <Link :href="route('admin.disbursements.edit', d.id)"
                          class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button @click="handleDelete(d)"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </Td>
              </tr>
            </template>
            <tr v-else>
              <td :colspan="canManage ? 8 : 7" class="py-2">
                <EmptyState title="Belum ada pencairan" description="Klik 'Tambah Pencairan' untuk membuat pencairan baru." />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="disbursements.links" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from '@/Components/SearchInput.vue'
import Badge from '@/Components/Badge.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import Th from '@/Components/Th.vue'; import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useAlert } from '@/Composables/useAlert'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ disbursements: Object, filters: Object })
const { confirmDelete, success } = useAlert()
const { formatRp: fmt } = useCurrency()
const page = usePage()
const canManage = computed(() => page.props.auth.user.role_value === 'superadmin')

const searchQuery  = ref(props.filters?.search  || '')
const purposeFilter = ref(props.filters?.purpose || '')
const statusFilter  = ref(props.filters?.status  || '')
const hasFilter = computed(() => searchQuery.value || purposeFilter.value || statusFilter.value)

function applyFilter() {
  router.get(route('admin.disbursements.index'), { search: searchQuery.value, purpose: purposeFilter.value, status: statusFilter.value }, { preserveState: true, replace: true })
}
function clearFilter() {
  searchQuery.value = ''; purposeFilter.value = ''; statusFilter.value = ''
  router.get(route('admin.disbursements.index'))
}
async function handleDelete(d) {
  const r = await confirmDelete(d.name)
  if (r.isConfirmed) router.delete(route('admin.disbursements.destroy', d.id), { onSuccess: () => success('Pencairan berhasil dihapus.') })
}
</script>
