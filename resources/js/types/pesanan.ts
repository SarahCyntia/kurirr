export interface Pesanan {
    uuid: string; // ID unik pesanan
    no: number; // Nomor urut
    nama_pelanggan: string; // Nama pelanggan
    produk: string; // Nama produk
    total_harga: number; // Total harga pesanan
    tanggal_pesan: string; // Format: YYYY-MM-DD
    pengirim: string; // Nama pengirim
    penerima: string; // Nama penerima
    alamat_pengiriman: string; // alamat pengir
    status: "Dikemas" | "Dikirim" | "Selesai"; // Status pesanan
  }