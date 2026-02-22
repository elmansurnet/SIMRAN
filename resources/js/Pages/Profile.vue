<template>
  <AppLayout title="Profil Saya">
    <div class="max-w-2xl mx-auto">
      <h2 class="page-title mb-6">Profil Saya</h2>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <!-- Avatar -->
        <div class="flex items-center space-x-5 mb-8 pb-6 border-b border-gray-100">
          <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold text-white shadow-md',
            user.role_value === 'superadmin' ? 'bg-gradient-to-br from-purple-500 to-purple-700' :
            user.role_value === 'pic' ? 'bg-gradient-to-br from-blue-500 to-blue-700' :
            'bg-gradient-to-br from-amber-500 to-orange-600']">
            {{ initials }}
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-800">{{ form.name || user.name }}</h3>
            <p class="text-sm text-gray-500">{{ user.email }}</p>
            <Badge :color="user.role_value === 'superadmin' ? 'purple' : user.role_value === 'pic' ? 'blue' : 'amber'" class="mt-1">
              {{ user.role }}
            </Badge>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <h4 class="font-semibold text-green-800 text-sm uppercase tracking-wide">Informasi Dasar</h4>

          <FormField label="Nama Lengkap" :error="form.errors.name" required>
            <input v-model="form.name" type="text" class="form-input" />
          </FormField>

          <FormField label="Email" :error="form.errors.email" required>
            <input v-model="form.email" type="email" class="form-input" />
          </FormField>

          <div class="border-t border-gray-100 pt-5">
            <h4 class="font-semibold text-green-800 text-sm uppercase tracking-wide mb-4">Ubah Password</h4>
            <p class="text-xs text-gray-400 mb-4">Kosongkan semua field password jika tidak ingin mengubah.</p>

            <div class="space-y-4">
              <FormField label="Password Saat Ini" :error="form.errors.current_password">
                <div class="relative">
                  <input v-model="form.current_password" :type="showCurrent ? 'text' : 'password'"
                         class="form-input pr-10" placeholder="Password saat ini" />
                  <button type="button" @click="showCurrent = !showCurrent"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
              </FormField>

              <FormField label="Password Baru" :error="form.errors.password">
                <div class="relative">
                  <input v-model="form.password" :type="showNew ? 'text' : 'password'"
                         class="form-input pr-10" placeholder="Min. 8 karakter" />
                  <button type="button" @click="showNew = !showNew"
                          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
                <!-- Password strength indicator -->
                <div v-if="form.password" class="mt-2">
                  <div class="flex space-x-1">
                    <div v-for="i in 4" :key="i"
                         :class="['h-1.5 flex-1 rounded-full transition-all duration-300',
                           i <= passwordStrength ? strengthColors[passwordStrength - 1] : 'bg-gray-200']"></div>
                  </div>
                  <p :class="['text-xs mt-1 font-medium', strengthTextColors[passwordStrength - 1]]">
                    {{ strengthLabels[passwordStrength - 1] }}
                  </p>
                </div>
              </FormField>

              <FormField v-if="form.password" label="Konfirmasi Password Baru" :error="form.errors.password_confirmation">
                <input v-model="form.password_confirmation" :type="showNew ? 'text' : 'password'"
                       class="form-input" placeholder="Ulangi password baru" />
              </FormField>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center justify-center space-x-2">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
            </button>
            <button type="button" @click="resetForm" class="btn-secondary">Reset</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({ user: Object })
const page = usePage()
const authUser = computed(() => page.props.auth.user)

const showCurrent = ref(false)
const showNew = ref(false)

const form = useForm({
  name:                  props.user.name,
  email:                 props.user.email,
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

const initials = computed(() =>
  form.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
)

const passwordStrength = computed(() => {
  const p = form.password
  if (!p) return 0
  let score = 0
  if (p.length >= 8)              score++
  if (/[A-Z]/.test(p))            score++
  if (/[0-9]/.test(p))            score++
  if (/[^A-Za-z0-9]/.test(p))     score++
  return score
})

const strengthColors     = ['bg-red-500', 'bg-orange-500', 'bg-amber-500', 'bg-green-500']
const strengthTextColors = ['text-red-600', 'text-orange-600', 'text-amber-600', 'text-green-600']
const strengthLabels     = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat']

function submit() { form.put(route('profile.update')) }

function resetForm() {
  form.name                  = props.user.name
  form.email                 = props.user.email
  form.current_password      = ''
  form.password              = ''
  form.password_confirmation = ''
  form.clearErrors()
}
</script>
