<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";


// Import store autentikasi untuk mengambil data user yang sedang login
import { useAuthStore } from "@/stores/auth";

// Inisialisasi store agar bisa akses data user yang login
const store = useAuthStore();

const kurir = ref({
    name: "",
    email: "",
    phone: "",
    photo: "",
    status: "",
    rating: 0,
    alamat: "",
    jenis_kendaraan: "",
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


onMounted(() => {
    getProfile();
});
</script>

<template>
    <div class="profile-card shadow d-flex">
      <div class="profile-image-container">
        <img :src="kurir.photo" class="profile-image" alt="Foto" />
      </div>
      <div class="profile-info">
        <h3 class="profile-name">👤 {{ kurir.name }}</h3>
        <p class="profile-email">📧 {{ kurir.email }}</p>
        <p class="profile-phone">📞 {{ kurir.phone }}</p>
        <p class="profile-alamat">🏠 {{ kurir.alamat }}</p>
        <p class="profile-jenis_kendaraan">🛵 {{ kurir.jenis_kendaraan }}</p>
        <p class="profile-status">
          <span :class="kurir.status === 'aktif' ? 'status-active' : 'status-inactive'">
            📌 {{ kurir.status || '-' }}
          </span>
        </p>
      </div>
    </div>
  </template>
  
  <style scoped>
  .profile-card {
    display: flex;
    align-items: center;
    max-width: 700px;
    margin: 100px auto;
    padding: 100px 100px;
    border-radius: 15px;
    background: linear-gradient(to right, #f0f8ff, #e6f7ff);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  
  .profile-image-container {
    flex-shrink: 0;
    margin-right: 70px;
  }
  
  .profile-image {
    width: 250px;
    height: 250px;
    border-radius: 12px;
    object-fit: cover;
    border: 3px solid #376186;
  }
  
  .profile-info {
    flex: 1;
  }
  
  .profile-name {
    font-size: 2rem;
    font-weight: bold;
    color: #000000;
    margin-bottom: 10px;
  }
  
  .profile-email,
  .profile-jenis_kendaraan,
  .profile-alamat,
  .profile-phone {
    color: #403c3c;
    font-size: 1.5rem;
    margin: 15px 0;
  }
  
  .profile-status {
    margin-top: 12px;
    font-weight: bold;
  }
  
  .status-active {
    color: #28a745;
    background-color: #d4edda;
    padding: 5px 10px;
    border-radius: 10px;
  }
  
  .status-inactive {
    color: #dc3545;
    background-color: #f8d7da;
    padding: 5px 10px;
    border-radius: 10px;
  }
  </style>
  