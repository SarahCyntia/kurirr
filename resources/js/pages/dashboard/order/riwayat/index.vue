<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
// import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import Swal from "sweetalert2";
import type { Input } from "@/types";

const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteInput } = useDelete({
  onSuccess: () => refresh(),
});

// const showRincian = (data: Input) => {
//   alert(`
//     No Resi: ${data.no_resi}
//     Paket: ${data.paket}
//     Kurir: ${data.name}
//     Penerima: ${data.penerima}
//     Alamat: ${data.alamat}
//     Tanggal Input: ${data.tanggal_Input}
//     Tanggal Penerimaan: ${data.tanggal_penerimaan || "-"}
//     Status: ${data.status}
//   `);
// };
const showRincian = (data: Input) => {
  Swal.fire({
    title: <strong>Detail Input</strong>,
    html: `
      <div style="text-align: left;">
        <p><b>No Resi:</b> ${data.nama_barang}</p>
        <p><b>Paket:</b> ${data.alamat_asal}</p>
        <p><b>Kurir:</b> ${data.alamat_tujuan}</p>
        <p><b>Penerima:</b> ${data.penerima}</p>
        <p><b>Alamat:</b> ${data.penerima}</p>
        <p><b>Berat Paket:</b> ${data.berat_paket}</p>
        <p><b>Biaya Pengiriman:</b> ${data.biaya_pengiriman}</p>
        <p><b>Metode Pengiriman:</b> ${data.metode_pengiriman}</p>
        <p><b>Tanggal Dibuat:</b> ${data.Tanggal_dibuat || "-"}</p>
        <p><b>Tanggal Input:</b> ${data.tanggal_Input || "-"}</p>
        <p><b>Tanggal Penerimaan:</b> ${data.tanggal_penerimaan || "-"}</p>
        <p><b>Status:</b> ${data.status}</p>
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
  column.accessor("no", { header: "#" }),
  column.accessor("nama_barang", { header: "Nama Barang" }),
  column.accessor("alamat_asal", { header: "Alamat asal" }),
  column.display({
    id: "rincian",
    header: "Detail Input",
    cell: (cell) =>
      h(
        "button",
        {
          class: "btn btn-sm btn-info",
          onClick: () => showRincian(cell.row.original),
        },
        "Lihat Detail"
      ),
  }),
  //   column.display({
  //   id: "rincian",
  //   header: "Tracking",
  //   cell: (cell) =>
  //     h(
  //       resolveComponent("RouterLink"),
  //       {
  //         to: /dashboard/tracking?resi=${cell.row.original.no_resi},
  //         class: "btn btn-sm btn-info",
  //       },
  //       () => "Tracking"
  //     ),
  // }),
  column.accessor("id", {
    header: "Aksi",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: "btn btn-sm btn-danger",
            onClick: () => {
              deleteInput(/Input/${cell.getValue()});
            },
          },
          h("i", { class: "la la-trash fs-2" })
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
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Input</h2>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-Input" url="/Input" :columns="columns"></paginate>
    </div>
  </div>
</template>