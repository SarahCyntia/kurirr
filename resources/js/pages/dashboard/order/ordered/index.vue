<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper, type Row } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
import Swal from "sweetalert2";
import axios from "axios";
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

const statusSteps = ["menunggu", "dalam proses", "dikirim", "selesai"] as const;
const statusLabels: Record<string, string> = {
  "menunggu": "Menunggu",
  "dalam proses": "Dalam Proses",
  "dikirim": "Dikirim",
  "selesai": "Selesai"
};
const statusIcons: Record<string, "info" | "question" | "warning" | "success"> = {
  "menunggu": "info",
  "dalam proses": "question",
  "dikirim": "warning",
  "selesai": "success"
};
const statusColors: Record<string, string> = {
  "menunggu": "bg-info",
  "dalam proses": "bg-danger",
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

// const showRincian = (data: any) => {
//   console.log("data: ",data)

//   // Loop semua item riwayat
//   const newItems = data.riwayat.map((item: any) => {
//     const formattedText = `${item.deskripsi} (${formatDate(item.created_at)})`;
//     return {
//       id: item.id_riwayat,
//       riwayat: formattedText
//     };
//   });

//   console.log("map : ", newItems)

//   // Tambah hanya jika belum ditampilkan sebelumnya
//   newItems.forEach(item => {
//     const alreadyShown = riwayatTertampil.value.some(r => r.id === item.id);
//     if (!alreadyShown) {
//       riwayatTertampil.value.push(item);
//     }
//   });

//   console.log(riwayatTertampil.value);

//   const htmlContent = riwayatTertampil.value
//     .map((item, index) => `
//       <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px dashed #ddd;">
//         <strong>${index + 1}.</strong> ${item.riwayat}
//       </div>
//     `)
//     .join('');

//   Swal.fire({
//     title: "Detail Riwayat",
//     html: `<div style="text-align: left; padding: 10px;">${htmlContent}</div>`,
//     confirmButtonText: "Tutup",
//     width: 600,
//   });
// }


// interface RiwayatData {
//   waktu: string;
//   riwayat: string;
// }

// // Tambahkan di atas atau sebelum fungsi showRincian
// function formatDate(waktu: string): string {
//   return new Date(waktu).toLocaleString("id-ID", {
//     day: "numeric",
//     month: "long",
//     year: "numeric",
//     hour: "2-digit",
//     minute: "2-digit"
//   });
// }

// const showRincian = (data: any) => {
//   let riwayat: RiwayatData[] = [];

//   const raw = data.riwayat; // diasumsikan ini array dari tabel relasi `riwayat`

//   if (Array.isArray(raw)) {
//     riwayat = raw.map((data: any) => ({
//       riwayat: data.deskripsi,            // dari kolom 'deskripsi'
//       waktu: formatDate(data.created_at), // gunakan format tanggal yang rapi
//     }));
//   }

//   const htmlContent = riwayat.length
//     ? riwayat
//         .map(
//           (data, idx) => `
//             <div style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed #ccc;">
//               <strong>${idx + 1}. ${data.waktu}</strong><br/>
//               <span>${data.riwayat}</span>
//             </div>
//           `
//         )
//         .join("")
//     : "<p>Belum ada riwayat pengiriman.</p>";
//      console.log("RAW riwayat:", data.riwayat);

//   Swal.fire({
//     title: "Detail Riwayat Pengiriman",
//     html: `<div style="text-align: left; max-height: 300px; overflow-y: auto; padding: 10px;">${htmlContent}</div>`,
//     confirmButtonText: "Tutup",
//     width: 600,
//   });
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

watch(changedButtons, (val) => {
  localStorage.setItem("changedButtons", JSON.stringify([...val]));
}, { deep: true });
// const changedButtons = ref<Set<number>>(new Set());

// const showRincian = (data: Input) => {
//     Swal.fire({
//         title: "Detail Riwayat",
//         html: `
//             <div style="text-align: left; padding: 20px 20px">
//                 <label for="riwayatInput"><b>Riwayat Pengiriman:</b></label><br/>
//                 <input id="riwayatInput" type="text" value="${data.riwayat_pengiriman || ''}" style="
//                     width: 100%;
//                     padding: 8px;
//                     margin-top: 8px;
//                     margin-bottom: 12px;
//                     border: 1px solid #ccc;
//                     border-radius: 4px;
//                 "/>
//                 <button id="editBtn" style="
//                     padding: 10px 20px;
//                     background-color: #4CAF50;
//                     color: white;
//                     border: none;
//                     border-radius: 4px;
//                     cursor: pointer;
//                 ">Simpan Perubahan</button>
//             </div>
//         `,
//         showConfirmButton: true,
//         confirmButtonText: "Tutup",
//         didOpen: () => {
//             const editBtn = document.getElementById("editBtn") as HTMLButtonElement;
//             const inputEl = document.getElementById("riwayatInput") as HTMLInputElement;

//             if (editBtn && inputEl) {
//                 editBtn.addEventListener("click", () => {
//                     const newValue = inputEl.value;

//                     // Contoh: tampilkan hasil edit (bisa diganti dengan fungsi update ke server)
//                     Swal.fire({
//                         title: "Tersimpan!",
//                         text: `Nilai baru: ${newValue}`,
//                         icon: "success",
//                     });

//                     // TODO: kirim ke backend atau update local state di sini
//                     console.log("Data baru:", newValue);
//                 });
//             }
//         }
//     });
// };



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
    // column.accessor("riwayat", { header: "Riwayat" }),
   column.display({
  id: "riwayat",
  header: "Riwayat",
  cell: (cell) => {
    console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
    return h(
      "button",
      {
        class: "btn btn-sm btn-warning",
        onClick: () => showRincian(cell.row.original),
      },
      "Detail Riwayat"
    );
  },
}),


//     column.accessor("status", {
//   header: "Status",
//   cell: ({ row }) => {
//   return h(
//     "button",
//     {
//       class: "badge bg-secondary",
//       onClick: () => updateStatus(row),
//       style: "cursor: pointer; border: none; background: none;",
//     },
//     row.original.status
//   );
// }
// }),

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
    const row = cell.row.original; // Ambil seluruh data baris
    const status = row.status;     // Misalnya ada field status di data
    const isChanged = changedButtons.value.has(id);
    const label = isChanged ? "Tambah" : "Antar";

    return h(
      "button",
      {
        class: "btn btn-sm btn-info",
        disabled: status === "selesai", // ⛔ Tidak bisa diklik jika status selesai
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

    column.accessor("waktu", { header: "Waktu" }),
    // column.display({
    //     id: "rincian",
    //     header: "Detail Input",
    //     cell: (cell) =>
    //         h(
    //             "button",
    //             {
    //                 class: "btn btn-sm btn-danger",
    //                 onClick: () => showRincian(cell.row.original),
    //             },
    //             "Lihat Detail"
    //         ),
    // }),

    
    
];
const submit = async () => {
    await axios.put(`/ordered/${props.selected}`, formData.value);
    emit("refresh");
    emit("close");
};




// Untuk reload data
const refresh = () => paginateRef.value?.refetch();const props = defineProps<{ selected: string }>();
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


// onMounted(async () => {
//     if (props.selected) {
//         isLoading.value = true;
//         const { data } = await axios.get(`/ordered/${props.selected}`);
//         formData.value = data;
//         isLoading.value = false;
//     }
// });



// Reset saat form ditutup
watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo({ top: 0, behavior: "smooth" });
});
</script>

<template>
    <!-- Form -->
    <Form
        v-if="openForm"
        :selected="selected"
        @close="openForm = false"
        @refresh="refresh"
    />

    <!-- Card List -->
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Orderan</h2>
        </div>

        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-inputorder"
                url="/input"
                :columns="columns"
            />
        </div>
    </div>
</template>

<style scoped>
.btn {
    margin-top: 1rem;
    padding: 0.5rem 1.5rem;
}
</style>
