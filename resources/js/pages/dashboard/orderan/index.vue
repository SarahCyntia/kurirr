<script setup lang="ts">
import { ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";

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
    //   column.accessor("no", { header: "No" }),
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
    column.accessor("status", {
        header: "Status",
        cell: ({ getValue }) => {
            const status = getValue();
            const badgeClass =
                {
                    menunggu: "bg-success",
                    "dalam proses": "bg-warning",
                    "pengambilan paket": "bg-danger",
                    dikirim: "bg-primary",
                    selesai: "bg-info",
                }[status] || "bg-secondary";

            const label =
                {
                    menunggu: "Menunggu",
                    "dalam proses": "Dalam Proses",
                    "pengambilan paket": "Pengambilan Paket",
                    dikirim: "Dikirim",
                    selesai: "Selesai",
                }[status] || "Dibatalkan";

            return h("span", { class: `badge ${badgeClass}` }, label);
        },
    }),
      column.accessor("id",{
        header: "Order",
        cell: (cell) =>
            h(
                "button",
                {
                    class: "btn btn-sm btn-info",
                    onClick: () => {
                        selected.value = cell.getValue();
                        openForm.value = true;
                    },
                },
                "Ambil Order"
            ),
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
                url="/input?status=menunggu"
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
