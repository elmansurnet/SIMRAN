<template>
  <!--
    CurrencyInput — Drop-in replacement for <input type="number"> on currency fields.

    PROBLEM WITH type="number":
      • Browser forces numeric-only input; backspace & cursor jump as the browser
        tries to parse every keystroke, blocking natural typing (e.g. can't type "1,500,000").
      • On mobile, numeric keyboards show only digits with no decimal separators.

    THIS SOLUTION:
      • Uses type="text" so the user types freely.
      • Strips formatting (commas, "Rp", whitespace) on every input so the raw
        numeric value bound via v-model is always a plain number string ("1500000").
      • Shows thousands-formatted display on blur for readability.
      • Validation (min/max/budget limit) fires ONLY on blur and on submit — never
        mid-keystroke — so typing is never blocked.

    USAGE:
      <CurrencyInput
        v-model="form.amount"
        :min="1"
        :max="remainingBudget"
        max-message="Melebihi sisa anggaran"
        placeholder="0"
      />

    v-model receives and emits a plain number (not a string).
  -->
  <div class="relative">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium select-none pointer-events-none">
      Rp
    </span>
    <input
      ref="inputEl"
      type="text"
      inputmode="numeric"
      class="form-input pl-10"
      :class="{ 'border-red-400 focus:ring-red-300': localError }"
      :placeholder="placeholder"
      :value="displayValue"
      @input="handleInput"
      @blur="handleBlur"
      @focus="handleFocus"
    />
  </div>
  <!-- Formatted hint shown below the input -->
  <p v-if="modelValue && !localError" class="text-xs text-gray-400 mt-1">
    {{ formatted }}
  </p>
  <!-- Inline validation error (local) -->
  <p v-if="localError" class="text-xs text-red-500 mt-1">{{ localError }}</p>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue:  { type: [Number, String], default: '' },
  min:         { type: Number, default: 1 },
  max:         { type: Number, default: null },
  maxMessage:  { type: String, default: 'Melebihi batas maksimum' },
  placeholder: { type: String, default: '0' },
})

const emit = defineEmits(['update:modelValue'])

const inputEl    = ref(null)
const isFocused  = ref(false)
const localError = ref('')

// ── Helpers ───────────────────────────────────────────

/** Strip everything that isn't a digit or decimal point */
function strip(v) {
  return String(v ?? '').replace(/[^\d]/g, '')
}

/** Format a raw numeric string with thousands separators */
function format(raw) {
  if (!raw) return ''
  const n = Number(raw)
  if (isNaN(n)) return raw
  return n.toLocaleString('id-ID')
}

// ── Display value ─────────────────────────────────────

/**
 * While focused: show the raw numeric string so the user can edit freely.
 * While blurred: show the thousands-formatted value.
 */
const displayValue = computed(() => {
  const raw = strip(props.modelValue)
  if (!raw) return ''
  return isFocused.value ? raw : format(raw)
})

/** Human-readable hint (always shown when there's a value and no error) */
const formatted = computed(() => {
  const raw = strip(props.modelValue)
  if (!raw) return ''
  const n = Number(raw)
  if (isNaN(n)) return ''
  return 'Rp ' + n.toLocaleString('id-ID')
})

// ── Event handlers ────────────────────────────────────

function handleInput(e) {
  // Strip non-digits immediately so v-model always gets a clean number.
  // We do NOT show an error here — we wait for blur.
  const raw = strip(e.target.value)
  localError.value = ''

  // Keep cursor sane: restore caret after we replace the value
  const caretPos = e.target.selectionStart - (e.target.value.length - raw.length)

  // Emit as number (or '' if empty)
  emit('update:modelValue', raw === '' ? '' : Number(raw))

  // Sync input display to stripped value while typing
  e.target.value = raw
  const safe = Math.max(0, Math.min(caretPos, raw.length))
  e.target.setSelectionRange(safe, safe)
}

function handleFocus() {
  isFocused.value  = true
  localError.value = ''
  // Show raw value for easy editing
  if (inputEl.value) {
    const raw = strip(props.modelValue)
    inputEl.value.value = raw
  }
}

function handleBlur() {
  isFocused.value = false
  validate()
  // Show formatted value
  if (inputEl.value) {
    inputEl.value.value = displayValue.value
  }
}

// ── Validation (on blur / on submit — NOT on every keystroke) ─────────────

function validate() {
  const raw = strip(props.modelValue)
  if (!raw) { localError.value = ''; return true }

  const n = Number(raw)

  if (isNaN(n) || n < props.min) {
    localError.value = `Minimum Rp ${props.min.toLocaleString('id-ID')}`
    return false
  }

  if (props.max !== null && n > props.max) {
    localError.value = props.maxMessage + ` (maks Rp ${props.max.toLocaleString('id-ID')})`
    return false
  }

  localError.value = ''
  return true
}

// Expose validate() so parent forms can call it on submit
defineExpose({ validate })

// If the parent resets the model to '' clear any error
watch(() => props.modelValue, (v) => {
  if (v === '' || v === null || v === undefined) {
    localError.value = ''
  }
})
</script>