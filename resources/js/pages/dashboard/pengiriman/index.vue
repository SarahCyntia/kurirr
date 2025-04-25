<script setup lang="ts">
import { h, ref, watch, computed } from "vue";
import { useDelete, useUpdate } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
// import Form from "./Form.vue";
import Swal from "sweetalert2"; // Konfirmasi perubahan status
import type { Pengiriman } from "@/types";

const column = createColumnHelper<pengiriman>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const searchQuery = ref<string>(""); // Pencarian pengiriman
const filterStatus = ref<string>(""); // Filter status

const { delete: deletepengiriman } = useDelete({
  onSuccess: () => paginateRef.value.refetch(),
});

const { update: updateStatus } = useUpdate({
  onSuccess: () => paginateRef.value.refetch(),
});

// Opsi status pengiriman
const statusOptions = ["", "Baru", "Dalam Proses", "Selesai"];

const updateOrderStatus = async (uuid: string, newStatus: string) => {
  const result = await Swal.fire({
    title: "Konfirmasi Perubahan",
    text: `Apakah Anda yakin ingin mengubah status menjadi "${newStatus}"?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Ubah",
    cancelButtonText: "Batalkan",
  });

  if (result.isConfirmed) {
    updateStatus(`/pengiriman/${uuid}`, { status: newStatus });
  }
};

// Menyusun URL untuk pencarian dan filter
const getFilterUrl = computed(() => {
  let url = "pengiriman?";
  if (searchQuery.value) url += `search=${searchQuery.value}&`;
  if (filterStatus.value) url += `status=${filterStatus.value}&`;
  return url;
});

const columns = [
  column.accessor("no", { header: "No" }),
  column.accessor("nama_pelanggan", { header: "Nama Pelanggan" }),
  column.accessor("produk", { header: "Produk" }),
  column.accessor("tanggal_pesan", { header: "Tanggal Pesan" }),
  // column.accessor("pengirim", { header: "Pengirim" }),
  // column.accessor("penerima", { header: "Penerima" }),
  // column.accessor("alamat_pengiriman", { header: "Alamat Pengiriman" }),
  column.accessor("total_harga", { header: "Total Harga" }),
  column.accessor("status", {
  header: "Status",
  cell: (cell) =>
    h(
      "span",
      {
        class: `badge ${cell.getValue() === "Dikemas" ? "bg-success" : "bg-primary"}`,
      },
      cell.getValue()
    ),
}),

  // column.accessor("status", {
  //   header: "Status",
  //   cell: (cell) => {
  //     const value = ref(cell.getValue());

  //     return h(
  //       "select",
  //       {
  //         class: "form-select form-select-sm",
  //         value: value.value,
  //         onChange: (event: Event) => {
  //           const newStatus = (event.target as HTMLSelectElement).value;
  //           updateOrderStatus(cell.row.original.uuid, newStatus);
  //         },
  //       },
  //       statusOptions.map((option) =>
  //         h("option", { value: option, selected: option === value.value }, option)
  //       )
  //     );
  //   },
  // }),
  column.accessor("uuid", {
    header: "",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-info",
            title: "Lihat Detail pengiriman",
            onClick: () => {
              selected.value = cell.getValue();
              openForm.value = true;
            },
          },
          h("i", { class: "bi bi-eye fs-2" })
        ),
        // h(
        //   "button",
        //   {
        //     class: "btn btn-sm btn-icon btn-danger",
        //     onClick: () => deletepengiriman(`/pengiriman/${cell.getValue()}`),
        //   },
        //   h("i", { class: "la la-trash fs-2" })
        // ),
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
      <h2 class="mb-0">Daftar pengiriman</h2>
      <!-- <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah pengiriman
        <i class="la la-plus"></i>
      </button> -->
    </div>

    <!-- Pencarian & Filter -->
    <!-- <div class="card-body pb-0">
      <div class="row g-2">
        <div class="col-md-6">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Cari pengiriman berdasarkan nama atau produk..."
            @input="paginateRef.value.fetch(getFilterUrl)"
          />
        </div>
        <div class="col-md-4">
          <select
            v-model="filterStatus"
            class="form-select"
            @change="paginateRef.value.fetch(getFilterUrl)"
          >
            <option value="">Semua Status</option>
            <option v-for="option in statusOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-secondary w-100" @click="refresh">
            Cari
          </button>
        </div>
      </div>
    </div> -->

    <div class="card-body">
      <paginate ref="paginateRef" id="table-pengiriman" url="pengiriman" :columns="columns"></paginate>
    </div>
  </div>
</template>
