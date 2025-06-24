<!-- <script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import type { Input } from "@/types";

State input user
const noResi = ref("");
const ekspedisi = ref("");
const ekspedisiList = ["JNE", "TIKI", "POS"];

const result = ref<Input | null>(null);
const isLoading = ref(false);
const error = ref("");

Fungsi cari resi
const cariResi = async () => {
  if (!noResi.value || !ekspedisi.value) {
    error.value = "Silakan isi nomor resi dan pilih ekspedisi.";
    result.value = null;
    return;
  }

  isLoading.value = true;
  error.value = "";
  result.value = null;

  try {
    const response = await axios.get(`/cek-resi`, {
      params: {
        no_resi: noResi.value,
        ekspedisi: ekspedisi.value,
      },
    });
    result.value = response.data.data;
  } catch (err: any) {
    error.value = err.response?.data?.message || "Resi tidak ditemukan.";
  } finally {
    isLoading.value = false;
  }
  
};
</script> -->
<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import type { Input } from "@/types";
import Swal from "sweetalert2";


// State input user
const noResi = ref("");
const ekspedisi = ref("");
const ekspedisiList = ["JNE", "TIKI", "POS"];
const result = ref<Input | null>(null);
const isLoading = ref(false);
const error = ref("");

const rating = ref<number | null>(null);
const ulasan = ref("");
const isRatingSubmitted = ref(false);

// function formatDate(dateStr: string) {
//   if (!dateStr) return '-';
//   const date = new Date(dateStr);
//   return date.toLocaleDateString('id-ID', {
//     day: '2-digit', month: 'long', year: 'numeric',
//     hour: '2-digit', minute: '2-digit'
//   });
// }
const formatDate = (timestamp?: string) => {
  if (!timestamp) return new Date().toLocaleString('id-ID');
  return new Date(timestamp).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
// Fungsi cari resi
const cariResi = async () => {
  if (!noResi.value || !ekspedisi.value) {
    error.value = "Silakan isi nomor resi dan pilih ekspedisi.";
    result.value = null;
    return;
  }

  isLoading.value = true;
  error.value = "";
  result.value = null;

  try {
    const response = await axios.get(`/cek-resi`, {
      params: {
        no_resi: noResi.value,
        ekspedisi: ekspedisi.value,
      },
    });
    result.value = response.data.data;
    const ratingKey = `rating_${result.value?.no_resi}`;
    isRatingSubmitted.value = localStorage.getItem(ratingKey) === 'submitted';
  } catch (err: any) {
    error.value = err.response?.data?.message || "Resi tidak ditemukan.";
  } finally {
    isLoading.value = false;
    console.log(isLoading.value)
  }
};

// Fungsi untuk parsing riwayat pengiriman
const riwayat = (data: any) => {
  if (!data) return [];
  try {
    return typeof data === 'string' ? JSON.parse(data) : data;
  } catch (e) {
    console.error('Error parsing riwayat:', e);
    return [];
  }
};


// Fungsi untuk format tanggal
// const formatDate = (timestamp?: string) => {
//   if (!timestamp) return new Date().toLocaleString('id-ID');
//   return new Date(timestamp).toLocaleString('id-ID');
// };


const kirimRating = async () => {
  if (!rating.value || !ulasan.value.trim()) {
    error.value = "Mohon beri rating dan ulasan.";
    return;
  }

  try {
    console.log('Mengirim data:', {
      no_resi: result.value?.no_resi,
      rating: rating.value,
      ulasan: ulasan.value,
    });

    const response = await axios.post("/beri-rating", {
      no_resi: result.value?.no_resi,
      rating: rating.value, // ✅ tidak perlu parseInt
      ulasan: ulasan.value.trim(),
    });

    console.log('Response berhasil:', response.data);

    isRatingSubmitted.value = true;
    error.value = "";

    // Reset form
    rating.value = '';
    ulasan.value = '';

    // Simpan status ke localStorage
    localStorage.setItem(`rating_${result.value?.no_resi}`, 'submitted');

    // alert('Rating berhasil dikirim!');
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Rating berhasil dikirim!',
      confirmButtonText: 'OK',
    });

  } catch (err) {
    console.error('Error lengkap:', err);
    console.error('Response error:', err.response);
    error.value = err.response?.data?.message || "Gagal mengirim rating.";
  }
};

