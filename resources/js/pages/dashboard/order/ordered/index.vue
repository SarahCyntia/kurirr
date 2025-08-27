<script setup lang="ts">
import { computed, onMounted, ref, watch, nextTick } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper, type Row } from "@tanstack/vue-table";
import type { Input } from "@/types";
import { h } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

interface Riwayat {
  id: number;
  deskripsi: string;
  created_at: string;
}

interface RiwayatTampil {
  id: number;
  riwayat: string;
}

const inputData = ref<Input | null>(null);
const statusSteps = ["menunggu", "dalam proses", "masuk gudang", "keluar gudang", "diproses", "dikirim", "selesai"] as const;
const statusLabels = {
  menunggu: "Menunggu",
  "dalam proses": "Dalam Proses",
  "masuk gudang": "Masuk Gudang ",
  "keluar gudang": "Keluar Gudang",
  diproses: "Diproses",
  dikirim: "Dikirim",
  selesai: "Selesai"
};
const statusIcons = {
  menunggu: "info",
  "dalam proses": "question",
  "masuk gudang": "dark",
  "keluar gudang": "secondary",
  diproses: "muted",
  dikirim: "warning",
  selesai: "success"
};
const statusColors = {
  menunggu: "bg-info",
  "dalam proses": "bg-danger",
  "masuk gudang": "bg-danger",
  "keluar gudang": "bg-secondary",
  diproses: "bg-muted",
  dikirim: "bg-warning",
  selesai: "bg-success"
};

const changedButtons = ref<Set<number>>(new Set(JSON.parse(localStorage.getItem("changedButtons") || "[]")));
watch(changedButtons, (val) => {
  localStorage.setItem("changedButtons", JSON.stringify([...val]));
}, { deep: true });

const column = createColumnHelper<Input>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const formData = ref<any>({});
const isLoading = ref(false);
const riwayatTertampil = ref<RiwayatTampil[]>([]);

const formatDate = (waktu: string | null | undefined) => {
  if (!waktu) return "-";
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
};

const updateStatus = async (row: Row<Input>) => {
  const currentStatus = row.original.status;
  const currentIndex = statusSteps.indexOf(currentStatus);
  if (currentIndex === -1 || currentStatus === "selesai") return;

  const nextStatus = statusSteps[currentIndex + 1];

  const confirmed = await Swal.fire({
    icon: statusIcons[nextStatus],
    title: `Ubah Status ke \"${statusLabels[nextStatus]}\"?`,
    html: `Anda akan mengubah status ke <strong>${statusLabels[nextStatus]}</strong>.`,
    showCancelButton: true,
    confirmButtonText: "Ya, ubah"
  }).then(r => r.isConfirmed);

  if (!confirmed) return;

  try {
    await axios.put(`/ordered/${row.original.id}`, { status: nextStatus });

    Swal.fire("Berhasil", "Status diperbarui", "success");

    // ✅ Jangan ubah row.original langsung → tunggu refresh
    await refresh(); // Pastikan ini await agar data sync sebelum interaksi selanjutnya
    // await nextTick(); // Tunggu Vue untuk update DOM
  } catch {
    Swal.fire("Gagal", "Tidak bisa ubah status", "error");
  }
};

// const updateStatus = async (row: Row<Input>) => {
//   const currentStatus = row.original.status;
//   const currentIndex = statusSteps.indexOf(currentStatus);
//   if (currentIndex === -1 || currentStatus === "selesai") return;

//   const nextStatus = statusSteps[currentIndex + 1];
//   const confirmed = await Swal.fire({
//     icon: statusIcons[nextStatus],
//     title: `Ubah Status ke \"${statusLabels[nextStatus]}\"?`,
//     html: `Anda akan mengubah status ke <strong>${statusLabels[nextStatus]}</strong>.`,
//     showCancelButton: true,
//     confirmButtonText: "Ya, ubah"
//   }).then(r => r.isConfirmed);

//   if (!confirmed) return;
//   row.original.status = nextStatus;
//   try {
//     await axios.put(`/ordered/${row.original.id}`, { status: nextStatus });
//     Swal.fire("Berhasil", "Status diperbarui", "success");
//     refresh();
//   } catch {
//     row.original.status = currentStatus;
//     Swal.fire("Gagal", "Tidak bisa ubah status", "error");
//   }
// };

const showRincian = (data: any) => {
  riwayatTertampil.value = (data.riwayat || []).map((item: any) => ({
    id: item.id_riwayat,
    riwayat: `${item.deskripsi} (${formatDate(item.created_at)})`
  }));

  Swal.fire({
    title: "Detail Riwayat",
    html: `<div style='text-align:left;'>${riwayatTertampil.value.map((item, i) => `<div><b>${i + 1}.</b> ${item.riwayat}</div>`).join("")}</div>`,
    confirmButtonText: "Tutup",
    width: 600
  });
};


