export function useCurrency() {
  const formatRp = (value, short = false) => {
    const num = Number(value) || 0
    if (short && num >= 1_000_000_000) return 'Rp ' + (num / 1_000_000_000).toFixed(1) + 'M'
    if (short && num >= 1_000_000) return 'Rp ' + (num / 1_000_000).toFixed(1) + 'jt'
    if (short && num >= 1_000) return 'Rp ' + (num / 1_000).toFixed(0) + 'rb'
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
  }

  const parseRp = (str) => parseFloat(String(str).replace(/[^0-9.]/g, '')) || 0

  const formatPct = (val, decimals = 1) => Number(val).toFixed(decimals) + '%'

  return { formatRp, parseRp, formatPct }
}
