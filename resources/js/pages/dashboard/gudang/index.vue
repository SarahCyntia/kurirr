<script setup lang="ts">
import { onMounted, ref, watch, computed } from "vue";
import { useDelete } from "@/libs/hooks";
// import Form from "./Form.vue";
import { createColumnHelper, type Row } from "@tanstack/vue-table";
import { h } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

// ======================== INTERFACES ========================
interface Barang {
  id: number;
  kode_barang: string;
  nama_barang: string;
  kategori: string;
  supplier: string;
  stok_minimal: number;
  stok_maksimal: number;
  stok_tersedia: number;
  stok_reserved: number;
  harga_beli: number;
  harga_jual: number;
  satuan: string;
  lokasi_rak: string;
  zona_gudang: string;
  tanggal_masuk: string;
  tanggal_kadaluarsa?: string;
  batch_number?: string;
  kondisi: 'Baik' | 'Rusak' | 'Kadaluarsa';
  status: 'Aktif' | 'Nonaktif' | 'Discontinue';
  created_at: string;
  updated_at: string;
}

interface MovementHistory {
  id: number;
  barang_id: number;
  tipe_pergerakan: 'Masuk' | 'Keluar' | 'Transfer' | 'Adjustment' | 'Return';
  jumlah: number;
  lokasi_asal?: string;
  lokasi_tujuan?: string;
  keterangan: string;
  user_id: number;
  user_name: string;
  referensi_dokumen?: string;
  created_at: string;
}

interface StockOpname {
  id: number;
  barang_id: number;
  stok_sistem: number;
  stok_fisik: number;
  selisih: number;
  keterangan: string;
  status: 'Draft' | 'Confirmed' | 'Approved';
  created_at: string;
}

interface Supplier {
  id: number;
  nama: string;
  alamat: string;
  telepon: string;
  email: string;
  rating: number;
}

interface Kategori {
  id: number;
  nama: string;
  deskripsi: string;
}

interface User {
  id: number;
  nama: string;
  role: 'Admin' | 'Warehouse Staff' | 'Supervisor';
}

// ======================== REACTIVE DATA ========================
const barangData = ref<Barang | null>(null);
const movementHistory = ref<MovementHistory[]>([]);
const stockOpnameData = ref<StockOpname[]>([]);
const supplierList = ref<Supplier[]>([]);
const kategoriList = ref<Kategori[]>([]);
const userList = ref<User[]>([]);

// Filter dan search
const searchTerm = ref<string>("");
const selectedKategori = ref<string>("");
const selectedZona = ref<string>("");
const selectedStatus = ref<string>("");
const selectedKondisi = ref<string>("");
const showLowStock = ref<boolean>(false);
const showExpiringSoon = ref<boolean>(false);

// Form states
const openForm = ref<boolean>(false);
const openMovementForm = ref<boolean>(false);
const openStockOpnameForm = ref<boolean>(false);
const openReportModal = ref<boolean>(false);
const selected = ref<string>("");

// ======================== COMPUTED ========================
const lowStockItems = computed(() => {
  // Logic untuk menghitung barang dengan stok rendah
  return barangData.value ? [] : [];
});

const expiringItems = computed(() => {
  // Logic untuk menghitung barang yang akan kadaluarsa
  return barangData.value ? [] : [];
});

const totalValue = computed(() => {
  // Logic untuk menghitung total nilai inventory
  return 0;
});

// ======================== UTILITY FUNCTIONS ========================
function formatDate(waktu: string | null | undefined): string {
  if (!waktu) return "-";
  const date = new Date(waktu);
  if (isNaN(date.getTime())) return "-";
  return date.toLocaleString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
    timeZone: "Asia/Jakarta"
  });
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR"
  }).format(amount);
}

function getStockStatusColor(stok: number, minimal: number, maksimal: number): string {
  if (stok <= minimal) return "bg-danger";
  if (stok >= maksimal) return "bg-warning";
  return "bg-success";
}

