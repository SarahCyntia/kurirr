<script setup lang="ts">

</script>

<template>

  halo
  <main></main>
</template> 

<!-- <template>
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-2">Cek Resi</h1>
        <p class="mb-4">Cek resi untuk lacak paket anda di sini</p>

        <div class="grid md:grid-cols-2 gap-6">
            Left Column - Form
            <div>
                <div class="mb-4">
                    <label class="block text-gray-600 mb-2">Resi</label> <br />
                    <input
                        v-model="trackingNumber"
                        type="text"
                        placeholder="000123456"
                        class="w-full border rounded p-2"
                    />
                </div>

                <div class="flex space-x-2">
                    <button
                        @click="trackPackage"
                        class="bg-blue-500 text-white px-4 py-2 rounded me-2"
                    >
                        Cek Resi
                    </button>
                    <button
                        @click="resetForm"
                        class="bg-gray-200 text-black px-4 py-2 rounded"
                    >
                        Batal
                    </button>
                </div>
            </div>
            Right Column - Tracking Results
            <div v-if="showTrackingResult">
                <div class="mb-4">
                    <div class="mb-1 text-gray-600">Resi</div>
                    <div class="font-medium">{{ trackingData.trackingId }}</div>
                </div>

                <div class="mb-4">
                    <div class="mb-1 text-gray-600">Courier</div>
                    <div class="font-medium">{{ trackingData.courier }}</div>
                </div>

                <div class="mb-4">
                    <div class="mb-1 text-gray-600">Pelayanan</div>
                    <div class="font-medium">{{ trackingData.service }}</div>
                </div>

                <div class="mb-4">
                    <div class="mb-1 text-gray-600">Waktu Pengiriman</div>
                    <div class="font-medium">{{ trackingData.shipDate }}</div>
                </div>

                <div class="mb-4">
                    <div class="mb-1 text-gray-600">Pengirim</div>
                    <div class="font-medium">{{ trackingData.sender }}</div>
                </div>

                <div class="mb-4">
                    <div class="mb-1 text-gray-600">Tujuan</div>
                    <div class="font-medium">{{ trackingData.recipient }}</div>
                </div>

                Timeline
                <div class="border rounded mt-6">
                    <div
                        v-for="(status, index) in trackingData.timeline"
                        :key="index"
                        :class="[
                            'p-3 flex border-b',
                            index === 0 ? 'bg-blue-50' : '',
                        ]"
                    >
                        <div class="w-1/3 text-gray-600">{{ status.date }}</div>
                        <div class="w-2/3 font-medium">
                            {{ status.description }}
                        </div>
                    </div>
                </div>
                <table class="table-auto w-full border mt-4">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2 text-left">Tanggal</th>
                            <th class="border px-4 py-2 text-left">
                                Deskripsi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in trackingData.timeline"
                            :key="item.date"
                        >
                            <td class="border px-4 py-2">{{ item.date }}</td>
                            <td class="border px-4 py-2">
                                {{ item.description }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template> -->

<!-- <script>
export default {
    data() {
        return {
            selectedCourier: "J&T",
            trackingNumber: "",
            showTrackingResult: false,
            trackingData: {
                trackingId: "BERDU_CONTOH",
                courier: "JNE",
                service: "Berdu",
                shipDate: "15 Apr, 8:14 am",
                sender: "Berdu - Tangerang, Jawa Barat",
                recipient: "Gudang - Medan, Sumatera Utara",
                timeline: [
                    {
                        date: "8 Apr, 5:59 am",
                        description: "Paket telah diterima oleh Basuki",
                    },
                    {
                        date: "7 Apr, 2:20 pm",
                        description: "Sampai ke kota tujuan",
                    },
                    {
                        date: "5 Apr, 7:11 pm",
                        description: "Paket dikirim dari pusat ke tujuan",
                    },
                    {
                        date: "5 Apr, 11:33 am",
                        description: "Pengecekan pengiriman",
                    },
                    {
                        date: "4 Apr, 3:12 pm",
                        description: "Paket diterima di pusat",
                    },
                ],
            },
        };
    },
    methods: {
        trackPackage() {
            // In a real application, you would fetch the tracking data from an API
            // For this example, we're just showing the dummy data
            this.showTrackingResult = true;
        },
        resetForm() {
            this.trackingNumber = "";
            this.showTrackingResult = false;
        },
    },
};
</script> -->

<style scoped>
/* Additional styles if needed */
</style>

<!-- <template>
  <div>
    <h2 class="text-xl font-semibold mb-4">Halo, {{ role.name }}</h2>
    <div class="bg-gray-100 p-9 rounded shadow">
      <p>Riwayat pengirimanmu akan muncul di sini.</p>
    </div>
  </div>
</template>

<script setup>
const user = {
  name: 'tia',
  role: 'pengguna',
}
</script> -->
