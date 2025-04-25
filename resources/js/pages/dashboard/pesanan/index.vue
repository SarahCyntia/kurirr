<template>
  <div class="order-app">
    <h2>Daftar Pesanan</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Kurir</th>
          <th>Nama Pelanggan</th>
          <th>Nama Toko</th>
          <th>Produk</th>
          <th>Total Harga</th>
          <th>Pengirim</th>
          <th>Penerima</th>
          <th>Alamat</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td>{{ order.id }}</td>
          <td>{{ order.kurirId }}</td>
          <td>{{ order.namaPelanggan }}</td>
          <td>{{ order.namaToko }}</td>
          <td>{{ order.produk }}</td>
          <td>Rp {{ order.totalHarga.toLocaleString() }}</td>
          <td>{{ order.pengirim }}</td>
          <td>{{ order.penerima }}</td>
          <td>{{ order.alamat }}</td>
          <td>{{ order.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const orders = ref([])
let orderCounter = 1

const form = ref({
  namaPelanggan: '',
  namaToko: '',
  produk: '',
  totalHarga: '',
  alamat: '',
})

// Simulasi pelanggan kirim pesanan
const submitOrder = () => {
  orders.value.push({
    id: `ORD${String(orderCounter++).padStart(3, '0')}`,
    namaPelanggan: form.value.namaPelanggan,
    namaToko: form.value.namaToko,
    produk: form.value.produk,
    totalHarga: Number(form.value.totalHarga),
    alamat: form.value.alamat,
    status: 'Dalam Proses', // default awal
  })

  // Reset form
  form.value = {
    namaPelanggan: '',
    namaToko: '',
    produk: '',
    totalHarga: '',
    alamat: '',
  }
}
</script>

<style scoped>
.order-app {
  padding: 20px;
  font-family: Arial;
}
form {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
  max-width: 400px;
}
input {
  padding: 6px;
}
button {
  padding: 8px;
  background: #3498db;
  color: white;
  border: none;
  cursor: pointer;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  border: 1px solid #ccc;
  padding: 8px;
}
</style>
