<template>
  <AppLayout :title="isEdit ? 'Edit Alokasi Anggaran' : 'Tambah Alokasi Anggaran'">
    <div class="max-w-2xl mx-auto">

      <!-- Breadcrumb -->
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('admin.budget-allocations.index')" class="hover:text-green-700 transition">Alokasi Anggaran</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">{{ isEdit ? 'Edit' : 'Tambah' }}</span>
      </nav>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <!-- Title -->
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-gray-100">
          <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-green-900">{{ isEdit ? 'Edit Alokasi Anggaran' : 'Tambah Alokasi Anggaran' }}</h2>
            <p class="text-xs text-gray-400">{{ isEdit ? 'Perbarui data alokasi' : 'Buat alokasi anggaran baru' }}</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <FormField label="Nama Anggaran" :error="form.errors.name" required>
            <input v-model="form.name" type="text" class="form-input" placeholder="e.g. Anggaran Operasional 2025" />
          </FormField>

          <FormField label="Tipe Anggaran" :error="form.errors.type" required>
            <div class="grid grid-cols-2 gap-3">
              <button type="button" @click="form.type = 'monthly'"
                :class="['flex items-center justify-center space-x-2 p-3 rounded-xl border-2 transition-all',
                  form.type === 'monthly' ? 'border-green-600 bg-green-50 text-green-800' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="font-medium text-sm">Bulanan</span>
              </button>
              <button type="button" @click="form.type = 'yearly'"
                :class="['flex items-center justify-center space-x-2 p-3 rounded-xl border-2 transition-all',
                  form.type === 'yearly' ? 'border-green-600 bg-green-50 text-green-800' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="font-medium text-sm">Tahunan</span>
              </button>
            </div>
            <p v-if="form.errors.type" class="mt-1.5 text-xs text-red-600">{{ form.errors.type }}</p>
          </FormField>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <FormField label="Tanggal Mulai" :error="form.errors.start_date" required>
              <input v-model="form.start_date" type="date" class="form-input" />
            </FormField>
            <FormField label="Tanggal Selesai" :error="form.errors.end_date" required>
              <input v-model="form.end_date" type="date" class="form-input" />
            </FormField>
          </div>

          <!-- Period info -->
          <div v-if="periodDays !== null" :class="['flex items-center space-x-2 px-3 py-2 rounded-lg text-sm', periodDays >= 28 && periodDays <= 31 ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700']">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>Durasi periode: <strong>{{ periodDays }} hari</strong>{{ periodDays >= 28 && periodDays <= 31 ? ' — Auto-generate tersedia' : '' }}</span>
          </div>

          <FormField label="Jumlah Anggaran (Rp)" :error="form.errors.amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
              <input v-model="form.amount" type="number" min="1" step="1" class="form-input pl-10" placeholder="0" />
            </div>
            <p v-if="form.amount" class="mt-1 text-xs text-gray-500 font-medium">{{ fmt(form.amount) }}</p>
          </FormField>

          <FormField label="Keterangan" :error="form.errors.description" hint="Opsional — deskripsi singkat tujuan anggaran">
            <textarea v-model="form.description" rows="3" class="form-input resize-none" placeholder="Deskripsi alokasi anggaran..." />
          </FormField>

          <!-- Auto Generate -->
          <div :class="['rounded-xl border-2 p-4 transition-all', form.auto_generate ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50']">
            <label class="flex items-start space-x-3 cursor-pointer">
              <div class="relative mt-0.5">
                <input v-model="form.auto_generate" type="checkbox"
                  :disabled="periodDays === null || periodDays < 28 || periodDays > 31"
                  class="sr-only peer" />
                <div :class="['w-10 h-6 rounded-full transition-all peer-disabled:opacity-40',
                  form.auto_generate ? 'bg-green-600' : 'bg-gray-300']">
                  <div :class="['absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform',
                    form.auto_generate ? 'translate-x-5' : 'translate-x-1']"></div>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-800">Auto-generate Periode Berikutnya</p>
                <p class="text-xs text-gray-500 mt-0.5">Secara otomatis membuat alokasi untuk periode selanjutnya (hanya untuk periode 28–31 hari)</p>
              </div>
            </label>
            <p v-if="form.errors.auto_generate" class="mt-2 text-xs text-red-600">{{ form.errors.auto_generate }}</p>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center justify-center space-x-2">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui' : 'Simpan') }}</span>
            </button>
            <Link :href="route('admin.budget-allocations.index')" class="btn-secondary text-center">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ allocation: Object })
const isEdit = !!props.allocation
const { formatRp: fmt } = useCurrency()

const form = useForm({
  name:          props.allocation?.name          ?? '',
  type:          props.allocation?.type          ?? '',
  start_date:    props.allocation?.start_date    ?? '',
  end_date:      props.allocation?.end_date      ?? '',
  amount:        props.allocation?.amount        ?? '',
  description:   props.allocation?.description   ?? '',
  auto_generate: props.allocation?.auto_generate ?? false,
})

const periodDays = computed(() => {
  if (!form.start_date || !form.end_date) return null
  const diff = (new Date(form.end_date) - new Date(form.start_date)) / 86400000
  return diff < 0 ? null : Math.round(diff)
})

function submit() {
  if (isEdit) form.put(route('admin.budget-allocations.update', props.allocation.id))
  else form.post(route('admin.budget-allocations.store'))
}
</script>
