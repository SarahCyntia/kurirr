<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import Swal from "sweetalert2";
import type { Input } from "@/types";
import { useAuth } from '@/composables/useAuth'

const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const { user } = useAuth() // Asumsi ada composable untuk user yang login


const { delete: deleteInput } = useDelete({
    onSuccess: () => refresh(),
});

const showRincian = (data: Input) => {
    Swal.fire({
        // title: <strong>Detail Input</strong>,
        title: "Detail Riwayat",
        html: `
        <div style="text-align: left;">
       <p><b>Berat Paket:</b> ${data.berat_paket || "-"}</p>
       <p><b>Jarak:</b> ${data.jarak || "-"}</p>
        <p><b>Metode Pengiriman:</b> ${data.metode_pengiriman}</p>
        <p><b>Biaya Pengiriman:</b> ${data.biaya_pengiriman}</p>
        <p><b>Tanggal Order :</b> ${data.tanggal_order || "-"}</p>
        <p><b>Tanggal Dikemas:</b> ${data.tanggal_dikemas || "-"}</p>
        <p><b>Tanggal Pengambilan:</b> ${data.tanggal_pengambilan || "-"}</p>
        <p><b>Tanggal Dikirim:</b> ${data.tanggal_dikirim || "-"}</p>
        <p><b>Tanggal Penerimaan:</b> ${data.tanggal_penerimaan || "-"}</p>
      </div>
    `,
        // icon: "info",
        confirmButtonText: "Tutup",
        // customClass: {
        //   popup: 'text-start',
        // },
    });
};

const columns = [
    column.accessor("no", { header: "No" }),
    column.accessor("nama_barang", { header: "Nama Barang" }),
    column.accessor("alamat_asal", { header: "Alamat asal" }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
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
    column.accessor("id", {
    // Header kolom ditampilkan sebagai "Penilaian"
    header: "Penilaian",

    // Fungsi untuk merender isi sel di kolom ini
    cell: (cell) => {
        // Ambil data asli seluruh baris dari cell (bukan hanya nilai di kolom ini)
        const row = cell.row.original;

        // Cek apakah data 'nilai' dan 'ulasan' sudah terisi => berarti sudah dinilai
        const sudahDinilai = row.nilai && row.ulasan;

        // Kembalikan elemen button yang dirender menggunakan fungsi h (hyperscript)
        return h(
            "button",
            {
                // Tambahkan class untuk styling tombol.
                // Jika sudah dinilai, tombol berwarna abu (btn-secondary)
                // Jika belum, tombol berwarna biru (btn-info)
                class:
                    "btn btn-sm d-flex align-items-center gap-1 " +
                    (sudahDinilai ? "btn-secondary" : "btn-info"),

                // Jika sudah dinilai, tombol akan dinonaktifkan (disabled)
                disabled: sudahDinilai,

                // Fungsi yang dijalankan saat tombol diklik
                onClick: () => {
                    // Hanya buka form jika belum dinilai
                    if (!sudahDinilai) {
                        // Simpan ID yang dipilih ke dalam variabel `selected`
                        selected.value = cell.getValue();

                        // Tampilkan form penilaian
                        openForm.value = true;
                    }
                },
            },
            // Teks tombol berubah tergantung status penilaian
            sudahDinilai ? "Sudah Dinilai" : "Penilaian"
        );
    },
}),

    // column.accessor("id", {
    //     header: "Penilaian",
    //     cell: (cell) => {
    //         const row = cell.row.original; // Ambil data lengkap baris

    //         // Cek apakah penilaian sudah ada
    //         const sudahDinilai = row.nilai && row.ulasan;

    //         return h(
    //             "button",
    //             {
    //                 class:
    //                     "btn btn-sm d-flex align-items-center gap-1 " +
    //                     (sudahDinilai ? "btn-secondary" : "btn-info"),
    //                 disabled: sudahDinilai, // ❗ Disable kalau sudah dinilai
    //                 onClick: () => {
    //                     if (!sudahDinilai) {
    //                         selected.value = cell.getValue();
    //                         openForm.value = true;
    //                     }
    //                 },
    //             },
    //             sudahDinilai ? "Sudah Dinilai" : "Penilaian"
    //         );
    //     },
    // }),

    column.accessor("id", {
        header: "Detail",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () => showRincian(cell.row.original),
                    },
                    h("i", { class: "bi bi-building" })
                    // "Lihat"
                ),
            ]),
    }),
];

const refresh = () => paginateRef.value?.refetch();

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
            <h2 class="mb-0">List Riwayat</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-inputorder"
                url="/input?status=selesai"
                :columns="columns"
            />
            <!-- <paginate ref="paginateRef" id="table-inputorder" url="/input" :columns="columns"></paginate> -->
        </div>
    </div>
</template>
