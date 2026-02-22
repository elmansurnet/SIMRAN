import Swal from 'sweetalert2'

export function useAlert() {
  const success = (message) =>
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: message,
      showConfirmButton: false,
      timer: 2800,
      timerProgressBar: true,
      customClass: { popup: 'swal2-toast-green' },
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      },
    })

  const error = (title, message = '') =>
    Swal.fire({
      icon: 'error',
      title,
      text: message || undefined,
      confirmButtonColor: '#1B6B3A',
      confirmButtonText: 'Tutup',
    })

  const warning = (title, message = '') =>
    Swal.fire({
      icon: 'warning',
      title,
      text: message || undefined,
      confirmButtonColor: '#1B6B3A',
      confirmButtonText: 'Mengerti',
    })

  const confirmDelete = (name = 'data ini') =>
    Swal.fire({
      title: 'Konfirmasi Hapus',
      html: `Data <strong>${name}</strong> akan dihapus secara permanen dan tidak dapat dipulihkan.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#DC2626',
      cancelButtonColor: '#6B7280',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal',
      reverseButtons: true,
    })

  const confirmAction = (title, text, confirmText = 'Lanjutkan') =>
    Swal.fire({
      title,
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#1B6B3A',
      cancelButtonColor: '#6B7280',
      confirmButtonText: confirmText,
      cancelButtonText: 'Batal',
    })

  const budgetOverrun = (remaining) =>
    Swal.fire({
      icon: 'error',
      title: 'Anggaran Tidak Mencukupi',
      html: `Jumlah pencairan melebihi sisa anggaran tersedia:<br>
             <strong class="text-lg text-green-700">Rp ${formatRp(remaining)}</strong>`,
      confirmButtonColor: '#1B6B3A',
    })

  const formatRp = (val) =>
    Number(val).toLocaleString('id-ID', { minimumFractionDigits: 0 })

  return { success, error, warning, confirmDelete, confirmAction, budgetOverrun }
}