</script>
<template>
  <div class="card">
    <div>
      <h1 class="h1">Cek Resi Pengiriman</h1>
      <!-- <h1 class="mb-0">Cek Resi Pengiriman</h1> -->
    </div>

    <div class="card-body">
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Nomor Resi</label>
          <input v-model="noResi" type="text" class="form-control" placeholder="Masukkan No Resi"
            @keyup.enter="cariResi" />
        </div>
        <div class="col-md-6">
          <label class="form-label">Ekspedisi</label>
          <select v-model="ekspedisi" class="form-select">
            <option disabled value="">-- Pilih Ekspedisi --</option>
            <option v-for="item in ekspedisiList" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
        </div>
        <div class="col-12">
          <button class="btn btn-danger w-100 mt-2" @click="cariResi">Cari</button>
        </div>
      </div>
      <div v-if="isLoading" class="alert alert-info">Sedang mencari...</div>
      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <div v-if="result" class="table-responsive">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <th>No Resi</th>
              <td>{{ result.no_resi }}</td>
            </tr>
            <tr>
              <th>Ekspedisi</th>
              <td>{{ result.ekspedisi }}</td>
            </tr>
            <tr>
              <th>Nama Pengirim</th>
              <td>{{ result.nama_pengirim }}</td>
            </tr>
            <tr>
              <th>Nama Penerima</th>
              <td>{{ result.nama_penerima }}</td>
            </tr>
            <tr>
              <th>Alamat Penerima</th>
              <td>{{ result.alamat_penerima }}</td>
            </tr>
            <tr>
              <th>Jenis Barang</th>
              <td>{{ result.jenis_barang }}</td>
            </tr>
            <tr>
              <th>Berat Barang</th>
              <td>{{ result.berat_barang }} kg</td>
            </tr>
          </tbody>
        </table>
        <table class="table table-bordered">
          <tbody>
            <h2 class="h2">Status Pengiriman</h2>
            <table class="table table-bordered">
              <tr>
                <!-- <td class="status-text"> {{ result.keterangan_status }}</td> -->
                <td class="status-text"> {{ result.status }}</td>
              </tr>
            </table>
            <h2 class="h2">Riwayat Pengiriman</h2>
            <div class="shipping-history">
              <table class="table table-bordered">
                <!-- menggunakan array v-for -->
                <tr v-for="riwayat in result.riwayat" :key="riwayat?.id_riwayat">
                  <td>{{ riwayat.deskripsi }}</td>
                  <td>{{ formatDate(riwayat.created_at) }}</td>
                  <!-- <td>{{ result.riwayat }} {{ formatDate(result.created_at) }} </td> -->
                  <!-- <td class="timestamp">{{ result.timestamp }}</td> -->
                </tr>
              </table>
            </div>
          </tbody>
          <!-- <div v-if="result.status === 'selesai' && !isRatingSubmitted" class="mt-4">
          <h2 class="h2">Beri Penilaian Pengiriman</h2>
          <div class="mb-3">
            <label for="rating" class="form-label">Rating (1-5)</label>
            <select v-model="rating" class="form-select">
              <option disabled value="">-- Pilih Rating --</option>
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="ulasan" class="form-label">Ulasan</label>
            <textarea v-model="ulasan" class="form-control" rows="3" placeholder="Tulis pengalaman Anda..."></textarea>
          </div>
          <button class="btn btn-primary" @click="kirimRating">Kirim Penilaian</button>
        </div>
        
        <div v-else-if="isRatingSubmitted" class="alert alert-info">
          Terima kasih atas penilaian Anda!
        </div> -->
          <div>
            <!-- Template HTML Anda -->
            <div v-if="result.status === 'selesai' && !isRatingSubmitted" class="mt-4">
              <h2 class="h2">Beri Penilaian Pengiriman</h2>
              <div class="mb-3">
                <label for="rating" class="form-label">Rating (1-5)</label>
                <select v-model="rating" class="form-select">
                  <option disabled value="">-- Pilih Rating --</option>
                  <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="ulasan" class="form-label">Ulasan</label>
                <textarea v-model="ulasan" class="form-control" rows="3"
                  placeholder="Tulis pengalaman Anda..."></textarea>
              </div>
              <button class="btn btn-danger" @click="kirimRating">Kirim Penilaian</button>
            </div>

            <div v-else-if="isRatingSubmitted" class="alert alert-info">
              Terima kasih atas penilaian Anda!
            </div>
          </div>

        </table>
      </div>
    </div>
  </div>

