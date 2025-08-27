<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import type { Input } from "@/types";
import Swal from "sweetalert2";
import { computed } from "vue";


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

const hideMap = ref(false);

const activeStep = ref(0); // default = 0 (drop off)

// Fungsi update berdasarkan status
const updateActiveStep = (status: string) => {
  const statusLower = status.toLowerCase();

  if (statusLower === "menunggu") {
    activeStep.value = 0;
  } else if (
    statusLower === "dalam proses" ||
    statusLower === "diproses" ||
    statusLower === "masuk gudang" ||
    statusLower === "keluar gudang"
  ) {
    activeStep.value = 1;
  } else if (statusLower === "dikirim") {
    activeStep.value = 2;
  } else if (statusLower === "selesai" || statusLower === "terkirim") {
    activeStep.value = 3;
  } else {
    activeStep.value = 0; // fallback default
  }
};


// Fungsi cari resi
const cariResi = async () => {
  if (!noResi.value) {
    error.value = "Silakan isi nomor resi.";
    result.value = null;
    return;
  }

  hideMap.value = true;
  isLoading.value = true;
  error.value = "";
  result.value = null;

  try {
    const response = await axios.get(`/cek-resi`, {
      params: {
        no_resi: noResi.value,
      },
    });
    result.value = response.data.data;
    
    updateActiveStep(result.value?.status || "");

    const ratingKey = `rating_${result.value?.no_resi}`;
    isRatingSubmitted.value = localStorage.getItem(ratingKey) === 'submitted';
  } catch (err: any) {
    error.value = err.response?.data?.message || "Resi tidak ditemukan.";
  } finally {
    isLoading.value = false;
  }
};


const steps = ref([
  {
    label: "Drop off",
    icon: "/storage/photo/dro.png", // contoh file
  },
  {
    label: "Sedang diproses",
    icon: "/storage/photo/diproses.png",
  },
  {
    label: "Sedang diantar",
    icon: "/storage/photo/dikirimm.png",
  },
  {
    label: "Terkirim",
    icon: "/storage/photo/selesa.png",
  },
]);

const parsedRiwayat = computed(() => riwayat(result.value?.riwayat));