function getKondisiColor(kondisi: string): string {
  switch (kondisi) {
    case 'Baik': return "bg-success";
    case 'Rusak': return "bg-danger";
    case 'Kadaluarsa': return "bg-warning";
    default: return "bg-secondary";
  }
}

// ======================== ACTIONS ========================
const showMovementHistory = async (barang: Barang) => {
  try {
    const response = await axios.get(`/barang/${barang.id}/movement-history`);
    movementHistory.value = response.data;
    
    const htmlContent = movementHistory.value
      .map((movement, index) => `
        <div class="movement-item mb-3 p-3 border rounded">
          <div class="row">
            <div class="col-md-3">
              <strong>${movement.tipe_pergerakan}</strong>
              <br><small class="text-muted">${formatDate(movement.created_at)}</small>
            </div>
            <div class="col-md-2">
              <span class="badge ${movement.tipe_pergerakan === 'Masuk' ? 'bg-success' : 'bg-danger'}">
                ${movement.tipe_pergerakan === 'Masuk' ? '+' : '-'}${movement.jumlah}
              </span>
            </div>
            <div class="col-md-4">
              ${movement.lokasi_asal ? `${movement.lokasi_asal} → ` : ''}
              ${movement.lokasi_tujuan || ''}
            </div>
            <div class="col-md-3">
              <small>${movement.keterangan}</small>
              <br><small class="text-muted">oleh: ${movement.user_name}</small>
            </div>
          </div>
        </div>
      `)
      .join('');

    Swal.fire({
      title: `Riwayat Pergerakan - ${barang.nama_barang}`,
      html: `<div style="text-align: left; max-height: 400px; overflow-y: auto;">${htmlContent}</div>`,
      confirmButtonText: "Tutup",
      width: 800,
    });
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Gagal memuat riwayat pergerakan",
    });
  }
};

const moveStock = async (barang: Barang) => {
  const { value: formValues } = await Swal.fire({
    title: `Pindah Stok - ${barang.nama_barang}`,
    html: `
      <div class="mb-3">
        <label class="form-label">Tipe Pergerakan</label>
        <select id="tipe" class="form-select">
          <option value="Masuk">Barang Masuk</option>
          <option value="Keluar">Barang Keluar</option>
          <option value="Transfer">Transfer Lokasi</option>
          <option value="Adjustment">Penyesuaian Stok</option>
          <option value="Return">Return</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Jumlah</label>
        <input id="jumlah" type="number" class="form-control" min="1" max="${barang.stok_tersedia}">
      </div>
      <div class="mb-3">
        <label class="form-label">Lokasi Asal</label>
        <input id="lokasi_asal" type="text" class="form-control" value="${barang.lokasi_rak}">
      </div>
      <div class="mb-3">
        <label class="form-label">Lokasi Tujuan</label>
        <input id="lokasi_tujuan" type="text" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea id="keterangan" class="form-control" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">No. Referensi Dokumen</label>
        <input id="referensi" type="text" class="form-control">
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    preConfirm: () => {
      const tipe = (document.getElementById('tipe') as HTMLSelectElement).value;
      const jumlah = (document.getElementById('jumlah') as HTMLInputElement).value;
      const lokasi_asal = (document.getElementById('lokasi_asal') as HTMLInputElement).value;
      const lokasi_tujuan = (document.getElementById('lokasi_tujuan') as HTMLInputElement).value;
      const keterangan = (document.getElementById('keterangan') as HTMLTextAreaElement).value;
      const referensi = (document.getElementById('referensi') as HTMLInputElement).value;

      if (!jumlah || !keterangan) {
        Swal.showValidationMessage('Harap isi semua field yang diperlukan');
        return null;
      }

      return {
        tipe,
        jumlah: parseInt(jumlah),
        lokasi_asal,
        lokasi_tujuan,
        keterangan,
        referensi
      };
    }
  });

  if (formValues) {
    try {
      await axios.post(`/barang/${barang.id}/movement`, formValues);
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Pergerakan stok berhasil dicatat",
        timer: 1500,
        showConfirmButton: false,
      });
      refresh();
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Gagal mencatat pergerakan stok",
      });
    }
  }
};

const stockOpname = async (barang: Barang) => {
  const { value: formValues } = await Swal.fire({
    title: `Stock Opname - ${barang.nama_barang}`,
    html: `
      <div class="mb-3">
        <label class="form-label">Stok Sistem Saat Ini</label>
        <input type="number" class="form-control" value="${barang.stok_tersedia}" readonly>
      </div>
      <div class="mb-3">
        <label class="form-label">Stok Fisik (Hasil Hitung)</label>
        <input id="stok_fisik" type="number" class="form-control" min="0">
      </div>
      <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea id="keterangan_opname" class="form-control" rows="3" placeholder="Jelaskan hasil stock opname..."></textarea>
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    preConfirm: () => {
      const stok_fisik = (document.getElementById('stok_fisik') as HTMLInputElement).value;
      const keterangan = (document.getElementById('keterangan_opname') as HTMLTextAreaElement).value;

      if (!stok_fisik || !keterangan) {
        Swal.showValidationMessage('Harap isi semua field');
        return null;
      }

      const selisih = parseInt(stok_fisik) - barang.stok_tersedia;

      return {
        stok_sistem: barang.stok_tersedia,
        stok_fisik: parseInt(stok_fisik),
        selisih,
        keterangan
      };
    }
  });

  if (formValues) {
    try {
      await axios.post(`/barang/${barang.id}/stock-opname`, formValues);
      Swal.fire({
        icon: "success",
        title: "Stock Opname Berhasil",
        text: `Selisih: ${formValues.selisih >= 0 ? '+' : ''}${formValues.selisih}`,
        timer: 2000,
        showConfirmButton: false,
      });
      refresh();
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Gagal menyimpan stock opname",
      });
    }
  }
};