</template>
<style scoped>
.card {
  max-width: 1000px;
  margin: 4rem auto;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  background-color: #fbd1df;
  border: 1px solid #f5717c;
  font-size: 1.15rem;
}

.timestamp {
  font-size: 14px;
  color: #666;
  margin-top: 5px;
}

.h1 {
  font-weight: bold;
  color: #b23a48;
  text-align: center;
  font-size: 2rem;
  margin-bottom: 2.5rem;
}

.h2 {
  font-weight: bold;
  color: #8d212e;
  text-align: center;
  font-size: 1.75rem;
  margin: 2rem 0;
}

.form-label {
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  color: #333;
}

.form-control,
.form-select {
  padding: 0.85rem;
  font-size: 1.15rem;
  border-radius: 10px;
  border: 1px solid #ced4da;
  transition: border-color 0.3s ease;
}

.form-control:focus,
.form-select:focus {
  border-color: #e83e8c;
  outline: none;
  box-shadow: 0 0 0 0.15rem rgba(232, 62, 140, 0.25);
}

.btn {
  font-size: 1.2rem;
  padding: 0.8rem 1.5rem;
  border-radius: 10px;
  font-weight: bold;
}

.btn-primary,
.btn-danger {
  background-color: #e83e8c;
  border: none;
}

.btn-primary:hover,
.btn-danger:hover {
  background-color: #d63384;
}

.alert-info,
.alert-danger {
  padding: 1rem 1.25rem;
  border-radius: 10px;
  font-size: 1.1rem;
  font-weight: 500;
  margin-top: 1.5rem;
}

.alert-info {
  background-color: #ffe3ec;
  color: #b23a48;
}

.alert-danger {
  background-color: #f8d7da;
  color: #842029;
}

.table {
  margin-top: 2rem;
  font-size: 1.05rem;
}

.table-bordered th,
.table-bordered td {
  vertical-align: middle;
  background-color: #fff;
  padding: 0.75rem 1rem;
  /* border-radius: 5px; */
}

.table-bordered .status-text {
  /* vertical-align: middle; */
  text-align: justify;
  text-indent: 30px;
  background-color: #fff;
  padding: 0.75rem 1rem;
  font-size: 2.5rem;
  text-transform: uppercase;
  /* border-radius: 5px; */
}

.badge {
  padding: 0.5em 1em;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 14px;
  display: inline-block;
  text-transform: capitalize;
}

.bg-success {
  background-color: #198754;
  color: white;
}

.bg-warning {
  background-color: #ffc107;
  color: black;
}

.bg-danger {
  background-color: #dc3545;
  color: white;
}

.bg-primary {
  background-color: #bc26c1;
  color: white;
}

.bg-info {
  background-color: #f00dc3;
  color: black;
}

.bg-secondary {
  background-color: #6c757d;
  color: white;
}

.status-text {
  font-weight: 600;
  /* cetak tebal menengah */
  font-size: 1.1rem;
  /* ukuran tulisan menengah */
  color: #333;
  /* warna teks agar jelas */
  text-align: center;
}
</style>