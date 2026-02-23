<template>
  <AppLayout :title="isEdit ? 'Edit Pencairan' : 'Tambah Pencairan'">
    <div class="max-w-3xl mx-auto">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('admin.disbursements.index')" class="hover:text-green-700">Pencairan</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 font-medium">{{ isEdit ? 'Edit' : 'Tambah' }}</span>
      </nav>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-gray-100">
          <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-green-900">{{ isEdit ? 'Edit Pencairan Anggaran' : 'Tambah Pencairan Anggaran' }}</h2>
            <p class="text-xs text-gray-400">{{ isEdit ? 'Perbarui data pencairan' : 'Buat pencairan anggaran baru' }}</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <!-- Purpose -->
          <FormField label="Tujuan Pencairan" :error="form.errors.purpose" required>
            <div class="grid grid-cols-2 gap-3">
              <button type="button" @click="form.purpose = 'activity'"
                :class="['flex items-center space-x-2.5 p-3 rounded-xl border-2 transition-all',
                  form.purpose === 'activity' ? 'border-blue-500 bg-blue-50 text-blue-800' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="font-medium text-sm">Kegiatan</span>
              </button>
              <button type="button" @click="form.purpose = 'operational'"
                :class="['flex items-center space-x-2.5 p-3 rounded-xl border-2 transition-all',
                  form.purpose === 'operational' ? 'border-teal-500 bg-teal-50 text-teal-800' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                <span class="font-medium text-sm">Operasional</span>
              </button>
            </div>
          </FormField>

          <FormField label="Nama Kegiatan / Operasional" :error="form.errors.name" required>
            <input v-model="form.name" type="text" class="form-input" placeholder="e.g. Seminar Nasional 2025" />
          </FormField>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <FormField label="Tanggal Mulai" :error="form.errors.start_date" required>
              <input v-model="form.start_date" type="date" class="form-input" />
            </FormField>
            <FormField label="Tanggal Selesai" :error="form.errors.end_date" required>
              <input v-model="form.end_date" type="date" class="form-input" />
            </FormField>
          </div>

          <FormField label="PIC (Person In Charge)" :error="form.errors.pic_id" required>
            <select v-model="form.pic_id" class="form-input">
              <option value="">-- Pilih PIC --</option>
              <option v-for="pic in pics" :key="pic.id" :value="pic.id">
                {{ pic.name }} &lt;{{ pic.email }}&gt;
              </option>
            </select>
          </FormField>

          <FormField label="Ketua Pelaksana" :error="form.errors.chairperson" required>
            <input v-model="form.chairperson" type="text" class="form-input" placeholder="Nama ketua pelaksana kegiatan" />
          </FormField>

          <!-- Budget Allocation with remaining display -->
          <FormField label="Alokasi Anggaran" :error="form.errors.budget_allocation_id" required>
            <select v-model="form.budget_allocation_id" @change="updateRemaining" class="form-input">
              <option value="">-- Pilih Alokasi Anggaran --</option>
              <option v-for="a in allocations" :key="a.id" :value="a.id">
                {{ a.name }} — Sisa: Rp {{ fmtNum(a.remaining) }}
              </option>
            </select>
            <Transition name="slide-down">
              <div v-if="selectedAllocation" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex justify-between items-center text-sm">
                  <span class="text-green-700 font-medium">Sisa Anggaran Tersedia</span>
                  <span class="text-green-800 font-bold text-base">{{ fmt(selectedAllocation.remaining) }}</span>
                </div>
                <ProgressBar :percentage="(1 - selectedAllocation.remaining / selectedAllocation.amount) * 100"
                             size="sm" :show-label="false" class="mt-2" />
              </div>
            </Transition>
          </FormField>

          <FormField label="Jumlah Pencairan (Rp)" :error="form.errors.amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
              <input v-model="form.amount" type="text" input="numeric" class="form-input pl-10"
                     placeholder="0" @input="checkBudgetOverrun" />
            </div>
            <p v-if="form.amount" class="mt-1 text-xs text-gray-500 font-medium">{{ fmt(form.amount) }}</p>
            <!-- Inline overrun warning -->
            <div v-if="showOverrunWarning" class="mt-2 flex items-center space-x-2 text-red-600 text-xs">
              <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
              <span>Jumlah melebihi sisa anggaran yang tersedia!</span>
            </div>
          </FormField>

          <!-- Auto Generate -->
          <div :class="['rounded-xl border-2 p-4 transition-all', form.auto_generate ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50']">
            <label class="flex items-start space-x-3 cursor-pointer">
              <div class="relative mt-0.5 shrink-0">
                <input v-model="form.auto_generate" type="checkbox"
                  :disabled="periodDays === null || periodDays < 28 || periodDays > 31"
                  class="sr-only" />
                <div :class="['w-10 h-6 rounded-full transition-all relative',
                  form.auto_generate ? 'bg-green-600' : 'bg-gray-300',
                  (periodDays === null || periodDays < 28 || periodDays > 31) ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer']"
                  @click="(periodDays >= 28 && periodDays <= 31) && (form.auto_generate = !form.auto_generate)">
                  <div :class="['absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform',
                    form.auto_generate ? 'translate-x-5' : 'translate-x-1']"></div>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-800">Auto-generate Periode Berikutnya</p>
                <p class="text-xs text-gray-500 mt-0.5">Otomatis buat pencairan untuk periode selanjutnya (28–31 hari)</p>
              </div>
            </label>
            <p v-if="form.errors.auto_generate" class="mt-2 text-xs text-red-600">{{ form.errors.auto_generate }}</p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" :disabled="form.processing || showOverrunWarning" class="btn-primary flex items-center justify-center space-x-2">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui' : 'Simpan') }}</span>
            </button>
            <Link :href="route('admin.disbursements.index')" class="btn-secondary text-center">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import { useAlert } from '@/Composables/useAlert'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ disbursement: Object, pics: Array, allocations: Array })
