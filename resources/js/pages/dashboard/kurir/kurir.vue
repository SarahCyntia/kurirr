<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
// import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteUser } = useDelete({
  onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
  column.accessor("no", {
    header: "No.",
  }),
  column.accessor("name", {
    header: "Nama",
  }),
  column.accessor("jk", {
    header: "Jenis Kelamin",
  }),
  column.accessor("phone", {
    header: "No. Telp",
  }),
  column.accessor("photo", {
  header: "Foto Profil",
  cell: (cell) =>
    cell.getValue()
      ? h("img", {
        src: `/storage/${cell.getValue()}`,
          alt: "Foto Kurir",
          style: "width: 50px; height: 50px; border-radius: 8px;",
        })
      : "Tidak ada foto",
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
              deleteUser(`/master/users/${cell.getValue()}`),
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
      <h2 class="mb-0">List Kurir</h2>
      <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah Kurir
        <i class="la la-plus"></i>
      </button>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-users" url="/master/users" :columns="columns"></paginate>
    </div>
  </div>
</template>
