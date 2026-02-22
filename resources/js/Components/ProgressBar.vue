<template>
  <div>
    <div v-if="showLabel" class="flex justify-between text-xs text-gray-500 mb-1">
      <span>{{ label }}</span>
      <span :class="pctClass">{{ percentage.toFixed(1) }}%</span>
    </div>
    <div :class="['w-full rounded-full overflow-hidden', sizeClass, trackClass]">
      <div
        :class="['rounded-full transition-all duration-1000 ease-out', barClass]"
        :style="{ width: animatedWidth + '%' }"
      />
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
const props = defineProps({
  percentage: { type: Number, default: 0 },
  label:      { type: String, default: '' },
  showLabel:  { type: Boolean, default: true },
  size:       { type: String, default: 'md' },
})
const animatedWidth = ref(0)
const sizeClass  = computed(() => ({ sm: 'h-1.5', md: 'h-2.5', lg: 'h-4' }[props.size] || 'h-2.5'))
const trackClass = 'bg-gray-100'
const barClass   = computed(() => {
  const pct = props.percentage
  if (pct >= 90) return 'bg-red-500'
  if (pct >= 70) return 'bg-amber-500'
  return 'bg-green-500'
})
const pctClass = computed(() => {
  const pct = props.percentage
  if (pct >= 90) return 'text-red-600 font-semibold'
  if (pct >= 70) return 'text-amber-600 font-semibold'
  return 'text-green-700'
})
onMounted(() => {
  setTimeout(() => { animatedWidth.value = Math.min(props.percentage, 100) }, 100)
})
</script>
