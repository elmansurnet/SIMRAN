<template>
  <AppLayout title="Alokasi Anggaran">

    <!-- Header -->
    <div class="page-header">
      <div>
        <h2 class="page-title">Alokasi Anggaran</h2>
        <p class="text-sm text-gray-400 mt-0.5">Kelola alokasi anggaran institusi</p>
      </div>
      <Link v-if="canManage" :href="route('admin.budget-allocations.create')" class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Alokasi</span>
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3">
        <SearchInput v-model="searchQuery" placeholder="Cari nama anggaran..." class="flex-1" />
        <select v-model="typeFilter" class="form-input sm:w-44">
          <option value="">Semua Tipe</option>
          <option value="monthly">Bulanan</option>
          <option value="yearly">Tahunan</option>
        </select>
        <button @click="applyFilter" class="btn-primary whitespace-nowrap">Cari</button>
        <button v-if="hasFilter" @click="clearFilter" class="btn-secondary whitespace-nowrap">Reset</button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Nama Anggaran</Th>
              <Th>Tipe</Th>
              <Th>Periode</Th>
              <Th class="text-right">Total</Th>
              <Th class="text-right">Terpakai</Th>
              <Th>Utilisasi</Th>
              <Th>Status</Th>
              <Th v-if="canManage">Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="allocations.data.length">
              <tr
                v-for="(a, i) in allocations.data"
                :key="a.id"
                :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']"
              >
                <Td>
                  <div class="flex items-center space-x-2">
                    <span
                      v-if="a.is_low_budget"
                      class="w-2 h-2 rounded-full bg-red-500 shrink-0 animate-pulse"
                      title="Anggaran rendah"
                    ></span>
                    <div>
                      <p class="font-medium text-gray-800">{{ a.name }}</p>
                      <p v-if="a.description" class="text-xs text-gray-400 truncate max-w-xs">{{ a.description }}</p>
                    </div>
                  </div>
                </Td>
                <Td>
                  <Badge :color="a.type_value === 'monthly' ? 'blue' : 'purple'">{{ a.type }}</Badge>
                </Td>
                <Td>
                  <p class="text-xs whitespace-nowrap">{{ a.start_date }}</p>
                  <p class="text-xs text-gray-400 whitespace-nowrap">{{ a.end_date }}</p>
                </Td>
                <Td class="text-right font-medium">{{ fmt(a.amount) }}</Td>
                <Td class="text-right">
                  <p class="font-medium text-red-600">{{ fmt(a.total_disbursed) }}</p>
                  <p class="text-xs font-semibold text-green-700">Sisa: {{ fmt(a.remaining) }}</p>
                </Td>
                <Td class="min-w-32">
                  <ProgressBar :percentage="a.utilization_pct" size="sm" :show-label="true" label="" />
                </Td>
                <Td>
                  <Badge
                    :color="a.is_low_budget ? 'red' : a.utilization_pct >= 70 ? 'amber' : 'green'"
                    :dot="true"
                  >
                    {{ a.is_low_budget ? 'Kritis' : a.utilization_pct >= 70 ? 'Waspada' : 'Normal' }}
                  </Badge>
                </Td>
                <Td v-if="canManage">
                  <div class="flex items-center space-x-1">
                    <!-- Detail -->
                    <Link
                      :href="route('admin.budget-allocations.show', a.id)"
                      class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                      title="Detail"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <!-- Edit -->
                    <Link
                      :href="route('admin.budget-allocations.edit', a.id)"
                      class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                      title="Edit"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <!-- Delete -->
                    <button
                      @click="handleDelete(a)"
                      class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      title="Hapus"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </Td>
              </tr>
            </template>

            <!-- Empty State -->
            <tr v-else>
              <td :colspan="canManage ? 8 : 7" class="py-2">
                <EmptyState
                  title="Belum ada alokasi anggaran"
                  description="Klik tombol 'Tambah Alokasi' untuk membuat alokasi baru."
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :links="allocations.links" />
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
import Th from '@/Components/Th.vue'
import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useAlert } from '@/Composables/useAlert'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ allocations: Object, filters: Object })
const { confirmDelete, success } = useAlert()
const { formatRp: fmt } = useCurrency()
const page = usePage()
const canManage = computed(() => page.props.auth.user.role_value === 'superadmin')

const searchQuery = ref(props.filters?.search || '')
const typeFilter  = ref(props.filters?.type   || '')
const hasFilter   = computed(() => searchQuery.value || typeFilter.value)

function applyFilter() {
  router.get(
    route('admin.budget-allocations.index'),
    { search: searchQuery.value, type: typeFilter.value },
    { preserveState: true, replace: true }
  )
}

function clearFilter() {
  searchQuery.value = ''
  typeFilter.value  = ''
  router.get(route('admin.budget-allocations.index'))
}

async function handleDelete(allocation) {
  const result = await confirmDelete(allocation.name)
  if (result.isConfirmed) {
    router.delete(route('admin.budget-allocations.destroy', allocation.id), {
      onSuccess: () => success('Alokasi anggaran berhasil dihapus.'),
    })
  }
}
</script>