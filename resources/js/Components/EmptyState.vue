<template>
  <div class="text-center py-16 px-4">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
    </div>
    <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ title }}</h3>
    <p class="text-sm text-gray-400 max-w-xs mx-auto">{{ description }}</p>
    <slot />
  </div>
</template>
<script setup>
defineProps({
  title:       { type: String, default: 'Data tidak ditemukan' },
  description: { type: String, default: 'Belum ada data yang tersedia.' },
})
</script>
