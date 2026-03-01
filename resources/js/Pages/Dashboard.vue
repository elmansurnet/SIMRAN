<template>
  <AppLayout title="Dashboard">

    <!-- ── Welcome Banner ─────────────────────────────── -->
    <div class="bg-gradient-to-r from-green-800 via-green-700 to-emerald-600 rounded-2xl p-5 sm:p-6 mb-6 text-white overflow-hidden relative animate-fade-in">
      <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full"></div>
      <div class="absolute -right-2 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
      <div class="relative">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-green-200 text-sm font-medium">Selamat datang,</p>
            <h2 class="text-2xl font-bold mt-0.5">{{ user.name }}</h2>
            <p class="text-green-200 text-sm mt-1 flex items-center space-x-2">
              <span class="px-2 py-0.5 bg-white/20 rounded-full text-xs font-semibold">{{ user.role }}</span>
              <span>{{ currentDateTime }}</span>
            </p>
          </div>
          <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-white/20 text-3xl shrink-0">
            {{ user.initials }}
          </div>
        </div>
      </div>
    </div>

    <!-- ── Low Budget Alerts (Superadmin / Auditor) ─── -->
    <div v-if="lowBudgetAlerts?.length" class="mb-6 animate-slide-down">
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start space-x-3">
          <div class="shrink-0 mt-0.5">
            <svg class="w-5 h-5 text-amber-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-amber-800">{{ lowBudgetAlerts.length }} alokasi anggaran mendekati batas minimum</p>
            <div class="mt-2 flex flex-wrap gap-2">
              <span v-for="a in lowBudgetAlerts.slice(0, 4)" :key="a.id"
                    class="inline-flex items-center px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg text-xs font-medium">
                {{ a.name }}: Rp {{ fmt(a.remaining) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Stats Grid ──────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6 stagger-children">

      <!-- SUPERADMIN Stats -->
      <template v-if="user.role_value === 'superadmin'">
        <StatCard label="Total Anggaran"          :value="num(stats.total_budget)"        icon="💰" color="green"  :delay="0" />
        <StatCard label="Total Pencairan"         :value="num(stats.total_disbursed)"     icon="📋" color="blue"   :delay="80" />
        <StatCard label="Sisa Anggaran"           :value="num(stats.remaining_budget)"    icon="🏦" color="teal"   :delay="160" />
        <StatCard label="Total Realisasi"         :value="num(stats.total_expense)"       icon="📉" color="red"    :delay="240" />
        <StatCard label="Total Pemasukan"         :value="num(stats.total_income)"        icon="📈" color="teal"   :delay="320" />
        <StatCard label="Kas Saat Ini"            :value="num(stats.current_cash)"        icon="💵" color="green"  :delay="400" :highlight="true" />
        <StatCard label="Total Pengguna"          :value="num(stats.total_users)"         icon="👥" color="purple" :delay="480" :is-currency="false" />
        <StatCard label="Kegiatan Aktif"          :value="num(stats.active_disbursements)" icon="🔔" color="orange" :delay="560" :is-currency="false" />
        <StatCard label="Total Pencairan (semua)" :value="num(stats.total_disbursements)" icon="📌" color="indigo" :delay="640" :is-currency="false" />
      </template>

      <!-- PIC Stats -->
      <template v-else-if="user.role_value === 'pic'">
        <StatCard label="Total Dana Pencairan"  :value="num(stats.total_disbursed)"      icon="💰" color="green"  :delay="0" />
        <StatCard label="Total Pengeluaran"     :value="num(stats.total_expense)"        icon="📉" color="red"    :delay="80" />
        <StatCard label="Total Pemasukan"       :value="num(stats.total_income)"         icon="📈" color="teal"   :delay="160" />
        <StatCard label="Sisa Dana Keseluruhan" :value="num(stats.remaining_funds)"      icon="💵" color="green"  :delay="240" :highlight="true" />
        <StatCard label="Kegiatan Aktif"        :value="num(stats.active_disbursements)" icon="🔔" color="orange" :delay="320" :is-currency="false" />
        <StatCard label="Total Kegiatan"        :value="num(stats.total_disbursements)"  icon="📋" color="blue"   :delay="400" :is-currency="false" />
      </template>

      <!-- AUDITOR Stats -->
      <template v-else-if="user.role_value === 'auditor'">
        <StatCard label="Total Anggaran"      :value="num(stats.total_budget)"      icon="💰" color="green"  :delay="0" />
        <StatCard label="Total Pencairan"     :value="num(stats.total_disbursed)"   icon="📋" color="blue"   :delay="80" />
        <StatCard label="Sisa Anggaran"       :value="num(stats.remaining_budget)"  icon="🏦" color="teal"   :delay="160" />
        <StatCard label="Total Realisasi"     :value="num(stats.total_expense)"     icon="📉" color="red"    :delay="240" />
        <StatCard label="Total Pemasukan"     :value="num(stats.total_income)"      icon="📈" color="teal"   :delay="320" />
        <StatCard label="Kas Saat Ini"        :value="num(stats.current_cash)"      icon="💵" color="green"  :delay="400" :highlight="true" />
      </template>

      <!-- APPLICANT Stats -->
      <template v-else-if="user.role_value === 'applicant'">
        <StatCard label="Total Proposal"        :value="num(stats.total)"    icon="📄" color="blue"   :delay="0"   :is-currency="false" />
        <StatCard label="Disetujui"             :value="num(stats.approved)" icon="✅" color="green"  :delay="80"  :is-currency="false" />
        <StatCard label="Menunggu Persetujuan"  :value="num(stats.pending)"  icon="⏳" color="orange" :delay="160" :is-currency="false" />
        <StatCard label="Ditolak"               :value="num(stats.rejected)" icon="❌" color="red"    :delay="240" :is-currency="false" />
      </template>

      <!-- APPROVER Stats -->
      <template v-else-if="user.role_value === 'approver'">
        <StatCard label="Menunggu Persetujuan Saya" :value="num(stats.pending_count)"  icon="⏳" color="orange" :delay="0"   :is-currency="false" />
        <StatCard label="Sudah Saya Setujui"        :value="num(stats.approved_count)" icon="✅" color="green"  :delay="80"  :is-currency="false" />
        <StatCard label="Total Ditugaskan"          :value="num(stats.total_count)"    icon="📋" color="blue"   :delay="160" :is-currency="false" />
      </template>

    </div>

    <!-- ── Charts (only for roles that have financial charts) ── -->
    <div v-if="showCharts" class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

      <!-- Line/Bar Chart -->
      <div class="xl:col-span-2 card p-5 animate-fade-in-up" style="animation-delay:200ms">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-green-800">
            {{ user.role_value === 'pic' ? 'Pengeluaran 6 Bulan Terakhir' : 'Tren Keuangan 6 Bulan Terakhir' }}
          </h3>
          <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">6 bulan terakhir</span>
        </div>
        <div class="relative h-52">
          <canvas ref="chartCanvas" />
        </div>
      </div>

      <!-- Donut chart -->
      <div class="card p-5 animate-fade-in-up" style="animation-delay:280ms">
        <h3 class="font-semibold text-green-800 mb-4">
          {{ user.role_value === 'pic' ? 'Utilisasi Dana' : 'Utilisasi Anggaran' }}
        </h3>
        <div class="relative h-40 flex items-center justify-center">
          <canvas ref="donutCanvas" class="absolute inset-0" />
          <div class="text-center z-10 pointer-events-none">
            <!-- ✅ toFixed() is safe: donutPct is always a number (0 fallback) -->
            <p class="text-2xl font-bold text-green-700">{{ donutPct.toFixed(1) }}%</p>
            <p class="text-xs text-gray-400">Terpakai</p>
          </div>
        </div>
        <div class="mt-3 space-y-2">
          <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-1.5">
              <div class="w-3 h-3 rounded-full bg-green-500"></div>
              <span class="text-gray-600">{{ user.role_value === 'pic' ? 'Sisa Dana' : 'Sisa Anggaran' }}</span>
            </div>
            <span class="font-medium text-gray-700">{{ fmt(remainingForDonut) }}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-1.5">
              <div class="w-3 h-3 rounded-full bg-red-400"></div>
              <span class="text-gray-600">Terpakai</span>
            </div>
            <span class="font-medium text-gray-700">{{ fmt(usedForDonut) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Active Disbursements (PIC only) ───────────── -->
    <div v-if="user.role_value === 'pic' && activeDisbursements?.length" class="mb-6">
      <h3 class="font-semibold text-green-800 mb-3 flex items-center space-x-2">
        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
        <span>Kegiatan Aktif Saya</span>
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link
          v-for="d in activeDisbursements" :key="d.id"
          :href="route('pic.disbursements.show', d.id)"
          class="card p-4 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 animate-fade-in-up"
        >
          <div class="flex justify-between items-start mb-2">
            <p class="text-sm font-semibold text-gray-800 truncate pr-2">{{ d.name }}</p>
              <span v-if="d.days_remaining >= 0" class="shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="{
                  'bg-blue-100 text-blue-700':  d.status === 'upcoming',
                  'bg-green-100 text-green-700': d.status === 'active',
                  'bg-amber-100 text-amber-700': d.status === 'grace'
                }">
                <template v-if="d.status === 'upcoming'">
                  Mulai {{ d.days_remaining }} hari lagi
                </template>

                <template v-else-if="d.status === 'grace'">
                  Batas laporan {{ d.days_remaining }} hari lagi
                </template>

                <template v-else>
                  Sedang berlangsung
                </template>
              </span>
          </div>
          <ProgressBar :percentage="num(d.realization_pct)" size="sm" :show-label="false" class="mb-2" />
          <div class="flex justify-between text-xs text-gray-500">
            <!-- ✅ num() ensures always a number before .toFixed() -->
            <span>Realisasi: {{ num(d.realization_pct).toFixed(1) }}%</span>
            <span class="font-medium text-green-700">Sisa: {{ fmt(d.remaining_funds) }}</span>
          </div>
        </Link>
      </div>
    </div>

    <!-- ── Applicant: Proposal List ────────────────────── -->
    <div v-if="user.role_value === 'applicant'" class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-green-800">Proposal Terbaru Saya</h3>
        <Link :href="route('applicant.proposals.index')" class="text-xs text-green-600 hover:text-green-800 font-medium">
          Lihat semua →
        </Link>
      </div>
      <div v-if="proposals?.length" class="card divide-y divide-gray-50">
        <div v-for="p in proposals" :key="p.id"
             class="px-5 py-3 flex items-center space-x-3 hover:bg-gray-50 transition">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ p.name }}</p>
            <p class="text-xs text-gray-400 font-mono">{{ p.code }} · {{ p.created_at }}</p>
          </div>
          <div class="text-right shrink-0">
            <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold',
              p.status_color === 'green' ? 'bg-green-100 text-green-700' :
              p.status_color === 'red'   ? 'bg-red-100 text-red-700' :
              p.status_color === 'amber' ? 'bg-amber-100 text-amber-700' :
              'bg-gray-100 text-gray-600']">
              {{ p.status_label }}
            </span>
            <p class="text-xs text-gray-500 mt-0.5">{{ fmt(p.proposed_amount) }}</p>
          </div>
        </div>
      </div>
      <div v-else class="card p-8 text-center">
        <p class="text-sm text-gray-400">Belum ada proposal. <Link :href="route('applicant.proposals.create')" class="text-green-600 font-medium">Ajukan sekarang →</Link></p>
      </div>
    </div>

    <!-- ── Approver: Pending Proposals ─────────────────── -->
    <div v-if="user.role_value === 'approver'" class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-green-800 flex items-center space-x-2">
          <span v-if="num(stats.pending_count) > 0" class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
          <span>Proposal Menunggu Persetujuan Saya</span>
        </h3>
        <Link :href="route('approver.proposals.index')" class="text-xs text-green-600 hover:text-green-800 font-medium">
          Lihat semua →
        </Link>
      </div>
      <div v-if="pendingProposals?.length" class="card divide-y divide-gray-50">
        <Link v-for="p in pendingProposals" :key="p.id"
              :href="route('approver.proposals.show', p.id)"
              class="px-5 py-3 flex items-center space-x-3 hover:bg-gray-50 transition block">
          <div class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ p.name }}</p>
            <p class="text-xs text-gray-400">{{ p.applicant }} · {{ p.code }}</p>
          </div>
          <p class="text-sm font-semibold text-gray-700 shrink-0">{{ fmt(p.proposed_amount) }}</p>
        </Link>
      </div>
      <div v-else class="card p-8 text-center">
        <p class="text-sm text-gray-400">Tidak ada proposal yang perlu disetujui. ✅</p>
      </div>
    </div>

    <!-- ── Bottom Grid (Superadmin / PIC / Auditor) ────── -->
    <div v-if="showBottomGrid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- Recent Disbursements -->
      <div v-if="recentDisbursements?.length" class="card animate-fade-in-up" style="animation-delay:320ms">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-green-800">Pencairan Terbaru</h3>
          <Link
            :href="user.role_value === 'superadmin' ? route('admin.disbursements.index') : route('auditor.disbursements.index')"
            class="text-xs text-green-600 hover:text-green-800 font-medium"
          >Lihat semua →</Link>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-for="d in recentDisbursements" :key="d.id"
               class="px-5 py-3 flex items-center space-x-3 hover:bg-gray-50 transition">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-base"
                 :class="d.status === 'active' ? 'bg-green-100' : d.status === 'expired' ? 'bg-gray-100' : 'bg-blue-100'">
              {{ d.status === 'active' ? '🟢' : d.status === 'expired' ? '⚫' : '🔵' }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ d.name }}</p>
              <p class="text-xs text-gray-400">{{ d.pic_name }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-sm font-semibold text-gray-700">{{ fmt(d.amount) }}</p>
              <!-- ✅ d.realization: backend now sends this key (was only realization_pct) -->
              <p class="text-xs text-gray-400">{{ num(d.realization).toFixed(1) }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div v-if="recentTransactions?.length" class="card animate-fade-in-up" style="animation-delay:400ms">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h3 class="font-semibold text-green-800">Transaksi Terbaru</h3>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-for="t in recentTransactions" :key="t.id"
               class="px-5 py-3 flex items-center space-x-3 hover:bg-gray-50 transition">
            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
              t.type_value === 'expense' ? 'bg-red-100 text-red-700' : 'bg-teal-100 text-teal-700']">
              {{ t.type_value === 'expense' ? '↓' : '↑' }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-700 truncate">{{ t.description }}</p>
              <!-- ✅ t.date: backend now sends this key (was only transaction_date) -->
              <p class="text-xs text-gray-400 truncate">{{ t.disbursement_name }} · {{ t.date }}</p>
            </div>
            <p :class="['text-sm font-semibold shrink-0',
              t.type_value === 'expense' ? 'text-red-600' : 'text-teal-600']">
              {{ t.type_value === 'expense' ? '-' : '+' }}{{ fmt(t.amount) }}
            </p>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Quick Actions (Superadmin) ─────────────────── -->
    <div v-if="user.role_value === 'superadmin'" class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3 animate-fade-in-up">
      <Link :href="route('admin.budget-allocations.create')"
            class="flex flex-col items-center justify-center p-4 rounded-xl bg-green-700 text-white hover:bg-green-800 transition-all duration-200 hover:-translate-y-0.5 shadow-sm">
        <svg class="w-6 h-6 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="text-xs font-semibold text-center">Tambah Anggaran</span>
      </Link>
      <Link :href="route('admin.disbursements.create')"
            class="flex flex-col items-center justify-center p-4 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 hover:-translate-y-0.5 shadow-sm">
        <svg class="w-6 h-6 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="text-xs font-semibold text-center">Buat Pencairan</span>
      </Link>
      <Link :href="route('admin.users.create')"
            class="flex flex-col items-center justify-center p-4 rounded-xl bg-purple-600 text-white hover:bg-purple-700 transition-all duration-200 hover:-translate-y-0.5 shadow-sm">
        <svg class="w-6 h-6 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
        <span class="text-xs font-semibold text-center">Tambah Pengguna</span>
      </Link>
      <a :href="route('admin.reports.global')" target="_blank"
         class="flex flex-col items-center justify-center p-4 rounded-xl bg-amber-600 text-white hover:bg-amber-700 transition-all duration-200 hover:-translate-y-0.5 shadow-sm">
        <svg class="w-6 h-6 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span class="text-xs font-semibold text-center">Cetak Laporan</span>
      </a>
    </div>

    <!-- ── Quick Actions (Applicant) ──────────────────── -->
    <div v-if="user.role_value === 'applicant'" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
      <Link :href="route('applicant.proposals.create')"
            class="flex items-center space-x-3 p-4 rounded-xl bg-teal-700 text-white hover:bg-teal-800 transition-all shadow-sm hover:-translate-y-0.5">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span class="text-sm font-semibold">Ajukan Proposal Baru</span>
      </Link>
      <Link :href="route('applicant.proposals.index')"
            class="flex items-center space-x-3 p-4 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-sm hover:-translate-y-0.5">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="text-sm font-semibold">Lihat Semua Proposal</span>
      </Link>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import ProgressBar from '@/Components/ProgressBar.vue'
import { useCurrency } from '@/Composables/useCurrency'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  role:                 String,
  stats:                Object,
  chart_data:           Array,
  recent_disbursements: Array,
  recent_transactions:  Array,
  active_disbursements: Array,
  proposals:            Array,   // applicant
  pending_proposals:    Array,   // approver
})

