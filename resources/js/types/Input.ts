export interface Input {
    id: number
    nama_pengirim: string
    alamat_pengirim: string
    no_telp_pengirim: string
    nama_penerima: string
    alamat_penerima: string
    no_telp_penerima : string
    jenis_barang : string
    ekspedisi: string;  
    jenis_layanan : string
    berat_barang : number
    riwayat : string
    no_resi : number
    biaya : number
    penilaian?: number | null;  // Penilaian opsional
    komentar?: string | null;   // Komentar opsional
    tanggal_order: Date 
    tanggal_dikemas: Date 
    tanggal_dikirim: Date
    tanggal_penerimaan: Date 


    asal_provinsi_id: number;
    asal_kota_id: number;
    tujuan_provinsi_id: number;
    tujuan_kota_id: number;

    created_at: string
    updated_at: string
  }
  
// export interface Input {
//     id: number
//     nama_barang: string
//     alamat_asal: string
//     alamat_tujuan: string
//     penerima: string
//     berat_paket: number
//     tanggal_order : Date
//     tanggal_dikemas : Date
//     tanggal_pengambilan : Date
//     tanggal_dikirim : Date
//     tanggal_penerimaan : Date
//     nilai : string
//     ulasan : string
//     jarak : number
//     biaya_pengiriman: string
//     status: 'menunggu'| 'pengambilan paket' | 'dikirim' | 'selesai' | 'dalam proses' | 'dibatalkan' // ← Pastikan enum-nya sesuai isi aslinya
//     created_at: string
//     updated_at: string
//   }
  