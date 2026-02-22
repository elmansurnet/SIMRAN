<template>
  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold', colorClass]">
    <span v-if="dot" class="w-1.5 h-1.5 rounded-full mr-1.5" :class="dotClass"></span>
    <slot />
  </span>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps({
  color: { type: String, default: 'gray' },
  dot:   { type: Boolean, default: false },
})
const map = {
  green:  { bg: 'bg-green-100 text-green-800',  dot: 'bg-green-500' },
  red:    { bg: 'bg-red-100 text-red-800',       dot: 'bg-red-500' },
  blue:   { bg: 'bg-blue-100 text-blue-800',     dot: 'bg-blue-500' },
  amber:  { bg: 'bg-amber-100 text-amber-800',   dot: 'bg-amber-500' },
  purple: { bg: 'bg-purple-100 text-purple-800', dot: 'bg-purple-500' },
  gray:   { bg: 'bg-gray-100 text-gray-700',     dot: 'bg-gray-400' },
  teal:   { bg: 'bg-teal-100 text-teal-800',     dot: 'bg-teal-500' },
}
const colorClass = computed(() => (map[props.color] || map.gray).bg)
const dotClass   = computed(() => (map[props.color] || map.gray).dot)
</script>
