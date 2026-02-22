<template>
  <div v-if="links && links.length > 3" class="flex items-center justify-between py-3 px-4 border-t border-gray-100">
    <p class="text-sm text-gray-500 hidden sm:block">
      Menampilkan data halaman ini
    </p>
    <div class="flex flex-wrap gap-1 justify-center sm:justify-end">
      <template v-for="link in links" :key="link.label">
        <Link
          v-if="link.url"
          :href="link.url"
          preserve-scroll
          :class="[
            'px-3 py-1.5 rounded-lg text-sm border transition-all duration-150',
            link.active
              ? 'bg-green-700 text-white border-green-700 shadow-sm'
              : 'bg-white text-gray-600 border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-700',
          ]"
          v-html="link.label"
        />
        <span
          v-else
          :class="[
            'px-3 py-1.5 rounded-lg text-sm border cursor-not-allowed',
            'bg-gray-50 text-gray-300 border-gray-100',
          ]"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ links: Array })
</script>
