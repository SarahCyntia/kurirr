<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";
// import type { Riwayat } from "@/types";


// Import store autentikasi untuk mengambil data user yang sedang login
import { useAuthStore } from "@/stores/auth";

// const riwayat = ref<Riwayat[]>([]);

// const getRiwayat = async () => {
//   try {
//     const response = await axios.get("/kurir/riwayat"); // Ganti endpoint sesuai backend-mu
//     riwayat.value = response.data;
//   } catch (error) {
//     toast.error("Gagal mengambil riwayat");
//   }
// };

// onMounted(() => {
//   getProfile();
//   getRiwayat();
// });


// Inisialisasi store agar bisa akses data user yang login
const store = useAuthStore();

const kurir = ref({
    name: "",
    email: "",
    phone: "",
    photo: "",
    status: "",
    rating: 0,
});

const getProfile = async () => {
    kurir.value = {
        name: store.user.name,
        email: store.user.email,
        phone: store.user.phone,
        photo: store.user.photo ? "/storage/" + store.user.photo : "/default-avatar.png",
        // status: store.user.kurir?.status,
        // status: store.user?.kurir?.status || "-",
        status: store.user.kurir?.status,
        alamat: store.user.kurir?.alamat,
        jenis_kendaraan: store.user.kurir?.jenis_kendaraan,
        rating: store.user.rating,
    };
};

// const getProfile = async () => {
//     const response = await axios.get(/kurir/);
//     console.log(response.data)
//     const user = response.data;

//     kurir.value = {
//         name: user.name,
//         email: user.email,
//         phone: user.phone,
//         photo: user.photo ? "/storage/" + user.photo : "/default-avatar.png",
//         status: user.kurir?.status ?? "-",
//         rating: user.kurir?.rating ?? 0,
//     };
// };

onMounted(() => {
    getProfile();
});
</script>

<template>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profil Kurir</h3>
            <img :src="kurir.photo" class="rounded-circle mb-3 mt-5" width="50" height="50" alt="Foto ">
        </div>
        <!-- mengambil dari type -->
        <div class="card-body text-center">
            <h4>{{ kurir.name }}</h4>
            <p class="text-muted">📧 {{ kurir.email }}</p>
            <p>📞 {{ kurir.phone }}</p>
            <!-- <p>Status: <span :class="kurir.status === 'aktif' ? 'text-success' : 'text-danger'">{{ kurir.status
                    }}</span></p> -->
            <p class="d-flex align-items-center justify-content-center gap-2">
                Status:
                <span :class="kurir.status === 'aktif' ? 'text-success' : 'text-danger'">
                    📌{{ kurir.status }}
                </span>
            </p>
            <!-- <p>Rating: <span class="fw-bold">{{ kurir.rating }} / 5</span></p> -->
        </div>
    </div>

    <!-- <div>
        <p class="mt-10">Riwayat Pengiriman</p>
    </div> -->
</template>

<style scoped>
.text-muted {
    color: #376186;
    /* color: #6c757d; */
}
</style>