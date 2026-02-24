<template>
  <AppLayout title="Edit Transaksi">
    <div class="max-w-xl mx-auto">
      <!-- Breadcrumb -->
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('pic.disbursements.show', disbursement.id)" class="hover:text-green-700">
          {{ disbursement.name }}
        </Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">Edit Transaksi</span>
      </nav>

      <!-- Phase 3 notice -->
      <div v-if="disbursement.status === 'grace'"
           class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center space-x-2 text-sm text-amber-800">
        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"/>
        </svg>
        <span>Periode pelaporan — transaksi masih bisa diedit.</span>
      </div>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <h2 class="text-lg font-bold text-green-900 mb-5">Edit Transaksi</h2>

        <form @submit.prevent="submit" class="space-y-5">

          <FormField label="Tipe Transaksi" :error="form.errors.type" required>
            <div class="flex gap-3">
              <label class="flex items-center space-x-2 cursor-pointer flex-1 p-3 border rounded-xl transition"
                     :class="form.type === 'expense' ? 'border-red-400 bg-red-50' : 'border-gray-200'">
                <input type="radio" v-model="form.type" value="expense" class="text-red-500" />
                <span class="text-sm font-medium text-gray-700">Pengeluaran</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer flex-1 p-3 border rounded-xl transition"
                     :class="form.type === 'income' ? 'border-teal-400 bg-teal-50' : 'border-gray-200'">
                <input type="radio" v-model="form.type" value="income" class="text-teal-500" />
                <span class="text-sm font-medium text-gray-700">Pemasukan</span>
              </label>
            </div>
          </FormField>

          <FormField label="Tanggal Transaksi" :error="form.errors.transaction_date" required>
            <!--
              :min = start_date  (can't go before the period)
              :max = transaction_deadline = end_date + extra_transaction_days
                     Allows Phase 3 dates; validated identically on the server.
            -->
            <input v-model="form.transaction_date" type="date" class="form-input"
                   :min="disbursement.start_date"
                   :max="disbursement.transaction_deadline" />
          </FormField>

          <FormField label="Keterangan" :error="form.errors.description" required>
            <textarea v-model="form.description" rows="2" class="form-input resize-none"
                      placeholder="Deskripsi transaksi..." />
          </FormField>

          <FormField label="Jumlah (Rp)" :error="form.errors.amount" required>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
              <input v-model="form.amount" type="number" class="form-input pl-10" min="1" step="1" />
            </div>
          </FormField>

          <FormField
            label="Ganti Bukti (PDF / Gambar, maks. 5 MB)"
            :error="form.errors.proof"
            :hint="transaction.proof_name
              ? `Bukti saat ini: ${transaction.proof_name}`
              : 'Belum ada bukti'"
          >
            <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center cursor-pointer hover:border-green-400 transition"
                 @click="$refs.proofInput.click()">
              <input ref="proofInput" type="file" class="hidden" accept=".pdf,image/*"
                     @change="handleFile" />
              <p v-if="selectedFile" class="text-sm font-medium text-gray-700">{{ selectedFile.name }}</p>
              <p v-else class="text-sm text-gray-400">
                {{ transaction.proof_name ? 'Klik untuk mengganti bukti' : 'Klik untuk upload bukti (opsional)' }}
              </p>
            </div>
          </FormField>

          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="btn-primary flex-1">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
            <Link :href="route('pic.disbursements.show', disbursement.id)"
                  class="btn-secondary flex-1 text-center">
              Batal
            </Link>
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

const props = defineProps({
  disbursement: Object,  // { id, name, start_date, end_date, transaction_deadline, status, status_label }
  transaction:  Object,  // { id, type, transaction_date, description, amount, proof_name }
})

const proofInput   = ref(null)
const selectedFile = ref(null)

// Pre-populate from existing transaction data sent by the controller
const form = useForm({
  type:             props.transaction.type,             // 'expense' | 'income'
  transaction_date: props.transaction.transaction_date, // 'Y-m-d' (transaction_date_raw from backend)
  description:      props.transaction.description,
  amount:           props.transaction.amount,
  proof:            null,  // null = keep existing proof; file = replace it
})

function handleFile(e) {
  const file = e.target.files[0]
  if (file) {
    selectedFile.value = file
    form.proof = file
  }
}

function submit() {
  // PUT to: pic.disbursements.transactions.update  →  /pic/disbursements/{id}/transactions/{tx}/
  form.put(
    route('pic.disbursements.transactions.update', [props.disbursement.id, props.transaction.id]),
    { forceFormData: true }
  )
}
</script>