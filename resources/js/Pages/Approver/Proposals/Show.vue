<template>
  <AppLayout :title="proposal.name">
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
      <Link :href="route('approver.proposals.index')" class="hover:text-green-700">Proposal</Link>
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
          <p class="text-sm text-gray-500 mt-1">
            {{ proposal.purpose }} · Pemohon: {{ proposal.applicant_name }} · {{ proposal.start_date }} – {{ proposal.end_date }}
          </p>
        </div>
        <a v-if="proposal.has_proposal_pdf" :href="route('approver.proposals.pdf', proposal.id)" target="_blank"
           class="btn-secondary text-sm shrink-0 flex items-center space-x-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          <span>Buka PDF Proposal</span>
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Details -->
      <div class="lg:col-span-2 space-y-5">
        <div class="card p-5 animate-fade-in-up" style="animation-delay:60ms">
          <h3 class="font-semibold text-green-800 mb-3">Detail Proposal</h3>
          <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-50">
              <tr><td class="py-2 text-gray-400 w-40">Nama Kegiatan</td><td class="py-2 font-medium">{{ proposal.name }}</td></tr>
              <tr><td class="py-2 text-gray-400">Tujuan</td><td class="py-2">{{ proposal.purpose }}</td></tr>
              <tr><td class="py-2 text-gray-400">Pemohon</td><td class="py-2">{{ proposal.applicant_name }}</td></tr>
              <tr><td class="py-2 text-gray-400">PIC</td><td class="py-2">{{ proposal.pic_name }}</td></tr>
              <tr><td class="py-2 text-gray-400">Ketua</td><td class="py-2">{{ proposal.chairperson }}</td></tr>
              <tr><td class="py-2 text-gray-400">Periode</td><td class="py-2">{{ proposal.start_date }} – {{ proposal.end_date }}</td></tr>
              <tr><td class="py-2 text-gray-400">Diusulkan</td><td class="py-2 font-medium">{{ fmt(proposal.proposed_amount) }}</td></tr>
              <tr>
                <td class="py-2 text-gray-400">Disetujui Admin</td>
                <td class="py-2 font-bold text-green-700 text-base">{{ fmt(proposal.approved_amount) }}</td>
              </tr>
              <tr v-if="proposal.superadmin_note">
                <td class="py-2 text-gray-400">Catatan Admin</td>
                <td class="py-2 italic text-gray-600">{{ proposal.superadmin_note }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Other approvers status -->
        <div class="card p-5 animate-fade-in-up" style="animation-delay:120ms">
          <h3 class="font-semibold text-green-800 mb-3">Status Semua Approver</h3>
          <div class="space-y-2">
            <div v-for="a in proposal.approvers" :key="a.name"
                 class="flex items-center justify-between p-2.5 rounded-lg"
                 :class="a.approved ? 'bg-green-50' : 'bg-gray-50'">
              <div>
                <p class="text-sm font-medium text-gray-800">{{ a.name }}</p>
                <p v-if="a.note" class="text-xs text-gray-500 italic">{{ a.note }}</p>
              </div>
              <div class="text-right">
                <span class="text-xs font-semibold" :class="a.approved ? 'text-green-700' : 'text-amber-600'">
                  {{ a.approved ? '✓ Disetujui' : '⏳ Menunggu' }}
                </span>
                <p v-if="a.approved_at" class="text-xs text-gray-400">{{ a.approved_at }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Approval Action -->
      <div class="animate-fade-in-up" style="animation-delay:60ms">
        <!-- Already approved -->
        <div v-if="myApproval.approved" class="card p-5 bg-green-50 border-green-200">
          <div class="text-center">
            <div class="w-14 h-14 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="font-bold text-green-800 text-lg">Sudah Disetujui</h3>
            <p class="text-sm text-green-600 mt-1">{{ myApproval.approved_at }}</p>
            <p v-if="myApproval.note" class="text-sm text-green-700 mt-2 italic bg-green-100 rounded-lg p-2">{{ myApproval.note }}</p>
          </div>
        </div>

        <!-- Pending approval -->
        <div v-else-if="proposal.status === 'forwarded'" class="card p-5 animate-fade-in-up">
          <h3 class="font-semibold text-green-800 mb-1">Berikan Persetujuan</h3>
          <p class="text-xs text-gray-400 mb-4">Pastikan Anda telah membaca proposal sebelum menyetujui.</p>
          <form @submit.prevent="submitApproval" class="space-y-4">
            <FormField label="Catatan (opsional)" :error="approveForm.errors.note">
              <textarea v-model="approveForm.note" rows="3" class="form-input resize-none" placeholder="Catatan persetujuan..." />
            </FormField>
            <button type="submit" :disabled="approveForm.processing"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold text-sm transition flex items-center justify-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              <span>{{ approveForm.processing ? 'Menyetujui...' : 'Setujui Proposal' }}</span>
            </button>
          </form>
        </div>

        <!-- Not in a state where approval is possible -->
        <div v-else class="card p-5 bg-gray-50">
          <p class="text-sm text-gray-500 text-center">
            Proposal ini berstatus <strong>{{ proposal.status_label }}</strong> dan tidak lagi memerlukan tindakan.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import FormField from '@/Components/FormField.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({
  proposal: { type: Object, required: true },
  myApproval: {
    type: Object,
    default: () => ({
      approved: false,
      approved_at: null,
      note: null,
    }),
  },
})
const { formatRp: fmt } = useCurrency()
const approveForm = useForm({ note: '' })

function submitApproval() {
  approveForm.post(route('approver.proposals.approve', props.proposal.id))
}
</script>
