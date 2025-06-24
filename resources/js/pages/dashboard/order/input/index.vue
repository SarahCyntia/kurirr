iy
<script setup lang="ts">
import { ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
// import { toast } from "vue3-toastify/index";
import axios from "axios";
import { saveAs } from 'file-saver';
// import { Row } from "element-plus/es/components/table-v2/src/components";

// Referensi dan variabel
const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

// Delete handler
const { delete: deleteInput } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});
// const printReceipt = (noResi: string) => {
//   const printUrl = `/cetak-resi/${noResi}`;
//   const newWindow = window.open(printUrl, '_blank');

//   // Check jika window berhasil dibuka
//   if (!newWindow) {
//     alert('Pop-up diblokir! Silakan izinkan pop-up untuk website ini.');
//     return;
//   }

//   // Optional: check jika halaman berhasil load
//   newWindow.onload = () => {
//     console.log('Halaman berhasil dimuat');
//   };

//   newWindow.onerror = () => {
//     console.error('Gagal memuat halaman');
//   };
// };

const bayar = async (rowData) => {
  if (rowData.isPaying) return;

  rowData.isPaying = true;

  const draft = {
    provinceDestination: rowData.provinceDestination,
    cityDestination: rowData.cityDestination,
    alamat_tujuan: rowData.alamat_tujuan,

    total_harga: rowData.total_harga,
    provinceOrigin: rowData.provinceOrigin,
    cityOrigin: rowData.cityOrigin,
    alamat_asal: rowData.alamat_asal,

    selectedService: rowData.selectedService,
    berat_barang: rowData.berat_barang,
    biaya: rowData.biaya,

    id: rowData.id,
  };

  sessionStorage.setItem("draftTransaksi", JSON.stringify(draft));

  try {
    const res = await axios.post("/api/payments", draft);
    // const res = await axios.post("/payment/create", draft);
    const { snap_token } = res.data;

    if (!snap_token) throw new Error("Token pembayaran tidak ditemukan.");

    window.snap.pay(snap_token, {
      onSuccess: function (result) {
        console.log("✅ Pembayaran berhasil:", result);
        rowData.status = "paid";
      },
      onPending: function (result) {
        console.log("⏳ Menunggu pembayaran:", result);
        rowData.status = "pending";
      },
      onError: function (result) {
        console.error("❌ Pembayaran gagal:", result);
      },
      onClose: function () {
        console.log("❎ Popup ditutup");
      },
    });
  } catch (err) {
    console.error("❌ Terjadi kesalahan:", err);
  } finally {
    rowData.isPaying = false;
  }
};


// Fungsi download PDF (opsional)
// const printReceipt = (noResi: string) => {
//   const printUrl = `/cetak-pdf/${noResi}`;
//   window.open(printUrl, '_blank'); // Buka PDF di tab baru
// };

const downloadReceipt = async (noResi: string) => {
  const response = await axios.get(`/download-resi/${noResi}`, {
    responseType: 'blob'
  });

  saveAs(response.data, `struk-${noResi}.pdf`);
};

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
  column.accessor("berat_barang", { header: "Berat Barang" }),
  column.accessor("ekspedisi", { header: "Ekspedisi" }),
  column.accessor("jenis_layanan", { header: "Jenis Layanan" }),
  column.accessor("no_resi", { header: "No Resi" }),
  column.accessor("biaya", { header: "Biaya" }),
  column.accessor("status", {
  header: "Status",
  cell: ({ row }) => {
  return h(
    "button",
    {
      class: "badge bg-secondary",
      onClick: () => updateStatus(row),
      style: "cursor: pointer; border: none; background: none;",
    },
    row.original.status
  );
}
}),
  // Dengan React Query
// column.accessor("status", {
//   header: "Status",
//   cell: ({ row }) => {
//     const data = row.original;
//     const queryClient = useQueryClient();
    
//     const updateStatusMutation = useMutation({
//       mutationFn: async (newStatus) => {
//         const response = await fetch(`/api/orders/${data.id}/status`, {
//           method: 'PUT',
//           headers: {
//             'Content-Type': 'application/json',
//             'Authorization': `Bearer ${getAuthToken()}`,
//           },
//           body: JSON.stringify({ status: newStatus })
//         });
        
//         if (!response.ok) {
//           throw new Error(`Failed to update status: ${response.status}`);
//         }
        
