<template>
  <AppLayout :title="allocation.name">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
      <Link :href="route('admin.budget-allocations.index')" class="hover:text-green-700">Alokasi Anggaran</Link>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-800 font-medium truncate">{{ allocation.name }}</span>
    </nav>

    <!-- Info Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <div class="lg:col-span-2 card p-6 animate-fade-in-up">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h2 class="text-xl font-bold text-green-900">{{ allocation.name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">
              <Badge :color="allocation.type_value === 'monthly' ? 'blue' : 'purple'">{{ allocation.type }}</Badge>
              &nbsp;·&nbsp; {{ allocation.start_date }} – {{ allocation.end_date }}
            </p>
          </div>
          <div class="flex space-x-2">
            <Link :href="route('admin.budget-allocations.edit', allocation.id)" class="btn-secondary text-sm py-1.5 px-3">Edit</Link>
          </div>
        </div>

        <p v-if="allocation.description" class="text-sm text-gray-600 mb-4 p-3 bg-gray-50 rounded-lg">{{ allocation.description }}</p>

        <!-- Progress -->
        <div class="space-y-2 mb-4">
          <ProgressBar :percentage="allocation.utilization_pct" label="Utilisasi Anggaran" :show-label="true" size="lg" />
        </div>

        <!-- Numbers -->
        <div class="grid grid-cols-3 gap-3 text-center">
          <div class="p-3 bg-green-50 rounded-xl">
            <p class="text-xs text-green-600 font-medium">Total Anggaran</p>
            <p class="text-sm font-bold text-green-800 mt-1">{{ fmt(allocation.amount) }}</p>
          </div>
          <div class="p-3 bg-red-50 rounded-xl">
            <p class="text-xs text-red-600 font-medium">Terpakai</p>
            <p class="text-sm font-bold text-red-700 mt-1">{{ fmt(allocation.total_disbursed) }}</p>
          </div>
          <div class="p-3 bg-teal-50 rounded-xl">
            <p class="text-xs text-teal-600 font-medium">Sisa</p>
            <p class="text-sm font-bold text-teal-800 mt-1">{{ fmt(allocation.remaining) }}</p>
          </div>
        </div>
      </div>

      <!-- Meta -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:80ms">
        <h3 class="font-semibold text-green-800 mb-3 text-sm">Informasi</h3>
        <dl class="space-y-2.5 text-sm">
          <div class="flex justify-between"><dt class="text-gray-500">Dibuat oleh</dt><dd class="font-medium text-gray-800">{{ allocation.creator_name }}</dd></div>
          <div class="flex justify-between"><dt class="text-gray-500">Tanggal dibuat</dt><dd class="font-medium text-gray-800">{{ allocation.created_at }}</dd></div>
          <div class="flex justify-between"><dt class="text-gray-500">Auto-generate</dt>
            <dd><Badge :color="allocation.auto_generate ? 'green' : 'gray'">{{ allocation.auto_generate ? 'Aktif' : 'Tidak' }}</Badge></dd>
          </div>
          <div class="flex justify-between"><dt class="text-gray-500">Total Pencairan</dt><dd class="font-medium text-gray-800">{{ allocation.disbursements?.length || 0 }}</dd></div>
        </dl>
      </div>
    </div>

    <!-- Disbursements List -->
    <div class="card animate-fade-in-up" style="animation-delay:160ms">
      <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-green-800">Pencairan dari Alokasi Ini</h3>
      </div>
      <div v-if="allocation.disbursements?.length" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Nama Kegiatan</Th>
              <Th>PIC</Th>
              <Th>Periode</Th>
              <Th class="text-right">Jumlah</Th>
              <Th>Realisasi</Th>
              <Th>Status</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <tr v-for="d in allocation.disbursements" :key="d.id" class="hover:bg-green-50/40 transition-colors">
              <Td><p class="font-medium">{{ d.name }}</p><p class="text-xs text-gray-400">{{ d.purpose }}</p></Td>
              <Td>{{ d.pic_name }}</Td>
              <Td><p class="text-xs whitespace-nowrap">{{ d.start_date }} – {{ d.end_date }}</p></Td>
              <Td class="text-right font-medium">{{ fmt(d.amount) }}</Td>
              <Td class="min-w-28"><ProgressBar :percentage="d.realization_pct" size="sm" :show-label="false" /></Td>
              <Td>
                <Badge :color="d.status === 'active' ? 'green' : d.status === 'expired' ? 'gray' : 'blue'" :dot="true">{{ d.status_label }}</Badge>
              </Td>
            </tr>
          </tbody>
        </table>
      </div>
      <EmptyState v-else title="Belum ada pencairan" description="Belum ada pencairan yang menggunakan alokasi ini." />
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import Th from '@/Components/Th.vue'
import Td from '@/Components/Td.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'
defineProps({ allocation: Object })
const { formatRp: fmt } = useCurrency()
</script>
