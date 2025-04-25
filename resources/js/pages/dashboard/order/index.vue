<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { order } from "@/types";

const column = createColumnHelper<order>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteorder } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("nama_pelanggan", {
        header: "Nama Pelanggan",
    }),
    column.accessor("produk", {
        header: "Produk",
    }),
    column.accessor("total_harga", {
        header: "Total Harga",
    }),
    column.accessor("pengirim", {
        header: "Pengirim",
    }),
    column.accessor("penerima", {
        header: "Penerima",
    }),
    column.accessor("alamat", {
        header: "Alamat",
    }),
    column.accessor("status", {
        header: "Status",
    }),
    column.accessor("uuid", {
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
                        onClick: () =>
                            deleteorder(`/order/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
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
    <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Order</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="table-users" url="/master/users" :columns="columns"></paginate>
        </div>
    </div>
</template>
