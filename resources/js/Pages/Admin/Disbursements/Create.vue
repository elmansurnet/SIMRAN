<template>
  <AppLayout :title="isEdit ? 'Edit Pencairan' : 'Tambah Pencairan'">
    <div class="max-w-2xl mx-auto">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('admin.disbursements.index')" class="hover:text-green-700">Pencairan</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">{{ isEdit ? 'Edit' : 'Tambah' }}</span>
      </nav>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <h2 class="text-xl font-bold text-green-900 mb-6">
          {{ isEdit ? 'Edit Pencairan' : 'Buat Pencairan Baru' }}
        </h2>

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

          <!-- ══ PROPOSAL IMPORT (activity only, create mode or edit with existing proposal) ══ -->
          <transition name="slide-down">
            <div v-if="form.purpose === 'activity'"
                 class="p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
              <label class="flex items-center space-x-2.5 cursor-pointer">
                <input type="checkbox" v-model="useProposal"
                       class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm font-medium text-blue-800">
                  {{ isEdit ? 'Terhubung ke proposal yang disetujui' : 'Gunakan data dari proposal yang disetujui' }}
                </span>
              </label>

              <transition name="slide-down">
                <div v-if="useProposal" class="space-y-3 pt-1">
                  <FormField label="Pilih Proposal" :error="null">
                    <select v-model="selectedProposalId" @change="applyProposal"
                            class="form-input bg-white"
                            :disabled="isEdit && !!form.proposal_id">
                      <option value="">— Pilih Proposal —</option>
                      <option v-for="p in approvedProposals" :key="p.id" :value="p.id">
                        [{{ p.code }}] {{ p.name }} — {{ fmt(p.approved_amount) }}
                      </option>
                    </select>
                  </FormField>
                  <div v-if="selectedProposalId" class="p-3 bg-white rounded-lg border border-blue-200 text-xs space-y-1 text-gray-600">
                    <p v-if="isEdit">✓ Pencairan ini terhubung ke proposal. Data yang terhubung tidak dapat diubah melalui formulir ini.</p>
                    <p v-else>✓ Data otomatis terisi dari proposal. Anda masih bisa mengubahnya.</p>
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
              <input v-model="form.amount" type="text" input="numeric" min="1" class="form-input pl-10"
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
            <button type="submit" :disabled="form.processing"
                    class="btn-primary flex items-center space-x-2 flex-1 justify-center">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui Pencairan' : 'Simpan Pencairan') }}</span>
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
  allocations:        Array,
  pics:               Array,
  approvedProposals:  { type: Array, default: () => [] },
  // ✅ ISSUE 1 FIX: editDisbursement prop now accepted.
  //    DisbursementController::edit() passes this; create() passes null.
  editDisbursement:   { type: Object, default: null },
})

const { formatRp: fmt } = useCurrency()

// ✅ isEdit drives all mode-dependent behaviour (title, submit URL, proposal lock)
const isEdit = !!props.editDisbursement

// ✅ Form is hydrated from editDisbursement when in edit mode.
//    Without this, the form always starts empty — the root cause of the regression.
const form = useForm({
  purpose:              props.editDisbursement?.purpose              ?? 'activity',
  budget_allocation_id: props.editDisbursement?.budget_allocation_id ?? '',
  name:                 props.editDisbursement?.name                 ?? '',
  chairperson:          props.editDisbursement?.chairperson          ?? '',
  pic_id:               props.editDisbursement?.pic_id               ?? '',
  amount:               props.editDisbursement?.amount               ?? '',
  start_date:           props.editDisbursement?.start_date           ?? '',
  end_date:             props.editDisbursement?.end_date             ?? '',
  proposal_id:          props.editDisbursement?.proposal_id          ?? null,
})

// ✅ In edit mode, show the proposal checkbox if the disbursement was created from a proposal
const useProposal = ref(isEdit ? !!props.editDisbursement?.proposal_id : false)

// ✅ In edit mode, pre-select the existing proposal in the dropdown (for display only)
const selectedProposalId = ref(
  isEdit ? (props.editDisbursement?.proposal_id ?? '') : ''
)

const purposeOptions = [
  { value: 'activity',    label: 'Kegiatan',    icon: '🎯', desc: 'Program atau acara terencana' },
  { value: 'operational', label: 'Operasional', icon: '⚙️', desc: 'Biaya rutin operasional' },
]

// ✅ ISSUE 1 FIX: purposeWatcher guard.
//    In the original code the watcher fired on initial render and could wipe
//    form.proposal_id when edit mode loaded a non-activity disbursement.
//    We skip the very first watch invocation using a flag.
let purposeWatcherActive = false
watch(() => form.purpose, (val) => {
  if (!purposeWatcherActive) {
    // First call happens on component mount — skip it to preserve edit data.
    purposeWatcherActive = true
    return
  }
  if (val !== 'activity') {
    useProposal.value = false
    selectedProposalId.value = ''
    form.proposal_id = null
  }
})

watch(useProposal, (val) => {
  if (!val) {
    selectedProposalId.value = ''
    form.proposal_id = null
  }
})

// ✅ applyProposal only fires when user manually selects a proposal (create mode).
//    In edit mode the select is disabled so this won't run.
function applyProposal() {
  if (isEdit) return  // extra safety guard
  const p = props.approvedProposals.find(x => x.id == selectedProposalId.value)
  if (!p) { form.proposal_id = null; return }

  form.name        = p.name
  form.chairperson = p.chairperson
  form.pic_id      = p.pic_id
  form.amount      = p.approved_amount
  form.start_date  = p.start_date
  form.end_date    = p.end_date
  form.proposal_id = p.id
}

function submit() {
  if (isEdit) {
    // ✅ ISSUE 1 FIX: edit mode submits to update route with disbursement ID
    form.put(route('admin.disbursements.update', props.editDisbursement.id))
  } else {
    form.post(route('admin.disbursements.store'))
  }
}
</script>