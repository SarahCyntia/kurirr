
<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { useAuthStore } from "@/stores/auth";
import { watch } from "vue";
import Swal from "sweetalert2";

// const User = ref({ name: "", role: "kurir" });
const showInput = ref(false);
const orderCount = ref(0);
const dalamProsesCount = ref(0);
const dijemputCount = ref(0);
const dikirimCount = ref(0);
const selesaiCount = ref(0);

const store = useAuthStore();

const getStatistikOrder = async () => {
    try {
        const res = await axios.get("/statistik-status"); // endpoint ini harus Anda buat di backend
        orderCount.value = res.data.total;
        dalamProsesCount.value = res.data.dalam proses;
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
        const res = await axios.get("/order-list", {
            params: { filter }, // kirim filter ke backend
        });
        orderCount.value = res.data.orderCount;
        dalamProsesCount.value = res.data.dalamProsesCount;
        dijemputCount.value = res.data.dijemputCount;
        dikirimCount.value = res.data.dikirimCount;
        selesaiCount.value = res.data.selesaiCount;
        // showTransaksi.value = true;
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
    showInput.value = false;
};

onMounted(() => {
    getProfile();
});
</script>

<template>
    <h1 class="mb-5">Statistik hari ini</h1>
    <!-- <div class="box-wrapper"> -->
        <div class="box-wrapper">
            <div class="card">
                <h2>Total Order</h2>
                <h1>{{ orderCount }}</h1>
                <!-- <h3 @click="getStatistikOrder()">Lihat Semua</h3> -->
            </div>
            <div class="card">
                <h2>Dijemput</h2>
                <h1>{{ dijemputCount }}</h1>
                <!-- <h3 @click="getStatistikOrder('Dijemput')">Lihat Detail</h3> -->
            </div>
            <div class="card">
                <h2>Dikirim</h2>
                <h1>{{ dikirimCount }}</h1>
                <!-- <h3 @click="getStatistikOrder('Dikirim')">Lihat Detail</h3> -->
            </div>
            <div class="card">
                <h2>Selesai</h2>
                <h1>{{ selesaiCount }}</h1>
                <!-- <h3 @click="getStatistikOrder('Selesai')">Lihat Detail</h3> -->
            </div>
        </div>
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
h2{
    background-color: lightblue;
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
