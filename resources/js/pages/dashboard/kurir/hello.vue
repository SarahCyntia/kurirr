
<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Manajemen Kurir</h1>
    
        <!-- Form Tambah Kurir -->
        <div class="mb-6 p-4 bg-gray-200 rounded-lg">
          <a href="/kurir/kelola" type="button" class="btn btn-primary mb-5 "> 
            <i class="fa fa-plus"></i> 
            Tambah Kurir
          </a>
          <div class="table-responsive">
            <table class="table align-middle border border-gray-300 w-full">
              <thead class="bg-gray-300">
                <tr>
                  <th class="border border-gray-300 p-2">No.</th>
                  <th class="border border-gray-300 p-2">Nama Kurir</th>
                  <th class="border border-gray-300 p-2">Jenis Kelamin</th>
                  <th class="border border-gray-300 p-2">Alamat</th>
                  <th class="border border-gray-300 p-2">Nomor HP</th>
                  <th class="border border-gray-300 p-2">Foto</th>
                  <th class="border border-gray-300 p-2">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border border-gray-300">
                  <td class="border border-gray-300 p-2">1.</td>
                  <td class="border border-gray-300 p-2">Joko</td>
                  <td class="border border-gray-300 p-2">Laki laki</td>
                  <td class="border border-gray-300 p-2">Jl. Bringin Jaya</td>
                  <td class="border border-gray-300 p-2">081111111111</td>
                  <td class="border border-gray-300 p-2">
                    <img src="/img/vecteezy_flower-and-mountain-landscape-background_11457898.jpg"  class="w-16 h-16 rounded object-cover"  id="gmb">
                    <!-- <img :src="new URL('/img/vecteezy_flower-and-mountain-landscape-background_11457898.jpg', import.meta.url).href" class="w-16 h-16 rounded object-cover"> -->
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                    <a href="/resources/js/pages/dashboard/kurir/kelola/kelola.vue" class="btn btn-sm btn-icon btn-info"> 
                      <i class="la la-pencil fs-2"></i>
                    </a> <!-- info : ungu-->
                    <button class="btn btn-sm btn-icon btn-danger">
                      <i class="la la-trash fs-2"></i>
                    </button>
                  </div>
                  </td>
                </tr>
                <tr class="border border-gray-300">
                  <td class="border border-gray-300 p-2">2.</td>
                  <td class="border border-gray-300 p-2">Susanti</td>
                  <td class="border border-gray-300 p-2">Perempuan</td>
                  <td class="border border-gray-300 p-2">Jl. Sawo</td>
                  <td class="border border-gray-300 p-2">081111111112</td>
                  <td class="border border-gray-300 p-2">
                    <img src="/img/vecteezy_sunset-lake-landscape-illustration_.jpg"  class="w-16 h-16 rounded object-cover"  id="gmb">
                    <!-- <img :src="new URL('/img/vecteezy_flower-and-mountain-landscape-background_11457898.jpg', import.meta.url).href" class="w-16 h-16 rounded object-cover"> -->
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-icon btn-info"> 
                      <i class="la la-pencil fs-2"></i>
                    </button> <!-- info : ungu-->
                    <button class="btn btn-sm btn-icon btn-danger">
                      <i class="la la-trash fs-2"></i>
                    </button>
                  </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
          <!-- <input v-model="newCourier.name" type="text" placeholder="Nama Kurir" class="p-2 border rounded w-full mb-2">
            <input v-model="newCourier.phone" type="text" placeholder="Nomor HP" class="p-2 border rounded w-full mb-2">
            <button @click="addCourier" class="bg-blue-500 text-white px-4 py-2 rounded">Tambah</button> -->
        </div>
    
    
        <!-- Daftar Kurir -->
        <!-- <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
          <thead>
            <tr class="bg-blue-500 text-white">
              <th class="p-2">Nama</th>
              <th class="p-2">Nomor HP</th>
              <th class="p-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(courier, index) in couriers" :key="index" class="border-t">
              <td class="p-2">{{ courier.name }}</td>
              <td class="p-2">{{ courier.phone }}</td>
              <td class="p-2">
                <button @click="editCourier(index)" class="bg-yellow-500 text-white px-2 py-1 rounded mr-2">Edit</button>
                <button @click="deleteCourier(index)" class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table> -->
      <!-- </div> -->
    </template>
    
    <script>
    export default {
      data() {
        return {
          couriers: [],
          newCourier: { name: '', phone: '' },
        };
      },
      methods: {
        addCourier() {
          if (this.newCourier.name && this.newCourier.phone) {
            this.couriers.push({ ...this.newCourier });
            this.newCourier = { name: '', phone: '' };
          }
        },
        editCourier(index) {
          const updatedName = prompt('Edit Nama Kurir', this.couriers[index].name);
          const updatedPhone = prompt('Edit Nomor HP', this.couriers[index].phone);
          if (updatedName && updatedPhone) {
            this.couriers[index].name = updatedName;
            this.couriers[index].phone = updatedPhone;
          }
        },
        deleteCourier(index) {
          if (confirm('Apakah Anda yakin ingin menghapus kurir ini?')) {
            this.couriers.splice(index, 1);
          }
        },
      },
    };
    </script>
    
    <style>
    body {
      font-family: Arial, sans-serif;
    }
    
    #gmb {
      width: 100px;
      height: 75px;
      object-fit: cover;
      border-radius: 5px;
    }
    </style>
    
    