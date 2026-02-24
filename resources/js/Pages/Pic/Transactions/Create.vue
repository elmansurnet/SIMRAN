<template>
  <AppLayout title="Tambah Transaksi">
    <div class="max-w-2xl mx-auto">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('pic.disbursements.index')" class="hover:text-green-700">Pencairan</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <Link :href="route('pic.disbursements.show', disbursement.id)"
              class="hover:text-green-700 truncate max-w-32">{{ disbursement.name }}</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">Tambah Transaksi</span>
      </nav>

      <!-- Phase 1 (Akan Datang) banner -->
      <div v-if="disbursement.status === 'upcoming'"
           class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center space-x-2 text-sm text-blue-700">
        <svg class="w-4 h-4 shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
            clip-rule="evenodd"/>
        </svg>
        <span>Periode belum dimulai — tanggal transaksi harus mulai dari <strong>{{ formatDate(disbursement.start_date) }}</strong>.</span>
      </div>

      <!-- Phase 3 (Periode Pelaporan) banner -->
      <div v-if="disbursement.status === 'grace'"
           class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center space-x-2 text-sm text-amber-800">
        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"/>
        </svg>
        <span>
          Periode pelaporan — transaksi masih bisa ditambahkan hingga
          <strong>{{ formatDate(disbursement.transaction_deadline) }}</strong>.
        </span>
      </div>

      <!-- Context card -->
      <div class="card p-4 mb-5 bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 animate-slide-down">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-green-600 font-medium">{{ disbursement.status_label }}</p>
            <p class="text-sm font-bold text-green-900 mt-0.5">{{ disbursement.name }}</p>
            <p class="text-xs text-green-600">
              {{ formatDate(disbursement.start_date) }} – {{ formatDate(disbursement.end_date) }}
            </p>
          </div>
          <div class="text-right">
            <p class="text-xs text-green-600">Sisa Dana</p>
            <p class="text-lg font-bold text-green-800">{{ fmt(disbursement.remaining_funds) }}</p>
          </div>
        </div>
      </div>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <!-- Type selector -->
        <div class="flex rounded-xl border-2 border-gray-200 overflow-hidden mb-6">
          <button type="button" @click="form.type = 'expense'"
                  :class="['flex-1 py-3 text-sm font-semibold transition-all flex items-center justify-center space-x-2',
                    form.type === 'expense' ? 'bg-red-600 text-white' : 'text-gray-500 hover:bg-gray-50']">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
            </svg>
            <span>Pengeluaran</span>
          </button>
          <button type="button" @click="form.type = 'income'"
                  :class="['flex-1 py-3 text-sm font-semibold transition-all flex items-center justify-center space-x-2',
                    form.type === 'income' ? 'bg-teal-600 text-white' : 'text-gray-500 hover:bg-gray-50']">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
            </svg>
            <span>Pemasukan</span>
          </button>
        </div>
        <p v-if="form.errors.type" class="text-xs text-red-600 -mt-4 mb-4">{{ form.errors.type }}</p>

        <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">

          <FormField label="Tanggal Transaksi" :error="form.errors.transaction_date" required>
            <!--
              :min = start_date (even during Phase 1, user can't pick a date before period)
              :max = transaction_deadline = end_date + extra_transaction_days
                     This allows Phase 3 dates while blocking Phase 4.
            -->
            <input v-model="form.transaction_date" type="date" class="form-input"
                   :min="disbursement.start_date"
                   :max="disbursement.transaction_deadline" />
            <p class="text-xs text-gray-400 mt-1">
              Rentang: {{ formatDate(disbursement.start_date) }} – {{ formatDate(disbursement.transaction_deadline) }}
            </p>
          </FormField>

          <FormField label="Keterangan" :error="form.errors.description" required>
            <textarea v-model="form.description" rows="3" class="form-input resize-none"
                      placeholder="Deskripsi transaksi yang jelas..." />
          </FormField>

          <FormField label="Jumlah (Rp)" :error="form.errors.amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
              <input v-model="form.amount" type="number" min="1" step="1" class="form-input pl-10" placeholder="0" />
            </div>
            <p v-if="form.amount" class="mt-1 text-xs font-medium text-gray-600">{{ fmt(form.amount) }}</p>
            <div v-if="form.amount && form.type"
                 :class="['mt-2 p-2.5 rounded-lg text-xs',
                   projectedBalance < 0 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700']">
              <span class="font-medium">Proyeksi sisa setelah transaksi: </span>
              <span class="font-bold">{{ fmt(projectedBalance) }}</span>
              <span v-if="projectedBalance < 0" class="ml-1 font-semibold">⚠ Saldo negatif!</span>
            </div>
          </FormField>

          <FormField label="Bukti Transaksi" :error="form.errors.proof" hint="Format: JPG, PNG, PDF — Maks. 5MB">
            <div @dragover.prevent="isDragging = true"
                 @dragleave="isDragging = false"
                 @drop.prevent="handleDrop"
                 :class="['border-2 border-dashed rounded-xl p-5 text-center transition-all cursor-pointer',
                   isDragging ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-green-400 hover:bg-green-50/30',
                   previewFile ? 'border-green-400 bg-green-50' : '']"
                 @click="$refs.fileInput.click()">
              <input ref="fileInput" type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                     @change="handleFileChange" />
              <div v-if="!previewFile" class="space-y-2">
                <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm text-gray-500">Klik atau seret file ke sini</p>
                <p class="text-xs text-gray-400">JPG, PNG, PDF — maks. 5MB</p>
              </div>
              <div v-else class="flex items-center space-x-3 justify-center">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="text-left">
                  <p class="text-sm font-medium text-green-800">{{ previewFile.name }}</p>
                  <p class="text-xs text-green-600">{{ (previewFile.size / 1024).toFixed(1) }} KB</p>
                </div>
                <button type="button" @click.stop="clearFile" class="text-red-400 hover:text-red-600 transition">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                      clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
          </FormField>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
                    :class="['flex items-center justify-center space-x-2 px-6 py-2.5 rounded-lg font-medium text-sm text-white transition-all disabled:opacity-50 active:scale-95',
                      form.type === 'income' ? 'bg-teal-600 hover:bg-teal-700' : 'bg-red-600 hover:bg-red-700']">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}</span>
            </button>
            <Link :href="route('pic.disbursements.show', disbursement.id)" class="btn-secondary text-center">
              Batal
            </Link>
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
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ disbursement: Object })
const { formatRp: fmt } = useCurrency()
const isDragging = ref(false)
const previewFile = ref(null)
const fileInput = ref(null)

const form = useForm({
  type:             '',
  transaction_date: '',
  description:      '',
  amount:           '',
  proof:            null,
})

const projectedBalance = computed(() => {
  if (!form.amount || !form.type) return null
  const amt = parseFloat(form.amount) || 0
  return form.type === 'income'
    ? props.disbursement.remaining_funds + amt
    : props.disbursement.remaining_funds - amt
})

function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}

function handleFileChange(e) {
  const file = e.target.files[0]
  if (file) { previewFile.value = file; form.proof = file }
}

function handleDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file) { previewFile.value = file; form.proof = file }
}

function clearFile() {
  previewFile.value = null
  form.proof = null
  if (fileInput.value) fileInput.value.value = ''
}

function submit() {
  form.post(route('pic.disbursements.transactions.store', props.disbursement.id), {
    forceFormData: true,
  })
}
</script>