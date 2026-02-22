<template>
  <AppLayout title="Detail Proposal">
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
      <Link :href="route('admin.proposals.index')" class="hover:text-green-700">Proposal</Link>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-800 font-medium">{{ proposal.code }}</span>
    </nav>

    <!-- Header Card -->
    <div class="card p-6 mb-5 animate-fade-in-up">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
          <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ proposal.code }}</span>
            <Badge :color="proposal.status_color" :dot="true">{{ proposal.status_label }}</Badge>
            <Badge :color="proposal.purpose_value === 'activity' ? 'blue' : 'teal'">{{ proposal.purpose }}</Badge>
          </div>
          <h2 class="text-2xl font-bold text-green-900">{{ proposal.name }}</h2>
          <p class="text-sm text-gray-500 mt-1">
            Pemohon: <strong>{{ proposal.applicant_name }}</strong>
            &nbsp;·&nbsp; PIC: <strong>{{ proposal.pic_name }}</strong>
            &nbsp;·&nbsp; {{ proposal.start_date }} – {{ proposal.end_date }}
          </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
          <a v-if="proposal.has_proposal_pdf" :href="route('admin.proposals.pdf', proposal.id)" target="_blank"
             class="btn-secondary text-sm flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>PDF Proposal</span>
          </a>
          <a v-if="proposal.has_certificate" :href="route('admin.proposals.certificate', proposal.id)" target="_blank"
             class="btn-primary text-sm flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Sertifikat</span>
          </a>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Details Column -->
      <div class="lg:col-span-2 space-y-5">
        <!-- Budget info -->
        <div class="card p-5 animate-fade-in-up" style="animation-delay:60ms">
          <h3 class="font-semibold text-green-800 mb-4">Informasi Anggaran</h3>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
              <p class="text-xs text-gray-400 uppercase tracking-wide">Diusulkan</p>
              <p class="text-xl font-bold text-gray-800 mt-1">{{ fmt(proposal.proposed_amount) }}</p>
            </div>
            <div class="rounded-xl p-4 text-center" :class="proposal.approved_amount ? 'bg-green-50' : 'bg-amber-50'">
              <p class="text-xs uppercase tracking-wide" :class="proposal.approved_amount ? 'text-green-500' : 'text-amber-500'">Disetujui</p>
              <p class="text-xl font-bold mt-1" :class="proposal.approved_amount ? 'text-green-800' : 'text-amber-600'">
                {{ proposal.approved_amount ? fmt(proposal.approved_amount) : 'Belum ditentukan' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Proposal info -->
        <div class="card p-5 animate-fade-in-up" style="animation-delay:120ms">
          <h3 class="font-semibold text-green-800 mb-3">Detail Kegiatan</h3>
          <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-50">
              <tr><td class="py-2 text-gray-500 w-40 font-medium">Nama Kegiatan</td><td class="py-2 font-medium">{{ proposal.name }}</td></tr>
              <tr><td class="py-2 text-gray-500 font-medium">Tujuan</td><td class="py-2">{{ proposal.purpose }}</td></tr>
              <tr><td class="py-2 text-gray-500 font-medium">Ketua Pelaksana</td><td class="py-2">{{ proposal.chairperson }}</td></tr>
              <tr><td class="py-2 text-gray-500 font-medium">PIC</td><td class="py-2">{{ proposal.pic_name }}</td></tr>
              <tr><td class="py-2 text-gray-500 font-medium">Pemohon</td><td class="py-2">{{ proposal.applicant_name }}</td></tr>
              <tr><td class="py-2 text-gray-500 font-medium">Periode</td><td class="py-2">{{ proposal.start_date }} – {{ proposal.end_date }}</td></tr>
              <tr v-if="proposal.forwarded_at"><td class="py-2 text-gray-500 font-medium">Diteruskan</td><td class="py-2 text-xs">{{ proposal.forwarded_at }} oleh {{ proposal.reviewer_name }}</td></tr>
              <tr v-if="proposal.approved_at"><td class="py-2 text-gray-500 font-medium">Disetujui</td><td class="py-2 text-xs text-green-700 font-semibold">{{ proposal.approved_at }}</td></tr>
              <tr v-if="proposal.superadmin_note"><td class="py-2 text-gray-500 font-medium">Catatan Admin</td><td class="py-2 text-gray-700 italic">{{ proposal.superadmin_note }}</td></tr>
            </tbody>
          </table>
        </div>

        <!-- Approvers -->
        <div class="card p-5 animate-fade-in-up" style="animation-delay:180ms">
          <h3 class="font-semibold text-green-800 mb-3">Status Persetujuan</h3>
          <div v-if="proposal.approvers?.length" class="space-y-2">
            <div v-for="a in proposal.approvers" :key="a.id"
                 class="flex items-center justify-between p-3 rounded-xl border"
                 :class="a.approved ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'">
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold"
                     :class="a.approved ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-600'">
                  {{ a.name.split(' ').map(w => w[0]).slice(0,2).join('') }}
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ a.name }}</p>
                  <p v-if="a.note" class="text-xs text-gray-500 italic">{{ a.note }}</p>
                </div>
              </div>
              <div class="text-right">
                <span v-if="a.approved" class="text-xs font-semibold text-green-700">✓ Disetujui</span>
                <span v-else class="text-xs text-amber-600">⏳ Menunggu</span>
                <p v-if="a.approved_at" class="text-xs text-gray-400 mt-0.5">{{ a.approved_at }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 italic">Belum ada approver yang ditugaskan.</p>
        </div>
      </div>

      <!-- Action Column -->
      <div class="space-y-5" v-if="proposal.status === 'draft'">
        <!-- Forward Form -->
        <div class="card p-5 animate-fade-in-up" style="animation-delay:60ms">
          <h3 class="font-semibold text-green-800 mb-4">Teruskan ke Approver</h3>
          <form @submit.prevent="submitForward" class="space-y-4">
            <FormField label="Jumlah Disetujui (Rp)" :error="forwardForm.errors.approved_amount" required>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input v-model="forwardForm.approved_amount" type="number" class="form-input pl-10"
                       :max="proposal.proposed_amount" step="1000" />
              </div>
              <p class="text-xs text-gray-400 mt-1">Maks: {{ fmt(proposal.proposed_amount) }}</p>
            </FormField>

            <FormField label="Pilih Approver" :error="forwardForm.errors.approver_ids" required>
              <div class="space-y-1.5 max-h-44 overflow-y-auto border border-gray-200 rounded-xl p-3">
                <label v-for="a in approvers" :key="a.id" class="flex items-center space-x-2 cursor-pointer p-1 hover:bg-green-50 rounded-lg">
                  <input type="checkbox" :value="a.id" v-model="forwardForm.approver_ids" class="rounded border-gray-300 text-green-600" />
                  <span class="text-sm text-gray-700">{{ a.name }}</span>
                </label>
              </div>
            </FormField>

            <FormField label="Catatan (opsional)" :error="forwardForm.errors.note">
              <textarea v-model="forwardForm.note" rows="2" class="form-input resize-none" placeholder="Catatan untuk approver..." />
            </FormField>

            <button type="submit" :disabled="forwardForm.processing" class="btn-primary w-full">
              {{ forwardForm.processing ? 'Meneruskan...' : '➤ Teruskan ke Approver' }}
            </button>
          </form>
        </div>

        <!-- Reject Form -->
        <div class="card p-5 border-red-100 animate-fade-in-up" style="animation-delay:120ms">
          <h3 class="font-semibold text-red-700 mb-3">Tolak Proposal</h3>
          <form @submit.prevent="submitReject" class="space-y-3">
            <FormField label="Alasan Penolakan" :error="rejectForm.errors.note" required>
              <textarea v-model="rejectForm.note" rows="2" class="form-input resize-none" placeholder="Jelaskan alasan penolakan..." />
            </FormField>
            <button type="submit" :disabled="rejectForm.processing"
                    class="w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition">
              {{ rejectForm.processing ? 'Menolak...' : '✕ Tolak Proposal' }}
            </button>
          </form>
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

const props = defineProps({ proposal: Object, approvers: Array })
const { formatRp: fmt } = useCurrency()

const forwardForm = useForm({ approved_amount: props.proposal.proposed_amount, approver_ids: [], note: '' })
const rejectForm  = useForm({ note: '' })

function submitForward() { forwardForm.post(route('admin.proposals.forward', props.proposal.id)) }
function submitReject()  { rejectForm.post(route('admin.proposals.reject', props.proposal.id)) }
</script>
