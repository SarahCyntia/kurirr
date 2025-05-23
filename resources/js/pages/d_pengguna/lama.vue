<template>
  <div class="app-container">
    <header>
      <h1>Kurir RajaLike</h1>
      <p>Estimasi Ongkos Kirim Kurir Indonesia</p>
    </header>

    <form @submit.prevent="onSubmit" novalidate>
      <label for="origin">Kota Asal:</label>
      <select v-model="origin" id="origin" required>
        <option disabled value="">-- Pilih Kota Asal --</option>
        <option v-for="city in cities" :key="city.value" :value="city.value">
          {{ city.text }}
        </option>
      </select>

      <label for="destination">Kota Tujuan:</label>
      <select v-model="destination" id="destination" required>
        <option disabled value="">-- Pilih Kota Tujuan --</option>
        <option v-for="city in cities" :key="city.value" :value="city.value">
          {{ city.text }}
        </option>
      </select>

      <label for="weight">Berat Paket (gram):</label>
      <input
        type="number"
        id="weight"
        min="1"
        v-model.number="weight"
        placeholder="Masukkan berat paket dalam gram"
        required
      />

      <label>Pilih Kurir:</label>
      <div class="couriers">
        <div
          class="courier-option"
          v-for="courier in availableCouriers"
          :key="courier.value"
        >
          <input
            type="checkbox"
            :id="courier.value"
            :value="courier.value"
            v-model="selectedCouriers"
          />
          <label :for="courier.value">{{ courier.text }}</label>
        </div>
      </div>

      <button type="submit">Hitung Ongkir</button>
    </form>

    <section
      id="results"
      v-show="showResults"
      aria-live="polite"
      aria-atomic="true"
    >
      <h2>Hasil Estimasi Ongkir</h2>
      <div v-if="costs.length === 0" class="no-results">
        Maaf, tidak ada data ongkir untuk rute ini.
      </div>
      <div
        v-for="cost in costs"
        :key="cost.courier"
        class="result-item"
      >
        <span>{{ cost.courier }}</span>
        <span><strong>{{ formatRupiah(cost.price) }}</strong></span>
      </div>
    </section>

    <footer>
      &copy; 2024 Kurir RajaLike. Buatan AI. Data ongkir simulasi saja.
    </footer>
  </div>
</template>

<script setup>
import { ref } from "vue";

const cities = [
  { value: "jakarta", text: "Jakarta" },
  { value: "bandung", text: "Bandung" },
  { value: "surabaya", text: "Surabaya" },
  { value: "medan", text: "Medan" },
  { value: "makassar", text: "Makassar" },
];

const availableCouriers = [
  { value: "JNE", text: "JNE" },
  { value: "TIKI", text: "TIKI" },
  { value: "POS", text: "POS Indonesia" },
];

// Tarif simulasi per 100 gram
const rateTable = {
  jakarta: {
    bandung: { JNE: 9000, TIKI: 8500, POS: 7000 },
    surabaya: { JNE: 11000, TIKI: 10500, POS: 9000 },
    medan: { JNE: 14000, TIKI: 13500, POS: 12500 },
    makassar: { JNE: 15000, TIKI: 14500, POS: 13000 },
  },
  bandung: {
    jakarta: { JNE: 9000, TIKI: 8500, POS: 7000 },
    surabaya: { JNE: 12000, TIKI: 11500, POS: 9500 },
    medan: { JNE: 15000, TIKI: 14500, POS: 13000 },
    makassar: { JNE: 16000, TIKI: 15500, POS: 14000 },
  },
  surabaya: {
    jakarta: { JNE: 11000, TIKI: 10500, POS: 9000 },
    bandung: { JNE: 12000, TIKI: 11500, POS: 9500 },
    medan: { JNE: 17000, TIKI: 16500, POS: 15000 },
    makassar: { JNE: 18000, TIKI: 17500, POS: 16000 },
  },
  medan: {
    jakarta: { JNE: 14000, TIKI: 13500, POS: 12500 },
    bandung: { JNE: 15000, TIKI: 14500, POS: 13000 },
    surabaya: { JNE: 17000, TIKI: 16500, POS: 15000 },
    makassar: { JNE: 22000, TIKI: 21500, POS: 20000 },
  },
  makassar: {
    jakarta: { JNE: 15000, TIKI: 14500, POS: 13000 },
    bandung: { JNE: 16000, TIKI: 15500, POS: 14000 },
    surabaya: { JNE: 18000, TIKI: 17500, POS: 16000 },
    medan: { JNE: 22000, TIKI: 21500, POS: 20000 },
  },
};