const generateReport = async () => {
  const { value: reportType } = await Swal.fire({
    title: "Pilih Jenis Laporan",
    input: "select",
    inputOptions: {
      "stock_level": "Laporan Level Stok",
      "movement": "Laporan Pergerakan Barang",
      "low_stock": "Laporan Stok Rendah",
      "expiring": "Laporan Barang Kadaluarsa",
      "abc_analysis": "Analisis ABC",
      "supplier_performance": "Performa Supplier"
    },
    inputPlaceholder: "Pilih jenis laporan",
    showCancelButton: true,
    confirmButtonText: "Generate",
    cancelButtonText: "Batal"
  });

  if (reportType) {
    try {
      const response = await axios.get(`/reports/${reportType}`);
      // Logic untuk menampilkan atau download laporan
      window.open(`/reports/${reportType}/download`, '_blank');
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Gagal generate laporan",
      });
    }
  }
};

// ======================== TABLE COLUMNS ========================
const column = createColumnHelper<Barang>();
const paginateRef = ref<any>(null);

const columns = [
  column.accessor("kode_barang", { header: "Kode Barang" }),
  column.accessor("nama_barang", { header: "Nama Barang" }),
  column.accessor("kategori", { header: "Kategori" }),
  column.accessor("supplier", { header: "Supplier" }),
  
  column.display({
    id: "stok_info",
    header: "Informasi Stok",
    cell: ({ row }) => {
      const barang = row.original;
      const statusColor = getStockStatusColor(barang.stok_tersedia, barang.stok_minimal, barang.stok_maksimal);
      
      return h("div", { class: "text-center" }, [
        h("div", { class: `badge ${statusColor} mb-1` }, `${barang.stok_tersedia} ${barang.satuan}`),
        h("div", { class: "small text-muted" }, `Min: ${barang.stok_minimal} | Max: ${barang.stok_maksimal}`),
        barang.stok_reserved > 0 ? h("div", { class: "small text-warning" }, `Reserved: ${barang.stok_reserved}`) : null
      ]);
    }
  }),

  column.display({
    id: "lokasi_info",
    header: "Lokasi",
    cell: ({ row }) => {
      const barang = row.original;
      return h("div", { class: "text-center" }, [
        h("div", { class: "fw-bold" }, barang.lokasi_rak),
        h("div", { class: "small text-muted" }, barang.zona_gudang)
      ]);
    }
  }),

  column.display({
    id: "harga_info",
    header: "Harga",
    cell: ({ row }) => {
      const barang = row.original;
      return h("div", { class: "text-end" }, [
        h("div", { class: "small" }, `Beli: ${formatCurrency(barang.harga_beli)}`),
        h("div", { class: "small" }, `Jual: ${formatCurrency(barang.harga_jual)}`)
      ]);
    }
  }),

  column.accessor("kondisi", {
    header: "Kondisi",
    cell: ({ row }) => {
      const kondisi = row.original.kondisi;
      const colorClass = getKondisiColor(kondisi);
      return h("span", { class: `badge ${colorClass}` }, kondisi);
    }
  }),

  column.display({
    id: "expiry_info",
    header: "Kadaluarsa",
    cell: ({ row }) => {
      const barang = row.original;
      if (!barang.tanggal_kadaluarsa) return h("span", { class: "text-muted" }, "-");
      
      const expiry = new Date(barang.tanggal_kadaluarsa);
      const today = new Date();
      const diffTime = expiry.getTime() - today.getTime();
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      let colorClass = "text-success";
      if (diffDays <= 7) colorClass = "text-danger";
      else if (diffDays <= 30) colorClass = "text-warning";
      
      return h("div", { class: colorClass }, [
        h("div", formatDate(barang.tanggal_kadaluarsa)),
        h("div", { class: "small" }, `${diffDays} hari`)
      ]);
    }
  }),

  column.display({
    id: "actions",
    header: "Aksi",
    cell: ({ row }) => {
      const barang = row.original;
      
      return h("div", { class: "btn-group-vertical gap-1" }, [
        h("button", {
          class: "btn btn-sm btn-primary",
          onClick: () => showMovementHistory(barang)
        }, "Riwayat"),
        
        h("button", {
          class: "btn btn-sm btn-warning",
          onClick: () => moveStock(barang)
        }, "Pindah Stok"),
        
        h("button", {
          class: "btn btn-sm btn-info",
          onClick: () => stockOpname(barang)
        }, "Stock Opname"),
        
        h("button", {
          class: "btn btn-sm btn-success",
          onClick: () => {
            selected.value = barang.id.toString();
            openForm.value = true;
          }
        }, "Edit")
      ]);
    }
  })
];

