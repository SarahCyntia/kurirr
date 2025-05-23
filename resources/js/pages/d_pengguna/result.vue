<template>
  <div class="container">
    <h1>Hasil Estimasi Ongkir</h1>

    <div v-if="costs.length > 0">
      <div v-for="(item, index) in costs" :key="index" class="result-item">
        <strong>{{ item.courier }}</strong>: {{ formatRupiah(item.price) }}
      </div>
    </div>
    <p v-else>Tidak ada hasil untuk ditampilkan.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const costs = ref([])

onMounted(() => {
  const data = localStorage.getItem('shippingResults')
  if (data) {
    try {
      costs.value = JSON.parse(data)
    } catch (e) {
      console.error('Gagal parse data ongkir:', e)
    }
  }
})

function formatRupiah(number) {
  return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

<style scoped>
.container {
  padding: 2rem;
  font-family: 'Poppins', sans-serif;
  background-color: #f9f9f9;
  min-height: 100vh;
}
h1 {
  text-align: center;
  margin-bottom: 2rem;
}
.result-item {
  margin: 1rem 0;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
