<template>
  <AppLayout title="Manajemen Pengguna">
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Pengguna</h2>
        <p class="text-sm text-gray-400 mt-0.5">Kelola akun PIC dan Auditor</p>
      </div>
      <Link :href="route('admin.users.create')" class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
        <span>Tambah Pengguna</span>
      </Link>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-5 animate-fade-in">
      <div class="flex flex-col sm:flex-row gap-3">
        <SearchInput v-model="searchQuery" placeholder="Cari nama atau email..." class="flex-1" />
        <select v-model="roleFilter" class="form-input sm:w-44">
          <option value="">Semua Role</option>
          <option value="pic">PIC</option>
          <option value="auditor">Auditor</option>
        </select>
        <button @click="applyFilter" class="btn-primary whitespace-nowrap">Cari</button>
        <button v-if="hasFilter" @click="clearFilter" class="btn-secondary whitespace-nowrap">Reset</button>
      </div>
    </div>

    <div class="table-container animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-green-800">
            <tr>
              <Th>Pengguna</Th>
              <Th>Email</Th>
              <Th>Role</Th>
              <Th>Terdaftar</Th>
              <Th>Aksi</Th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 bg-white">
            <template v-if="users.data.length">
              <tr v-for="(u, i) in users.data" :key="u.id"
                  :class="['hover:bg-green-50/50 transition-colors', i % 2 === 1 ? 'bg-green-50/20' : '']">
                <Td>
                  <div class="flex items-center space-x-3">
                    <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0',
                      u.role_value === 'pic' ? 'bg-gradient-to-br from-blue-500 to-blue-700' : 'bg-gradient-to-br from-amber-500 to-orange-600']">
                      {{ u.initials }}
                    </div>
                    <p class="font-medium text-gray-800">{{ u.name }}</p>
                  </div>
                </Td>
                <Td class="text-gray-500">{{ u.email }}</Td>
                <Td>
                  <Badge :color="u.role_value === 'pic' ? 'blue' : 'amber'">{{ u.role }}</Badge>
                </Td>
                <Td class="text-gray-400 text-xs">{{ u.created_at }}</Td>
                <Td>
                  <div class="flex items-center space-x-1">
                    <Link :href="route('admin.users.edit', u.id)"
                          class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button @click="handleDelete(u)"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </Td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="5" class="py-2">
                <EmptyState title="Belum ada pengguna" description="Klik 'Tambah Pengguna' untuk menambahkan pengguna baru." />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="users.links" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SearchInput from '@/Components/SearchInput.vue'
import Badge from '@/Components/Badge.vue'
import Th from '@/Components/Th.vue'; import Td from '@/Components/Td.vue'
import Pagination from '@/Components/Pagination.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useAlert } from '@/Composables/useAlert'

const props = defineProps({ users: Object, filters: Object })
const { confirmDelete, success } = useAlert()

const searchQuery = ref(props.filters?.search || '')
const roleFilter  = ref(props.filters?.role   || '')
const hasFilter   = computed(() => searchQuery.value || roleFilter.value)

function applyFilter() {
  router.get(route('admin.users.index'), { search: searchQuery.value, role: roleFilter.value }, { preserveState: true, replace: true })
}
function clearFilter() {
  searchQuery.value = ''; roleFilter.value = ''
  router.get(route('admin.users.index'))
}
async function handleDelete(user) {
  const r = await confirmDelete(user.name)
  if (r.isConfirmed) router.delete(route('admin.users.destroy', user.id), { onSuccess: () => success('Pengguna berhasil dihapus.') })
}
</script>
