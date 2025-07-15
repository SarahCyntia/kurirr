<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper, type Row } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
import Swal from "sweetalert2";
import axios, { HttpStatusCode } from "axios";
import type { Kurir } from "@/stores/auth";
import { AUTO_ALIGNMENT } from "element-plus/es/components/virtual-list/src/defaults";
// import { Row } from "@tanstack/vue-table";

interface Riwayat {
  id: number
  deskripsi: string
  created_at: string
}

// Interface untuk data yang sudah ditampilkan
interface RiwayatTampil {
  id: number
  riwayat: string
}
const inputData = ref<Input | null>(null);
// Format tanggal ID
function formatDate(waktu: string | null | undefined): string {
  if (!waktu) return "-"; // atau bisa "Tanggal tidak tersedia"

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

const statusSteps = ["menunggu", "dalam proses", "masuk gudang", "Keluar gudang", "diproses", "dikirim", "selesai"] as const;
const statusLabels: Record<string, string> = {
  "menunggu": "Menunggu",
  "dalam proses": "Dalam Proses",
  "masuk gudang": "Masuk Gudang ",
  "keluar gudang": "Keluar Gudang",
  "diproses": "Diproses",
  "dikirim": "Dikirim",
  "selesai": "Selesai"
};
const statusIcons: Record<string, "info" | "question" | "dark" | "secondary" | "muted" | "warning" | "success"> = {
  "menunggu": "info",
  "dalam proses": "question",
  "masuk gudang": "dark",
  "keluar gudang": "secondary",
  "diproses": "muted",
  "dikirim": "warning",
  "selesai": "success"
};
const statusColors: Record<string, string> = {
  "menunggu": "bg-info",
  "dalam proses": "bg-danger",
  "masuk gudang": "bg-danger",
  "keluar gudang": "bg-secondary",
  "diproses": "bg-muted",
  "dikirim": "bg-warning",
  "selesai": "bg-success",
};

const updateStatus = async (row: Row<Input>) => {
  const currentStatus = row.original.status;
  const currentIndex = statusSteps.indexOf(currentStatus);

  if (currentIndex === -1) {
    console.error("Status tidak valid:", currentStatus);
    return;
  }
  // Cegah update jika status sudah "selesai"
  if (currentStatus === "selesai") {
    await Swal.fire({
      icon: "info",
      title: "Status Selesai",
      text: "Pengiriman telah selesai dan tidak dapat diubah lagi.",
    });
    return;
  }

  const nextIndex = currentIndex + 1;
  const nextStatus = statusSteps[nextIndex];
  const label = statusLabels[nextStatus];
  const icon = statusIcons[nextStatus];

  const confirmed = await Swal.fire({
    icon,
    title: `Ubah Status ke "${label}"?`,
    html: `Anda akan mengubah status pengiriman ini menjadi <strong style="text-transform: capitalize;">${label}</strong>.`,
    showCancelButton: true,
    confirmButtonText: "Ya, ubah",
    cancelButtonText: "Batal",
  }).then((result) => result.isConfirmed);

  if (!confirmed) return;

  // Optimistik update
  row.original.status = nextStatus;

  try {
    await axios.put(`/ordered/${row.original.id}`, {
      status: nextStatus,
    });

    Swal.fire({
      icon: "success",
      title: "Status Diperbarui",
      text: `Status berhasil diubah menjadi "${label}".`,
      timer: 1500,
      showConfirmButton: false,
    });

    refresh();
  } catch (error) {
    console.error("Gagal update status:", error);
    row.original.status = currentStatus;

    Swal.fire({
      icon: "error",
      title: "Gagal",
      text: "Status tidak berhasil diperbarui. Coba lagi.",
    });
  }
};


// List riwayat dari backend
const riwayatList = ref<Riwayat[]>([])

// List yang sudah pernah ditampilkan
const riwayatTertampil = ref<RiwayatTampil[]>([])

// Fungsi untuk menampilkan riwayat di SweetAlert
const showRincian = (data: any) => {
  // ⛔ Reset data lama
  console.log(Array.isArray(data.riwayat)); // Harusnya true


  riwayatTertampil.value = [];

  const newItems = data.riwayat.map((item: any) => {
    const formattedText = `${item.deskripsi} (${formatDate(item.created_at)})`;
    return {
      id: item.id_riwayat,
      riwayat: formattedText
    };
  });

  // ⛔ Jangan cek alreadyShown — langsung push semua!
  newItems.forEach(item => {
    riwayatTertampil.value.push(item);
  });

  const htmlContent = riwayatTertampil.value
    .map((item, index) => `
      <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px dashed #ddd;">
        <strong>${index + 1}.</strong> ${item.riwayat}
      </div>
    `)
    .join('');

  Swal.fire({
    title: "Detail Riwayat",
    html: `<div style="text-align: left; padding: 10px;">${htmlContent}</div>`,
    confirmButtonText: "Tutup",
    width: 600,
  });
};

const masukGudang = async (data: Input) => {
  const { isConfirmed } = await Swal.fire({
    title: "Konfirmasi",
    text: `Yakin ingin memasukkan paket ke gudang?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Masukkan",
  });

  if (isConfirmed) {
    try {
      await axios.post(`/gudang/masuk`, {
        input_id: data.id,
        deskripsi: "Paket masuk gudang oleh petugas",
      });

      Swal.fire("Berhasil", "Paket berhasil masuk gudang", "success");
      refresh();
    } catch (error) {
      Swal.fire("Gagal", "Terjadi kesalahan", "error");
    }
  }
};


// const masukGudang = async (data: Input) => {
//   const { isConfirmed } = await Swal.fire({
//     title: "Konfirmasi",
//     text: `Apakah Anda yakin ingin memasukkan paket ke gudang?`,
//     icon: "question",
//     showCancelButton: true,
//     confirmButtonText: "Ya, Masukkan",
//   });

//   if (isConfirmed) {
//     try {
//       await axios.post(`/gudang/masuk`, {
//         input_id: data.id,
//         deskripsi: "Paket masuk gudang",
//       });

//       Swal.fire("Berhasil", "Paket berhasil dimasukkan ke gudang.", "success");
//       refresh();
//     } catch (error) {
//       Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan.", "error");
//     }
//   }
// };

// const keluarGudang = async (data: Input) => {
//   const { isConfirmed } = await Swal.fire({
//     title: "Konfirmasi",
//     text: `Apakah Anda yakin ingin mengeluarkan paket dari gudang?`,
//     icon: "warning",
//     showCancelButton: true,
//     confirmButtonText: "Ya, Keluarkan",
//   });

//   if (isConfirmed) {
//     try {
//       await axios.post(`/gudang/keluar`, {
//         input_id: data.id,
//         deskripsi: "Paket keluar dari gudang",
//       });

//       Swal.fire("Berhasil", "Paket berhasil dikeluarkan dari gudang.", "success");
//       refresh();
//     } catch (error) {
//       Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan.", "error");
//     }
//   }
// };


// Referensi dan variabel
const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const tambahRiwayat = (logBaru: string) => {
  if (!formData.value.riwayat) {
    formData.value.riwayat = [];
  }

  formData.value.riwayat.push(logBaru);
};


// Delete handler
const { delete: deleteInput } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

const stored = localStorage.getItem("changedButtons");
const changedButtons = ref<Set<number>>(new Set(stored ? JSON.parse(stored) : []));
// const changedButtons = ref<Set<number>>(new Set());

watch(changedButtons, (val) => {
  localStorage.setItem("changedButtons", JSON.stringify([...val]));
}, { deep: true });

// const operKeKurir = async (id: number, kurir_id: string) => {
//   if (!kurir_id) return;
//   try {
//     const res = await axios.post(`/input/${id}/oper`, {
//       kurir_id_baru: kurir_id,
//     });
//     console.success(res.data.message);
//   } catch (err) {
//     console.error(err);
//     console.error('Gagal oper ke kurir.');
//   }
// };
// const daftarKurir = ref<Kurir[]>([]);

// onMounted(async () => {
//   const res = await axios.get('/api/kurir');
//   daftarKurir.value = res.data;
// });


// Kolom tabel
const columns = [
  column.accessor("no", { header: "No" }),
  column.accessor("nama_pengirim", { header: "Nama Pengirim" }),
  column.accessor("alamat_pengirim", { header: "Alamat Pengirim" }),
  column.accessor("no_telp_pengirim", { header: "No. Telp Pengirim" }),
  column.accessor("nama_penerima", { header: "Nama Penerima" }),
  column.accessor("alamat_penerima", { header: "Alamat Penerima" }),
  column.accessor("no_telp_penerima", { header: "No. Telp Penerima" }),
  column.accessor("jenis_barang", { header: "Jenis Barang" }),
  column.accessor("jenis_layanan", { header: "Jenis Layanan" }),
  column.accessor("berat_barang", { header: "Berat Barang" }),

  column.accessor("no_resi", { header: "No Resi" }),
  column.display({
    id: "riwayat",
    header: "Riwayat",
    cell: (cell) => {
      const row = cell.row.original;
      const hasRiwayat = row.riwayat && row.riwayat.length > 0;

      return hasRiwayat
        ? h(
          "button",
          {
            class: "btn btn-sm btn-warning",
            onClick: () => showRincian(row),
          },
          "Detail Riwayat"
        )
        : h("span", { class: "text-muted fst-italic" }, "Belum ada riwayat");
    },
  }),

  column.accessor("status", {
    header: "Status",
    cell: ({ row }) => {
      const status = row.original.status;
      const badgeClass = statusColors[status] || "bg-secondary"; // fallback kalau tidak ditemukan

      return h(
        "button",
        {
          class: `badge ${badgeClass}`,
          onClick: () => updateStatus(row),
          style: "cursor: pointer; border: none;",
        },
        status.charAt(0).toUpperCase() + status.slice(1) // Kapitalisasi awal
      );
    },
  }),


  column.accessor("id", {
    header: "Order",
    cell: (cell) => {
      const id = cell.getValue() as number;
      const row = cell.row.original;
      const status = row.status;
      const isChanged = changedButtons.value.has(id);
      const label = isChanged ? "Tambah" : "Antar";

      // Kalau status selesai, tampilkan ikon centang
      if (status === "selesai") {
        return h(
          "span",
          {
            class: "text-success fw-bold",
            style: "font-size: 1.2rem;",
          },
          "✓"
        );
      }

      // Kalau belum selesai, tetap tampilkan tombol aksi
      return h(
        "button",
        {
          class: "btn btn-sm btn-info",
          async onClick() {
            selected.value = id;

            if (isChanged) {
              openForm.value = true;
            } else {
              const result = await Swal.fire({
                title: "Ambil Orderan Ini?",
                text: "Yakin ingin mengambil orderan ini untuk dikirim?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Ambil",
                cancelButtonText: "Batal",
              });

              if (!result.isConfirmed) return;

              await Swal.fire({
                title: "Orderan Diambil",
                icon: "success",
                confirmButtonText: "OK",
              });

              changedButtons.value.add(id);
            }
          },
        },
        label
      );
    },
  }),



  // column.accessor("id", {
  //   header: "Order",

  //   cell: (cell) => {
  //     const id = cell.getValue() as number;
  //     const row = cell.row.original; // Ambil seluruh data baris
  //     const status = row.status;     // Misalnya ada field status di data
  //     const isChanged = changedButtons.value.has(id);
  //     const label = isChanged ? "Tambah" : "Antar";

  //     return h(
  //       "button",
  //       {
  //         class: "btn btn-sm btn-info",
  //         // disabled: status === "selesai", // ⛔ Tidak bisa diklik jika status selesai
  //         async onClick() {
  //           selected.value = id;

  //           if (isChanged) {
  //             openForm.value = true;
  //           } else {
  //             const result = await Swal.fire({
  //               title: "Ambil Orderan Ini?",
  //               text: "Yakin ingin mengambil orderan ini untuk dikirim?",
  //               icon: "question",
  //               showCancelButton: true,
  //               confirmButtonText: "Ya, Ambil",
  //               cancelButtonText: "Batal",
  //             });

  //             if (!result.isConfirmed) return;

  //             await Swal.fire({
  //               title: "Orderan Diambil",
  //               icon: "success",
  //               confirmButtonText: "OK",
  //             });

  //             changedButtons.value.add(id);
  //           }
  //         },
  //       },
  //       label
  //     );
  //   },
  // }),

  // column.accessor("id", {
  //   header: "Order",
  //   cell: (cell) => {
  //     const id = cell.getValue() as number;
  //     const isChanged = changedButtons.value.has(id);
  //     const label = isChanged ? "Tambah" : "Antar";

  //     return h(
  //       "button",
  //       {
  //         class: "btn btn-sm btn-info",
  //         async onClick() {
  //           selected.value = id;

  //           if (isChanged) {
  //             openForm.value = true;
  //           } else {
  //             const result = await Swal.fire({
  //               title: "Ambil Orderan Ini?",
  //               text: "Yakin ingin mengambil orderan ini untuk dikirim?",
  //               icon: "question",
  //               showCancelButton: true,
  //               confirmButtonText: "Ya, Ambil",
  //               cancelButtonText: "Batal",
  //             });

  //             if (!result.isConfirmed) return;

  //             await Swal.fire({
  //               title: "Orderan Diambil",
  //               icon: "success",
  //               confirmButtonText: "OK",
  //             });

  //             // Tambahkan ID ke Set dan tersimpan otomatis
  //             changedButtons.value.add(id);
  //           }
  //         },
  //       },
  //       label
  //     );
  //   },
  // }),

  // column.accessor("waktu", { header: "Waktu" }),
  //     column.display({
  //   id: "aksiGudang",
  //   header: "Aksi Gudang",
  //   cell: (cell) => {
  //     const row = cell.row.original;
  //     return h("div", { class: "d-flex flex-wrap gap-2" }, [
  //       h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-success",
  //           onClick: () => masukGudang(row),
  //         },
  //         "Masuk Gudang"
  //       ),
  //       h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-secondary",
  //           onClick: () => keluarGudang(row),
  //         },
  //         "Keluar Gudang"
  //       ),
  //     ]);
  //   },
  // }),

  column.display({
    id: "aksiGudang",
    header: "Aksi Gudang",
    cell: (cell) => {
      const row = cell.row.original;

      // Jika status sudah "masuk gudang", tampilkan badge (✅)
      if (row.status === "masuk gudang") {
        return h("span", { class: "badge bg-success" }, "Sudah Masuk");
      }

      // Jika belum, tampilkan tombol
      return h(
        "button",
        {
          class: "btn btn-sm btn-success",
          onClick: () => masukGudang(row),
          disabled: row.status === "selesai",
        },
        "Masuk Gudang"
      );
    },
  }),

  // column.display({
  //   id: "aksiGudang",
  //   header: "Aksi Gudang",
  //   cell: (cell) => {
  //     const row = cell.row.original;

  //     const changedButtons = [];

  //     // Kalau belum masuk gudang → tampilkan tombol masuk
  //     if (row.status !== 'masuk gudang') {
  //       changedButtons.push(h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-success",
  //           onClick: () => masukGudang(row),
  //         },
  //         "Masuk Gudang"
  //       ));
  //     }

  //     Kalau sudah masuk gudang → bisa keluar
  //     if (row.status === 'masuk gudang') {
  //       buttons.push(h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-secondary",
  //           onClick: () => keluarGudang(row),
  //         },
  //         "Keluar Gudang"
  //       ));
  //     }

  //     return h("div", { class: "d-flex flex-wrap gap-2" }, changedButtons);
  //   },
  // }),

];
const submit = async () => {
  await axios.put(`/ordered/${props.selected}`, formData.value);
  emit("refresh");
  emit("close");
};

// Untuk reload data
const refresh = () => paginateRef.value?.refetch(); const props = defineProps<{ selected: string }>();
const emit = defineEmits(["close", "refresh"]);

const formData = ref<any>({});
const isLoading = ref(false);
onMounted(async () => {
  if (props.selected) {
    isLoading.value = true;
    const { data } = await axios.get(`/ordered/${props.selected}`);

    // Mapping jika nama dari backend bukan "riwayat"
    formData.value = {
      ...data,
      riwayat: data.riwayat ?? data.riwayat_pengiriman ?? [],
    };

    isLoading.value = false;
  }
});

const url = computed(() => {
  const params = new URLSearchParams();
  ['masuk gudang', 'keluar gudang', 'diproses', 'dikirim', 'selesai'].forEach(status => {
    params.append('exclude_status[]', status);
  });
  return `/input?${params.toString()}`;
});

// onMounted(async () => {
//     if (props.selected) {
//         isLoading.value = true;
//         const { data } = await axios.get(`/ordered/${props.selected}`);
//         formData.value = data;
//         isLoading.value = false;
//     }
// });

// Token auth (dari Laravel Sanctum atau session)
// const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Fungsi ambil paket
function claimPackage(packageId) {
    if (confirm('Yakin ingin mengambil paket ini?')) {
        fetch(`/api/input/${packageId}/claim`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // 'X-CSRF-TOKEN': token // Untuk Laravel CSRF
                'RESI-': token // Untuk Laravel CSRF
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Paket berhasil diambil!');
                loadPackages(); // Refresh daftar paket
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan!');
        });
    }
}

// Load paket tersedia
function loadPackages() {
    fetch('/api/packages') // endpoint dari controller index()
        .then(response => response.json())
        .then(data => {
            // Render data ke dalam tabel/list
            renderPackages(data.data);
        });
}

// Render paket ke HTML
function renderPackages(input) {
    const container = document.getElementById('input-list');
    let html = '<table class="table"><thead><tr><th>No Resi</th><th>Pengirim</th><th>Penerima</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
    
    input.forEach(input => {
        html += `<tr>
            <td>${input.no_resi || '-'}</td>
            <td>${input.nama_pengirim}</td>
            <td>${input.nama_penerima}</td>
            <td>${input.status}</td>
            <td>`;
        
        // Jika paket belum diambil, tampilkan tombol "Ambil"
        if (!input.kurir_id) {
            html += `<button onclick="claimPackage(${input.id})" class="btn btn-primary btn-sm">Ambil Paket</button>`;
        } else {
            html += `<span class="badge badge-success">Sudah Diambil</span>`;
        }
        
        html += `</td></tr>`;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Load saat halaman pertama kali dibuka
document.addEventListener('DOMContentLoaded', function() {
    loadPackages();
});





// Reset saat form ditutup
watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo({ top: 0, behavior: "smooth" });
});

</script>

<template>
  <!-- Form -->
  <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />

  <!-- Card List -->
  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">Orderan</h2>
    </div>
    <paginate ref="paginateRef" id="table-inputorder" :url=url :columns="columns" />
    <!-- <div class="card-body">
      <p v-if="inputData">Data input: {{ inputData }}</p>
      <paginate ref="paginateRef" id="table-inputorder" url="/input?exclude_status=selesai" :columns="columns" />
      Tanpa spasi
    </div> -->
    <!-- <paginate ref="paginateRef" id="table-inputorder" url="/input?exclude_status=Masuk gudang" :columns="columns" /> -->

  </div>
</template>

<style scoped>
.btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;

}
</style>



