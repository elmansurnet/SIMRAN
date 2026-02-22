<template>
  <AppLayout title="Manajemen Pengguna">
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Pengguna</h2>
        <p class="text-sm text-gray-400 mt-0.5">Kelola akun pengguna sistem</p>
      </div>
      <Link :href="route('admin.users.create')" class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pengguna</span>
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3">
        <SearchInput v-model="searchQuery" placeholder="Cari nama atau email..." class="flex-1" />
        <select v-model="roleFilter" class="form-input sm:w-48">
          <option value="">Semua Role</option>
          <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
        </select>
        <button @click="applyFilter" class="btn-primary">Cari</button>
        <button v-if="hasFilter" @click="clearFilter" class="btn-secondary">Reset</button>
      </div>
    </div>

    <div class="table-container animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Pengguna</Th>
              <Th>Role</Th>
              <Th>Bergabung</Th>
              <Th>Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="users.data.length">
              <tr v-for="(u, i) in users.data" :key="u.id"
                  :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
                <!-- User info -->
                <Td>
                  <div class="flex items-center space-x-3">
                    <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 text-white', avatarColor(u.role_value)]">
                      {{ u.initials }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-gray-800 truncate">{{ u.name }}</p>
                      <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
                    </div>
                  </div>
                </Td>

                <!-- Role badge -->
                <Td>
                  <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold', roleBadge(u.role_value)]">
                    {{ u.role }}
                  </span>
                </Td>

                <!-- Joined date -->
                <Td class="text-sm text-gray-500">{{ u.created_at }}</Td>

                <!-- Actions -->
                <Td>
                  <div class="flex items-center space-x-1">
                    <!-- Edit -->
                    <Link :href="route('admin.users.edit', u.id)"
                          class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                          title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>

                    <!-- Delete — disabled with tooltip when dependencies exist -->
                    <div class="relative group">
                      <button
                        v-if="u.can_delete"
                        @click="confirmDelete(u)"
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                      <!-- Disabled delete button when dependencies exist -->
                      <span v-else
                            class="p-1.5 text-gray-200 cursor-not-allowed rounded-lg inline-flex"
                            :title="u.delete_reason">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </span>
                      <!-- Tooltip for disabled reason -->
                      <div v-if="!u.can_delete && u.delete_reason"
                           class="absolute right-0 top-8 z-10 w-64 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg
                                  invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-opacity pointer-events-none">
                        {{ u.delete_reason }}
                        <div class="absolute -top-1.5 right-2 w-3 h-3 bg-gray-900 rotate-45" />
                      </div>
                    </div>
                  </div>
                </Td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="4" class="py-2">
                <EmptyState title="Belum ada pengguna" description="Tambah pengguna pertama dengan klik tombol di atas." />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="users.links" />
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 animate-fade-in-up">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <h3 class="font-bold text-gray-900">Hapus Pengguna</h3>
        </div>
        <p class="text-sm text-gray-600 mb-5">
          Yakin ingin menghapus akun <strong>{{ deleteTarget.name }}</strong>?
          Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex gap-3">
          <Link :href="route('admin.users.destroy', deleteTarget.id)" method="delete" as="button"
                class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium text-sm transition text-center">
            Ya, Hapus
          </Link>
          <button @click="deleteTarget = null" class="flex-1 btn-secondary">Batal</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Th from '@/Components/Th.vue'
import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import SearchInput from '@/Components/SearchInput.vue'

const props = defineProps({
  users:   Object,
  filters: Object,
  roles:   Array,
})

const searchQuery  = ref(props.filters?.search || '')
const roleFilter   = ref(props.filters?.role   || '')
const deleteTarget = ref(null)

const hasFilter = computed(() => searchQuery.value || roleFilter.value)

function applyFilter() {
  router.get(
    route('admin.users.index'),
    { search: searchQuery.value, role: roleFilter.value },
    { preserveState: true, replace: true }
  )
}

function clearFilter() {
  searchQuery.value = ''
  roleFilter.value  = ''
  router.get(route('admin.users.index'))
}

function confirmDelete(user) {
  deleteTarget.value = user
}

// ── Role styling helpers ───────────────────────────────────────
/**
 * These map exactly to the UserRole enum's badgeClass() output and the
 * roleBadgeClass in AppLayout.vue — kept in sync intentionally.
 */
function roleBadge(roleValue) {
  const map = {
    superadmin: 'bg-purple-100 text-purple-800',
    pic:        'bg-blue-100   text-blue-800',
    auditor:    'bg-amber-100  text-amber-800',
    applicant:  'bg-teal-100   text-teal-800',
    approver:   'bg-indigo-100 text-indigo-800',
  }
  return map[roleValue] || 'bg-gray-100 text-gray-800'
}

function avatarColor(roleValue) {
  const map = {
    superadmin: 'bg-purple-600',
    pic:        'bg-blue-600',
    auditor:    'bg-amber-600',
    applicant:  'bg-teal-600',
    approver:   'bg-indigo-600',
  }
  return map[roleValue] || 'bg-gray-500'
}
</script>