const masukGudang = async (data: Input) => {
  const { isConfirmed } = await Swal.fire({
    title: "Konfirmasi",
    text: `Yakin ingin memasukkan ke gudang?`,
    icon: "question",
    showCancelButton: true
  });

  if (isConfirmed) {
    try {
      await axios.post(`/gudang/masuk`, {
        input_id: data.id,
        // deskripsi: "Paket masuk gudang oleh petugas"
        deskripsi: "Paket masuk gudang oleh kurir"
      });
      Swal.fire("Berhasil", "Paket masuk gudang", "success");
      refresh();
    } catch {
      Swal.fire("Gagal", "Terjadi kesalahan", "error");
    }
  }
};

// const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// async function claimPackage(packageId: number) {
//   const confirmed = await Swal.fire({
//     title: 'Ambil Paket Ini?',
//     text: 'Yakin ingin mengambil paket ini?',
//     icon: 'question',
//     showCancelButton: true,
//     confirmButtonText: 'Ya',
//     cancelButtonText: 'Batal'
//   });

//   if (!confirmed.isConfirmed) return;

//   try {
//     const response = await fetch(`/api/input/${packageId}/claim`, {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//         'X-CSRF-TOKEN': token!,
//       },
//     });

//     const data = await response.json();

//     if (data.success) {
//       Swal.fire('Berhasil', data.message, 'success');
//       refresh(); // ✅ data akan diperbarui dari server
//     } else {
//       Swal.fire('Gagal', data.message, 'error');
//     }
//   } catch (error) {
//     console.error('Gagal mengambil paket', error);
//     Swal.fire('Error', 'Terjadi kesalahan saat mengambil paket.', 'error');
//   }
// }

