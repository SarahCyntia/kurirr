iy
<script setup lang="ts">
import { ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
// import { toast } from "vue3-toastify/index";
import axios from "axios";
import { saveAs } from 'file-saver';
import { onMounted } from "vue";
import Swal from "sweetalert2";
import JsBarcode from "jsbarcode";
// import { Row } from "element-plus/es/components/table-v2/src/components";

// Referensi dan variabel
const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

// Delete handler
const { delete: deleteInput } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

const bayar = async (rowData) => {
  if (rowData.isPaying) return;

  rowData.isPaying = true;

  const draft = {
    provinceDestination: rowData.provinceDestination,
    cityDestination: rowData.cityDestination,
    alamat_tujuan: rowData.alamat_tujuan,

    total_harga: rowData.total_harga,
    provinceOrigin: rowData.provinceOrigin,
    cityOrigin: rowData.cityOrigin,
    alamat_asal: rowData.alamat_asal,

    selectedService: rowData.selectedService,
    berat_barang: rowData.berat_barang,
    biaya: rowData.biaya,

    id: rowData.id,
  };

  sessionStorage.setItem("draftTransaksi", JSON.stringify(draft));

  try {
    const res = await axios.post("/api/payment", draft);
    // const res = await axios.post("/payment/create", draft);
    const { snap_token } = res.data;

    if (!snap_token) throw new Error("Token pembayaran tidak ditemukan.");

    window.snap.pay(snap_token, {
      onSuccess: function (result) {
        console.log("✅ Pembayaran berhasil:", result);
        rowData.status = "paid";
      },
      onPending: function (result) {
        console.log("⏳ Menunggu pembayaran:", result);
        rowData.status = "pending";
      },
      onError: function (result) {
        console.error("❌ Pembayaran gagal:", result);
      },
      onClose: function () {
        console.log("❎ Popup ditutup");
      },
    });
  } catch (err) {
    console.error("❌ Terjadi kesalahan:", err);
  } finally {
    rowData.isPaying = false;
  }
};
const getPembayaranBadgeClass = (status: string | undefined) => {
    const statusMap: Record<string, string> = {
        settlement: "badge bg-success fw-bold",
        pending: "badge bg-warning text-dark fw-bold",
        expire: "badge bg-secondary fw-bold",
        cancel: "badge bg-dark fw-bold",
        deny: "badge bg-danger fw-bold",
        failure: "badge bg-danger fw-bold",
        refund: "badge bg-info text-dark fw-bold",
    };
    return statusMap[status?.toLowerCase() ?? ""] || "badge bg-secondary fw-bold";
};

const redirectToPayment = async (id: number) => {
    try {
        const { data } = await axios.get(`/payment/token/${id}`);
        const snapToken = data.snap_token;

        if (!snapToken) {
            Swal.fire({ icon: 'error', title: 'Token Tidak Tersedia' });
            return;
        }

        if (typeof window.snap === 'undefined') {
            Swal.fire({ icon: 'error', title: 'Snap Belum Siap' });
            return;
        }

        window.snap.pay(snapToken, {
            onSuccess: async (result: any) => {
                await axios.post('/manual-update-status', {
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    payment_type: result.payment_type
                });
                // Swal.fire({ icon: 'success', title: 'Pembayaran Berhasil' }).then(() => {
                //   refresh();
                //   });
                Swal.fire({ icon: 'success', title: 'Pembayaran Berhasil' }).then(
                    refresh()
                );
            },
            onPending: async (result: any) => {
                await axios.post('/manual-update-status', {
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    payment_type: result.payment_type
                });
                Swal.fire({ icon: 'info', title: 'Menunggu Pembayaran' });
            },
            onError: () => {
                Swal.fire({ icon: 'error', title: 'Pembayaran Gagal' });
            },
            onClose: () => {
                Swal.fire({ icon: 'warning', title: 'Dibatalkan' });
            }
        });
    } catch (error) {
        console.error("❌ Gagal ambil token:", error);
        Swal.fire({ icon: 'error', title: 'Error mengambil token' });
    }
};

const downloadReceipt = async (noResi: string) => {
  const response = await axios.get(`/download-resi/${noResi}`, {
    responseType: 'blob'
  });

  saveAs(response.data, `struk-${noResi}.pdf`);
};

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
  column.accessor("berat_barang", { header: "Berat Barang" }),
  column.accessor("ekspedisi", { header: "Ekspedisi" }),
  column.accessor("jenis_layanan", { header: "Jenis Layanan" }),
  column.accessor("no_resi", { header: "No Resi" }),
  column.accessor("biaya", { header: "Biaya" }),
  column.accessor("status", {
  header: "Status",
  cell: ({ row }) => {
  return h(
    "button",
    {
      class: "badge bg-secondary",
      onClick: () => updateStatus(row),
      style: "cursor: pointer; border: none; background: none;",
    },
    row.original.status
  );
}
}),

 column.display({
  id: "aksi",
  header: "Struk",
  cell: ({ row }) => {
    const noResi = row.original.no_resi;

    return h("div", { class: "d-flex gap-2" }, [
    
      h(
        "button",
        {
          class: "btn btn-sm btn-secondary",
          onClick: () => downloadReceipt(noResi),
          title: "Download PDF"
        },
        [
          h("i", { class: "la la-download me-1" }),
          "Download"
        ]
      )
    ]);
  },
}),


column.display({
  id: "redirectToPayment",
  header: "Aksi",
  cell: (cell) => {
    const row = cell.row.original;
    const status = row.status_pembayaran?.toLowerCase();
    const buttons = [];

    // Tampilkan tombol Bayar kalau belum ada payment_at dan belum settlement
    if (!row.payment_at && status !== 'settlement') {
      buttons.push(
        h(
          "button",
          {
            class: "btn btn-sm btn-success me-1",
            onClick: () => redirectToPayment(row.id),
          },
          [h("i", { class: "bi bi-credit-card me-1" }), "Bayar"]
        )
      );
    }

    return h("div", { class: "d-flex gap-1" }, buttons);
  }
}),

column.accessor("status_pembayaran", {
        header: "Pembayaran",
        cell: (cell) => {
            const status = cell.getValue()?.toLowerCase();
            const statusMap: Record<string, { label: string; class: string }> = {
                settlement: { label: "settlement", class: "badge bg-success fw-bold" },
                pending: { label: "Pending", class: "badge bg-warning text-dark fw-bold" },
                expire: { label: "expire", class: "badge bg-secondary fw-bold" },
                failure: { label: "cancel", class: "badge bg-danger fw-bold" },
                refund: { label: "Refund", class: "badge bg-info text-dark fw-bold" },
            };

            const { label, class: badgeClass } = statusMap[status] || {
                label: status ?? "Tidak Diketahui",
                class: "badge bg-secondary fw-bold"
            };

            return h("span", { class: badgeClass }, label);
        }
    }),
];

// Fungsi bayar yang sudah diperbaiki

onMounted(() => {
    if (!window.snap) {
        const script = document.createElement("script");
        script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
        script.setAttribute("data-client-key", "SB-Mid-client-XXXXX"); // ganti sesuai client key kamu
        script.async = true;
        document.body.appendChild(script);
    }
});
// Untuk reload data
const refresh = () => paginateRef.value?.refetch();


// const barcodeElement = document.getElementById("barcode");
//             if (barcodeElement && data.noResi) {
//                 JsBarcode(barcodeElement, data.noResi, {
//                     format: "CODE128",
//                     width: 2,
//                     height: 60,
//                     displayValue: true,
//                     fontSize: 14,
//                     margin: 2,
//                 });
//               }



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
      <h2 class="mb-0">List Order</h2>
      <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah <i class="la la-plus"></i>
      </button>
    </div>

    <div class="card-body">
      <paginate ref="paginateRef" id="table-inputorder" url="/input?status=menunggu" :columns="columns" />
    </div>
  </div>
</template>

<style scoped>
.btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
}
</style>
