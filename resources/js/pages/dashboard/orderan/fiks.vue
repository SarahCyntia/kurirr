<script setup lang="ts">
import { computed, onMounted, ref, watch, type Ref } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue"; 
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
import Swal from "sweetalert2";
import axios from "axios";
import { SMART_ALIGNMENT } from "element-plus/es/components/virtual-list/src/defaults";

// Referensi dan variabel
const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const tambahRiwayat = (logBaru: string) => {
  if (!formData.value.riwayat_pengiriman) {
    formData.value.riwayat_pengiriman = [];
  }

  formData.value.riwayat_pengiriman.push(logBaru);
};

const statusSteps = ["menunggu", "dalam proses", "masuk gudang", "keluar gudang", "diproses", "dikirim", "selesai"] as const;
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
  if (currentIndex === -1 || currentStatus === "selesai") return;

  const nextStatus = statusSteps[currentIndex + 1];

  const confirmed = await Swal.fire({
    icon: statusIcons[nextStatus],
    title: `Ubah Status ke "${statusLabels[nextStatus]}"?`,
    html: `Anda akan mengubah status ke <strong>${statusLabels[nextStatus]}</strong>.`,
    showCancelButton: true,
    confirmButtonText: "Ya, ubah"
  }).then(r => r.isConfirmed);

  if (!confirmed) return;

  const oldStatus = row.original.status;
  row.original.status = nextStatus;

  try {
    const updates: any = { status: nextStatus };

    // ✅ Tambahkan otomatis riwayat jika status jadi "selesai"
    if (nextStatus === "selesai") {
      const newLog = {
        deskripsi: "Paket telah diterima oleh yang bersangkutan",
        created_at: new Date().toISOString()
      };
      updates.riwayat = [...(row.original.riwayat || []), newLog];
    }

    await axios.put(`/ordered/${row.original.id}`, updates);

    Swal.fire("Berhasil", "Status diperbarui", "success");
    refresh();
  } catch {
    row.original.status = oldStatus;
    Swal.fire("Gagal", "Tidak bisa ubah status", "error");
  }
};


// const updateStatus = async (row: Row<Input>) => {
//   const currentStatus = row.original.status;
//   const currentIndex = statusSteps.indexOf(currentStatus);

//   if (currentIndex === -1) {
//     console.error("Status tidak valid:", currentStatus);
//     return;
//   }
//   // Cegah update jika status sudah "selesai"
//   if (currentStatus === "selesai") {
//     await Swal.fire({
//       icon: "info",
//       title: "Status Selesai",
//       text: "Pengiriman telah selesai dan tidak dapat diubah lagi.",
//     });
//     return;
//   }

//   const nextIndex = currentIndex + 1;
//   const nextStatus = statusSteps[nextIndex];
//   const label = statusLabels[nextStatus];
//   const icon = statusIcons[nextStatus];

//   const confirmed = await Swal.fire({
//     icon,
//     title: `Ubah Status ke "${label}"?`,
//     html: `Anda akan mengubah status pengiriman ini menjadi <strong style="text-transform: capitalize;">${label}</strong>.`,
//     showCancelButton: true,
//     confirmButtonText: "Ya, ubah",
//     cancelButtonText: "Batal",
//   }).then((result) => result.isConfirmed);

//   if (!confirmed) return;

//   // Optimistik update
//   row.original.status = nextStatus;

//   try {
//     await axios.put(`/ordered/${row.original.id}`, {
//       status: nextStatus,
//     });

//     Swal.fire({
//       icon: "success",
//       title: "Status Diperbarui",
//       text: `Status berhasil diubah menjadi "${label}".`,
//       timer: 1500,
//       showConfirmButton: false,
//     });

//     refresh();
//   } catch (error) {
//     console.error("Gagal update status:", error);
//     row.original.status = currentStatus;

//     Swal.fire({
//       icon: "error",
//       title: "Gagal",
//       text: "Status tidak berhasil diperbarui. Coba lagi.",
//     });
//   }
// };

// Delete handler
const { delete: deleteInput } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

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

// const keluarGudang = async (row: Input) => {
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
//         input_id: row.id,
//         deskripsi: "Paket keluar dari gudang",
//       });

