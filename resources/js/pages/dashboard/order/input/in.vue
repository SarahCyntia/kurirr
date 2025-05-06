<script setup lang="ts">
import { h, ref, watch, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import axios from "axios"; // Pastikan axios sudah terinstall

const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const inputData = ref<Input | null>(null); // Data  input yang terkait dengan user login

const { delete: deleteInput } = useDelete({
    // Ganti `delete.input` jadi `deleteInput`
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", { header: "No" }),
    column.accessor("nama_barang", { header: "Nama Barang" }),
    column.accessor("alamat_asal", { header: "Alamat Asal" }),
    column.accessor("alamat_tujuan", { header: "Alamat Tujuan" }),
    column.accessor("penerima", { header: "Penerima" }),
    column.accessor("metode_pengiriman", { header: "Metode Pengiriman" }),
    column.accessor("berat_paket", { header: "Berat " }),
    column.accessor("biaya_pengiriman", { header: "Biaya Pengiriman" }),
    column.accessor("tanggal_input", { header: "Tanggal Input" }),
    column.accessor("tanggal_penerimaan", { header: "Tanggal Penerimaan" }),
    // column.accessor("status", { header: "Status" }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            const badgeClass =
                status === "menunggu"
                    ? "bg-success"
                    : status === "dalam proses"
                    ? "bg-primary"
                    : status === "pengambilan paket"
                    ? "bg-danger"
                    : status === "dikirim"
                    ? "bg-warning"
                    : status === "selesai"
                    ? "bg-info"
                    : "bg-secondary"; // default kalau selain itu

            const label =
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

            return h("span", { class: `badge ${badgeClass}` }, label);
        },
        
    }),
//    column.accessor("id", {
//     header: "Aksi",
//     cell: (cell) =>
//         h("div", { class: "d-flex gap-2" }, [
//             h(
//                 "button",
//                 {
//                     class: "btn btn-sm btn-icon btn-danger", // Ganti warna jadi warning
//                     onClick: () => cancelInput(`input/${cell.getValue()}`), // Fungsi dibatalkan
//                 },
//                 h("i", { class: "bi bi-x-circle" }) // Ganti ikon jadi cancel
//             ),
//         ]),
// }),

];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Input Order</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
              Tambah
              <i class="la la-plus"></i>
          </button>
        </div>
        <div class="card-body">
            <p v-if="inputData">Data input: {{ inputData }}</p>
            <paginate ref="paginateRef" id="table-inputorder" url="/input?status=menunggu"
                :columns="columns"/>
                <!-- <paginate ref="paginateRef" id="table-transaksi" url="/trans?exclude_status=Terkirim"
                :columns="columns"/> -->
            <!-- Tanpa spasi -->
        </div>
    </div>
    <!-- <button
        type="button"
        class="btn btn-sm btn-primary ms-auto"
        v-if="!openForm"
        @click="$router.push('/dashboard')"
    >
        Kembali
    </button> -->
</template>

<style>

.btn {
  margin-top: 3rem;
  padding-right: 5rem;
  padding-left: 5rem;
}
  
</style>
