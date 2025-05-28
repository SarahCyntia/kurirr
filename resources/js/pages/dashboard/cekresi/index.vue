<script setup lang="ts">
import { ref } from "vue";
import type { Input } from "@/types";
import axios from "axios";

const nomorResi = ref<string>("");
const courier = ref<string>("");          // ← deklarasi courier
const loading = ref<boolean>(false);
const error = ref<string>("");
const hasilResi = ref<Input | null>(null);

const cekResi = async () => {
  if (!courier.value) {
    error.value = "Silakan pilih kurir terlebih dahulu.";
    return;
  }
  if (!nomorResi.value.trim()) {
    error.value = "Nomor resi tidak boleh kosong.";
    return;
  }

  loading.value = true;
  error.value = "";
  hasilResi.value = null;

  try {
    const response = await axios.get(`/cek-resi/${encodeURIComponent(nomorResi.value)}`, {
      params: {
        kurir: courier.value.toLowerCase(),
      },
    });
    hasilResi.value = response.data;
  } catch (err: any) {
    console.error(err);
    error.value = err.response?.data?.message || "Resi tidak ditemukan.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="card">
    <div class="card-body">
      <!-- Nomor Resi -->
      <div class="mb-3">
        <label for="nomorResi" class="form-label">Masukkan Nomor Resi</label>
        <input
          v-model="nomorResi"
          id="nomorResi"
          type="text"
          class="form-control"
          placeholder="Contoh: RESI-123456789-1234"
        />
      </div>

      <!-- Pilih Kurir -->
      <div class="mb-3">
        <label for="courier" class="form-label">Pilih Kurir</label>
        <select v-model="courier" id="courier" class="form-control">
          <option value="">-- Pilih Kurir --</option>
          <option value="jne">JNE</option>
          <option value="pos">POS</option>
          <option value="tiki">TIKI</option>
        </select>
      </div>

      <!-- Tombol Cek -->
      <button
        class="btn btn-danger"
        :disabled="loading || !nomorResi.trim() || !courier"
        @click="cekResi"
      >
        {{ loading ? "Mengecek..." : "Lacak Paket" }}
      </button>

      <!-- Error -->
      <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>

      <!-- Hasil -->
      <div v-if="hasilResi" class="mt-4">
        <h5>Detail Pengiriman:</h5>
        <ul class="list-group">
          <li class="list-group-item"><strong>No Resi:</strong> {{ hasilResi.no_resi }}</li>
          <li class="list-group-item"><strong>Nama Pengirim:</strong> {{ hasilResi.nama_pengirim }}</li>
          <li class="list-group-item"><strong>Alamat Pengirim:</strong> {{ hasilResi.alamat_pengirim }}</li>
          <li class="list-group-item"><strong>Nama Penerima:</strong> {{ hasilResi.nama_penerima }}</li>
          <li class="list-group-item"><strong>Alamat Penerima:</strong> {{ hasilResi.alamat_penerima }}</li>
          <li class="list-group-item"><strong>Status:</strong> {{ hasilResi.status }}</li>
        </ul>

        <h5 class="mt-4">Riwayat Pengiriman</h5>
        <ul v-if="hasilResi.riwayat_pengiriman" class="list-group">
          <li
            v-for="(item, idx) in JSON.parse(hasilResi.riwayat_pengiriman || '[]')"
            :key="idx"
            class="list-group-item"
          >
            <strong>{{ item.pesan }}</strong><br />
            <small class="text-muted">{{ item.waktu }}</small>
          </li>
        </ul>
        <p v-else>Tidak ada riwayat pengiriman.</p>
      </div>
    </div>
  </div>
</template>
<style scoped>
.card {
    max-width: 600px;
    margin: 3rem auto;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #f29b9b;
}

.card-header h2 {
    font-weight: bold;
    color: #333;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control {
    padding: 0.6rem;
    font-size: 1rem;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.btn-primary {
    background-color: #0d6efd;
    border: none;
    padding: 0.6rem 1.2rem;
    font-size: 1rem;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0b5ed7;
}

.alert-danger {
    padding: 0.75rem 1rem;
    background-color: #f8d7da;
    color: #842029;
    border-radius: 8px;
    font-weight: 500;
}

.mt-4 {
    margin-top: 1.5rem;
}

.list-group {
    padding: 0;
    list-style: none;
    border-radius: 8px;
    overflow: hidden;
}

.list-group-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #ddd;
    background-color: #f9f9f9;
}

.list-group-item:last-child {
    border-bottom: none;
}

</style>
