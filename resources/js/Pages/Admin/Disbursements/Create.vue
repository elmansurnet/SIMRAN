<template>
  <AppLayout title="Tambah Pencairan">
    <div class="max-w-2xl mx-auto">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('admin.disbursements.index')" class="hover:text-green-700">Pencairan</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">Tambah</span>
      </nav>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <h2 class="text-xl font-bold text-green-900 mb-6">Buat Pencairan Baru</h2>

        <form @submit.prevent="submit" class="space-y-5">

          <!-- ══ PURPOSE ══ -->
          <FormField label="Tujuan Pencairan" :error="form.errors.purpose" required>
            <div class="grid grid-cols-2 gap-3">
              <label v-for="opt in purposeOptions" :key="opt.value"
                     class="relative cursor-pointer flex items-center space-x-3 p-3.5 border-2 rounded-xl transition"
                     :class="form.purpose === opt.value
                       ? 'border-green-500 bg-green-50'
                       : 'border-gray-200 hover:border-green-300'">
                <input type="radio" :value="opt.value" v-model="form.purpose" class="hidden" />
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-lg shrink-0"
                     :class="form.purpose === opt.value ? 'bg-green-600 text-white' : 'bg-gray-100'">
                  {{ opt.icon }}
                </div>
                <div>
                  <p class="font-semibold text-sm text-gray-800">{{ opt.label }}</p>
                  <p class="text-xs text-gray-400">{{ opt.desc }}</p>
                </div>
              </label>
            </div>
          </FormField>

          <!-- ══ PROPOSAL IMPORT (activity only) ══ -->
          <transition name="slide-down">
            <div v-if="form.purpose === 'activity'"
                 class="p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
              <label class="flex items-center space-x-2.5 cursor-pointer">
                <input type="checkbox" v-model="useProposal"
                       class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm font-medium text-blue-800">
                  Gunakan data dari proposal yang disetujui
                </span>
              </label>

              <transition name="slide-down">
                <div v-if="useProposal" class="space-y-3 pt-1">
                  <FormField label="Pilih Proposal" :error="null">
                    <select v-model="selectedProposalId" @change="applyProposal"
                            class="form-input bg-white">
                      <option value="">— Pilih Proposal —</option>
                      <option v-for="p in approvedProposals" :key="p.id" :value="p.id">
                        [{{ p.code }}] {{ p.name }} — {{ fmt(p.approved_amount) }}
                      </option>
                    </select>
                  </FormField>
                  <div v-if="selectedProposalId" class="p-3 bg-white rounded-lg border border-blue-200 text-xs space-y-1 text-gray-600">
                    <p>✓ Data otomatis terisi dari proposal. Anda masih bisa mengubahnya.</p>
                  </div>
                </div>
              </transition>
            </div>
          </transition>

          <!-- ══ BUDGET ALLOCATION ══ -->
          <FormField label="Alokasi Anggaran" :error="form.errors.budget_allocation_id" required>
            <select v-model="form.budget_allocation_id" class="form-input">
              <option value="">— Pilih Alokasi —</option>
              <option v-for="a in allocations" :key="a.id" :value="a.id">
                {{ a.name }} (Sisa: {{ fmt(a.remaining_amount) }})
              </option>
            </select>
          </FormField>

          <!-- ══ NAME ══ -->
          <FormField label="Nama Kegiatan / Operasional" :error="form.errors.name" required>
            <input v-model="form.name" type="text" class="form-input"
                   placeholder="Misal: Seminar Nasional 2025" />
          </FormField>

          <!-- ══ CHAIRPERSON ══ -->
          <FormField label="Ketua Pelaksana" :error="form.errors.chairperson" required>
            <input v-model="form.chairperson" type="text" class="form-input"
                   placeholder="Nama ketua pelaksana" />
          </FormField>

          <!-- ══ PIC ══ -->
          <FormField label="PIC (Penanggung Jawab)" :error="form.errors.pic_id" required>
            <select v-model="form.pic_id" class="form-input">
              <option value="">— Pilih PIC —</option>
              <option v-for="p in pics" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </FormField>

          <!-- ══ AMOUNT ══ -->
          <FormField label="Jumlah Dana (Rp)" :error="form.errors.amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
              <input v-model="form.amount" type="number" min="1" step="1000" class="form-input pl-10"
                     placeholder="0" />
            </div>
          </FormField>

          <!-- ══ DATES ══ -->
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Tanggal Mulai" :error="form.errors.start_date" required>
              <input v-model="form.start_date" type="date" class="form-input" />
            </FormField>
            <FormField label="Tanggal Selesai" :error="form.errors.end_date" required>
              <input v-model="form.end_date" type="date" class="form-input" />
            </FormField>
          </div>

          <!-- ══ SUBMIT ══ -->
          <div class="pt-2 flex gap-3">
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center space-x-2 flex-1 justify-center">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pencairan' }}</span>
            </button>
            <Link :href="route('admin.disbursements.index')" class="btn-secondary">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({
  allocations:       Array,
  pics:              Array,
  approvedProposals: { type: Array, default: () => [] },
})

const { formatRp: fmt } = useCurrency()

const form = useForm({
  purpose:              'activity',
  budget_allocation_id: '',
  name:                 '',
  chairperson:          '',
  pic_id:               '',
  amount:               '',
  start_date:           '',
  end_date:             '',
  proposal_id:          null,
})

const useProposal       = ref(false)
const selectedProposalId = ref('')

const purposeOptions = [
  { value: 'activity',    label: 'Kegiatan',     icon: '🎯', desc: 'Program atau acara terencana' },
  { value: 'operational', label: 'Operasional',  icon: '⚙️', desc: 'Biaya rutin operasional' },
]

// When user unchecks "use proposal" — clear the proposal link
watch(useProposal, (val) => {
  if (!val) {
    selectedProposalId.value = ''
    form.proposal_id = null
  }
})

// When purpose changes away from activity — clear proposal
watch(() => form.purpose, (val) => {
  if (val !== 'activity') {
    useProposal.value = false
    selectedProposalId.value = ''
    form.proposal_id = null
  }
})

function applyProposal() {
  const p = props.approvedProposals.find(x => x.id == selectedProposalId.value)
  if (!p) { form.proposal_id = null; return }

  form.name       = p.name
  form.chairperson = p.chairperson
  form.pic_id     = p.pic_id
  form.amount     = p.approved_amount
  form.start_date = p.start_date
  form.end_date   = p.end_date
  form.proposal_id = p.id
}

function submit() {
  form.post(route('admin.disbursements.store'))
}
</script>