//         return response.json();
//       },
//       onSuccess: () => {
//         // Invalidate dan refetch orders data
//         queryClient.invalidateQueries(['orders']);
//         toast.success('Status berhasil diperbarui');
//       },
//       onError: (error) => {
//         if (error.message.includes('401')) {
//           toast.error('Sesi berakhir. Silakan login kembali.');
//           window.location.href = '/login';
//         } else {
//           toast.error('Gagal memperbarui status');
//         }
//       }
//     });
    
//     function handleClick() {
//       const currentStatus = data.status;
//       const nextStatus = currentStatus === "menunggu"
//         ? "dalam proses"
//         : currentStatus === "dalam proses"
//           ? "dikirim"
//           : currentStatus === "dikirim"
//             ? "selesai"
//             : "menunggu";
      
//       updateStatusMutation.mutate(nextStatus);
//     }
    
//     return h(
//       "button",
//       {
//         class: `badge ${getStatusStyle(data.status)} ${updateStatusMutation.isLoading ? 'opacity-50' : ''}`,
//         onClick: handleClick,
//         disabled: updateStatusMutation.isLoading,
//         style: "cursor: pointer; border: none; padding: 0.5rem 1rem;",
//       },
//       updateStatusMutation.isLoading ? "Memproses..." : data.status
//     );
//   },
// }),

  // column.accessor("waktu", { header: "Waktu" }),

  // column.accessor("created_at", {
  //     header: "Tanggal Input",
  //     cell: (cell) => {
  //         const val = cell.getValue();
  //         const date = new Date(val);
  //         return date.toLocaleString("id-ID");
  //     },
  // }),

 column.display({
  id: "aksi",
  header: "Aksi",
  cell: ({ row }) => {
    const noResi = row.original.no_resi;

    return h("div", { class: "d-flex gap-2" }, [
      // h(
      //   "button",
      //   {
      //     class: "btn btn-sm btn-info",
      //     onClick: () => printReceipt(noResi),
      //     title: "Cetak PDF"
      //   },
      //   [
      //     h("i", { class: "la la-file-pdf-o me-1" }),
      //     "Cetak PDF"
      //   ]
      // ),
      h(
        "button",
        {
          class: "btn btn-sm btn-secondary",
          onClick: () => downloadReceipt(noResi),
          title: "Download PDF"
        },
        [
          h("i", { class: "la la-download me-1" }),
          "Download Struk"
        ]
      )
    ]);
  },
}),


  // column.display({
  //     id: "aksi",
  //     header: "Aksi",
  //     cell: ({ row }) => {
  //         const noResi = row.original.no_resi;
  //         return h(
  //             "button",
  //             {
  //                 class: "btn btn-sm btn-info",
  //                 onClick: () => window.open(`/cetak-resi/${noResi}`, "_blank"),
  //             },
  //             "Cetak Struk"
  //         );
  //     },
  // }),
// column.display({
//   id: "bayar",
//   header: "Bayar",
//   cell: (cell) => {
//     console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
    
//     const rowData = cell.row.original;
//     const isPaying = rowData.isPaying || false;

//     return h(
//       "button",
//       {
//         class: `btn btn-sm btn-success ${isPaying ? 'disabled' : ''}`,
//         disabled: isPaying,
//         onClick: () => handleBayar(cell.row.original),
//       },
//       [
//         h("i", { class: "la la-credit-card me-1" }),
//         isPaying ? "Memproses..." : "Bayar"
//       ]
//     );
//   },
// }),

column.display({
  id: "bayar",
  header: "Bayar",
  cell: (cell) => {
    const rowData = cell.row.original;
    const isPaying = rowData.isPaying || false;

    return h(
      "button",
      {
        class: `btn btn-sm btn-success ${isPaying ? 'disabled' : ''}`,
        disabled: isPaying,
        onClick: () => bayar(rowData),
      },
      [
        h("i", { class: "la la-credit-card me-1" }),
        isPaying ? "Memproses..." : "Bayar"
      ]
    );
  },
}),


];

// Fungsi bayar yang sudah diperbaiki


// Untuk reload data
const refresh = () => paginateRef.value?.refetch();

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
      <h2 class="mb-0">List Order</h2>
      <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah <i class="la la-plus"></i>
      </button>
    </div>

    <div class="card-body">
      <paginate ref="paginateRef" id="table-inputorder" url="/input?status=menunggu" :columns="columns" />
    </div>
  </div>
</template>

<style scoped>
.btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
}
</style>
