export interface order {
    id: number
    nama_pelanggan: string
    produk: string
    total_harga: string
    pengirim: string
    penerima: string
    alamat: string
    status: 'dikirim' | 'dikirimkan' | 'dikir' // ← Pastikan enum-nya sesuai isi aslinya
    created_at: string
    updated_at: string
  }
  