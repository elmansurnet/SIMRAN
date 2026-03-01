<template>
  <!-- Public page — no AppLayout, uses its own minimal shell -->
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-white">

    <!-- Navbar -->
    <nav class="bg-green-800 text-white px-6 py-4 shadow-lg">
      <div class="max-w-3xl mx-auto flex items-center justify-between">
        <div>
          <p class="text-lg font-black tracking-wide">SIMRAN UNISYA</p>
          <p class="text-xs text-green-300">Verifikasi Proposal Publik</p>
        </div>
        <a href="/login" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg font-medium transition">Masuk</a>
      </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-10">

      <!-- Search Box -->
      <div class="card p-6 mb-8 shadow-lg">
        <h2 class="text-lg font-bold text-green-900 mb-1">Cari Proposal</h2>
        <p class="text-sm text-gray-400 mb-4">Masukkan kode proposal atau nama kegiatan untuk memverifikasi status.</p>
        <form @submit.prevent="doSearch" class="flex gap-3">
          <input v-model="searchQuery" type="text" class="form-input flex-1"
                 placeholder="Contoh: PROP-2025-0001 atau nama kegiatan..." />
          <button type="submit" :disabled="!searchQuery.trim()" class="btn-primary px-5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </button>
        </form>
      </div>

      <!-- Results -->
      <div v-if="results !== null">
        <div v-if="results.length === 0" class="text-center py-10 text-gray-400">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="font-medium">Proposal tidak ditemukan</p>
          <p class="text-sm mt-1">Coba dengan kode atau nama yang berbeda.</p>
        </div>

        <div v-for="p in results" :key="p.id" class="card p-6 mb-4 shadow-sm animate-fade-in-up">
          <!-- Proposal Header -->
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
            <div>
              <div class="flex flex-wrap gap-2 mb-1">
                <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ p.code }}</span>
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                  p.status === 'approved' ? 'bg-green-100 text-green-800' :
                  p.status === 'forwarded' ? 'bg-blue-100 text-blue-800' :
                  p.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'
                ]">{{ p.status_label }}</span>
              </div>
              <h3 class="text-lg font-bold text-green-900">{{ p.name }}</h3>
              <p class="text-sm text-gray-500 mt-0.5">{{ p.purpose }} · {{ p.start_date }} – {{ p.end_date }}</p>
            </div>
            <a v-if="p.has_certificate" :href="route('proposals.certificate.download', p.id)" target="_blank"
               class="shrink-0 flex items-center space-x-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Pengesahan PDF</span>
            </a>
          </div>

          <!-- Info Grid -->
          <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm mb-4">
            <div><span class="text-gray-400">Pemohon</span><p class="font-medium text-gray-800">{{ p.applicant_name }}</p></div>
            <div><span class="text-gray-400">PIC</span><p class="font-medium text-gray-800">{{ p.pic_name }}</p></div>
            <div><span class="text-gray-400">Ketua Pelaksana</span><p class="font-medium text-gray-800">{{ p.chairperson }}</p></div>
            <div>
              <span class="text-gray-400">Anggaran Disetujui</span>
              <p class="font-bold text-green-700 text-base">{{ p.approved_amount ? fmt(p.approved_amount) : '—' }}</p>
            </div>
          </div>

          <!-- Approved timestamp -->
          <div v-if="p.approved_at" class="text-sm text-green-700 font-medium bg-green-50 rounded-lg px-3 py-1.5 mb-3">
            ✓ Disetujui pada {{ p.approved_at }}
          </div>

          <!-- Approver list -->
          <div v-if="p.approvers?.length">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-2">Approver</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="a in p.approvers" :key="a.name"
                    :class="['text-xs px-2.5 py-1 rounded-full font-medium',
                      a.approved ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500']">
                {{ a.approved ? '✓' : '⏳' }} {{ a.name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Initial state (no search yet) -->
      <div v-else class="text-center py-10 text-gray-400">
        <svg class="w-14 h-14 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-base font-medium">Cari kode atau nama proposal</p>
        <p class="text-sm mt-1">Gunakan kotak pencarian di atas untuk memverifikasi status proposal.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({ results: { type: Array, default: null }, query: String })
const { formatRp: fmt } = useCurrency()

const searchQuery = ref(props.query || '')

function doSearch() {
  router.get(route('proposals.search'), { q: searchQuery.value }, { preserveState: true })
}
</script>
