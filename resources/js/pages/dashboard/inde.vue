<template>
    halo
</template>

<!-- <template>
  <div class="dashboard-container">
    <h1 class="dashboard-title">📦 Laporan Kurir</h1>

    Filter tanggal
    <div class="filter-section">
      <label>
        Dari:
        <input type="date" v-model="filter.startDate" />
      </label>
      <button @click="filterData" class="btn-primary">Cari</button>
    </div>

    Ringkasan
    <div class="summary-box">
      <p><strong>Total Paket Diterima:</strong> {{ filteredReports.length }}</p>
    </div>

    Tabel laporan
    <table class="report-table">
      <thead>
        <tr>
          <th>Kurir</th>
          <th>Tanggal Pengambilan</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in filteredReports" :key="item.resi">
          <td>{{ item.kurir }}</td>
          <td>{{ item.tanggal_pengambilan }}</td>
          <td>{{ item.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'

import { onMounted } from 'vue'
import axios from 'axios'

interface Ordered {
  kurir: string
  tanggal_pengambilan: string
  status: string
}

const report = ref<Ordered[]>([])

onMounted(async () => {
  try {
    const response = await axios.get('/api/laporan-kurir', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    report.value = response.data
  } catch (error) {
    console.error('Gagal mengambil data laporan kurir:', error)
  }
})


const userLogin = ref({
  id: 3,
  name: 'Andi',
  role: 'kurir'
})

interface Ordered {
  resi: string
  kurir: string
  tanggal_pengambilan: string
  status: string
}

const reports = ref<Ordered[]>([
 
])

const filter = ref({
  startDate: '',
  endDate: '',
})

const filteredReports = computed(() => {
  const start = filter.value.startDate
  const end = filter.value.endDate
  return reports.value.filter((item) => {
    if (item.kurir !== userLogin.value.name) return false
    const date = item.tanggal_pengambilan
    if (start && date < start) return false
    if (end && date > end) return false
    return true
  })
})

const filterData = () => {
}
</script>

<style scoped>
.dashboard-container {
  padding: 2rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f9fafe;
  border-radius: 8px;
  max-width: 900px;
  margin: auto;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

.dashboard-title {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.filter-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.filter-section label {
  display: flex;
  flex-direction: column;
  font-size: 14px;
  color: #444;
}

input[type="date"] {
  padding: 6px 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

.btn-primary:hover {
  background-color: #1e40af;
}

.summary-box {
  margin-top: 1rem;
  background-color: #e2e8f0;
  padding: 12px 16px;
  border-radius: 8px;
  font-weight: 500;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1.5rem;
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.report-table th,
.report-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
}

.report-table thead {
  background-color: #f1f5f9;
  color: #111827;
}

.report-table tr:hover {
  background-color: #f9fafb;
}
</style> -->


<!-- <template>
<main>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Dashboard</h1>
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">Primary Card</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="#">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">Warning Card</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="#">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">Success Card</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="#">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">Danger Card</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="#">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</template> -->