const columns = [
  column.accessor("no", { header: "No" }),
  column.accessor("nama_pengirim", { header: "Nama Pengirim" }),
  column.accessor("alamat_pengirim", { header: "Alamat Pengirim" }),
  column.accessor("no_telp_pengirim", { header: "No. Telp Pengirim" }),
  column.accessor("nama_penerima", { header: "Nama Penerima" }),
  column.accessor("alamat_penerima", { header: "Alamat Penerima" }),
  column.accessor("no_telp_penerima", { header: "No. Telp Penerima" }),
  column.accessor("jenis_barang", { header: "Jenis Barang" }),
  column.accessor("ekspedisi", { header: "ekspedisi" }),
  column.accessor("berat_barang", { header: "Berat Barang" }),
  column.accessor("no_resi", { header: "No Resi" }),
  column.display({
    id: "riwayat",
    header: "Riwayat",
    cell: ({ row }) => row.original.riwayat?.length ? h("button", { class: "btn btn-sm btn-warning", onClick: () => showRincian(row.original) }, "Detail Riwayat") : h("span", { class: "text-muted fst-italic" }, "Belum ada riwayat")
  }),
  column.accessor("status", {
    header: "Status",
    cell: ({ row }) => h("button", {
      class: `badge ${statusColors[row.original.status] || "bg-secondary"}`,
      onClick: () => updateStatus(row),
      style: "cursor:pointer; border:none"
    }, row.original.status.charAt(0).toUpperCase() + row.original.status.slice(1))
  }),

  // column.accessor("id", {
  //   header: "Order",
  //   cell: ({ row }) => {
  //     const id = row.original.id;
  //     const isChanged = changedButtons.value.has(id);
  //     const label = isChanged ? "Tambah" : "Antar";

  //     if (row.original.status === "selesai") {
  //       return h("span", { class: "text-success fw-bold" }, "✓");
  //     }

  //     return h("button", {
  //       class: "btn btn-sm btn-info",
  //       onClick: async () => {
  //         selected.value = id;

  //         if (isChanged) {
  //           openForm.value = true;
  //         } else {
  //           const result = await Swal.fire({
  //             title: "Ambil Orderan Ini?",
  //             text: "Yakin ingin mengambil orderan ini untuk dikirim?",
  //             icon: "question",
  //             showCancelButton: true,
  //             confirmButtonText: "Ya",
  //             cancelButtonText: "Batal"
  //           });

  //           if (result.isConfirmed) {
  //             try {
  //               const { data } = await axios.post(`/input/${id}/claim`);
  //               if (data.success) {
  //                 await Swal.fire("Orderan Diambil", data.message, "success");
  //                 changedButtons.value.add(id);
  //                 refresh(); // ✅ refresh supaya data hilang dari kurir lain
  //               } else {
  //                 await Swal.fire("Gagal", data.message, "error");
  //               }
  //             } catch (error) {
  //               await Swal.fire("Error", "Gagal mengambil orderan", "error");
  //             }
  //           }
  //         }
  //       }
  //     }, label);
  //   }
  // }),


  column.accessor("id", {
    header: "Order",
    cell: ({ row }) => {
      const id = row.original.id;
      const status = row.original.status;

      // Jika status selesai → tampilkan centang
      if (status === "selesai") {
        return h("span", { class: "text-success fw-bold" }, "✓");
      }

      // Jika status menunggu → tombol Antar
      if (status === "menunggu") {
        return h(
          "button",
          {
            class: "btn btn-sm btn-primary",
            async onClick() {
              const confirm = await Swal.fire({
                title: "Ambil Orderan Ini?",
                text: "Yakin ingin mengambil orderan ini untuk dikirim?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya",
                cancelButtonText: "Batal"
              });

              if (!confirm.isConfirmed) return;

              try {
                // ✅ 1. Klaim kurir
                const claimRes = await axios.post(`/input/${id}/claim`);
                console.log("CLAIM RESULT:", claimRes.data);

                if (!claimRes.data.success) {
                  return Swal.fire("Gagal", claimRes.data.message, "error");
                }
                // const claimRes = await axios.post(`/input/${id}/claim`);
                // if (!claimRes.data.success) {
                //   return Swal.fire("Gagal", claimRes.data.message, "error");
                // }

                // ✅ 2. Update status jadi "dalam proses"
                await axios.put(`/ordered/${id}`, {
                  status: "dalam proses",
                });

                // // ✅ 3. Tambah riwayat
                // await axios.post("/riwayat", {
                //   input_id: id,
                //   deskripsi: "Paket diambil kurir"
                // });

                // ✅ 4. Refresh tabel
                await Swal.fire("Berhasil", "Orderan berhasil diambil", "success");
                refresh();
              } catch (err) {
                await Swal.fire("Error", "Terjadi kesalahan saat memproses", "error");
              }
            }
          },
          "Antar"
        );
      }

      // Selain itu (status sudah "dalam proses", dst) → tombol Tambah
      return h(
        "button",
        {
          class: "btn btn-sm btn-success",
          onClick: () => {
            selected.value = row.original.id;
            openForm.value = true;
          }
        },
        "Tambah"
      );
    }
  }),

  column.display({
    id: "aksiGudang",
    header: "Aksi Gudang",
    cell: ({ row }) => {
      const status = row.original.status;

      if (status === "masuk gudang") {
        return h("span", { class: "badge bg-success" }, "Sudah Masuk");
      }

      // ❌ Jika status masih "menunggu" → tombol disable
      if (status === "menunggu" || status === "selesai") {
        return h("button", {
          class: "btn btn-sm btn-secondary",
          disabled: true,
          title: "Status harus lebih dari 'Menunggu'",
          style: "cursor: not-allowed"
        }, "Masuk Gudang");
      }

      // ✅ Selain itu (dalam proses, keluar gudang, dst) → tombol aktif
      return h("button", {
        class: "btn btn-sm btn-success",
        onClick: () => masukGudang(row.original)
      }, "Masuk Gudang");
    }
  })

  // column.display({
  //   id: "aksiGudang",
  //   header: "Aksi Gudang",
  //   cell: ({ row }) => row.original.status === "masuk gudang" ? h("span", { class: "badge bg-success" }, "Sudah Masuk") : h("button", { class: "btn btn-sm btn-success", onClick: () => masukGudang(row.original) }, "Masuk Gudang")
  // })
];

const url = computed(() => {
  const params = new URLSearchParams();

  // Status yang tidak ditampilkan
  ['masuk gudang', 'keluar gudang', 'diproses', 'dikirim', 'selesai'].forEach(status => {
    params.append('exclude_status[]', status);
    params.append('status_pembayaran', 'settlement');

  });


  // Tambahkan filter kurir_id kosong (belum diambil)
  // params.append('kurir_id', 'null');

  return `/input?${params.toString()}`;
});


const refresh = () => paginateRef.value?.refetch();
onMounted(async () => {
  if (props.selected) {
    isLoading.value = true;
    const { data } = await axios.get(`/ordered/${props.selected}`);
    formData.value = {
      ...data,
      riwayat: data.riwayat ?? data.riwayat_pengiriman ?? []
    };
    isLoading.value = false;
  }
});

const props = defineProps<{ selected: string }>();
// const emit = defineEmits(["close", "refresh"]);
// const emit = defineEmits(["success", "refresh"]);
watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo({ top: 0, behavior: "smooth" });
});
</script>

<template>
  <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />
  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">Orderan</h2>
    </div>

    <paginate ref="paginateRef" id="table-inputorder" :url="url" :columns="columns" />
  </div>
</template>

<style scoped>
.btn {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
}
</style>