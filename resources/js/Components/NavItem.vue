<template>
  <Link
    :href="href"
    :class="[
      'flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
      isActive
        ? 'bg-white/20 text-white shadow-sm'
        : 'text-green-100 hover:bg-white/10 hover:text-white',
    ]"
  >
    <span class="shrink-0 opacity-90">
      <slot name="icon" />
    </span>
    <span class="truncate">{{ label }}</span>
    <span v-if="badge" class="ml-auto bg-white/20 text-white text-xs px-1.5 py-0.5 rounded-full">
      {{ badge }}
    </span>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  href:  { type: String, required: true },
  label: { type: String, required: true },
  badge: { type: [String, Number], default: null },
})

const isActive = computed(() => {
  const currentUrl = usePage().url
  try {
    const linkPath = new URL(props.href, window.location.origin).pathname
    return currentUrl.startsWith(linkPath)
  } catch {
    return false
  }
})
</script>
