export interface Input {
    id: number
    nama_barang: string
    alamat_asal: string
    alamat_tujuan: string
    penerima: string
    berat_paket: string
    biaya_pengiriman: string
    tanggal_order : Date
    tanggal_dikemas : Date
    tanggal_pengambilan : Date
    tanggal_dikirim : Date
    tanggal_penerimaan : Date
    status: 'menunggu'| 'pengambilan paket' | 'dikirim' | 'selesai' | 'dalam proses' | 'dibatalkan' // ← Pastikan enum-nya sesuai isi aslinya
    created_at: string
    updated_at: string
  }
  