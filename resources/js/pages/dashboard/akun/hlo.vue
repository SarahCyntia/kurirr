<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";

// Import store autentikasi untuk mengambil data user yang sedang login
import { useAuthStore } from "@/stores/auth";

// Inisialisasi store agar bisa akses data user yang login
const store = useAuthStore();
// const role = ref(""); 

// const getProfile = async () => {
//     role.value = store.user.role;

//     if (role.value === "kurir") {
//         kurir.value = {
//             name: store.user.name,
//             email: store.user.email,
//             phone: store.user.phone,
//             photo: store.user.photo ? "/storage/" + store.user.photo : "/default-avatar.png",
//             status: store.user.kurir?.status || "-",
//             rating: store.user.kurir?.rating || 0,
//         };
//     } else {
//         kurir.value = {
//             name: store.user.name,
//             email: store.user.email,
//             phone: store.user.phone,
//             photo: store.user.photo ? "/storage/" + store.user.photo : "/default-avatar.png",
//             status: "-", 
//             rating: 0,   
//         };
//     }
// };


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
        status: store.user.kurir?.status,
    };
};

onMounted(() => {
    getProfile();
});
</script>

<template>
    <div class="card" >
        <!-- <div class="card-header" >
            <h3 class="card-title">Profil</h3>
            <img :src="kurir.photo" class="rounded-full object-cover mt-3" width="50" height="50" mt-6 alt="Foto Kurir">
        </div>
        <div class="card-body text-center">
            <h4>{{ kurir.name }}</h4>
            <p class="text-muted">{{ kurir.email }}</p>
            <p>Nomor Telepon: {{ kurir.phone }}</p>
            <p>Status: <span :class="kurir.status === 'aktif' ? 'text-success' : 'text-danger'">{{ kurir.status
                    }}</span></p>
            <p class="d-flex align-items-center justify-content-center gap-2">
                Status:
                <span :class="kurir.status === 'aktif' ? 'text-success' : 'text-danger'">
                    {{ kurir.status }}
                </span>
            </p>
            <p>Rating: <span class="fw-bold">{{ kurir.rating }} / 5</span></p>
        </div> -->
    </div>
</template>

<style scoped>
.text-muted {
    color: #376186;
    /* color: #6c757d; */
}
</style>