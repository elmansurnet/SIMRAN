<template>
  <AppLayout title="Proposal untuk Disetujui">
    <div class="page-header">
      <div>
        <h2 class="page-title">Proposal untuk Disetujui</h2>
        <p class="text-sm text-gray-400 mt-0.5">Daftar proposal yang membutuhkan persetujuan Anda</p>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex space-x-1 mb-5 bg-gray-100 rounded-xl p-1 w-fit">
      <button v-for="tab in tabs" :key="tab.value"
              @click="setTab(tab.value)"
              class="px-4 py-1.5 rounded-lg text-sm font-medium transition"
              :class="activeTab === tab.value ? 'bg-white text-green-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
        {{ tab.label }}
      </button>
    </div>

    <div class="table-container animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Kode / Nama</Th>
              <Th>Pemohon</Th>
              <Th class="text-right">Anggaran Disetujui</Th>
              <Th>Status Proposal</Th>
              <Th>Persetujuan Saya</Th>
              <Th>Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="items.data.length">
              <tr v-for="(p, i) in items.data" :key="p.id"
                  :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
                <Td>
                  <p class="font-mono text-xs text-gray-400">{{ p.code }}</p>
                  <p class="font-medium text-gray-800 truncate max-w-52">{{ p.name }}</p>
                </Td>
                <Td class="text-sm text-gray-600">{{ p.applicant_name }}</Td>
                <Td class="text-right font-semibold text-green-700">{{ fmt(p.approved_amount) }}</Td>
                <Td><Badge :color="p.status_color" :dot="true">{{ p.status_label }}</Badge></Td>
                <Td>
                  <span v-if="p.my_approved" class="inline-flex items-center space-x-1 text-xs text-green-700 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ p.my_approved_at }}</span>
                  </span>
                  <span v-else class="text-xs text-amber-600 font-medium">⏳ Menunggu</span>
                </Td>
                <Td>
                  <Link :href="route('approver.proposals.show', p.id)"
                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition inline-flex" title="Detail & Setujui">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </Link>
                </Td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="6" class="py-2">
                <EmptyState title="Tidak ada proposal" :description="activeTab === 'pending' ? 'Tidak ada proposal yang menunggu persetujuan Anda.' : 'Belum ada proposal yang Anda setujui.'" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="items.links" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import Th from '@/Components/Th.vue'; import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ items: Object, filters: Object })
const { formatRp: fmt } = useCurrency()

const activeTab = ref(props.filters?.status || '')
const tabs = [
  { label: 'Semua',   value: '' },
  { label: 'Menunggu', value: 'pending' },
  { label: 'Sudah',   value: 'approved' },
]

function setTab(v) {
  activeTab.value = v
  router.get(route('approver.proposals.index'), { status: v }, { preserveState: true, replace: true })
}
</script>