const page = usePage()
const user = computed(() => page.props.auth.user)
const { formatRp: fmt } = useCurrency()

const chartCanvas = ref(null)
const donutCanvas = ref(null)

// ✅ Safe number helper: prevents toFixed() crash when value is undefined/null/NaN.
//    Root cause of "Cannot read properties of undefined (reading 'toFixed')":
//    backend sent no value or different key name; this provides a guaranteed numeric default.
function num(val) {
  const n = Number(val)
  return isNaN(n) ? 0 : n
}

const currentDateTime = computed(() =>
  new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
)

// ✅ Charts only shown for roles that have financial data (budget/disbursement)
const showCharts = computed(() =>
  ['superadmin', 'pic', 'auditor'].includes(user.value?.role_value)
)

// ✅ Bottom grid (recent disbursements/transactions) only for financial roles
const showBottomGrid = computed(() =>
  ['superadmin', 'pic', 'auditor'].includes(user.value?.role_value)
)

const donutPct = computed(() => {
  const s = props.stats
  if (!s) return 0
  const roleVal = user.value?.role_value
  const total = roleVal === 'pic' ? num(s.total_disbursed) : num(s.total_budget)
  const used  = roleVal === 'pic' ? num(s.total_expense)   : num(s.total_disbursed)
  if (total === 0) return 0
  return Math.min((used / total) * 100, 100)
})

