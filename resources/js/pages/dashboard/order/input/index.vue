iy
<script setup lang="ts">
import { ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
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
const printReceipt = (noResi: string) => {
    const printUrl = `/cetak-resi/${noResi}`;
    const newWindow = window.open(printUrl, '_blank');
    
    // Check jika window berhasil dibuka
    if (!newWindow) {
        alert('Pop-up diblokir! Silakan izinkan pop-up untuk website ini.');
        return;
    }
    
    // Optional: check jika halaman berhasil load
    newWindow.onload = () => {
        console.log('Halaman berhasil dimuat');
    };
    
    newWindow.onerror = () => {
        console.error('Gagal memuat halaman');
    };
};

// Fungsi download PDF (opsional)
const downloadReceipt = (noResi: string) => {
    const downloadUrl = `/download-resi/${noResi}`;
    window.open(downloadUrl, '_blank');
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
   column.accessor("status", {
  header: "Status",
  cell: ({ row }) => {
    const data = row.original;

    // Klik untuk ganti status ke berikutnya (contoh siklus)
    function handleClick() {
      const status = data.status;

      const nextStatus = status === "menunggu"
        ? "dalam proses"
        : status === "dalam proses"
        ? "dikirim"
        : status === "dikirim"
        ? "selesai"
        : "menunggu";

      data.status = nextStatus;
    }

    return h(
      "button",
      {
        class: "badge bg-secondary",
        onClick: handleClick,
        style: "cursor: pointer; border: none; background: none;",
      },
      data.status
    );
  },
}),

  // column.accessor("riwayat", { header: "Riwayat" }),
  column.accessor("waktu", { header: "Waktu" }),
    
    // column.accessor("created_at", {
    //     header: "Tanggal Input",
    //     cell: (cell) => {
    //         const val = cell.getValue();
    //         const date = new Date(val);
    //         return date.toLocaleString("id-ID");
    //     },
    // }),

// column.display({
//         id: "aksi",
//         header: "Aksi",
//         cell: ({ row }) => {
//             const noResi = row.original.no_resi;
//             return h("div", { class: "d-flex gap-2" }, [
//                 h(
//                     "button",
//                     {
//                         class: "btn btn-sm btn-info",
//                         onClick: () => printReceipt(noResi),
//                         title: "Cetak PDF"
//                     },
//                     [
//                         h("i", { class: "la la-file-pdf-o me-1" }),
//                         "Cetak PDF"
//                     ]
//                 ),
//                 h(
//                     "button",
//                     {
//                         class: "btn btn-sm btn-secondary",
//                         onClick: () => downloadReceipt(noResi),
//                         title: "Download PDF"
//                     },
//                     [
//                         h("i", { class: "la la-download me-1" }),
//                         "Download"
//                     ]
//                 )
//             ]);
//         },
//     }),
    
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

];

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
