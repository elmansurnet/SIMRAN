<template>
  <div class="min-h-screen flex bg-gradient-to-br from-green-900 via-green-800 to-emerald-700">

    <!-- Left Panel: Brand -->
    <div class="hidden lg:flex flex-col justify-between w-96 xl:w-[480px] p-12 relative overflow-hidden">
      <!-- Background decorations -->
      <div class="absolute top-0 left-0 w-full h-full opacity-10">
        <div class="absolute top-20 -left-10 w-64 h-64 rounded-full bg-white/30 blur-3xl"></div>
        <div class="absolute bottom-20 -right-10 w-80 h-80 rounded-full bg-emerald-300/30 blur-3xl"></div>
      </div>
      <!-- Grid pattern overlay -->
      <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,<svg width=%2240%22 height=%2240%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M0 0h40v40H0z%22 fill=%22none%22/><path d=%22M0 40h40M40 0v40%22 stroke=%22white%22 stroke-width=%220.5%22/></svg>');"></div>

      <div class="relative">
        <!-- Logo -->
        <div class="flex items-center space-x-3 mb-8">
          <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
              <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div>
            <p class="text-white font-black text-xl tracking-wide">SIMRAN</p>
            <p class="text-green-300 text-xs font-medium">UNISYA</p>
          </div>
        </div>

        <h1 class="text-4xl font-black text-white leading-tight mb-4">
          Sistem Manajemen<br/>dan Realisasi Anggaran
          <span class="text-green-300">UNISYA</span>
        </h1>
        <p class="text-green-100 text-base leading-relaxed">
          Platform terpadu untuk pengelolaan alokasi dan realisasi anggaran di Universitas Islam Syarifuddin.
        </p>
      </div>

      <div class="relative space-y-4">
        <div v-for="feature in features" :key="feature.text"
             class="flex items-center space-x-3 bg-white/10 backdrop-blur rounded-xl px-4 py-3">
          <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg shrink-0">
            {{ feature.icon }}
          </div>
          <p class="text-white text-sm font-medium">{{ feature.text }}</p>
        </div>
      </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
      <div class="w-full max-w-md">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="inline-flex items-center space-x-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div>
              <p class="text-white font-black text-lg">SIMRAN UNISYA</p>
            </div>
          </div>
        </div>

        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-white/20">
          <div class="mb-7">
            <h2 class="text-2xl font-bold text-white">Selamat Datang</h2>
            <p class="text-green-200 text-sm mt-1">Masuk ke akun Anda untuk melanjutkan</p>
          </div>

          <!-- Status message -->
          <div v-if="status" class="mb-5 p-3 bg-green-500/20 border border-green-400/30 rounded-xl text-green-100 text-sm">
            {{ status }}
          </div>

          <form @submit.prevent="submit" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-green-100 mb-1.5">Email</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                </svg>
                <input v-model="form.email" type="email" autocomplete="username"
                       class="w-full bg-white/10 border border-white/20 text-white placeholder-green-300 rounded-xl px-4 py-3 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-white/40 focus:border-white/40 transition"
                       placeholder="email@unisya.ac.id" />
              </div>
              <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-300">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-green-100 mb-1.5">Password</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input v-model="form.password" :type="showPw ? 'text' : 'password'" autocomplete="current-password"
                       class="w-full bg-white/10 border border-white/20 text-white placeholder-green-300 rounded-xl px-4 py-3 pl-10 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-white/40 focus:border-white/40 transition"
                       placeholder="Password Anda" />
                <button type="button" @click="showPw = !showPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-green-300 hover:text-white transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
              </div>
              <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-300">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input v-model="form.remember" type="checkbox"
                       class="w-4 h-4 rounded border-white/30 bg-white/10 text-green-500 focus:ring-green-500" />
                <span class="text-sm text-green-100">Ingat saya</span>
              </label>
            </div>

            <button type="submit" :disabled="form.processing"
                    class="w-full bg-white text-green-800 font-bold py-3 rounded-xl hover:bg-green-50 active:scale-[0.98] transition-all duration-150 disabled:opacity-60 flex items-center justify-center space-x-2 shadow-lg">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
              <span>{{ form.processing ? 'Masuk...' : 'Masuk' }}</span>
            </button>
          </form>
        </div>

        <p class="text-center text-green-300/70 text-xs mt-6">
          SIMRAN UNISYA © {{ new Date().getFullYear() }} — Universitas Islam Syarifuddin
        </p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineProps({ status: String, canResetPassword: Boolean })

const showPw = ref(false)

const form = useForm({
  email:    '',
  password: '',
  remember: false,
})

const features = [
  { icon: '💰', text: 'Kelola alokasi anggaran dengan mudah' },
  { icon: '📋', text: 'Pantau pencairan dan realisasi kegiatan' },
  { icon: '📊', text: 'Laporan keuangan otomatis & terperinci' },
  { icon: '🔒', text: 'Sistem peran: Admin, PIC, dan Auditor' },
]

function submit() {
  form.post(route('login'), { onFinish: () => form.reset('password') })
}
</script>
