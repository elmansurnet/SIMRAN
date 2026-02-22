<template>
  <AppLayout :title="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'">
    <div class="max-w-xl mx-auto">
      <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-5">
        <Link :href="route('admin.users.index')" class="hover:text-green-700">Pengguna</Link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 font-medium">{{ isEdit ? 'Edit' : 'Tambah' }}</span>
      </nav>

      <div class="card p-6 sm:p-8 animate-fade-in-up">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-gray-100">
          <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-green-900">{{ isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
            <p class="text-xs text-gray-400">{{ isEdit ? 'Perbarui data pengguna' : 'Buat akun pengguna sistem' }}</p>
          </div>
        </div>

        <!-- Avatar Preview (kept from original) -->
        <div v-if="form.name" class="flex justify-center mb-6">
          <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold text-white shadow-md transition-all',
            avatarGradient]">
            {{ initials }}
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <FormField label="Nama Lengkap" :error="form.errors.name" required>
            <input v-model="form.name" type="text" class="form-input" placeholder="Nama lengkap pengguna" />
          </FormField>

          <FormField label="Email" :error="form.errors.email" required>
            <input v-model="form.email" type="email" class="form-input" placeholder="email@unisya.ac.id" />
          </FormField>

          <!-- Role Selection — driven by `roles` prop from UserController, all 5 roles -->
          <FormField label="Role" :error="form.errors.role" required>
            <div class="grid grid-cols-2 gap-3">
              <button
                v-for="r in roles"
                :key="r.value"
                type="button"
                @click="form.role = r.value"
                :class="[
                  'flex items-center space-x-2.5 p-3.5 rounded-xl border-2 transition-all text-left',
                  form.role === r.value
                    ? `${roleActiveClass(r.value)}`
                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                ]"
              >
                <div :class="['w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0',
                  form.role === r.value ? roleIconBg(r.value) : 'bg-gray-300']">
                  {{ r.abbr }}
                </div>
                <div>
                  <p class="text-sm font-semibold">{{ r.label }}</p>
                  <p class="text-xs opacity-70">{{ r.desc }}</p>
                </div>
              </button>
            </div>
          </FormField>

          <!-- Password (kept original show/hide toggle) -->
          <FormField label="Password" :error="form.errors.password" :required="!isEdit"
                     :hint="isEdit ? 'Kosongkan jika tidak ingin mengubah password' : ''">
            <div class="relative">
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-input pr-10"
                     :placeholder="isEdit ? 'Kosongkan jika tidak diubah' : 'Min. 8 karakter'" />
              <button type="button" @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
          </FormField>

          <!-- Confirm password only shown when a password is typed (kept from original) -->
          <FormField v-if="form.password" label="Konfirmasi Password" :error="form.errors.password_confirmation">
            <input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'"
                   class="form-input" placeholder="Ulangi password" />
          </FormField>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="btn-primary flex items-center justify-center space-x-2">
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Perbarui' : 'Simpan') }}</span>
            </button>
            <Link :href="route('admin.users.index')" class="btn-secondary text-center">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormField from '@/Components/FormField.vue'

const props = defineProps({
  editUser: { type: Object, default: null },
  // UserController::roleOptions() returns all 5 roles with value, label, abbr, desc
  roles:    { type: Array, required: true },
})

const isEdit       = !!props.editUser
const showPassword = ref(false)

const form = useForm({
  name:                  props.editUser?.name  ?? '',
  email:                 props.editUser?.email ?? '',
  role:                  props.editUser?.role  ?? '',
  password:              '',
  password_confirmation: '',
})

// Initials for avatar preview (kept from original)
const initials = computed(() =>
  form.name
    ? form.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
    : ''
)

// Avatar gradient changes per selected role (extends original 2-role logic to all 5)
const avatarGradient = computed(() => {
  const map = {
    superadmin: 'bg-gradient-to-br from-purple-500 to-purple-700',
    pic:        'bg-gradient-to-br from-blue-500 to-blue-700',
    auditor:    'bg-gradient-to-br from-amber-500 to-orange-600',
    applicant:  'bg-gradient-to-br from-teal-500 to-teal-700',
    approver:   'bg-gradient-to-br from-indigo-500 to-indigo-700',
  }
  return map[form.role] || 'bg-gradient-to-br from-gray-400 to-gray-600'
})

// Active border+bg colour per role (keeps original pic=blue, auditor=amber style)
function roleActiveClass(value) {
  const map = {
    superadmin: 'border-purple-500 bg-purple-50 text-purple-800',
    pic:        'border-blue-500 bg-blue-50 text-blue-800',
    auditor:    'border-amber-500 bg-amber-50 text-amber-800',
    applicant:  'border-teal-500 bg-teal-50 text-teal-800',
    approver:   'border-indigo-500 bg-indigo-50 text-indigo-800',
  }
  return map[value] || 'border-green-500 bg-green-50 text-green-800'
}

// Icon badge background
function roleIconBg(value) {
  const map = {
    superadmin: 'bg-purple-600',
    pic:        'bg-blue-600',
    auditor:    'bg-amber-600',
    applicant:  'bg-teal-600',
    approver:   'bg-indigo-600',
  }
  return map[value] || 'bg-green-600'
}

function submit() {
  if (isEdit) form.put(route('admin.users.update', props.editUser.id))
  else form.post(route('admin.users.store'))
}
</script>