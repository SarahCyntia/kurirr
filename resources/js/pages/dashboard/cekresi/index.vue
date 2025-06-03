<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import type { Input } from "@/types";

// State input user
const noResi = ref("");
const ekspedisi = ref("");
const ekspedisiList = ["JNE", "TIKI", "POS"];

const result = ref<Input | null>(null);
const isLoading = ref(false);
const error = ref("");

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
  } catch (err: any) {
    error.value = err.response?.data?.message || "Resi tidak ditemukan.";
  } finally {
    isLoading.value = false;
  }
};
</script>
<template>
  <div class="card">
    <div class="card-header">
      <h1 class="mb-0">Cek Resi Pengiriman</h1>
    </div>

    <div class="card-body">
      <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Nomor Resi</label>
          <input v-model="noResi" type="text" class="form-control" placeholder="Masukkan No Resi"
            @keyup.enter="cariResi" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Ekspedisi</label>
          <select v-model="ekspedisi" class="form-select">
            <option disabled value="">-- Pilih Ekspedisi --</option>
            <option v-for="item in ekspedisiList" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
        </div>
        <div class="col-md-4">
          <button class="btn btn-danger w-100" @click="cariResi">Cari</button>
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
            <h2 class="h2">Riwayat Pengiriman</h2>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Status</th>
                  <th>Deskripsi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in result.history" :key="index">
                  <td>{{ item.waktu }}</td>
                  <td>
                    <span :class="{
                      badge: true,
                      'bg-success': item.status === 'menunggu',
                      'bg-warning': item.status === 'dalam proses',
                      'bg-danger': item.status === 'pengambilan paket',
                      'bg-primary': item.status === 'dikirim',
                      'bg-info': item.status === 'selesai',
                      'bg-secondary': !['menunggu', 'dalam proses', 'pengambilan paket', 'dikirim', 'selesai'].includes(item.status),
                    }">
                      {{
                        item.status === 'menunggu'
                          ? 'Menunggu'
                          : item.status === 'pengambilan paket'
                            ? 'Pengambilan Paket'
                            : item.status === 'dalam proses'
                              ? 'Dalam Proses'
                              : item.status === 'dikirim'
                                ? 'Dikirim'
                                : item.status === 'selesai'
                      ? 'Selesai'
                      : 'Dibatalkan'
                      }}
                    </span>
                  </td>
                  <td>{{ item.deskripsi }}</td>
                </tr>
              </tbody>
            </table>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.card {
  max-width: 700px;
  margin: 3rem auto;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  background-color: #fbd1df;
  /* Soft pink background */
  border: 1px solid #f5717c;
}

.card-header h1 {
  font-weight: bold;
  color: #b23a48;
  /* Warna pink gelap */
  text-align: center;

  margin-bottom: 2rem;
}

.h2 {
  font-weight: bold;
  color: #8d212e;
  /* Warna pink gelap */
  text-align: center;
  margin-bottom: 2rem;
  /* margin-right: 100px; */
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #333;
}

.form-control,
.form-select {
  padding: 0.65rem;
  font-size: 1rem;
  border-radius: 8px;
  border: 1px solid #ced4da;
  transition: border-color 0.3s ease;
}

.form-control:focus,
.form-select:focus {
  border-color: #e83e8c;
  /* Pink accent */
  outline: none;
  box-shadow: 0 0 0 0.15rem rgba(232, 62, 140, 0.25);
}

.btn-primary {
  background-color: #e83e8c;
  /* Pink utama */
  border: none;
  padding: 0.6rem 1.2rem;
  font-size: 1rem;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #d63384;
}

.alert-info,
.alert-danger {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  margin-top: 1rem;
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
  margin-top: 1.5rem;
  font-size: 0.95rem;
}

.table-bordered th,
.table-bordered td {
  vertical-align: middle;
  background-color: #fff;
}

.badge {
  padding: 0.4em 0.75em;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: 12px;
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
</style>