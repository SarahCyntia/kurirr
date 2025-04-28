<script setup lang="ts">
import { h, ref, watch, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Kurir } from "@/types";
import axios from "axios"; // Pastikan axios sudah terinstall

const column = createColumnHelper<Kurir>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const kurirData = ref<Kurir | null>(null); // Data kurir yang terkait dengan user login

const { delete: deleteKurir } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", { header: "No" }),
    column.accessor("user.name", { header: "Nama" }),
    column.accessor("user.email", { header: "Email" }),
    column.accessor("user.phone", { header: "No. Telp" }),
    // column.accessor("status", { header: "Status" }),
    column.accessor("jenis_kendaraan", { header: "Jenis Kendaraan" }),
    column.accessor("alamat", { header: "Alamat" }),
    column.accessor("penilaian", { header: "Penilaian" }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) =>
        h(
            "span",
            {
          class: `badge ${cell.getValue() === "aktif" ? "bg-primary" : "bg-warning"}`,
        },
        cell.getValue() === "aktif" ? "Aktif" : "Nonaktif"
    ),
}),
column.accessor("user.photo", {
    //     header: "Foto Profil",
    //     cell: (cell) =>
    //         cell.getValue()
    //             ? h("img", {
    //                   src: `/storage/${cell.getValue()}`,
    //                   alt: "Foto Kurir",
    //                   style: "width: 50px; height: 50px; border-radius: 8px;",
    //               })
    //             : "Tidak ada foto",
    // }),

    header: "Foto",
    cell: (cell) => {
        const val = cell.getValue();
        console.log("Photo path:", val);
        return h("img", {
            src: val ? `/storage/${val}` : "/img/default.png",
            alt: "Foto Kurir",
            class: "img-thumbnail",
            style: "width: 50px; height: 50px;",
        });
    },
}),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () => deleteKurir(`kurir/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

// Fungsi untuk mengambil data kurir berdasarkan pengguna yang login
// const fetchKurirData = async () => {
//     try {
//         const response = await axios.get("/kurir/user");
//         kurirData.value = response.data;
//     } catch (error) {
//         console.error("Failed to fetch kurir data", error);
//     }
// };

// onMounted(() => {
//     fetchKurirData(); // Ambil data saat komponen dimuat
// });

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
            <h2 class="mb-0">Data Kurir</h2>
            <!-- <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
              Tambah
              <i class="la la-plus"></i>
          </button> -->
        </div>
        <div class="card-body">
            <!-- <p v-if="kurirData">Data Kurir: {{ kurirData }}</p> -->
            <paginate
                ref="paginateRef"
                id="table-kurir"
                url="kurir"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
