<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/libs/axios';
import { useAuthStore } from "@/stores/auth";
import { watch } from 'vue';
import Swal from 'sweetalert2';

const User = ref({ name: "", role: "kurir" });
const showTransaksi = ref(false);
const transaksiList = ref([]);
const todayCount = ref(0);
const yesterdayCount = ref(0);
const monthCount = ref(0);
const transaksiCount = ref(0);

const store = useAuthStore();

const getProfile = async () => {
  User.value.name = store.user.name;

  if (User.value.role === 'kurir') {
    try {
      const res = await axios.get("/kurir/transaksi-count");
      transaksiCount.value.today = res.data.today;
      transaksiCount.value.yesterday = res.data.yesterday;
      transaksiCount.value.month = res.data.month;
      transaksiCount.value.custom = res.data.custom; // opsional jika digunakan
    } catch (error) {
      console.error("Gagal mengambil jumlah transaksi", error);
    }
  }
};

const getTransaksiList = async (filter = null) => {
  // if (User.value.role === 'kurir') {
    try {
      const res = await axios.get('/kurir/transaksi-list', {
        params: { filter }  // kirim filter ke backend
      });
      transaksiList.value = res.data.data;
      yesterdayCount.value = res.data.yesterdayCount;
      todayCount.value = res.data.todayCount;
      monthCount.value = res.data.monthCount;
      showTransaksi.value = true;
    }  catch (error) {
      if (error.response && error.response.status === 403) {
        Swal.fire({
          icon: 'error',
          title: 'Akses Ditolak',
          text: error.response.data.message || 'Anda tidak memiliki izin untuk melihat data ini.'
        });
      } else {
        console.error("Gagal mengambil daftar transaksi", error);
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: 'Gagal mengambil data transaksi.'
        });
      }
    // }
  }
};

const closeTransaksiList = () => {
  showTransaksi.value = false;
};



onMounted(() => {
  getProfile();
});

// watch(() => store.user, (newUser) => {
//   if (newUser.role === 'kurir') {
//     getProfile();
//   }
// }, { immediate: true });

</script>

<template>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"></a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link active" aria-current="page" href="#"
                        >🏠 Home</a
                    >
                    <router-link class="nav-link" to="/dashboard/order/input">🛒 Input</router-link>
                    <router-link class="nav-link" to="/dashboard/order/data">🛵 Order</router-link>
                    <a class="nav-link disabled" aria-disabled="true"
                        >Disabled</a
                    >
                </div>
            </div>
        </div>
    </nav>
    <!-- <DashboardHome v-if="currentTab === 'dashboard'" /> -->
</template>
<style>
.navbar .nav-link,
  .navbar .navbar-brand {
    font-size: 2rem; /* Ubah nilai ini sesuai keinginan, misal 1.5rem, 20px, dsb */
    padding-left: 45rem;
  }

</style>