const isEdit = !!props.disbursement
const { budgetOverrun } = useAlert()
const { formatRp: fmt } = useCurrency()
const fmtNum = (v) => Number(v).toLocaleString('id-ID')

const form = useForm({
  purpose:              props.disbursement?.purpose              ?? '',
  name:                 props.disbursement?.name                 ?? '',
  start_date:           props.disbursement?.start_date           ?? '',
  end_date:             props.disbursement?.end_date             ?? '',
  pic_id:               props.disbursement?.pic_id               ?? '',
  chairperson:          props.disbursement?.chairperson          ?? '',
  budget_allocation_id: props.disbursement?.budget_allocation_id ?? '',
  amount:               props.disbursement?.amount               ?? '',
  auto_generate:        props.disbursement?.auto_generate        ?? false,
})

const selectedAllocation = ref(
  props.disbursement
    ? props.allocations?.find(a => a.id === props.disbursement.budget_allocation_id) ?? null
    : null
)

const showOverrunWarning = computed(() => {
  if (!selectedAllocation.value || !form.amount) return false
  return parseFloat(form.amount) > selectedAllocation.value.remaining
})

const periodDays = computed(() => {
  if (!form.start_date || !form.end_date) return null
  const diff = (new Date(form.end_date) - new Date(form.start_date)) / 86400000
  return diff < 0 ? null : Math.round(diff)
})

function updateRemaining() {
  selectedAllocation.value = props.allocations?.find(a => a.id === parseInt(form.budget_allocation_id)) ?? null
}

function checkBudgetOverrun() {
  if (showOverrunWarning.value) budgetOverrun(selectedAllocation.value.remaining)
}

function submit() {
  if (isEdit) form.put(route('admin.disbursements.update', props.disbursement.id))
  else form.post(route('admin.disbursements.store'))
}
</script>
