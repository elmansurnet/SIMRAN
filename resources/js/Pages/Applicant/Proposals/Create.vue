<template>
  <AppLayout title="Ajukan Proposal">
    <div class="max-w-2xl mx-auto">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('applicant.proposals.index')" class="hover:text-green-700">Proposal Saya</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 font-medium">Ajukan Baru</span>
      </nav>

      <h2 class="page-title mb-6">Ajukan Proposal Kegiatan</h2>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <form @submit.prevent="submit" class="space-y-5">

          <!-- Tujuan -->
          <FormField label="Tujuan Kegiatan" :error="form.errors.purpose" required>
            <div class="flex gap-3">
              <label class="flex items-center space-x-2 cursor-pointer flex-1 p-3 border rounded-xl transition"
                     :class="form.purpose === 'activity' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" v-model="form.purpose" value="activity" class="text-green-600" />
                <span class="text-sm font-medium text-gray-700">Kegiatan</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer flex-1 p-3 border rounded-xl transition"
                     :class="form.purpose === 'operational' ? 'border-teal-500 bg-teal-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" v-model="form.purpose" value="operational" class="text-teal-600" />
                <span class="text-sm font-medium text-gray-700">Operasional</span>
              </label>
            </div>
          </FormField>

          <!-- Name -->
          <FormField label="Nama Kegiatan / Proposal" :error="form.errors.name" required>
            <input v-model="form.name" type="text" class="form-input" placeholder="Contoh: Seminar Nasional Teknologi Pendidikan 2025" />
          </FormField>

          <!-- Period -->
          <div class="grid grid-cols-2 gap-4">
            <FormField label="Tanggal Mulai" :error="form.errors.start_date" required>
              <input v-model="form.start_date" type="date" class="form-input" />
            </FormField>
            <FormField label="Tanggal Selesai" :error="form.errors.end_date" required>
              <input v-model="form.end_date" type="date" class="form-input" :min="form.start_date" />
            </FormField>
          </div>

          <!-- PIC -->
          <FormField label="PIC (Penanggung Jawab)" :error="form.errors.pic_id" required>
            <select v-model="form.pic_id" class="form-input">
              <option value="">Pilih PIC...</option>
              <option v-for="p in pics" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </FormField>

          <!-- Chairperson -->
          <FormField label="Ketua Pelaksana" :error="form.errors.chairperson" required>
            <input v-model="form.chairperson" type="text" class="form-input" placeholder="Nama ketua pelaksana kegiatan" />
          </FormField>

          <!-- Amount -->
          <FormField label="Jumlah Anggaran yang Diusulkan (Rp)" :error="form.errors.proposed_amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
              <input v-model="form.proposed_amount" type="text" input="numeric" class="form-input pl-10" placeholder="0" />
            </div>
            <p v-if="form.proposed_amount" class="text-xs text-gray-400 mt-1">
              {{ fmt(Number(form.proposed_amount)) }}
            </p>
          </FormField>

          <!-- PDF Upload -->
          <FormField label="Upload Proposal (PDF, maks 10 MB)" :error="form.errors.proposal_pdf">
            <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-green-400 transition cursor-pointer"
                 @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="handleDrop">
              <input ref="fileInput" type="file" accept=".pdf" class="hidden" @change="handleFile" />
              <div v-if="selectedFile">
                <svg class="w-8 h-8 text-red-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <p class="text-sm font-medium text-gray-700">{{ selectedFile.name }}</p>
                <p class="text-xs text-gray-400">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
              </div>
              <div v-else>
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <p class="text-sm text-gray-400">Klik atau drag PDF ke sini</p>
                <p class="text-xs text-gray-300">Opsional — maks 10 MB</p>
              </div>
            </div>
          </FormField>

          <!-- Submit -->
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center space-x-2 flex-1 justify-center">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              <span>{{ form.processing ? 'Mengajukan...' : 'Ajukan Proposal' }}</span>
            </button>
            <Link :href="route('applicant.proposals.index')" class="btn-secondary flex-1 text-center">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ pics: Array })
const { formatRp: fmt } = useCurrency()

const fileInput   = ref(null)
const selectedFile = ref(null)

const form = useForm({
  purpose:         'activity',
  name:            '',
  start_date:      '',
  end_date:        '',
  pic_id:          '',
  chairperson:     '',
  proposed_amount: '',
  proposal_pdf:    null,
})

function handleFile(e) {
  const file = e.target.files[0]
  if (file) { selectedFile.value = file; form.proposal_pdf = file }
}
function handleDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file?.type === 'application/pdf') { selectedFile.value = file; form.proposal_pdf = file }
}

function submit() {
  form.post(route('applicant.proposals.store'))
}
</script>
