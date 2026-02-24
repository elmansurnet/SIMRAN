<template>
  <AppLayout title="Pengaturan Sistem">
    <div class="max-w-2xl mx-auto">
      <h2 class="page-title mb-6">Pengaturan Sistem</h2>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-gray-100">
          <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-green-900">Pengaturan Umum</h3>
            <p class="text-xs text-gray-400">Konfigurasi perilaku sistem</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <FormField label="Nama Aplikasi" :error="form.errors.app_name" required>
            <input v-model="form.app_name" type="text" class="form-input" placeholder="SIMRAN UNISYA" />
          </FormField>

          <FormField
            label="Hari Tambahan Input Transaksi (Periode Pelaporan)"
            :error="form.errors.extra_transaction_days"
            hint="Jumlah hari setelah tanggal akhir kegiatan di mana PIC masih bisa menginput transaksi. Isi 0 untuk menonaktifkan periode pelaporan."
            required
          >
            <div class="flex items-center space-x-3">
              <input v-model.number="form.extra_transaction_days"
                     type="number" min="0" max="365"
                     class="form-input w-32"
                     placeholder="0" />
              <span class="text-sm text-gray-500">hari</span>
            </div>
          </FormField>

          <!-- Live explanation box -->
          <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 space-y-1">
            <p class="font-semibold">💡 Cara kerja hari tambahan:</p>
            <p>Misal kegiatan berakhir <strong>30 Jun</strong> dan hari tambahan = <strong>{{ form.extra_transaction_days || 0 }}</strong>:</p>
            <ul class="list-disc list-inside space-y-0.5 mt-1">
              <li>Fase 1 (Akan Datang): PIC sudah bisa input rencana transaksi</li>
              <li>Fase 2 (Aktif): transaksi berjalan normal</li>
              <li v-if="(form.extra_transaction_days || 0) > 0">
                Fase 3 (Periode Pelaporan): masih bisa input s/d
                <strong>{{ previewDeadline }}</strong>
              </li>
              <li>Fase 4 (Selesai): transaksi dikunci sepenuhnya</li>
            </ul>
          </div>

          <div class="pt-2">
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center space-x-2">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Active approvers info -->
      <div class="card p-6 mt-5 animate-fade-in-up" style="animation-delay:80ms">
        <h3 class="font-semibold text-green-800 mb-3">Daftar Approver Aktif</h3>
        <p class="text-sm text-gray-500 mb-4">
          Approver dipilih per-proposal saat meneruskan.
          Tambah/hapus approver di menu <strong>Manajemen Pengguna</strong>.
        </p>
        <div v-if="approvers.length" class="space-y-2">
          <div v-for="a in approvers" :key="a.id"
               class="flex items-center space-x-3 p-3 bg-indigo-50 rounded-xl border border-indigo-100">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-xs font-bold text-white">
              {{ a.name.split(' ').map(w => w[0]).slice(0,2).join('') }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-800">{{ a.name }}</p>
              <p class="text-xs text-gray-400">{{ a.email }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 italic">Belum ada approver terdaftar.</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'

const props = defineProps({
  settings:  Object,
  approvers: Array,
})

const form = useForm({
  app_name:               props.settings.app_name,
  extra_transaction_days: props.settings.extra_transaction_days,
})

// Live deadline preview using a fixed example date of June 30
const previewDeadline = computed(() => {
  const base  = new Date('2025-06-30')
  const days  = parseInt(form.extra_transaction_days) || 0
  base.setDate(base.getDate() + days)
  return base.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
})

function submit() {
  form.put(route('admin.settings.update'))
}
</script>