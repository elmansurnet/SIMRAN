<template>
  <AppLayout :title="proposal.name">
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
      <Link :href="route('applicant.proposals.index')" class="hover:text-green-700">Proposal Saya</Link>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-800 font-medium truncate">{{ proposal.code }}</span>
    </nav>

    <!-- Header -->
    <div class="card p-6 mb-5 animate-fade-in-up">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
          <div class="flex flex-wrap gap-2 mb-2">
            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ proposal.code }}</span>
            <Badge :color="proposal.status_color" :dot="true">{{ proposal.status_label }}</Badge>
          </div>
          <h2 class="text-2xl font-bold text-green-900">{{ proposal.name }}</h2>
          <p class="text-sm text-gray-500 mt-1">{{ proposal.purpose }} · {{ proposal.start_date }} – {{ proposal.end_date }}</p>
        </div>
        <div class="flex gap-2 shrink-0">
          <a v-if="proposal.has_certificate" :href="route('applicant.proposals.certificate', proposal.id)" target="_blank"
             class="btn-primary text-sm flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Unduh Pengesahan</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Status Timeline -->
    <div class="card p-5 mb-5 animate-fade-in-up" style="animation-delay:60ms">
      <h3 class="font-semibold text-green-800 mb-4">Status Pengajuan</h3>
      <div class="flex items-center space-x-2">
        <template v-for="(step, i) in timeline" :key="step.label">
          <div class="flex flex-col items-center text-center" style="min-width:80px">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mb-1"
                 :class="step.done ? 'bg-green-600 text-white' : step.active ? 'bg-amber-400 text-white' : 'bg-gray-200 text-gray-400'">
              {{ step.done ? '✓' : (i + 1) }}
            </div>
            <p class="text-xs font-medium" :class="step.done ? 'text-green-700' : step.active ? 'text-amber-600' : 'text-gray-400'">{{ step.label }}</p>
          </div>
          <div v-if="i < timeline.length - 1" class="flex-1 h-0.5" :class="step.done ? 'bg-green-300' : 'bg-gray-200'"></div>
        </template>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- Budget -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:120ms">
        <h3 class="font-semibold text-green-800 mb-3">Anggaran</h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center py-2 border-b border-gray-50">
            <span class="text-sm text-gray-500">Jumlah Diusulkan</span>
            <span class="font-semibold text-gray-800">{{ fmt(proposal.proposed_amount) }}</span>
          </div>
          <div class="flex justify-between items-center py-2">
            <span class="text-sm" :class="proposal.approved_amount ? 'text-green-600' : 'text-gray-400'">Jumlah Disetujui</span>
            <span :class="proposal.approved_amount ? 'font-bold text-green-700' : 'text-gray-300'">
              {{ proposal.approved_amount ? fmt(proposal.approved_amount) : 'Belum ditentukan' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:150ms">
        <h3 class="font-semibold text-green-800 mb-3">Detail</h3>
        <table class="w-full text-sm">
          <tbody class="divide-y divide-gray-50">
            <tr><td class="py-1.5 text-gray-400 w-36">PIC</td><td class="py-1.5 font-medium">{{ proposal.pic_name }}</td></tr>
            <tr><td class="py-1.5 text-gray-400">Ketua</td><td class="py-1.5">{{ proposal.chairperson }}</td></tr>
            <tr><td class="py-1.5 text-gray-400">Diajukan</td><td class="py-1.5">{{ proposal.created_at }}</td></tr>
            <tr v-if="proposal.approved_at"><td class="py-1.5 text-gray-400">Disetujui</td><td class="py-1.5 text-green-700 font-semibold">{{ proposal.approved_at }}</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Admin Note -->
      <div v-if="proposal.superadmin_note" class="card p-5 border-amber-100 bg-amber-50 animate-fade-in-up" style="animation-delay:180ms">
        <h3 class="font-semibold text-amber-800 mb-2">Catatan Admin</h3>
        <p class="text-sm text-amber-700">{{ proposal.superadmin_note }}</p>
      </div>

      <!-- Approvers -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:210ms">
        <h3 class="font-semibold text-green-800 mb-3">Status Persetujuan</h3>
        <div v-if="proposal.approvers?.length" class="space-y-2">
          <div v-for="a in proposal.approvers" :key="a.name"
               class="flex items-center justify-between p-2.5 rounded-lg"
               :class="a.approved ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500'">
            <span class="text-sm font-medium">{{ a.name }}</span>
            <div class="text-right">
              <span class="text-xs font-semibold">{{ a.approved ? '✓ Disetujui' : '⏳ Menunggu' }}</span>
              <p v-if="a.approved_at" class="text-xs opacity-70">{{ a.approved_at }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 italic">Belum ada approver ditugaskan.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ proposal: Object })
const { formatRp: fmt } = useCurrency()

const timeline = computed(() => {
  const s = props.proposal.status
  return [
    { label: 'Diajukan',     done: true,                       active: false },
    { label: 'Direview',     done: ['forwarded','approved','rejected'].includes(s), active: s === 'draft' },
    { label: 'Disetujui',    done: s === 'approved',           active: s === 'forwarded' },
    { label: 'Pengesahan',   done: props.proposal.has_certificate, active: false },
  ]
})
</script>