const riwayat = (data: any) => {
  if (!data) return [];
  try {
    return typeof data === 'string' ? JSON.parse(data) : data;
  } catch (e) {
    console.error('Error parsing riwayat:', e);
    return [];
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
        <div class="col-12">
          <button class="btn btn-danger w-100 mt-2" @click="cariResi">Cari</button>
        </div>
      </div>

      <div v-if="isLoading" class="alert alert-info">Sedang mencari...</div>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <div class="tracking-map-wrapper text-center mb-5" v-if="!hideMap">
        <img src="/storage/photo/images1.png" alt="Tracking Map" class="map-image" />
      </div>

      <!-- Progress Status Visual -->
      <div v-if="result" class="tracking-steps my-5 d-flex justify-between">
        <!-- Status Saat Ini -->
        <div v-for="(step, index) in steps" :key="step.label" class="step text-center"
        :class="{ active: index <= activeStep }">
        <div v-if="index !== steps.length - 1" class="step-line"></div>
        <div class="icon-wrapper">
          <img :src="step.icon" alt="icon" width="60" />
        </div>
        <div class="label fw-bold mt-2">{{ step.label }}</div>
        <div v-if="index < steps.length - 1" class="line"></div>
      </div>
    </div>
    

      <div v-if="result" class="table-responsive">
        <table class="table table-bordered">
          <tbody>
            <h2 class="h2">Status Pengiriman</h2>
            <table class="table table-bordered">
              <tr>
                <!-- <td class="status-text"> {{ result.keterangan_status }}</td> bukan yg ini -->
                <td class="status-text"> {{ result.status }}</td>
              </tr>
            </table>

            <!-- Riwayat Pengiriman Timeline -->
            <h2 class="h2">Riwayat Pengiriman</h2>
            <div v-if="parsedRiwayat.length > 0" class="timeline">
              <div v-for="(item, index) in parsedRiwayat" :key="index" class="timeline-item">
                <div class="timeline-dot" :class="{ 'done': index === 0 }"></div>
                <div class="timeline-content">
                  <div class="timeline-date">{{ formatDate(item.created_at) }}</div>
                  <div class="timeline-description">{{ item.deskripsi }}</div>
                </div>
              </div>
            </div>
            <div v-else class="riwayat-text">Belum ada riwayat pengiriman.</div>

            <!-- <h2 class="h2">Riwayat Pengiriman</h2>
            <div class="shipping-history">
              <table v-if="parsedRiwayat.length > 0" class="table table-bordered">
                <tr v-for="item in parsedRiwayat" :key="item?.id_riwayat">
                  <td>{{ item.deskripsi }}</td>
                  <td>{{ formatDate(item.created_at) }}</td>
                </tr>
              </table>
              <div v-else class="riwayat-text">
                Belum ada riwayat pengiriman.
              </div>
            </div> -->
          </tbody>
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
/* .timeline {
  position: relative;
  margin: 2rem auto;
  padding-left: 30px;
  border-left: 3px solid #f5717c;
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
  padding-left: 20px;
}

.timeline-dot {
  position: absolute;
  top: 0;
  left: -10px;
  width: 20px;
  height: 20px;
  background-color: #fff;
  border: 4px solid #f5717c;
  border-radius: 50%;
  z-index: 1;
} */

/* .timeline-dot.done {
      background-color: #f5717c;
    } */

/* .timeline-content {
  padding: 5px 10px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.timeline-date {
  font-weight: bold;
  color: #8d212e;
  font-size: 0.95rem;
}

.timeline-description {
  margin-top: 5px;
  font-size: 1.1rem;
  color: #444;
} */

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
  /* padding: 0.85rem; */
  font-size: 1.15rem;
  width: 200%;
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
  font-size: 1.5rem;
  text-transform: uppercase;
  /* border-radius: 5px; */
}

.table-bordered .riwayat-text {
  /* vertical-align: middle; */
  text-align: justify;
  text-indent: 30px;
  background-color: #fff;
  padding: 0.75rem 1rem;
  font-size: 1.3rem;
  /* text-transform: uppercase; */
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

.tracking-steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  padding: 2rem 1rem;
}

.step {
  position: relative;
  text-align: center;
  flex: 1;
}

.step .icon-wrapper {
  width: 70px;
  height: 70px;
  background-color: #f8d7da;
  border: 4px dashed #f5717c;
  border-radius: 50%;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step .icon-wrapper img {
  width: 40px;
  height: 40px;
}

.step .label {
  margin-top: 10px;
  font-weight: 600;
  color: #c53141;
}

.step.active .icon-wrapper {
  background-color: #f5717c;
}

.step.active .label {
  color: #b02335;
}

.line {
  position: absolute;
  top: 50%;
  right: -50%;
  height: 4px;
  width: 100%;
  background-color: #f5717c;
  z-index: -1;
}

.tracking-steps {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  gap: 20px;
}

.step {
  flex: 1;
  position: relative;
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  border: 3px dashed #f08080;
  border-radius: 50%;
  padding: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: transparent;
  /* semua icon transparan */
  transition: all 0.3s ease;
}

.step.active .icon-wrapper {
  border-color: #f08080;
  /* warna tetap sama jika aktif */
  border-style: dashed;
  /* tetap dashed */
  background-color: transparent;
}

.label {
  margin-top: 10px;
  font-weight: bold;
  color: #991b1b;
}

.line {
  position: absolute;
  top: 40px;
  /* tengah icon-wrapper */
  left: 100%;
  width: 100%;
  height: 3px;
  background-color: #f08080;
  z-index: -1;
}

.map-image {
  width: 100%;
  /* Buat full container */
  max-width: 600px;
  /* Maksimum ukuran yang diinginkan */
  height: auto;
  /* Biar tidak gepeng */
  margin: 0 auto;
  /* Tengah jika width dibatasi */
  display: block;
}


.timeline {
  position: relative;
  margin: 2rem auto;
  padding-top: 30px; /* Ubah dari padding-left ke padding-top */
  border-top: 3px solid #f5717c; /* Ubah dari border-left ke border-top */
  border-left: none; /* Hilangkan border kiri */
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
  margin-right: 2rem; /* Tambah margin kanan untuk spacing */
  padding-top: 20px; /* Ubah dari padding-left ke padding-top */
  display: inline-block; /* Buat item sejajar horizontal */
  vertical-align: top;
}

.timeline-item:last-child {
  margin-right: 0; /* Hilangkan margin untuk item terakhir */
}

.timeline-item:last-child::after {
  display: none; /* Hilangkan garis untuk item terakhir */
}

.timeline-dot {
  position: absolute;
  top: -10px; /* Ubah posisi ke atas */
  left: 50%; /* Posisikan di tengah horizontal */
  transform: translateX(-50%); /* Center horizontal */
  width: 20px;
  height: 20px;
  background-color: #fff;
  border: 4px solid #f5717c;
  border-radius: 50%;
  z-index: 1;
}

.timeline-item::after {
  content: '';
  position: absolute;
  top: 0; /* Posisi di atas */
  left: 100%; /* Mulai dari kanan item */
  width: 2rem; /* Lebar garis penghubung */
  height: 3px;
  background-color: #f5717c;
  z-index: 0;
}

.timeline-content {
  padding: 15px 10px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  text-align: center;
  width: 140px; /* Lebar tetap untuk kotak putih */
  height: 100px; /* Tinggi tetap untuk kotak putih */
  display: flex;
  flex-direction: column;
  justify-content: center; /* Center konten vertikal */
  align-items: center; /* Center konten horizontal */
  box-sizing: border-box; /* Include padding dalam perhitungan ukuran */
}

.timeline-date {
  font-weight: bold;
  color: #8d212e;
  font-size: 0.9rem;
}

.timeline-description {
  margin-top: 8px;
  font-size: 0.95rem; /* Sedikit lebih kecil */
  color: #444;
  line-height: 1.3; /* Line height lebih ketat */
  word-wrap: break-word; /* Break kata panjang */
  overflow-wrap: break-word;
  hyphens: auto; /* Auto hyphenation jika didukung */
}

/* Responsive untuk layar kecil */
@media (max-width: 768px) {
  .timeline {
    border-top: none;
    border-left: 3px solid #f5717c;
    padding-left: 30px;
    padding-top: 0;
  }
  
  .timeline-item {
    display: block;
    width: 100%;
    margin-right: 0;
    padding-top: 0;
    padding-left: 20px;
  }
  
  .timeline-item::after {
    display: none;
  }
  
  .timeline-dot {
    top: 0;
    left: -10px;
    transform: none;
  }
  
  .timeline-content {
    text-align: left;
  }
}
</style>