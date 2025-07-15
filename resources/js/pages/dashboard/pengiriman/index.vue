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


  const antarPaket = async (data: Input) => {
  const { isConfirmed } = await Swal.fire({
    title: "Konfirmasi",
    text: `Apakah Anda yakin ingin mengantar paket ini?`,
    icon: "info",
    showCancelButton: true,
    confirmButtonText: "Ya, Antar",
  });

  if (isConfirmed) {
    try {
      await axios.post(`/pengantaran`, {
        input_id: data.id,
        deskripsi: "Paket dalam proses pengantaran",
      });

      Swal.fire("Berhasil", "Paket sedang diantar.", "success");
      refresh();
    } catch (error) {
      Swal.fire("Gagal", "Terjadi kesalahan saat mengantar paket.", "error");
    }
  }
};

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
        <p><b>Asal Provinsi :</b> ${data.asal_provinsi_id || '-'}</p>
        <p><b>Asal Kota :</b> ${data.asal_kota_id || '-'}</p>
        <p><b>No. Telpon Pengirim :</b> ${data.no_telp_pengirim}</p>
        <p><b>Tujuan Provinsi :</b> ${data.tujuan_provinsi_id || '-'}</p>
        <p><b>Tujuan Kota :</b> ${data.tujuan_kota_id || '-'}</p>
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

// const masukGudang = async (data: Input) => {
//   const { isConfirmed } = await Swal.fire({
//     title: "Konfirmasi",
//     text: `Apakah Anda yakin ingin memasukkan paket ke gudang?`,
//     icon: "question",
//     showCancelButton: true,
//     confirmButtonText: "Ya, Masukkan",
//   });

//   if (isConfirmed) {
//     try {
//       await axios.post(`/gudang/masuk`, {
//         input_id: data.id,
//         deskripsi: "Paket masuk gudang",
//       });

//       Swal.fire("Berhasil", "Paket berhasil dimasukkan ke gudang.", "success");
//       refresh();
//     } catch (error) {
//       Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan.", "error");
//     }
//   }
// };

const keluarGudang = async (data: Input) => {
  const { isConfirmed } = await Swal.fire({
    title: "Konfirmasi",
    text: `Apakah Anda yakin ingin mengeluarkan paket dari gudang?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Keluarkan",
  });

  if (isConfirmed) {
    try {
      await axios.post(`/gudang/keluar`, {
        input_id: data.id,
        deskripsi: "Paket keluar dari gudang",
      });

      Swal.fire("Berhasil", "Paket berhasil dikeluarkan dari gudang.", "success");
      refresh();
    } catch (error) {
      Swal.fire("Gagal", "Terjadi kesalahan saat menyimpan.", "error");
    }
  }
};

// Kolom tabel
const columns = [
  column.accessor("no", { header: "No" }),
  column.accessor("nama_pengirim", { header: "Nama Pengirim" }),
  column.accessor("alamat_pengirim", { header: "Alamat Pengirim" }),
  column.accessor("nama_penerima", { header: "Nama Penerima" }),
  column.accessor("alamat_penerima", { header: "Alamat Penerima" }),
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
                  : "Masuk Gudang";

      return h("span", { class: `badge ${badgeClass}` }, label);
    },
  }),

  column.display({
    id: "riwayat",
    header: "Riwayat",
    cell: (cell) => {
      const row = cell.row.original;
      const hasRiwayat = row.riwayat && row.riwayat.length > 0;

      return hasRiwayat
        ? h(
          "button",
          {
            class: "btn btn-sm btn-warning",
            onClick: () => showRincian(row),
          },
          "Detail Riwayat"
        )
        : h("span", { class: "text-muted fst-italic" }, "Belum ada riwayat");
    },
  }),
  //     column.display({
  //   id: "riwayat",
  //   header: "Riwayat",
  //   cell: (cell) => {
  //     console.log("ROW ORIGINAL:", cell.row.original); // 🔍 Debug log
  //     return h(
  //       "button",
  //       {
  //         class: "btn btn-sm btn-warning",
  //         onClick: () => showRincian(cell.row.original),
  //       },
  //       "Lihat Riwayat"
  //     );
  //   },
  // }),

  column.accessor("id", {
    header: "Detail",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-danger",
            onClick: () => showRincians(cell.row.original),
          },
          h("i", { class: "bi bi-building" })
          // "Lihat"
        ),
      ]),
  }),
  
column.display({
  id: "aksiGudang",
  header: "Aksi Gudang",
  cell: (cell) => {
    const row = cell.row.original;
    
    // Jika sudah keluar gudang (berdasarkan flag keluar_gudang atau riwayat)
    if (row.keluar_gudang === true || row.sudah_keluar_gudang) {
      return h("div", { class: "d-flex align-items-center justify-content-center" }, [
        h("span", { 
          class: "text-success fs-5 d-flex align-items-center gap-1",
          title: "Paket sudah keluar dari gudang" 
        }, [
          h("i", { class: "bi bi-check-circle-fill" }),
          "Keluar Gudang"
        ])
      ]);
    }
    
    // Jika belum keluar gudang, tampilkan tombol
    return h("div", { class: "d-flex flex-wrap gap-2" }, [
      h(
        "button",
        {
          class: "btn btn-sm btn-secondary",
          onClick: () => keluarGudang(row),
        },
        "Keluar Gudang"
      ),
    ]);
  },
}),

// column.display({
//   id: "aksiGudang",
//   header: "Aksi Gudang",
//   cell: (cell) => {
//     const row = cell.row.original;

//     if (!isGudang.value) {
//       return h("span", { class: "text-muted fst-italic" }, "Hanya untuk Gudang");
//     }

//     return h("div", { class: "d-flex flex-wrap gap-2" }, [
//       h(
//         "button",
//         {
//           class: "btn btn-sm btn-success",
//           onClick: () => masukGudang(row),
//         },
//         "Masuk Gudang"
//       ),
//       h(
//         "button",
//         {
//           class: "btn btn-sm btn-secondary",
//           onClick: () => keluarGudang(row),
//         },
//         "Keluar Gudang"
//       ),
//     ]);
//   },
// }),


];
const submit = async () => {
  await axios.put(`/orderan/${props.selected}`, formData.value);
  emit("refresh");
  emit("close");
};

// Untuk reload data
const refresh = () => paginateRef.value?.refetch(); const props = defineProps<{ selected: string }>();
const emit = defineEmits(["close", "refresh"]);

const formData = ref<any>({});
const isLoading = ref(false);

onMounted(async () => {
  if (props.selected) {
    isLoading.value = true;
    const { data } = await axios.get(`/orderan/${props.selected}`);
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
  <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />

  <!-- Card List -->
  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">Pengiriman</h2>
    </div>

    <!-- <div class="card-body">
      <paginate ref="paginateRef" id="table-inputorder" url="/input" :columns="columns" />
    </div> -->
     <!-- <div class="card-body">
        <paginate ref="paginateRef" id="table-inputorder" url="/input?aksi=keluar"
        :columns="columns"/>
    </div> -->
    <paginate ref="paginateRef" id="table-inputorder" url="/input?status=Masuk gudang" :columns="columns" />
  </div>
</template>

<style scoped>
.btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
}
</style>