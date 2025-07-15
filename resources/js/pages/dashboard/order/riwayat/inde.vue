<script setup lang="ts">
import { onMounted, ref, watch, type Ref } from "vue";
import { useDelete } from "@/libs/hooks";
// import Form from "./Form.vue"; 
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
import Swal from "sweetalert2";
import axios from "axios";
import { SMART_ALIGNMENT } from "element-plus/es/components/virtual-list/src/defaults";

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

interface Riwayat {
  id: number
  deskripsi: string
  created_at: string
}

// Interface untuk data yang sudah ditampilkan
interface RiwayatTampil {
  id: number
  riwayat: string
}

// Format tanggal ID
function formatDate(waktu: string | null | undefined): string {
  if (!waktu) return "-"; // atau bisa "Tanggal tidak tersedia"
  
  const date = new Date(waktu);
  if (isNaN(date.getTime())) return "-";

  return date.toLocaleString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
    timeZone: "Asia/Jakarta" 
  });
}
const riwayatList = ref<Riwayat[]>([])

// List yang sudah pernah ditampilkan
const riwayatTertampil = ref<RiwayatTampil[]>([])

// Fungsi untuk menampilkan riwayat di SweetAlert
const showRincian = (data: any) => {
  // ⛔ Reset data lama
 console.log(Array.isArray(data.riwayat)); // Harusnya true


  riwayatTertampil.value = [];

  const newItems = data.riwayat.map((item: any) => {
    const formattedText = `${item.deskripsi} (${formatDate(item.created_at)})`;
    return {
      id: item.id_riwayat,
      riwayat: formattedText
    };
  });

  // ⛔ Jangan cek alreadyShown — langsung push semua!
  newItems.forEach(item => {
    riwayatTertampil.value.push(item);
  });

  const htmlContent = riwayatTertampil.value
    .map((item, index) => `
      <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px dashed #ddd;">
        <strong>${index + 1}.</strong> ${item.riwayat}
      </div>
    `)
    .join('');

  Swal.fire({
    title: "Detail Riwayat",
    html: `<div style="text-align: left; padding: 10px;">${htmlContent}</div>`,
    confirmButtonText: "Tutup",
    width: 600,
  });
};


const showRincians = (data: Input) => {
  Swal.fire({
    // title: <strong>Detail Input</strong>,
    title: "Detail Riwayat",

    html: `
    <div style="text-align: left;">
        <p><b>Asal Provinsi :</b> ${data.asal_provinsi.name || '-'}</p>
        <p><b>Asal Kota :</b> ${data.asal_kota.name || '-'}</p>
        <p><b>No. Telpon Pengirim :</b> ${data.no_telp_pengirim}</p>
        <p><b>Tujuan Provinsi :</b> ${data.tujuan_provinsi.name || '-'}</p>
        <p><b>Tujuan Kota :</b> ${data.tujuan_kota.name|| '-'}</p>
        <p><b>No. Telpon Penerima :</b> ${data.no_telp_penerima || '-'}</p>
        <p><b>Jenis Barang :</b> ${data.jenis_barang || '-'}</p>
        <p><b>Ekspedisi :</b> ${data.ekspedisi || '-'}</p>
        <p><b>Berat Barang :</b> ${data.berat_barang || '-'}</p>
        <p><b>Jenis Layanan :</b> ${data.jenis_layanan || '-'}</p>
      </div>
    `,
    confirmButtonText: "Tutup",
  });
};
// const lihatPenilaian = (data: Input) => {
//   Swal.fire({
//     // title: <strong>Detail Input</strong>,
//     title: "Detail Penilaian",

//     html: `
//     <div style="text-align: left;">
//         <p><b>Rating :</b> ${data.rating || '-'}</p>
//         <p><b>Ulasan :</b> ${data.ulasan || '-'}</p>
//       </div>
//     `,
//     confirmButtonText: "Tutup",
//   });
// };
const lihatPenilaian = (data: Input) => {
  Swal.fire({
    title: "Detail Penilaian",
    html: `
      <div style="text-align: left;">
        <p><b>Rating :</b> ${data.rating || '-'}</p>
        <p><b>Ulasan :</b> ${data.ulasan || '-'}</p>
      </div>
    `,
    confirmButtonText: "Tutup",
  });
};

// Kolom tabel
const columns = [
      column.accessor("no", { header: "No" }),
    column.accessor("nama_pengirim", { header: "Nama Pengirim" }),
    column.accessor("alamat_pengirim", { header: "Alamat Pengirim" }),
    column.accessor("nama_penerima", { header: "Nama Penerima" }),
    column.accessor("alamat_penerima", { header: "Alamat Penerima" }),
    
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

    column.display({
  id: "riwayat",
  header: "Riwayat",
  cell: (cell) => {
    console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
    return h(
      "button",
      {
        class: "btn btn-sm btn-warning",
        onClick: () => showRincian(cell.row.original),
      },
      "Lihat Riwayat"
    );
  },
}),

      column.display({
    id: "order",
    header: "Detail Order",
    cell: (cell) => {
      console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
      return h(
        "button",
        {
          class: "btn btn-sm btn-danger",
          onClick: () => showRincians(cell.row.original),
        },
        "Lihat Detail"
      );
    },
  }),
column.display({
  id: "penilaian",
  header: "Penilaian",
  cell: (cell) => {
    const row = cell.row.original;

    // Penilaian dianggap ada jika ada rating atau ulasan
    const hasPenilaian =
      (typeof row.rating === "number" && row.rating > 0) ||
      (typeof row.ulasan === "string" && row.ulasan.trim() !== "");

    return hasPenilaian
      ? h(
          "button",
          {
            class: "btn btn-sm btn-secondary",
            onClick: () => lihatPenilaian(row),
          },
          "Lihat Penilaian"
        )
      : h("span", { class: "text-muted fst-italic" }, "Belum ada penilaian");
  },
}),

// column.display({
// id: "penilaian",
// header: "Penilaian",
// cell: (cell) => {
// console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
// return h(
//   "button",
//   {
//     class: "btn btn-sm btn-secondary",
//     onClick: () => lihatPenilaian(cell.row.original),
//   },
//   "Lihat Penilaian"
// );
// },
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
            <h2 class="mb-0">Riwayat Pengiriman</h2>
        </div>

         <div class="card-body">
        <paginate ref="paginateRef" id="table-inputorder" url="/input?status=selesai"
        :columns="columns"/>
      <!-- <paginate ref="paginateRef" id="table-inputorder" url="/input" :columns="columns"></paginate> -->
    </div>
    </div>
</template>

<style scoped>
.btn {
    margin-top: 1rem;
    padding: 0.5rem 1.5rem;
}
</style>