// ======================== LIFECYCLE ========================
onMounted(async () => {
  try {
    // Load master data
    const [supplierRes, kategoriRes, userRes] = await Promise.all([
      axios.get('/api/suppliers'),
      axios.get('/api/kategoris'),
      axios.get('/api/users')
    ]);
    
    supplierList.value = supplierRes.data;
    kategoriList.value = kategoriRes.data;
    userList.value = userRes.data;
  } catch (error) {
    console.error('Failed to load master data:', error);
  }
});

// Delete handler
const { delete: deleteBarang } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

// Refresh function
const refresh = () => paginateRef.value?.refetch();

// Watch for form close
watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo({ top: 0, behavior: "smooth" });
});
</script>

<template>
  <!-- Dashboard Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title">Total Barang</h6>
              <h3>1,234</h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-boxes fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title">Nilai Inventory</h6>
              <h3>{{ formatCurrency(totalValue) }}</h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-dollar-sign fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-3">
      <div class="card bg-warning text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title">Stok Rendah</h6>
              <h3>{{ lowStockItems.length }}</h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-3">
      <div class="card bg-danger text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h6 class="card-title">Akan Kadaluarsa</h6>
              <h3>{{ expiringItems.length }}</h3>
            </div>
            <div class="align-self-center">
              <i class="fas fa-calendar-times fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Modals -->
  <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />

  <!-- Main Content -->
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0">
          <i class="fas fa-warehouse me-2"></i>
          Manajemen Gudang
        </h2>
        <div class="btn-group">
          <button class="btn btn-success" @click="openForm = true">
            <i class="fas fa-plus me-1"></i>
            Tambah Barang
          </button>
          <button class="btn btn-info" @click="generateReport">
            <i class="fas fa-chart-bar me-1"></i>
            Laporan
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card-body border-bottom">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Pencarian</label>
          <input 
            v-model="searchTerm" 
            type="text" 
            class="form-control" 
            placeholder="Cari barang..."
          >
        </div>
        
        <div class="col-md-2">
          <label class="form-label">Kategori</label>
          <select v-model="selectedKategori" class="form-select">
            <option value="">Semua Kategori</option>
            <option v-for="kategori in kategoriList" :key="kategori.id" :value="kategori.nama">
              {{ kategori.nama }}
            </option>
          </select>
        </div>
        
        <div class="col-md-2">
          <label class="form-label">Zona Gudang</label>
          <select v-model="selectedZona" class="form-select">
            <option value="">Semua Zona</option>
            <option value="A">Zona A</option>
            <option value="B">Zona B</option>
            <option value="C">Zona C</option>
          </select>
        </div>
        
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select v-model="selectedStatus" class="form-select">
            <option value="">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
            <option value="Discontinue">Discontinue</option>
          </select>
        </div>
        
        <div class="col-md-3">
          <label class="form-label">Filter Khusus</label>
          <div class="d-flex gap-2">
            <div class="form-check">
              <input v-model="showLowStock" class="form-check-input" type="checkbox" id="lowStock">
              <label class="form-check-label" for="lowStock">Stok Rendah</label>
            </div>
            <div class="form-check">
              <input v-model="showExpiringSoon" class="form-check-input" type="checkbox" id="expiringSoon">
              <label class="form-check-label" for="expiringSoon">Akan Kadaluarsa</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card-body">
      <paginate 
        ref="paginateRef" 
        id="table-warehouse" 
        url="/api/barang" 
        :columns="columns"
        :search="searchTerm"
        :filters="{
          kategori: selectedKategori,
          zona: selectedZona,
          status: selectedStatus,
          kondisi: selectedKondisi,
          low_stock: showLowStock,
          expiring_soon: showExpiringSoon
        }"
      />
    </div>
  </div>

  <!-- Quick Actions Panel -->
  <div class="position-fixed bottom-0 end-0 p-3">
    <div class="card shadow">
      <div class="card-body">
        <h6 class="card-title">Quick Actions</h6>
        <div class="d-grid gap-2">
          <button class="btn btn-sm btn-primary" @click="openMovementForm = true">
            <i class="fas fa-exchange-alt me-1"></i>
            Pergerakan Stok
          </button>
          <button class="btn btn-sm btn-warning" @click="openStockOpnameForm = true">
            <i class="fas fa-clipboard-check me-1"></i>
            Stock Opname
          </button>
          <button class="btn btn-sm btn-info" @click="generateReport">
            <i class="fas fa-download me-1"></i>
            Export Data
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.btn-group-vertical .btn {
  margin-bottom: 2px;
}

.movement-item {
  background-color: #f8f9fa;
}

.card-body {
  position: relative;
}

.position-fixed {
  z-index: 1000;
}

.badge {
  font-size: 0.8em;
  padding: 0.5em 0.75em;
}

.small {
  font-size: 0.75em;
}

.fw-bold {
  font-weight: 600;
}

.text-end {
  text-align: right;
}

.text-center {
  text-align: center;
}

.gap-1 > * {
  margin-bottom: 0.25rem;
}

.gap-2 > * {
  margin-right: 0.5rem;
}

.gap-3 > * {
  margin-right: 0.75rem;
}

.g-3 > * {
  padding: 0.75rem;
}

.d-grid {
  display: grid;
}

.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

.align-self-center {
  align-self: center;
}

.me-1 {
  margin-right: 0.25rem;
}

.me-2 {
  margin-right: 0.5rem;
}

.mb-0 {
  margin-bottom: 0;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

.mb-3 {
  margin-bottom: 1rem;
}

.mb-4 {
  margin-bottom: 1.5rem;
}

.p-3 {
  padding: 1rem;
}

.border-bottom {
  border-bottom: 1px solid #dee2e6;
}

.shadow {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>