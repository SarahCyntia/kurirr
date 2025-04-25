<template>
    <div class="order-list">
      <h2>Daftar Pesanan</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Pelanggan</th>
            <th>Produk</th>
            <th>Status</th>
            <th>Aksi Kurir</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.id }}</td>
            <td>{{ order.penerima }}</td>
            <td>{{ order.produk }}</td>
            <td>{{ order.status }}</td>
            <td>
              <select v-model="order.status" @change="handleStatusChange(order)">
                <option>Packing</option>
                <option>Dikirim</option>
                <option>Selesai</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
  
      <h2>Ringkasan Pengguna</h2>
      <ul>
        <li v-for="(user, name) in userSummary" :key="name">
          {{ name }} - Total Pesanan: {{ user.total }} - Total Harga: Rp {{ user.totalHarga.toLocaleString() }}
        </li>
      </ul>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  
  const orders = ref([])
  
  const generateDummyOrders = () => {
    const names = ['Rina', 'Andi', 'Budi']
    const products = ['Sembako', 'Blender', 'Laptop']
    const defaultStatuses = ['Packing', 'Dikirim', 'Selesai']
  
    for (let i = 1; i <= 5; i++) {
      orders.value.push({
        id: `ORD${String(i).padStart(3, '0')}`,
        penerima: names[i % names.length],
        produk: products[i % products.length],
        status: defaultStatuses[0],
        totalHarga: Math.floor(Math.random() * 500000 + 100000),
      })
    }
  }
  
  // Otomatis hitung jika pesanan sudah selesai/dikirim
  const userSummary = computed(() => {
    const summary = {}
    for (const order of orders.value) {
      if (order.status === 'Dikirim' || order.status === 'Selesai') {
        if (!summary[order.penerima]) {
          summary[order.penerima] = {
            total: 0,
            totalHarga: 0,
          }
        }
        summary[order.penerima].total += 1
        summary[order.penerima].totalHarga += order.totalHarga
      }
    }
    return summary
  })
  
  // Simulasi aksi kurir ubah status
  const handleStatusChange = (order) => {
    // Simulasi sistem otomatis
    console.log(`Kurir mengubah status pesanan ${order.id} menjadi ${order.status}`)
  }
    
  onMounted(() => {
    generateDummyOrders()
  })
  </script>
  
  <style scoped>
  .order-list {
    padding: 20px;
    font-family: sans-serif;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }
  th, td {
    border: 1px solid #ccc;
    padding: 8px 10px;
  }
  th {
    background-color: #f4f4f4;
  }
  select {
    padding: 4px 6px;
  }
  </style>
  