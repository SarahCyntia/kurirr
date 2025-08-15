<template>
  <div class="bg-gray-200 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow w-full max-w-2xl">
      <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Kalkulator Ongkos Kirim (V2)</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Provinsi -->
        <div>
          <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Provinsi Tujuan</label>
          <select
            id="province"
            v-model="selectedProvince"
            @change="fetchCities"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow"
          >
            <option value="">-- Pilih Provinsi --</option>
            <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
              {{ prov.name }}
            </option>
          </select>
        </div>

        <!-- Kota -->
        <div>
          <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Kota / Kabupaten Tujuan</label>
          <select
            id="city"
            v-model="selectedCity"
            :disabled="!cities.length"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm disabled:bg-gray-50 disabled:cursor-not-allowed"
          >
            <option value="">-- Pilih Kota / Kabupaten --</option>
            <option v-for="city in cities" :key="city.id" :value="city.id">
              {{ city.name }}
            </option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const provinces = ref<{ id: number; name: string }[]>([])
const cities = ref<{ id: number; name: string }[]>([])
const selectedProvince = ref('')
const selectedCity = ref('')

// Fetch data provinsi saat halaman dimuat
onMounted(async () => {
  try {
    const response = await axios.get('/provinces') // Ganti sesuai endpoint Laravel kamu
    provinces.value = response.data
  } catch (err) {
    console.error('Gagal memuat provinsi:', err)
  }
})

// Fetch kota berdasarkan provinsi
const fetchCities = async () => {
  if (!selectedProvince.value) {
    cities.value = []
    selectedCity.value = ''
    return
  }

  try {
    const response = await axios.get(`/cities/${selectedProvince.value}`)
    cities.value = response.data
  } catch (err) {
    console.error('Gagal memuat kota:', err)
    cities.value = []
  }
}
</script>
