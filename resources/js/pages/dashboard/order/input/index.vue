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
  cell: ({ row, getValue }) => {
    const status = getValue();
    const data = row.original;

    const badgeClass =
      status === "menunggu"
        ? "bg-success"
        : status === "dalam proses"
        ? "bg-warning"
        : status === "pengambilan paket"
        ? "bg-danger"
        : status === "dikirim"
        ? "bg-primary"
        : status === "selesai"
        ? "bg-info"
        : "bg-secondary";

    let label =
      status === "menunggu"
        ? "Menunggu"
        : status === "pengambilan paket"
        ? "Pengambilan Paket"
        : status === "dalam proses"
        ? "Dalam Proses"
        : status === "dikirim"
        ? "Dikirim"
        : status === "selesai"
        ? "Selesai"
        : "Dibatalkan";

    // Ambil tanggal dari field yang sesuai dengan status
    let input: string | null = null;
    switch (status) {
      case "menunggu":
        input = data.tanggal_order;
        break;
      case "dalam proses":
        input = data.tanggal_dikemas;
        break;
      case "dikirim":
        input = data.tanggal_dikirim;
        break;
      case "selesai":
        input = data.tanggal_penerimaan;
        break;
    }

    if (input) {
      const date = new Date(input);
      const formatted = date.toLocaleString("id-ID");
      label += ` (${formatted})`;
    }

    return h("span", { class: `badge ${badgeClass}` }, label);
  },
}),
  column.accessor("riwayat", { header: "Riwayat" }),
    
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
            return h(
                "button",
                {
                    class: "btn btn-sm btn-info",
                    onClick: () => window.open(`/cetak-resi/${noResi}`, "_blank"),
                },
                "Cetak Struk"
            );
        },
    }),

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
