<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { useAuthStore } from "@/stores/auth";
import { watch } from "vue";
import Swal from "sweetalert2";

const User = ref({ name: "", role: "kurir" });
const showTransaksi = ref(false);
const orderCount = ref(0);
const dijemputCount = ref(0);
const dikirimCount = ref(0);
const selesaiCount = ref(0);

const store = useAuthStore();

const getStatistikOrder = async () => {
    try {
        const res = await axios.get("/kurir/statistik-status"); // endpoint ini harus Anda buat di backend
        orderCount.value = res.data.total;
        dijemputCount.value = res.data.dijemput;
        dikirimCount.value = res.data.dikirim;
        selesaiCount.value = res.data.selesai;
    } catch (error) {
        console.error("Gagal ambil statistik status", error);
    }
};

const getStatistikOrderList = async (filter = null) => {
    // if (User.value.role === 'kurir') {
    try {
        const res = await axios.get("/kurir/order-list", {
            params: { filter }, // kirim filter ke backend
        });
        orderCount.value = res.data.orderCount;
        dijemputCount.value = res.data.dijemputCount;
        dikirimCount.value = res.data.dikirimCount;
        selesaiCount.value = res.data.selesaiCount;
        showTransaksi.value = true;
    } catch (error) {
        if (error.response && error.response.status === 403) {
            Swal.fire({
                icon: "error",
                title: "Akses Ditolak",
                text:
                    error.response.data.message ||
                    "Anda tidak memiliki izin untuk melihat data ini.",
            });
        } else {
            console.error("Gagal mengambil daftar transaksi", error);
            Swal.fire({
                icon: "error",
                title: "Terjadi Kesalahan",
                text: "Gagal mengambil data transaksi.",
            });
        }
        // }
    }
};

const closeStatistikOrder = () => {
    showTransaksi.value = false;
};

onMounted(() => {
    getProfile();
});
</script>

<template>
    <!-- <div>
        <form class="back-black p-4  rounded shadow mx-auto" action="_action-pengaturan.php" method="post" enctype="multipart/form-data">
        <h4 class="text-dark mb-5">Status kurir</h4>
  <input type="hidden" name="action" value="ubah-pengaturan-dashboard">
  <input type="hidden" name="id" value="1">

  <div class="form-check mb-5">
    <input class="form-check-input" type="radio" name="status_kurir" id="akfit" value="1" checked>
    <label class="form-check-label" for="akfit">
      Menerima Pesanan
    </label>
  </div>

  <div class="form-check mb-5">
    <input class="form-check-input" type="radio" name="status_kurir" id="nonaktif" value="0">
    <label class="form-check-label" for="nonaktif">
      Tidak Menerima Pesanan
    </label>
  </div>


  <button type="submit" class="btn btn-primary w-20  ">Simpan</button>
</form>
</div> -->
    <h1 class="mb-5">Statistik hari ini</h1>
    <!-- <div class="box-wrapper"> -->
        <div class="box-wrapper">
            <div class="card p-5 mb-10">
                <h2>Total Order</h2><br>
                <h1 class="mt-3">{{ totalOrder }}</h1>
                <!-- <h3 @click="getStatistikOrder()">Lihat Semua</h3> -->
            </div>
            <div class="card p-5 mb-10">
                <h2>Dijemput</h2><br>
                <h1 class="mt-3">{{ dijemputCount }}</h1>
                <!-- <h3 @click="getStatistikOrder('Dijemput')">Lihat Detail</h3> -->
            </div>
            <div class="card p-5 mb-10">
                <h2>Dikirim</h2>
                <h1 class="mt-3">{{ dikirimCount }}</h1>
                <!-- <h3 @click="getStatistikOrder('Dikirim')">Lihat Detail</h3> -->
            </div>
            <div class="card p-5 mb-10">
                <h2>Selesai</h2>
                <h1 class="mt-3">{{ selesaiCount }}</h1>
                <!-- <h3 @click="getStatistikOrder('Selesai')">Lihat Detail</h3> -->
            </div>
        </div>
    <!-- </div> -->
    <!-- <h1 class="mb-5">Statistik Riwayat</h1>
    <div class="box-wrapper">
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/riwayat"
                >Riwayat</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Dijemput</router-link
            >
        </div>
        <div class="card p-5 mb-10">
            <h1>0</h1>
            <router-link class="nav-link" to="/dashboard/order/data"
                >Dikirim</router-link
            >
        </div>
    </div> -->

    <!-- <h1 class="mb-5">Statistik laporan</h1>
    <div class="card p-5 mb-10">
        <h1>0</h1>
        <router-link class="nav-link" to="/dashboard/order/data"
            >Data Order</router-link
        >
    </div> -->
</template>

<main></main>
<style>

div.card {
    border: 1px solid black;
    padding: 25px;
    background-color: lightblue;
    height: 100px;
    width: 18%;
    font-size: 20px;
    text-align: center;
    display: flex;
    flex-wrap: wrap; /* biar responsif kalau layar kecil */
}


.box-wrapper {
    display: flex;
    gap: 20px; /* jarak antar box */
    flex-wrap: wrap; /* biar responsif kalau layar kecil */
}
</style>
