<template>
  <AppLayout title="Dashboard">
    <!-- ══════════════════════════════════════
         SUPERADMIN
    ══════════════════════════════════════ -->
    <template v-if="role === 'superadmin'">
      <div class="page-header">
        <div>
          <h2 class="page-title">Dashboard Superadmin</h2>
          <p class="text-sm text-gray-400">Ringkasan keuangan & aktivitas sistem</p>
        </div>
      </div>

      <!-- Stats row -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
        <StatCard label="Total Anggaran" :value="fmt(stats.total_budget)" icon="💰" color="green" />
        <StatCard label="Total Pencairan" :value="fmt(stats.total_disbursed)" icon="📋" color="blue" />
        <StatCard label="Sisa Anggaran" :value="fmt(stats.remaining_budget)" icon="🏦" color="teal" />
        <StatCard label="Kas Tersedia" :value="fmt(stats.current_cash)" icon="💵" :color="stats.current_cash < 0 ? 'red' : 'green'" />
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <StatCard label="Pengeluaran" :value="fmt(stats.total_expense)" icon="📤" color="red" small />
        <StatCard label="Pemasukan" :value="fmt(stats.total_income)" icon="📥" color="teal" small />
        <StatCard label="Kegiatan Aktif" :value="stats.active_count" icon="⚡" color="amber" small />
        <StatCard label="Pengguna Aktif" :value="stats.user_count" icon="👥" color="indigo" small />
      </div>

      <!-- Monthly chart -->
      <div class="card p-5 mb-6 animate-fade-in-up">
        <h3 class="font-semibold text-green-800 mb-4">Pengeluaran 6 Bulan Terakhir</h3>
        <div class="flex items-end space-x-2 h-40">
          <div v-for="(val, label) in monthly_expenses" :key="label"
               class="flex-1 flex flex-col items-center space-y-1">
            <span class="text-xs text-gray-400">{{ shortFmt(safeNum(val)) }}</span>
            <div class="w-full bg-green-600 rounded-t-md transition-all duration-500"
                 :style="`height: ${barPct(safeNum(val))}px`" />
            <span class="text-xs text-gray-400 truncate w-full text-center">{{ label }}</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Recent disbursements -->
        <div class="card p-5 animate-fade-in-up">
          <h3 class="font-semibold text-green-800 mb-3">Pencairan Terbaru</h3>
          <div class="space-y-2">
            <div v-for="d in recent_disbursements" :key="d.id"
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ d.name }}</p>
                <p class="text-xs text-gray-400">PIC: {{ d.pic_name }}</p>
              </div>
              <div class="text-right shrink-0 ml-3">
                <p class="text-sm font-bold text-gray-800">{{ fmt(d.amount) }}</p>
                <p class="text-xs text-gray-400">{{ safeNum(d.realization_pct).toFixed(1) }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent transactions -->
        <div class="card p-5 animate-fade-in-up">
          <h3 class="font-semibold text-green-800 mb-3">Transaksi Terbaru</h3>
          <div class="space-y-2">
            <div v-for="t in recent_transactions" :key="t.id"
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ t.description }}</p>
                <p class="text-xs text-gray-400">{{ t.disbursement_name }} · {{ t.transaction_date }}</p>
              </div>
              <span :class="['text-sm font-bold shrink-0 ml-2', t.type_value === 'expense' ? 'text-red-600' : 'text-teal-600']">
                {{ t.type_value === 'expense' ? '-' : '+' }}{{ fmt(t.amount) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ══════════════════════════════════════
         PIC
    ══════════════════════════════════════ -->
    <template v-else-if="role === 'pic'">
      <div class="page-header">
        <h2 class="page-title">Dashboard PIC</h2>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
        <StatCard label="Total Pencairan" :value="fmt(stats.total_disbursed)" icon="📋" color="blue" />
        <StatCard label="Total Pengeluaran" :value="fmt(stats.total_expense)" icon="📤" color="red" />
        <StatCard label="Kegiatan Aktif" :value="stats.active_count" icon="⚡" color="green" small />
        <StatCard label="Total Kegiatan" :value="stats.total_count" icon="📁" color="gray" small />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-5 animate-fade-in-up">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-green-800">Pencairan Saya</h3>
            <Link :href="route('pic.disbursements.index')" class="text-xs text-green-600 hover:underline">Lihat semua</Link>
          </div>
          <div class="space-y-3">
            <div v-for="d in disbursements" :key="d.id"
                 class="p-3 rounded-xl border border-gray-100 hover:bg-green-50 transition">
              <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-gray-800 truncate flex-1">{{ d.name }}</p>
                <Badge :color="d.status === 'active' ? 'green' : d.status === 'grace' ? 'amber' : 'gray'">
                  {{ d.status_label }}
                </Badge>
              </div>
              <div class="flex items-center justify-between text-xs text-gray-400">
                <span>{{ fmt(d.amount) }}</span>
                <span>Sisa: {{ fmt(d.remaining_funds) }}</span>
              </div>
              <!-- Realization bar -->
              <div class="mt-2 bg-gray-100 rounded-full h-1.5">
                <div class="bg-green-500 h-1.5 rounded-full transition-all"
                     :style="`width: ${Math.min(100, safeNum(d.realization_pct)).toFixed(1)}%`" />
              </div>
            </div>
          </div>
        </div>

        <div class="card p-5 animate-fade-in-up">
          <h3 class="font-semibold text-green-800 mb-3">Transaksi Terbaru Saya</h3>
          <div class="space-y-2">
            <div v-for="t in recent_transactions" :key="t.id"
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ t.description }}</p>
                <p class="text-xs text-gray-400">{{ t.disbursement_name }} · {{ t.transaction_date }}</p>
              </div>
              <span :class="['text-sm font-bold shrink-0 ml-2', t.type_value === 'expense' ? 'text-red-600' : 'text-teal-600']">
                {{ t.type_value === 'expense' ? '-' : '+' }}{{ fmt(t.amount) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ══════════════════════════════════════
         AUDITOR
    ══════════════════════════════════════ -->
    <template v-else-if="role === 'auditor'">
      <div class="page-header">
        <h2 class="page-title">Dashboard Auditor</h2>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
        <StatCard label="Total Anggaran" :value="fmt(stats.total_budget)" icon="💰" color="green" />
        <StatCard label="Total Pengeluaran" :value="fmt(stats.total_expense)" icon="📤" color="red" />
        <StatCard label="Total Pemasukan" :value="fmt(stats.total_income)" icon="📥" color="teal" />
        <StatCard label="Kas Saat Ini" :value="fmt(stats.current_cash)" icon="💵" color="blue" />
      </div>

      <div class="card p-5 animate-fade-in-up">
        <h3 class="font-semibold text-green-800 mb-3">Pencairan Terbaru</h3>
        <div class="space-y-2">
          <div v-for="d in recent_disbursements" :key="d.id"
               class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-800 truncate">{{ d.name }}</p>
              <p class="text-xs text-gray-400">PIC: {{ d.pic_name }}</p>
            </div>
            <div class="text-right shrink-0 ml-3">
              <p class="text-sm font-bold text-gray-800">{{ fmt(d.amount) }}</p>
              <p class="text-xs" :class="safeNum(d.realization_pct) > 90 ? 'text-red-500' : 'text-gray-400'">
                {{ safeNum(d.realization_pct).toFixed(1) }}% terpakai
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ══════════════════════════════════════
         APPLICANT
    ══════════════════════════════════════ -->
    <template v-else-if="role === 'applicant'">
      <div class="page-header">
        <div>
          <h2 class="page-title">Dashboard Pemohon</h2>
        </div>
        <Link :href="route('applicant.proposals.create')" class="btn-primary flex items-center space-x-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Ajukan Proposal</span>
        </Link>
      </div>

      <div class="grid grid-cols-3 gap-4 mb-6 stagger-children">
        <StatCard label="Total Proposal" :value="stats.total" icon="📄" color="blue" />
        <StatCard label="Menunggu" :value="stats.pending" icon="⏳" color="amber" />
        <StatCard label="Disetujui" :value="stats.approved" icon="✅" color="green" />
      </div>

      <div class="card p-5 animate-fade-in-up">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-green-800">Proposal Terbaru Saya</h3>
          <Link :href="route('applicant.proposals.index')" class="text-xs text-green-600 hover:underline">Lihat semua</Link>
        </div>
        <div v-if="proposals?.length" class="space-y-2">
          <Link v-for="p in proposals" :key="p.id"
                :href="route('applicant.proposals.show', p.id)"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-green-50 transition block">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-800 truncate">{{ p.name }}</p>
              <p class="text-xs text-gray-400">{{ p.code }} · {{ p.created_at }}</p>
            </div>
            <div class="ml-3 text-right shrink-0">
              <Badge :color="p.status_color" :dot="true">{{ p.status_label }}</Badge>
              <p class="text-xs text-gray-400 mt-1">{{ fmt(p.proposed_amount) }}</p>
            </div>
          </Link>
        </div>
        <EmptyState v-else title="Belum ada proposal" description="Klik Ajukan Proposal untuk memulai." />
      </div>
    </template>

    <!-- ══════════════════════════════════════
         APPROVER
    ══════════════════════════════════════ -->
    <template v-else-if="role === 'approver'">
      <div class="page-header">
        <h2 class="page-title">Dashboard Approver</h2>
      </div>

      <div class="grid grid-cols-2 gap-4 mb-6 stagger-children">
        <StatCard label="Menunggu Persetujuan" :value="stats.pending_count" icon="⏳" color="amber" />
        <StatCard label="Sudah Disetujui" :value="stats.approved_count" icon="✅" color="green" />
      </div>

      <div class="card p-5 animate-fade-in-up">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-green-800">Menunggu Persetujuan Anda</h3>
          <Link :href="route('approver.proposals.index')" class="text-xs text-green-600 hover:underline">Lihat semua</Link>
        </div>
        <div v-if="pending_proposals?.length" class="space-y-2">
          <Link v-for="p in pending_proposals" :key="p.id"
                :href="route('approver.proposals.show', p.id)"
                class="flex items-center justify-between p-3 bg-amber-50 rounded-xl hover:bg-amber-100 transition block">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-800 truncate">{{ p.name }}</p>
              <p class="text-xs text-gray-400">{{ p.code }} · Pemohon: {{ p.applicant }}</p>
            </div>
            <p class="text-sm font-bold text-gray-800 shrink-0 ml-3">{{ fmt(p.proposed_amount) }}</p>
          </Link>
        </div>
        <EmptyState v-else title="Tidak ada proposal yang menunggu" description="Anda sudah menyetujui semua proposal yang ditugaskan." />
      </div>
    </template>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCurrency } from '@/Composables/useCurrency'

// ── Props (all optional so Vue never crashes when a role-specific prop is absent) ──
const props = defineProps({
  role:                 { type: String, required: true },
  stats:                { type: Object, default: () => ({}) },
  // Superadmin
  monthly_expenses:     { type: Object, default: () => ({}) },
  recent_disbursements: { type: Array,  default: () => [] },
  recent_transactions:  { type: Array,  default: () => [] },
  // PIC
  disbursements:        { type: Array,  default: () => [] },
  // Applicant
  proposals:            { type: Array,  default: () => [] },
  // Approver
  pending_proposals:    { type: Array,  default: () => [] },
})

const { formatRp: fmt } = useCurrency()

/**
 * WHY safeNum():
 *   Laravel's decimal cast can return numeric strings (e.g. "45.23") not JS numbers.
 *   Calling .toFixed() on a string throws "Cannot read properties of undefined".
 *   Number(val ?? 0) coerces safely: null → 0, undefined → 0, "45.23" → 45.23.
 *   This is the canonical fix — not optional chaining or try/catch.
 */
function safeNum(val) {
  return Number(val ?? 0)
}

// Bar chart helpers (superadmin monthly chart)
const maxExpense = computed(() => {
  const vals = Object.values(props.monthly_expenses).map(v => safeNum(v))
  return Math.max(...vals, 1) // avoid division by zero
})

function barPct(val) {
  return Math.round((safeNum(val) / maxExpense.value) * 120) // max 120px height
}

function shortFmt(val) {
  const n = safeNum(val)
  if (n >= 1_000_000_000) return `${(n / 1_000_000_000).toFixed(1)}M`
  if (n >= 1_000_000)     return `${(n / 1_000_000).toFixed(1)}Jt`
  if (n >= 1_000)         return `${(n / 1_000).toFixed(0)}Rb`
  return n.toString()
}
</script>

<script>
// ── StatCard inline sub-component ───────────────────────────────
import { defineComponent, h } from 'vue'

export const StatCard = defineComponent({
  name: 'StatCard',
  props: {
    label: String,
    value: [String, Number],
    icon:  String,
    color: { type: String, default: 'green' },
    small: Boolean,
  },
  setup(props) {
    const colorMap = {
      green:  'bg-green-50  text-green-800  border-green-100',
      blue:   'bg-blue-50   text-blue-800   border-blue-100',
      red:    'bg-red-50    text-red-800    border-red-100',
      teal:   'bg-teal-50   text-teal-800   border-teal-100',
      amber:  'bg-amber-50  text-amber-800  border-amber-100',
      indigo: 'bg-indigo-50 text-indigo-800 border-indigo-100',
      gray:   'bg-gray-50   text-gray-800   border-gray-100',
    }
    return () =>
      h('div', { class: `card p-4 border animate-fade-in-up ${colorMap[props.color] ?? colorMap.green}` }, [
        h('p', { class: 'text-xs uppercase tracking-wide font-medium opacity-70' }, props.label),
        h('p', { class: `font-bold mt-1 ${props.small ? 'text-xl' : 'text-2xl'}` }, String(props.value ?? 0)),
      ])
  },
})
</script>