const remainingForDonut = computed(() => {
  const s = props.stats
  if (!s) return 0
  return user.value?.role_value === 'pic' ? num(s.remaining_funds) : num(s.remaining_budget)
})

const usedForDonut = computed(() => {
  const s = props.stats
  if (!s) return 0
  return user.value?.role_value === 'pic' ? num(s.total_expense) : num(s.total_disbursed)
})

const lowBudgetAlerts    = computed(() => page.props.low_budget_alerts || [])
const recentDisbursements = computed(() => props.recent_disbursements || [])
const recentTransactions  = computed(() => props.recent_transactions  || [])
const activeDisbursements = computed(() => props.active_disbursements  || [])
const proposals           = computed(() => props.proposals || [])
const pendingProposals    = computed(() => props.pending_proposals || [])

let barChart   = null
let donutChart = null

onMounted(() => {
  // ── Line/Bar Chart ────────────────────────────────
  // ✅ chart_data is now an array of {month, expense, income} from backend.
  //    Previously backend sent monthly_expenses as a keyed object which
  //    .map(d => d.month) cannot iterate.
  if (chartCanvas.value && props.chart_data?.length) {
    const labels   = props.chart_data.map(d => d.month)
    const expenses = props.chart_data.map(d => num(d.expense))
    const incomes  = props.chart_data.map(d => num(d.income))

    const datasets = [
      {
        label: 'Pengeluaran',
        data: expenses,
        backgroundColor: 'rgba(220, 38, 38, 0.15)',
        borderColor: 'rgba(220, 38, 38, 0.8)',
        borderWidth: 2,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: 'rgba(220, 38, 38, 0.9)',
        pointRadius: 4,
      },
    ]
    if (user.value?.role_value !== 'pic') {
      datasets.push({
        label: 'Pemasukan',
        data: incomes,
        backgroundColor: 'rgba(20, 184, 166, 0.15)',
        borderColor: 'rgba(20, 184, 166, 0.8)',
        borderWidth: 2,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: 'rgba(20, 184, 166, 0.9)',
        pointRadius: 4,
      })
    }

    barChart = new Chart(chartCanvas.value, {
      type: 'line',
      data: { labels, datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
          tooltip: {
            callbacks: {
              label: (ctx) => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID'),
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              font: { size: 10 },
              callback: (val) => 'Rp ' + (val / 1_000_000).toFixed(0) + 'jt',
            },
            grid: { color: 'rgba(0,0,0,0.04)' },
          },
          x: { ticks: { font: { size: 10 } }, grid: { display: false } },
        },
        animation: { duration: 1000 },
      },
    })
  }

  // ── Donut Chart ───────────────────────────────────
  if (donutCanvas.value && showCharts.value) {
    const used      = num(donutPct.value)
    const remaining = Math.max(100 - used, 0)
    donutChart = new Chart(donutCanvas.value, {
      type: 'doughnut',
      data: {
        datasets: [{
          data: [used || 0.001, remaining],  // avoid all-zero data crash
          backgroundColor: ['#16a34a', '#dcfce7'],
          borderWidth: 0,
          hoverOffset: 4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        animation: { animateRotate: true, duration: 1200 },
      },
    })
  }
})

</script>