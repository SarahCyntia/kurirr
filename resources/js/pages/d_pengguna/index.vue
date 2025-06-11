<template>
  <div class="main">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger px-4">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    
    <!-- Dashboard Pengguna di kiri -->
    <div>
      <button class="btn btn-pink" :class="{ 'fw-bold': currentTab === 'dashboard' }"
        @click="currentTab = 'dashboard'">
        Dashboard Pengguna
      </button>
    </div>

    <!-- Tombol lainnya di kanan -->
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-pink" :class="{ 'fw-bold': currentTab === 'ongkir' }"
        @click="currentTab = 'ongkir'">
        Check Ongkir
      </button>
      <button class="btn btn-pink" :class="{ 'fw-bold': currentTab === 'resi' }"
        @click="currentTab = 'resi'">
        Check Resi
      </button>
      <!-- <button class="btn btn-pink" :class="{ 'fw-bold': currentTab === 'riwayatt' }"
        @click="currentTab = 'riwayatt'">
        Riwayat
      </button>
      <button class="btn btn-pink" :class="{ 'fw-bold': currentTab === 'tracking' }"
        @click="currentTab = 'tracking'">
        Lacak Barang
      </button> -->
    </div>
    
  </div>
</nav>



    <!-- <section class="content">
      <div class="text">
        <h1>
          Layanan Pengiriman <br />
          <span>Cepat & Terpercaya</span>
        </h1>
        <p class="par">
          Kami siap mengantarkan paket Anda ke seluruh Indonesia dengan <strong>aman</strong> dan <strong>tepat waktu</strong>.
        </p>
        <button class="cn">
          <a href="/halaman/kirimpaket.html">Kirim Paket</a>
        </button>
      </div>
    </section> -->

    <!-- Konten -->
  <div class="container py-4">
    <Home v-if="currentTab === 'dashboard'" />
    <Check v-if="currentTab === 'ongkir'" />
    <!-- <OrderPage v-if="currentTab === 'order'" /> -->
    <ResiPage v-if="currentTab === 'resi'" />
    <!-- <RiwayatPage v-if="currentTab === 'riwayat'" /> -->
    <!-- <RiwayatPage v-if="currentTab === 'riwayatt'" />
    <TrackPage v-if="currentTab === 'tracking'" /> -->
  </div>

  </div>

</template>

<script setup lang="ts">
import { ref } from 'vue';

import Check from "@/pages/d_pengguna/cekongkir/index.vue";
import Home from "@/pages/d_pengguna/dashboard/index.vue";
import ResiPage from "@/pages/dashboard/cekresi/index.vue";

const currentTab = ref<'dashboard' | 'ongkir' >('dashboard');

</script>
<style scoped>
/* @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap'); */

.btn-outline-light.fw-bold {
  border: 2px solid rgb(241, 241, 241);
}

.btn-pink {
  color: #fff;
  border-color: #ff69b4;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.btn-pink:hover,
.btn-pink:focus {
  background-color: #ff85c1; /* sedikit lebih muda */
  border-color: #ff85c1;
  color: #fff;
}

.btn-pink.fw-bold {
  box-shadow: 0 0 10px #ff69b4;
}


/* body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
  background-color: #ff85c1;
} */



.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem 8rem;
  background: #ff4d6d;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logo {
  font-weight: 700;
  color: #fff;
  font-size: 1.8rem;
}

.menu ul {
  display: flex;
  list-style: none;
  gap: 1.5rem;
  margin: 0;
  padding: 0;
}

.menu a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}
.menu a:hover {
  color: #ffe8f1;
}

.content {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 3rem 1rem;
  text-align: center;
}

.text h1 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #333;
}
.text span {
  color: #ff4d6d;
}
.par {
  font-size: 1.2rem;
  color: #555;
  margin: 1.5rem 0;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

.cn {
  background: #ff4d6d;
  padding: 0.8rem 1.6rem;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(255, 77, 109, 0.4);
}
.cn:hover {
  transform: translateY(-2px);
  background-color: #e8435f;
}

.cn a {
  color: white;
  text-decoration: none;
}

@media (max-width: 768px) {
  .nav {
    flex-direction: column;
    gap: 1rem;
  }

  .menu ul {
    flex-direction: column;
    gap: 0.5rem;
  }

  .text h1 {
    font-size: 2rem;
  }

  .par {
    font-size: 1rem;
  }
}
</style>
