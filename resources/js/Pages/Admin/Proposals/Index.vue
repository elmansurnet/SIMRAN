<template>
  <AppLayout title="Manajemen Proposal">
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Proposal</h2>
        <p class="text-sm text-gray-400 mt-0.5">Tinjau dan teruskan proposal kegiatan ke approver</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3">
        <SearchInput v-model="searchQuery" placeholder="Cari nama atau kode..." class="flex-1" />
        <select v-model="statusFilter" class="form-input sm:w-48">
          <option value="">Semua Status</option>
          <option value="draft">Menunggu Review</option>
          <option value="forwarded">Menunggu Persetujuan</option>
          <option value="approved">Disetujui</option>
          <option value="rejected">Ditolak</option>
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
              <Th>Kode / Nama</Th>
              <Th>Pemohon</Th>
              <Th>Tujuan</Th>
              <Th class="text-right">Jumlah Diusulkan</Th>
              <Th class="text-right">Jumlah Disetujui</Th>
              <Th>Status</Th>
              <Th>Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="proposals.data.length">
              <tr v-for="(p, i) in proposals.data" :key="p.id"
                  :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
                <Td>
                  <p class="font-mono text-xs text-gray-400">{{ p.code }}</p>
                  <p class="font-medium text-gray-800 truncate max-w-52">{{ p.name }}</p>
                  <p class="text-xs text-gray-400">{{ p.start_date }} – {{ p.end_date }}</p>
                </Td>
                <Td class="text-sm text-gray-600">{{ p.applicant_name }}</Td>
                <Td>
                  <Badge :color="p.purpose_value === 'activity' ? 'blue' : 'teal'">{{ p.purpose }}</Badge>
                </Td>
                <Td class="text-right font-medium">{{ fmt(p.proposed_amount) }}</Td>
                <Td class="text-right">
                  <span v-if="p.approved_amount" class="font-semibold text-green-700">{{ fmt(p.approved_amount) }}</span>
                  <span v-else class="text-gray-300">—</span>
                </Td>
                <Td>
                  <Badge :color="p.status_color" :dot="true">{{ p.status_label }}</Badge>
                </Td>
                <Td>
                  <div class="flex items-center space-x-1">
                    <Link :href="route('admin.proposals.show', p.id)"
                          class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Detail">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <button v-if="p.status === 'draft'" @click="openForward(p)"
                            class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Teruskan">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                      </svg>
                    </button>
                    <button v-if="p.status === 'draft'" @click="openReject(p)"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Tolak">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>
                  </div>
                </Td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="7" class="py-2">
                <EmptyState title="Belum ada proposal" description="Belum ada proposal yang masuk." />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="proposals.links" />
    </div>

    <!-- Forward Modal -->
    <div v-if="showForward" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 animate-fade-in-up">
        <h3 class="font-bold text-lg text-green-900 mb-4">Teruskan Proposal</h3>
        <p class="text-sm text-gray-600 mb-4">Proposal: <strong>{{ forwardTarget?.name }}</strong></p>

        <div class="space-y-4">
          <FormField label="Jumlah Disetujui (Rp)" :error="forwardForm.errors.approved_amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
              <input v-model="forwardForm.approved_amount" type="number" class="form-input pl-10"
                     :max="forwardTarget?.proposed_amount" />
            </div>
            <p class="text-xs text-gray-400 mt-1">Maks: {{ fmt(forwardTarget?.proposed_amount) }}</p>
          </FormField>

          <FormField label="Pilih Approver" :error="forwardForm.errors.approver_ids" required>
            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-xl p-3">
              <label v-for="a in approvers" :key="a.id" class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" :value="a.id" v-model="forwardForm.approver_ids"
                       class="rounded border-gray-300 text-green-600" />
                <span class="text-sm">{{ a.name }} <span class="text-gray-400 text-xs">{{ a.email }}</span></span>
              </label>
            </div>
          </FormField>

          <FormField label="Catatan (opsional)" :error="forwardForm.errors.note">
            <textarea v-model="forwardForm.note" rows="2" class="form-input resize-none" />
          </FormField>
        </div>

        <div class="flex gap-3 mt-5">
          <button @click="submitForward" :disabled="forwardForm.processing" class="btn-primary flex-1">
            {{ forwardForm.processing ? 'Meneruskan...' : 'Teruskan ke Approver' }}
          </button>
          <button @click="showForward = false" class="btn-secondary flex-1">Batal</button>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showReject" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-fade-in-up">
        <h3 class="font-bold text-lg text-red-700 mb-4">Tolak Proposal</h3>
        <p class="text-sm text-gray-600 mb-4">Proposal: <strong>{{ rejectTarget?.name }}</strong></p>
        <FormField label="Alasan Penolakan" :error="rejectForm.errors.note" required>
          <textarea v-model="rejectForm.note" rows="3" class="form-input resize-none" placeholder="Jelaskan alasan penolakan..." />
        </FormField>
        <div class="flex gap-3 mt-5">
          <button @click="submitReject" :disabled="rejectForm.processing"
                  class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
            {{ rejectForm.processing ? 'Menolak...' : 'Tolak Proposal' }}
          </button>
          <button @click="showReject = false" class="btn-secondary flex-1">Batal</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Th from '@/Components/Th.vue'; import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import SearchInput from '@/Components/SearchInput.vue'
import FormField from '@/Components/FormField.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ proposals: Object, approvers: Array, filters: Object })
const { formatRp: fmt } = useCurrency()

const searchQuery  = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')
const hasFilter    = computed(() => searchQuery.value || statusFilter.value)

function applyFilter() {
  router.get(route('admin.proposals.index'), { search: searchQuery.value, status: statusFilter.value }, { preserveState: true, replace: true })
}
function clearFilter() { searchQuery.value = ''; statusFilter.value = ''; router.get(route('admin.proposals.index')) }

// Forward
const showForward   = ref(false)
const forwardTarget = ref(null)
const forwardForm   = useForm({ approved_amount: '', approver_ids: [], note: '' })

function openForward(p) { forwardTarget.value = p; forwardForm.approved_amount = p.proposed_amount; forwardForm.approver_ids = []; forwardForm.note = ''; showForward.value = true }
function submitForward() {
  forwardForm.post(route('admin.proposals.forward', forwardTarget.value.id), {
    onSuccess: () => { showForward.value = false }
  })
}

// Reject
const showReject   = ref(false)
const rejectTarget = ref(null)
const rejectForm   = useForm({ note: '' })

function openReject(p) { rejectTarget.value = p; rejectForm.note = ''; showReject.value = true }
function submitReject() {
  rejectForm.post(route('admin.proposals.reject', rejectTarget.value.id), {
    onSuccess: () => { showReject.value = false }
  })
}
</script>