//       // ✅ Update flag lokal agar tombol berubah
//       row.keluar_gudang = true;

//       Swal.fire("Berhasil", "Paket berhasil dikeluarkan dari gudang.", "success");
//     } catch (error) {
//       Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan.", "error");
//     }
//   }
// };

//   const antarPaket = async (data: Input) => {
//   const { isConfirmed } = await Swal.fire({
//     title: "Konfirmasi",
//     text: `Apakah Anda yakin ingin mengantar paket ini?`,
//     icon: "info",
//     showCancelButton: true,
//     confirmButtonText: "Ya, Antar",
//   });

//   if (isConfirmed) {
//     try {
//       await axios.post(`/pengantaran`, {
//         input_id: data.id,
//         deskripsi: "Paket dalam proses pengantaran",
//       });

//       Swal.fire("Berhasil", "Paket sedang diantar.", "success");
//       refresh();
//     } catch (error) {
//       Swal.fire("Gagal", "Terjadi kesalahan saat mengantar paket.", "error");
//     }
//   }
// };

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


const showRincians = (data: Input) => {
  console.log(data)
  Swal.fire({
    // title: <strong>Detail Input</strong>,
    title: "Detail Riwayat",

    html: `
    <div style="text-align: left;">
        <p><b>Asal Provinsi :</b> ${data.asal_provinsi.name || '-'}</p>
        <p><b>Asal Kota :</b> ${data.asal_kota.name || '-'}</p>
        <p><b>No. Telpon Pengirim :</b> ${data.no_telp_pengirim}</p>
        <p><b>Tujuan Provinsi :</b> ${data.tujuan_provinsi.name || '-'}</p>
        <p><b>Tujuan Kota :</b> ${data.tujuan_kota.name || '-'}</p>
        <p><b>No. Telpon Penerima :</b> ${data.no_telp_penerima || '-'}</p>
        <p><b>Jenis Barang :</b> ${data.jenis_barang || '-'}</p>
        <p><b>Ekspedisi :</b> ${data.ekspedisi || '-'}</p>
        <p><b>Berat Barang :</b> ${data.berat_barang || '-'}</p>
        <p><b>Jenis Layanan :</b> ${data.jenis_layanan || '-'}</p>
      </div>
    `,
    confirmButtonText: "Tutup",
  });
};

const stored = localStorage.getItem("changedButtons");
const changedButtons = ref<Set<number>>(new Set(stored ? JSON.parse(stored) : []));
  // const changedButtons = ref<Set<number>>(new Set());

watch(changedButtons, (val) => {
  localStorage.setItem("changedButtons", JSON.stringify([...val]));
}, { deep: true });
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

// 


