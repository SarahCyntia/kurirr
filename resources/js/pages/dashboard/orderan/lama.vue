<template>
  <div>
    <h2 class="text-lg font-bold mb-2">📦 Paket Kurir Saya</h2>
    <ul>
      <li v-for="paket in pakets" :key="paket.id" class="p-2 border rounded mb-2">
        Resi: {{ paket.no_resi }}<br />
        Status: {{ paket.status }}<br />
        <button @click="ajukanPindah(paket.no_resi)">Ajukan Pindah Gudang</button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/libs/axios';
import { toast } from 'vue3-toastify';

const pakets = ref([]);

const ajukanPindah = async (resi: string) => {
  const tujuan = prompt("Masukkan gudang tujuan:");
  if (tujuan) {
    await axios.post('/api/paket/ajukan-pindah', {
      no_resi: resi,
      gudang_tujuan: tujuan,
    });
    toast.success("✅ Permintaan pindah dikirim");
  }
};

onMounted(async () => {
  const res = await axios.get('/api/paket/kurir');
  pakets.value = res.data;
});
</script>
