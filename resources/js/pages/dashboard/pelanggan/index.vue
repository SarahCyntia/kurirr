<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete, useUpdate } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Pelanggan } from "@/types"; // Ubah Pelanggan menjadi Pelanggan agar sesuai dengan struktur baru

const column = createColumnHelper<Pelanggan>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const PelangganData = ref<Pelanggan | null>(null);

const { delete: deletePelanggan } = useDelete({
  onSuccess: () => paginateRef.value.refetch(),
});

// const { update: updateFeedback } = useUpdate({
//   onSuccess: () => paginateRef.value.refetch(),
// });

const columns = [
  // column.accessor("rating", {
  //   header: "Rating",
  //   cell: (cell) => {
  //     const rating = cell.getValue();
  //     return rating 
  //     ? h("span", {class: "fw-bold text-warning"},
  //       "*".repeat(rating))//menampilkan bingtang
  //       :"belum ada rating"
  //   },
  // }),

  // column.accessor("rating", {
  //   header: "Rating",
  //   cell: (cell) => {
  //     const value = ref(cell.getValue());
  //     const updateRating = (event: Event) => {
  //       const newRating = (event.target as HTMLSelectElement).value;
  //       value.value = newRating;
  //       updateFeedback(`/Pelanggan/${cell.row.original.id}`, { rating: newRating });
  //     };

  //     return h(
  //       "select",
  //       {
  //         class: "form-select form-select-sm",
  //         value: value.value,
  //         onChange: updateRating,
  //       },
  //       [1, 2, 3, 4, 5].map((option) =>
  //         h("option", { value: option, selected: option === value.value }, option)
  //       )
  //     );
  //   },
  // }),
  column.accessor("no", { header: "No" }),
    column.accessor("user.name", { header: "Nama" }),
    column.accessor("user.phone", { header: "No. Telp" }),
    column.accessor("user.photo", {
        header: "Foto",
        cell: (cell) =>
            h("img", {
                src: cell.getValue() ? `/storage/${cell.getValue()}` : "/img/default.png",
                alt: "Foto Pelanggan",
                class: "img-thumbnail",
                style: "width: 50px; height: 50px;",
            }),
    }),
    column.accessor("user.email", { header: "Email" }),
    column.accessor("alamat", { header: "Alamat" }),
    // column.accessor("keluhan", { header: "Keluhan" }),
    // column.accessor("orderan", { header: "Orderan" }),
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
                        onClick: () => deletePelanggan(`/pelanggan/${cell.getValue()}`),
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
      <h2 class="mb-0">Daftar Pelanggan</h2>
      <!-- <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah Pelanggan
        <i class="la la-plus"></i>
      </button> -->
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-pelanggan" url="/pelanggan" :columns="columns"></paginate>
    </div>
  </div>
</template>
