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
  column.accessor("no", { header: "No" }),
  // column.accessor("nama", { header: "Nama" }),
  column.accessor("phone", { header: "Kontak" }),
  // column.accessor("email", { header: "Email" }),
  column.accessor("alamat", { header: "Alamat" }),
  // column.accessor("riwayat_pengiriman", { header: "Riwayat Pengiriman" }),
  column.accessor("keluhan", { header: "Keluhan" }),
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
                        onClick: () => deletePelanggan(`/pelanggans/${cell.getValue()}`),
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
      <paginate ref="paginateRef" id="table-Pelanggan" url="/pelanggan" :columns="columns"></paginate>
    </div>
  </div>
</template>
