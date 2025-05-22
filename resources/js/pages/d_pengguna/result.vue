<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const origin = route.query.origin || '';
const destination = route.query.destination || '';
const weight = route.query.weight || '';
const selectedCouriers = route.query.couriers
  ? route.query.couriers.split(',')
  : [];

const costs = ref([]);

// Ambil dan parse data ongkir dari query param costs (JSON string)
if (route.query.costs) {
  try {
    costs.value = JSON.parse(decodeURIComponent(route.query.costs));
  } catch (e) {
    costs.value = [];
  }
}

function formatRupiah(number) {
  return (
    "Rp " +
    number
      .toString()
      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
  );
}
</script>

<template>
  <div class="result-container">
    <h1>Hasil Estimasi Ongkir</h1>
    <p><strong>Kota Asal:</strong> {{ origin }}</p>
    <p><strong>Kota Tujuan:</strong> {{ destination }}</p>
    <p><strong>Berat Paket:</strong> {{ weight }} gram</p>

    <div v-if="costs.length === 0" class="no-results">
      Maaf, tidak ada data ongkir untuk rute ini.
    </div>

    <div v-for="cost in costs" :key="cost.courier" class="result-item">
      <span>{{ cost.courier }}</span>
      <span><strong>{{ formatRupiah(cost.price) }}</strong></span>
    </div>
  </div>
</template>

<style scoped>
.result-container {
  padding: 1rem;
  max-width: 600px;
  margin: 0 auto;
}
.result-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #ccc;
}
.no-results {
  color: red;
  font-style: italic;
  margin-top: 1rem;
}
</style>
