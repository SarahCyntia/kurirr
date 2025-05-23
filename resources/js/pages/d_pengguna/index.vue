<template>
  <div class="main">
    <nav class="nav">
      <div class="icon">
        <h2 class="logo">KurirKu</h2>
      </div>
      <div class="menu">
        <ul>
          <li><a href="/halaman/index.html">Beranda</a></li>
          <li><a href="/halaman/tentang.html">Tentang</a></li>
          <li><a href="/halaman/layanan.html">Layanan</a></li>
          <li><a href="/halaman/kontak.html">Kontak</a></li>
        </ul>
      </div>
      <div class="search">
        <input v-model="nomorResi" type="search" placeholder="Masukkan nomor resi" />
        <button class="btn" @click="cekResi">Cek Resi</button>
        <button class="cn" @click="logout">Logout</button>
      </div>
    </nav>

    <section class="content">
      <div class="text">
        <h1>Layanan Pengiriman <br /><span>Cepat & Terpercaya</span></h1>
        <p class="par">
          Kami siap mengantarkan paket Anda ke seluruh Indonesia dengan aman
          dan tepat waktu.
        </p>
        <button class="cn">
          <a href="/halaman/kirimpaket.html">Kirim Paket</a>
        </button>
      </div>
    </section>

    <div class="search-result" v-if="hasilResi">
      <div class="hasil-box">
        <h3>Informasi Pengiriman</h3>
        <p><strong>Nama:</strong> {{ hasilResi.nama }}</p>
        <p><strong>Status:</strong> {{ hasilResi.status }}</p>
        <p><strong>Lokasi Saat Ini:</strong> {{ hasilResi.lokasi }}</p>
        <p><strong>Estimasi Tiba:</strong> {{ hasilResi.estimasi }}</p>
      </div>
    </div>
    <p v-if="resNotFound" style="color: red;">Resi tidak ditemukan.</p>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import type { Input } from "@/types";
import axios from "axios";

const nomorResi = ref("");
const loading = ref(false);
const error = ref("");
const hasilResi = ref<Input | null>(null);

const cekResi = async () => {
  if (!courier.value) {
    error.value = "Silakan pilih kurir terlebih dahulu.";
    return;
  }

  loading.value = true;
  error.value = "";
  hasilResi.value = null;

  try {
    const response = await axios.get(`/cek-resi/${nomorResi.value}`, {
      params: {
        kurir: courier.value.toLowerCase(), // paksa huruf kecil
      },
    });
    hasilResi.value = response.data;
  } catch (err: any) {
    error.value = err.response?.data?.message || "Resi tidak ditemukan.";
  } finally {
    loading.value = false;
  }
};

</script>
<style scoped>

/* Reset dasar */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background: linear-gradient(135deg, #1d2b64, #f8cdda);
    color: #fff;
    min-height: 100vh;
}

/* Navigasi */
.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 60px;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    font-size: 28px;
    font-weight: bold;
    color: #f9ca24;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.4);
}

.menu ul {
    display: flex;
    list-style: none;
    gap: 25px;
}

.menu ul li a {
    text-decoration: none;
    color: white;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 5px;
    transition: 0.3s;
}

.menu ul li a:hover {
    background-color: #f9ca24;
    color: #1d1d1d;
}

.search {
    display: flex;
    gap: 10px;
}

.search input {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    outline: none;
    width: 180px;
}

.btn {
    background-color: #f0932b;
    border: none;
    padding: 8px 16px;
    color: white;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn:hover {
    background-color: #e67e22;
}

/* Konten utama */
.content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 80px 60px;
    flex-wrap: wrap;
}

.text {
    max-width: 600px;
}

.text h1 {
    font-size: 48px;
    line-height: 1.4;
    color: #fff;
}

.text h1 span {
    color: #f9ca24;
}

.text .par {
    font-size: 18px;
    margin: 20px 0;
    color: #ecf0f1;
}

.cn {
    padding: 12px 25px;
    background-color: #f0932b;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}

.cn a {
    text-decoration: none;
    color: white;
}

.cn:hover {
    background-color: #e67e22;
}

/* Form login */
.form {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 30px;
    border-radius: 15px;
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    width: 320px;
    margin-top: 20px;
}

.form h2 {
    margin-bottom: 20px;
    color: #fff;
    text-align: center;
}

.form input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: none;
    border-radius: 8px;
    background-color: #fff;
    color: #333;
    font-size: 14px;
    outline: none;
}

.form .btn a {
    text-decoration: none;
    color: white;
}

.link {
    margin-top: 10px;
    text-align: center;
    font-size: 14px;
}

.link a {
    color: #f9ca24;
    text-decoration: none;
}

.liw {
    text-align: center;
    margin-top: 10px;
    color: #f1f1f1;
}
.search-result {
    margin-top: 20px;
    padding: 20px;
    background-color: #f9f9f9;
    border-left: 4px solid #1d2b64;
    border-radius: 6px;
    max-width: 400px;
    font-size: 16px;
}

.hasil-box h3 {
    color: #1d2b64;
    margin-bottom: 10px;
}



</style>
