export interface Input {
    id: number
    nama_barang: string
    alamat_asal: string
    alamat_tujuan: string
    penerima: string
    status: 'dikirim' | 'selesai' | 'dalam proses' | 'dibatalkan' // ← Pastikan enum-nya sesuai isi aslinya
    created_at: string
    updated_at: string
  }
  