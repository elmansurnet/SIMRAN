<template>
  <AppLayout title="Laporan Global">
    <div class="page-header">
      <div>
        <h2 class="page-title">Laporan Global</h2>
        <p class="text-sm text-gray-400 mt-0.5">Ringkasan realisasi anggaran seluruh institusi</p>
      </div>
      <a :href="route('admin.reports.global')" target="_blank"
         class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span>Unduh PDF</span>
      </a>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 mb-6 stagger-children">
      <StatCard label="Total Anggaran"      :value="stats.total_budget"      icon="💰" color="green"  :delay="0" />
      <StatCard label="Total Pencairan"     :value="stats.total_disbursed"   icon="📋" color="blue"   :delay="80" />
      <StatCard label="Sisa Anggaran"       :value="stats.remaining_budget"  icon="🏦" color="teal"   :delay="160" />
      <StatCard label="Total Realisasi"     :value="stats.total_expense"     icon="📉" color="red"    :delay="240" />
      <StatCard label="Total Pemasukan"     :value="stats.total_income"      icon="📈" color="teal"   :delay="320" />
      <StatCard label="Kas Saat Ini"        :value="stats.current_cash"      icon="💵" color="green"  :delay="400" :highlight="true" />
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
      <!-- Bar chart: monthly expenses -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:200ms">
        <h3 class="font-semibold text-green-800 mb-4">Pengeluaran per Bulan (6 Bulan)</h3>
        <div class="h-52 relative">
          <canvas ref="barCanvas" />
        </div>
      </div>
      <!-- Top disbursements table -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:280ms">
        <h3 class="font-semibold text-green-800 mb-4">Top 10 Kegiatan berdasarkan Realisasi</h3>
        <div class="space-y-3 max-h-52 overflow-y-auto pr-1">
          <div v-for="d in topDisbursements" :key="d.name"
               class="flex items-center space-x-3">
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center mb-1">
                <p class="text-xs font-medium text-gray-700 truncate pr-2">{{ d.name }}</p>
                <span :class="['text-xs font-bold shrink-0', d.realization_pct >= 90 ? 'text-red-600' : d.realization_pct >= 70 ? 'text-amber-600' : 'text-green-700']">
                  {{ d.realization_pct.toFixed(1) }}%
                </span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div :class="['h-1.5 rounded-full transition-all duration-1000',
                  d.realization_pct >= 90 ? 'bg-red-500' : d.realization_pct >= 70 ? 'bg-amber-500' : 'bg-green-500']"
                  :style="{ width: Math.min(d.realization_pct, 100) + '%' }">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Table -->
    <div class="card animate-fade-in-up" style="animation-delay:360ms">
      <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-green-800">Ringkasan Realisasi per Kegiatan</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Kegiatan</Th>
              <Th>PIC</Th>
              <Th>Status</Th>
              <Th class="text-right">Dana</Th>
              <Th class="text-right">Realisasi</Th>
              <Th>Progres</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <tr v-for="(d, i) in topDisbursements" :key="d.name"
                :class="['hover:bg-green-50/40 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
              <Td>
                <p class="font-medium text-gray-800 truncate max-w-48">{{ d.name }}</p>
                <p class="text-xs text-gray-400">{{ d.pic_name }}</p>
              </Td>
              <Td class="text-xs text-gray-500">{{ d.pic_name }}</Td>
              <Td>
                <Badge :color="d.status === 'active' ? 'green' : d.status === 'expired' ? 'gray' : 'blue'" :dot="true">
                  {{ d.status_label }}
                </Badge>
              </Td>
              <Td class="text-right font-medium">{{ fmt(d.amount) }}</Td>
              <Td class="text-right">
                <span :class="['font-semibold', d.realization_pct >= 90 ? 'text-red-600' : d.realization_pct >= 70 ? 'text-amber-600' : 'text-green-700']">
                  {{ fmt(d.total_expense) }}
                </span>
              </Td>
              <Td class="min-w-32">
                <ProgressBar :percentage="d.realization_pct" size="sm" :show-label="false" />
                <p class="text-xs text-gray-400 mt-0.5">{{ d.realization_pct.toFixed(1) }}%</p>
              </Td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import Th from '@/Components/Th.vue'; import Td from '@/Components/Td.vue'
import { useCurrency } from '@/Composables/useCurrency'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({ stats: Object, monthly_expenses: Object, top_disbursements: Array })
const { formatRp: fmt } = useCurrency()
const barCanvas = ref(null)
const topDisbursements = props.top_disbursements || []

onMounted(() => {
  if (barCanvas.value && props.monthly_expenses) {
    const labels  = Object.keys(props.monthly_expenses)
    const data    = Object.values(props.monthly_expenses)
    new Chart(barCanvas.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Pengeluaran (Rp)',
          data,
          backgroundColor: 'rgba(220, 38, 38, 0.7)',
          borderColor: 'rgba(220, 38, 38, 1)',
          borderWidth: 1,
          borderRadius: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: (ctx) => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID') } },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { font: { size: 10 }, callback: (v) => 'Rp ' + (v / 1e6).toFixed(0) + 'jt' },
            grid: { color: 'rgba(0,0,0,0.04)' },
          },
          x: { ticks: { font: { size: 10 } }, grid: { display: false } },
        },
        animation: { duration: 800 },
      },
    })
  }
})
</script>
