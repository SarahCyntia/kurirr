<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import axios from '@/libs/axios';
import { toast } from 'vue3-toastify';

const pakets = ref<any[]>([]);
const resi = ref('');
const resiInput = ref<HTMLInputElement | null>(null);

const laporan = ref({
  masuk: 0,
  keluar: 0,
  total_di_gudang: 0,
});
const gudangTujuan = ref('');

const pindahGudang = async (noResi: string) => {
  try {
    await axios.post('/api/paket/pindah-gudang', {
      no_resi: noResi,
      gudang_tujuan: gudangTujuan.value,
    });
    toast.success('📦 Paket dipindah ke gudang baru');
    fetchPaketGudang();
  } catch (e) {
    toast.error('❌ Gagal memindahkan paket');
  }
};

const fetchPaketGudang = async () => {
  const res = await axios.get('/api/paket/di-gudang');
  pakets.value = res.data;
};

const fetchLaporan = async () => {
  const res = await axios.get('/api/laporan/gudang');
  laporan.value = res.data;
};

const ambilPaket = async (noResi: string) => {
  try {
    await updateLokasi(noResi); // Simpan lokasi sebelum ambil
    await axios.post('/api/paket/ambil', { no_resi: noResi });
    toast.success('✅ Paket diambil untuk pengiriman');
    await fetchPaketGudang();
    await fetchLaporan();
  } catch (e) {
    toast.error('❌ Gagal mengambil paket');
  }
};

const lepasPaket = async () => {
  try {
    await axios.post('/api/paket/lepas', { no_resi: resi.value });
    toast.success('📦 Paket dilepas ke gudang');
    resi.value = '';
    await fetchPaketGudang();
    await fetchLaporan();
    nextTick(() => resiInput.value?.focus());
  } catch (e) {
    toast.error('❌ Gagal melepas paket');
  }
};

const updateLokasi = async (noResi: string) => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(async (position) => {
      const { latitude, longitude } = position.coords;
      await axios.post('/api/paket/update-lokasi', {
        no_resi: noResi,
        latitude,
        longitude
      });
    });
  } else {
    toast.warning("📍 Lokasi tidak didukung browser");
  }
};

onMounted(async () => {
  await fetchPaketGudang();
  await fetchLaporan();
  nextTick(() => resiInput.value?.focus());
});
</script>

<template>
  <div class="max-w-3xl mx-auto p-4">
    <!-- Ringkasan Gudang -->
    <div class="mb-6 bg-gray-100 p-4 rounded shadow">
      <h3 class="text-lg font-semibold mb-2">📦 Ringkasan Gudang</h3>
      <p>Masuk Hari Ini: <strong>{{ laporan.masuk }}</strong></p>
      <p>Keluar Hari Ini: <strong>{{ laporan.keluar }}</strong></p>
      <p>Sisa Di Gudang: <strong>{{ laporan.total_di_gudang }}</strong></p>
    </div>

    <!-- Input Scan Barcode -->
    <div class="my-4 flex gap-2">
      <input
        v-model="resi"
        @keyup.enter="lepasPaket"
        ref="resiInput"
        placeholder="Scan / input no resi"
        class="border border-gray-300 p-2 rounded w-full"
      />
      <button @click="lepasPaket" class="px-4 py-2 bg-blue-500 text-white rounded">
        Lepas ke Gudang
      </button>
    </div>

    <!-- Daftar Paket -->
    <h2 class="text-xl font-bold mb-2">Daftar Paket di Gudang</h2>
    <ul v-if="pakets.length > 0">
      <li
        v-for="paket in pakets"
        :key="paket.id"
        class="border border-gray-300 p-3 mb-3 rounded shadow-sm"
      >
        <div class="text-sm text-gray-700">No Resi: <strong>{{ paket.no_resi }}</strong></div>
        <div class="text-sm text-gray-700">Tujuan: {{ paket.tujuan_kota }}</div>
        <button
          @click="ambilPaket(paket.no_resi)"
          class="mt-2 bg-green-600 px-3 py-1 text-white rounded"
        >
          Ambil untuk Pengiriman
        </button>
      </li>
      <input v-model="gudangTujuan" placeholder="Gudang tujuan" />
    <button @click="pindahGudang(paket.no_resi)">Pindah Gudang</button>

    </ul>
    <p v-else class="text-gray-500">Tidak ada paket di gudang.</p>
  </div>
</template>
<style scoped>

/* Reset dan Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  color: #2d3748;
}

/* Container Utama */
.max-w-3xl {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin: 20px auto;
  overflow: hidden;
}

/* Ringkasan Gudang */
.bg-gray-100 {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  border: none;
  color: white;
  position: relative;
  overflow: hidden;
}

.bg-gray-100::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

.bg-gray-100 h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.bg-gray-100 p {
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255,255,255,0.2);
}

.bg-gray-100 p:last-child {
  border-bottom: none;
  font-weight: 600;
  font-size: 1.2rem;
}

.bg-gray-100 strong {
  background: rgba(255,255,255,0.2);
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 700;
}

/* Input Section */
.my-4 {
  background: white;
  padding: 1.5rem;
  border-radius: 15px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  border: 1px solid rgba(255,255,255,0.2);
}

.my-4 input {
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.my-4 input:focus {
  outline: none;
  border-color: #4facfe;
  background: white;
  box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1), inset 0 2px 4px rgba(0,0,0,0.05);
  transform: translateY(-1px);
}

.my-4 button {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 12px;
  padding: 12px 24px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.my-4 button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.my-4 button:active {
  transform: translateY(0);
}

/* Header Daftar Paket */
h2 {
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.75rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  text-align: center;
}

/* Daftar Paket */
ul {
  display: grid;
  gap: 1rem;
  list-style: none;
}

li {
  background: white;
  border: none;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

li::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

li:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

li .text-sm {
  font-size: 1rem;
  margin-bottom: 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

li .text-sm:first-child strong {
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
  font-size: 1.1rem;
}

/* Tombol Aksi */
.bg-green-600 {
  background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
  margin-right: 0.5rem;
}

.bg-green-600:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(72, 187, 120, 0.6);
}

/* Input Gudang Tujuan */
input[placeholder="Gudang tujuan"] {
  background: #f7fafc;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 12px;
  margin-right: 0.5rem;
  margin-top: 0.5rem;
  transition: all 0.3s ease;
}

input[placeholder="Gudang tujuan"]:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Tombol Pindah Gudang */
button:last-child {
  background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
  border: none;
  border-radius: 10px;
  padding: 8px 16px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(237, 137, 54, 0.4);
  margin-top: 0.5rem;
}

button:last-child:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(237, 137, 54, 0.6);
}

/* Pesan Kosong */
.text-gray-500 {
  text-align: center;
  font-size: 1.1rem;
  color: #a0aec0;
  font-style: italic;
  padding: 2rem;
  background: white;
  border-radius: 15px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.05);
}

/* Responsive Design */
@media (max-width: 768px) {
  .max-w-3xl {
    margin: 10px;
    border-radius: 15px;
  }
  
  .my-4 {
    flex-direction: column;
    gap: 1rem;
  }
  
  .my-4 button {
    width: 100%;
  }
  
  li {
    padding: 1rem;
  }
  
  .bg-gray-100 p {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}

/* Animasi Loading */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.loading {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth Scroll */
html {
  scroll-behavior: smooth;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a6fd8, #6b46a3);
}
</style>