const origin = ref("");
const destination = ref("");
const weight = ref(null);
const selectedCouriers = ref(["JNE", "TIKI", "POS"]);

const costs = ref([]);
const showResults = ref(false);

function formatRupiah(number) {
  return (
    "Rp " +
    number
      .toString()
      .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
  );
}

function calculateCosts(originCity, destinationCity, weightGram, couriers) {
  const result = [];
  if (originCity === destinationCity) return result;

  const rates = rateTable[originCity]?.[destinationCity];
  if (!rates) return result;

  const weight100g = Math.ceil(weightGram / 100);

  couriers.forEach((courier) => {
    const ratePer100g = rates[courier];
    if (ratePer100g) {
      let price = weight100g * ratePer100g;
      const randomFactor = 0.9 + Math.random() * 0.2; // 0.9 to 1.1
      price = Math.round((price * randomFactor) / 100) * 100;
      result.push({ courier, price });
    }
  });
  return result;
}

function onSubmit() {
  showResults.value = false;
  costs.value = [];

  if (!origin.value || !destination.value || !weight.value || weight.value < 1) {
    alert("Pastikan semua input sudah benar dan berat paket lebih dari 0");
    return;
  }
  if (origin.value === destination.value) {
    alert("Kota asal dan tujuan tidak boleh sama.");
    return;
  }
  if (selectedCouriers.value.length === 0) {
    alert("Pilih minimal satu kurir.");
    return;
  }

  costs.value = calculateCosts(
    origin.value,
    destination.value,
    weight.value,
    selectedCouriers.value
  );
  showResults.value = true;
}
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");

* {
  box-sizing: border-box;
}
.app-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #4a90e2, #50e3c2);
  font-family: "Poppins", sans-serif;
  color: #333;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 1rem;
  margin: 0;
}
header {
  text-align: center;
  margin-bottom: 2rem;
  color: white;
  text-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
}
header h1 {
  font-weight: 600;
  font-size: 2.5rem;
  margin-bottom: 0.2rem;
}
header p {
  font-size: 1.1rem;
  opacity: 0.85;
}
form {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  padding: 2rem;
  max-width: 480px;
  width: 100%;
  color: #333;
}
label {
  display: block;
  margin-bottom: 0.3rem;
  font-weight: 600;
  margin-top: 1rem;
}
select,
input[type="number"] {
  width: 100%;
  padding: 0.6rem 0.8rem;
  font-size: 1rem;
  border-radius: 6px;
  border: 1.8px solid #ccc;
  transition: border-color 0.3s ease;
}
select:focus,
input[type="number"]:focus {
  outline: none;
  border-color: #4a90e2;
}
.couriers {
  margin-top: 1rem;
}
.courier-option {
  display: flex;
  align-items: center;
  margin-bottom: 0.6rem;
}
.courier-option input {
  margin-right: 0.8rem;
  width: 18px;
  height: 18px;
}
button {
  margin-top: 2rem;
  width: 100%;
  background-color: #4a90e2;
  color: white;
  font-size: 1.2rem;
  padding: 0.8rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
button:hover {
  background-color: #3b73c3;
}
#results {
  margin-top: 2rem;
  width: 100%;
  max-width: 480px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  padding: 1.8rem 2rem;
  color: #333;
}
.result-item {
  border-bottom: 1px solid #eee;
  padding: 0.8rem 0;
  display: flex;
  justify-content: space-between;
  font-size: 1.05rem;
}
.result-item:last-child {
  border-bottom: none;
}
.no-results {
  text-align: center;
  color: #777;
  font-style: italic;
}
footer {
  margin-top: auto;
  color: white;
  font-size: 0.9rem;
  opacity: 0.6;
  padding: 1rem 0;
}
@media (max-width: 520px) {
  form,
  #results {
    max-width: 100%;
  }
}
</style>
