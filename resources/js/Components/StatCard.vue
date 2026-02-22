<template>
  <div
    :class="[
      'bg-white rounded-xl border p-5 flex items-start space-x-4 transition-all duration-200',
      'hover:shadow-md hover:-translate-y-0.5 animate-fade-in-up',
      highlight
        ? 'border-green-400 bg-gradient-to-br from-green-50 to-emerald-50 ring-1 ring-green-200'
        : 'border-gray-100 shadow-sm',
    ]"
    :style="{ animationDelay: delay + 'ms' }"
  >
    <!-- Icon -->
    <div
      :class="[
        'w-11 h-11 rounded-xl flex items-center justify-center shrink-0',
        iconBg,
      ]"
    >
      <span class="text-xl leading-none">{{ icon }}</span>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <p class="text-xs text-gray-500 font-medium uppercase tracking-wider truncate">{{ label }}</p>
      <p
        :class="[
          'text-lg sm:text-xl font-bold mt-0.5 truncate',
          highlight ? 'text-green-700' : 'text-gray-800',
        ]"
      >
        {{ displayValue }}
      </p>
      <p v-if="sub" class="text-xs text-gray-400 mt-0.5 truncate">{{ sub }}</p>
    </div>

    <!-- Trend badge -->
    <div v-if="trend !== null" :class="['shrink-0 text-xs font-semibold px-2 py-1 rounded-full', trendClass]">
      {{ trend > 0 ? '↑' : '↓' }} {{ Math.abs(trend) }}%
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useCurrency } from '@/Composables/useCurrency'

const props = defineProps({
  label:      { type: String, required: true },
  value:      { type: [String, Number], required: true },
  icon:       { type: String, default: '📊' },
  color:      { type: String, default: 'green' },
  highlight:  { type: Boolean, default: false },
  sub:        { type: String, default: null },
  trend:      { type: Number, default: null },
  isCurrency: { type: Boolean, default: true },
  delay:      { type: Number, default: 0 },
})

const { formatRp } = useCurrency()
const animated = ref(0)

const colorMap = {
  green:   'bg-green-100',
  blue:    'bg-blue-100',
  red:     'bg-red-100',
  amber:   'bg-amber-100',
  teal:    'bg-teal-100',
  purple:  'bg-purple-100',
  orange:  'bg-orange-100',
  indigo:  'bg-indigo-100',
}

const iconBg = computed(() => colorMap[props.color] || 'bg-gray-100')

const trendClass = computed(() =>
  props.trend > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
)

const displayValue = computed(() => {
  if (!props.isCurrency) return animated.value.toLocaleString('id-ID')
  return formatRp(animated.value)
})

onMounted(() => {
  const target = Number(props.value) || 0
  if (target === 0) return
  const duration = 1000 + props.delay
  const start = performance.now()
  const step = (now) => {
    const progress = Math.min((now - start) / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    animated.value = Math.floor(eased * target)
    if (progress < 1) requestAnimationFrame(step)
    else animated.value = target
  }
  requestAnimationFrame(step)
})
</script>
