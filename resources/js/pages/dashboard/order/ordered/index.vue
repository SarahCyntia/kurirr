<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

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


// Delete handler
const { delete: deleteInput } = useDelete({
    onSuccess: () => paginateRef.value?.refetch(),
});

const showRincian = (data: Input) => {
    Swal.fire({
        title: "Detail Riwayat",
        html: `
            <div style="text-align: left; padding: 20px 20px">
                <p><b>Riwayat Pengiriman:</b> ${data.riwayat_pengiriman || '-'} <button id="editBtn" style="
                    margin-top: 10px;
                    padding: 6px 12px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">Edit</button></p>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: "Tutup",
        didOpen: () => {
            const editBtn = document.getElementById("editBtn");
            if (editBtn) {
                editBtn.addEventListener("click", () => {
                    Swal.fire("Edit ditekan!", "Fungsi edit bisa dipanggil di sini.", "info");
                    // Tambahkan aksi edit di sini, misalnya buka form input baru
                });
            }
        }
    });
};

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
    
//     column.accessor("riwayat_pengiriman", {
//     header: "Riwayat Pengiriman",
//     cell: (cell) => {
//         const riwayat = cell.getValue();

//         return h(
//             "button",
//             {
//                 class: "btn btn-sm btn-danger",
//                 onClick: () => {
//                     Swal.fire({
//                         title: "Riwayat Pengiriman",
//                         html: Array.isArray(riwayat)
//                             ? `<ul style="text-align: left;">${riwayat
//                                   .map((item: string) => `<li>${item}</li>`)
//                                   .join("")}</ul>`
//                             : `<p>${riwayat || "Belum ada riwayat."}</p>`,
//                         icon: "info",
//                         confirmButtonText: "Tutup",
//                     });
//                 },
//             },
//             "Lihat Detail"
//         );  
//     },
// }),

    column.accessor("no_resi", { header: "No Resi" }),
    column.accessor("riwayat", { header: "Riwayat" }),
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
        header: "Order",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status;
            const label = status === "dalam proses" ? "Tambah" : "Antar";

            return h(
                "button",
                {
                    class: "btn btn-sm btn-info",
                    onClick: async () => {
                        selected.value = cell.getValue();

                        if (status !== "dalam proses") {
                            // Konfirmasi sebelum lanjut
                            const result = await Swal.fire({
                                title: "Ambil Orderan Ini?",
                                text: "Yakin ingin mengambil orderan ini untuk dikirim?",
                                icon: "question",
                                showCancelButton: true,
                                confirmButtonText: "Ya, Ambil",
                                cancelButtonText: "Batal",
                            });

                            if (!result.isConfirmed) return;

                            // Tampilkan notifikasi berhasil
                            await Swal.fire({
                                title: "Orderan Diambil",
                                // text: "Silakan lanjutkan proses pengiriman.",
                                icon: "success",
                                confirmButtonText: "OK", // Tambahkan tombol OK
                                showConfirmButton: true, // Pastikan tombol OK tampil
                            });
                        }

                        // Buka form
                        openForm.value = true;
                    },
                },
                label
            );
        },
    }),
    column.accessor("waktu", { header: "Waktu" }),

    // column.display({
    //     id: "rincian",
    //     header: "Riwayat",
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
