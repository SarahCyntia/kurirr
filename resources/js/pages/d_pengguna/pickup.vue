<template>
    <div class="flex h-screen bg-gray-100">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-md p-4">
        <h2 class="text-2xl font-bold mb-6">KurirApp</h2>
        <nav class="space-y-4">
          <a href="#" class="block text-gray-700 hover:text-blue-600">🏠 Dashboard</a>
          <a href="#" class="block text-gray-700 hover:text-blue-600">📦 Pengiriman Saya</a>
          <a href="#" class="block text-gray-700 hover:text-blue-600">⚙️ Akun</a>
          <a href="#" class="block text-red-500 hover:text-red-700">🚪 Logout</a>
        </nav>
      </aside>
  
      <!-- Main Content -->
      <main class="flex-1 p-6 overflow-auto">
        <!-- Header -->
        <header class="mb-6 flex justify-between items-center">
          <h1 class="text-3xl font-semibold">Dashboard Kurir</h1>
          <div class="text-right">
            <p class="text-gray-600">Selamat datang,</p>
            <p class="font-bold">{{ user.name }}</p>
          </div>
        </header>
  
        <!-- Ringkasan -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div class="bg-white rounded-2xl shadow p-4">
            <p class="text-gray-500">Total Pengiriman</p>
            <p class="text-2xl font-bold">{{ summary.total }}</p>
          </div>
          <div class="bg-white rounded-2xl shadow p-4">
            <p class="text-gray-500">Dalam Perjalanan</p>
            <p class="text-2xl font-bold text-yellow-500">{{ summary.inProgress }}</p>
          </div>
          <div class="bg-white rounded-2xl shadow p-4">
            <p class="text-gray-500">Terkirim</p>
            <p class="text-2xl font-bold text-green-500">{{ summary.delivered }}</p>
          </div>
        </section>
  
        <!-- Tabel Pengiriman Terbaru -->
        <section class="bg-white rounded-2xl shadow p-4">
          <h2 class="text-xl font-semibold mb-4">Pengiriman Terbaru</h2>
          <table class="w-full text-left">
            <thead>
              <tr class="text-gray-600 border-b">
                <th class="py-2">#</th>
                <th>Tujuan</th>
                <th>Status</th>
                <th>Waktu</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in recentShipments"
                :key="index"
                class="border-b hover:bg-gray-50"
              >
                <td class="py-2">{{ index + 1 }}</td>
                <td>{{ item.destination }}</td>
                <td>
                  <span
                    :class="{
                      'text-yellow-500': item.status === 'Dalam Perjalanan',
                      'text-green-600': item.status === 'Terkirim',
                      'text-gray-500': item.status === 'Menunggu',
                    }"
                  >
                    {{ item.status }}
                  </span>
                </td>
                <td>{{ item.time }}</td>
              </tr>
            </tbody>
          </table>
        </section>
      </main>
    </div>
  </template>
  
  <script lang="ts" setup>
  interface User {
    name: string;
  }
  
  interface Shipment {
    destination: string;
    status: string;
    time: string;
  }
  
  const user: User = {
    name: "Ahmad Kurir",
  };
  
  const summary = {
    total: 32,
    inProgress: 5,
    delivered: 25,
  };
  
  const recentShipments: Shipment[] = [
    { destination: "Jakarta Selatan", status: "Terkirim", time: "24 Apr 10:00" },
    { destination: "Depok", status: "Dalam Perjalanan", time: "24 Apr 12:30" },
    { destination: "Bogor", status: "Menunggu", time: "25 Apr 08:00" },
  ];
  </script>
  
  <style scoped>
  /* Tambahan styling jika dibutuhkan */
  </style>
  