import { ref, onMounted } from 'vue'

export function useCountUp(target, duration = 1200, prefix = '', suffix = '') {
  const display = ref(prefix + '0' + suffix)
  const numTarget = Number(target) || 0

  onMounted(() => {
    if (numTarget === 0) {
      display.value = prefix + '0' + suffix
      return
    }
    const startTime = performance.now()
    const step = (currentTime) => {
      const elapsed = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)
      const eased = 1 - Math.pow(1 - progress, 3) // ease-out cubic
      const current = Math.floor(eased * numTarget)
      display.value = prefix + current.toLocaleString('id-ID') + suffix
      if (progress < 1) requestAnimationFrame(step)
      else display.value = prefix + numTarget.toLocaleString('id-ID') + suffix
    }
    requestAnimationFrame(step)
  })

  return { display }
}