// Kolom tabel
const columns = [
  column.accessor("no", { header: "No" }),
  column.accessor("nama_pengirim", { header: "Nama Pengirim" }),
  column.accessor("alamat_pengirim", { header: "Alamat Pengirim" }),
  column.accessor("nama_penerima", { header: "Nama Penerima" }),
  column.accessor("alamat_penerima", { header: "Alamat Penerima" }),
  column.accessor("no_resi", { header: "No Resi" }),
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
  
      column.display({
    id: "order",
    header: "Detail Order",
    cell: (cell) => {
      console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
      return h(
        "button",
        {
          class: "btn btn-sm btn-danger",
          onClick: () => showRincians(cell.row.original),
        },
        "Lihat Detail"
      );
    },
  }),

  // column.accessor("id", {
  //   header: "Detail Order",
  //   cell: (cell) =>
  //     h("div", { class: "d-flex gap-2" }, [
  //       h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-icon btn-danger",
  //           onClick: () => showRincians(cell.row.original),
  //         },
  //         h("i", { class: "bi bi-building" })
  //         // "Lihat"
  //       ),
  //     ]),
  // }),

column.accessor("id", {
  header: "Orderan",
  cell: ({ row }) => {
    const id = row.original.id;
    const status = row.original.status;

    // Jika status selesai → tampilkan centang
    if (status === "selesai") {
      return h("span", { class: "text-success fw-bold" }, "✓");
    }

    // Jika status menunggu → tombol Antar
    if (status === "keluar gudang") {
      return h(
        "button",
        {
          class: "btn btn-sm btn-primary",
          async onClick() {
            const confirm = await Swal.fire({
              title: "Ambil Orderan Ini?",
              text: "Yakin ingin mengambil orderan ini untuk dikirim?",
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "Ya",
              cancelButtonText: "Batal"
            });

            if (!confirm.isConfirmed) return;

            try {
              // ✅ 1. Klaim kurir
              const claimRes = await axios.post(`/input/${id}/claim`);
              if (!claimRes.data.success) {
                return Swal.fire("Gagal", claimRes.data.message, "error");
              }

              // ✅ 2. Update status jadi "dalam proses"
              await axios.put(`/ordered/${id}`, {
                status: "diproses",
              });
              // await axios.put(`/orderan/${id}`, {
              //   status: "diproses",
              // });

              // // ✅ 3. Tambah riwayat
              // await axios.post("/riwayat", {
              //   input_id: id,
              //   deskripsi: "Paket diambil kurir"
              // });

              // ✅ 4. Refresh tabel
              await Swal.fire("Berhasil", "Orderan berhasil diambil", "success");
              refresh();
            } catch (err) {
              await Swal.fire("Error", "Terjadi kesalahan saat memproses", "error");
            }
          }
        },
        "Antar"
      );
    }

    // Selain itu (status sudah "dalam proses", dst) → tombol Tambah
    return h(
      "button",
      {
        class: "btn btn-sm btn-success",
        onClick: () => {
          selected.value = row.original.id;
          openForm.value = true;
        }
      },
      "Tambah"
    );
  }
}),

// column.accessor("id", {
//   header: "Order",
//   cell: (cell) => {
//     const id = cell.getValue() as number;
//     const row = cell.row.original;
//     const status = row.status;
//     const isChanged = changedButtons.value.has(id);
//     // const label = isChanged ? "Tambah" : "Antar";
//     // Kalau statusnya "keluar gudang", maka tombol harus jadi "Antar"
//     const label = status === "keluar gudang" ? "Antar" : "Tambah";


//     // Kalau status selesai, tampilkan ikon centang
//     if (status === "selesai") {
//       return h(
//         "span",
//         {
//           class: "text-success fw-bold",
//           style: "font-size: 1.2rem;",
//         },
//         "✓"
//       );
//     }

//     // Kalau belum selesai, tetap tampilkan tombol aksi
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

//             changedButtons.value.add(id);
//           }
//         },
//       },
//       label
//     );
//   },
// }),
];

const submit = async () => {
  await axios.put(`/Orderan/${props.selected}`, formData.value);
  emit("refresh");
  emit("close");
};

const url = computed(() => {
  const params = new URLSearchParams();

  // Tampilkan beberapa status
  ["menunggu", "dalam proses", "masuk gudang", "selesai"].forEach(status => {
    params.append("exclude_status[]", status);
  });
  // ["keluar gudang", "diproses", "dikirim"].forEach(status => {
  //   params.append("status[]", status);
  // });

  return `/input?${params.toString()}`;
});


// Untuk reload data
const refresh = () => paginateRef.value?.refetch(); const props = defineProps<{ selected: string }>();
const emit = defineEmits(["close", "refresh"]);

const formData = ref<any>({});
const isLoading = ref(false);

onMounted(async () => {
  if (props.selected) {
    isLoading.value = true;
    const { data } = await axios.get(`/orderan/${props.selected}`);
    formData.value = data;
    isLoading.value = false;
  }
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

    <!-- <div class="card-body">
      <paginate ref="paginateRef" id="table-inputorder" url="/input" :columns="columns" />
    </div> -->
     <!-- <div class="card-body">
        <paginate ref="paginateRef" id="table-inputorder" url="/input?aksi=keluar"
        :columns="columns"/>
    </div> -->
    <!-- <paginate ref="paginateRef" id="table-inputorder" url="/input?status=Keluar gudang" :columns="columns" /> -->
     <paginate
  ref="paginateRef"
  id="table-inputorder"
  :url="url"
  :columns="columns"
/>

  </div>
</template>

<style scoped>
.btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
}
</style>