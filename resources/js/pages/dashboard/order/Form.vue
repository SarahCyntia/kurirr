<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { order } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

// const order = ref<order>({} as order);
// // const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
// // const photo = ref<any>([]);
const photo = ref<any[]>([]);
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const order = ref({
  nama_pelanggan: "",
  produk: "",
  total_harga: "",
  pengirim: "",
  penerima: "",
  alamat: "",
  status: "",
  
} as any); // atau define tipe order baru
const formRef = ref();

// const formSchema = Yup.object().shape({
//   // order_id: Yup.string().required("ID order harus diisi"),
//   // name: Yup.string().required("Nama order harus diisi"),
//   // email: Yup.string().email("Email harus valid").required("Email harus diisi"),
//   // phone: Yup.string().required("Nomor Telepon harus diisi"),
//   // password: Yup.string()
//   //   .min(6, "Password minimal 6 karakter")
//   //   .optional(),
//   status: Yup.string().required("Pilih status order"),
//   rating: Yup.number().min(1).max(5).required("Pilih rating"),
// });

// function getEdit() {
//   block(document.getElementById("form-order"));
//   ApiService.get("order", props.selected)
//     // .then(({ data }) => {
//     //   order.value = data.order;
//     //   photo.value = data.order.photo ? ["/storage/" + data.order.photo] : [];
//     //   order.value.password = ""; // Kosongkan password saat edit
//     // })
//     .catch((err: any) => {
//       toast.error(err.response.data.message);
//     })
//     .finally(() => {
//       unblock(document.getElementById("form-order"));
//     });
// }

const formSchema = Yup.object().shape({
  // order_id: Yup.string().required("ID order harus diisi"),
  nama_pelanggan: Yup.string().required("Nama pelanggan harus diisi"),
  produk: Yup.string().required("Nama produk harus diisi"),
  total_harga: Yup.string().required("total harga harus diisi"),
  pengirim: Yup.string().required("Nama pengirirm harus diisi"),
  penerima: Yup.string().required("Nama penerima harus diisi"),
  alamat: Yup.string().required("alamat harus diisi"),
  status: Yup.string().required("Pilih status order"),
  // rating: Yup.number().min(1).max(5).required("Pilih rating"),
});


// function getEdit() {
//   block(document.getElementById("form-order"));
//   ApiService.get("order", props.selected)
//     .then(({ data }) => {
//       console.log(data);
//       // Pastikan struktur sesuai response dari backend
//       order.value = {
//         status: data.user.status || "nonaktif", // Tambahkan status,
//         status: data.user.status || "nonaktif", // Tambahkan status,
        
//       };
//       console.log(order.value);
//       photo.value = data.user.photo
//         ? ["/storage/" + data.user.photo]
//         : [];

//       order.value.password = ""; // kosongkan password saat edit
//     })
//     .catch((err: any) => {
//       toast.error(err.response?.data?.message || "Gagal mengambil data");
//     })
//     .finally(() => {
//       unblock(document.getElementById("form-order"));
//     });
// }

function submit() {
  const formData = new FormData();
  // formData.append("order_id", order.value.order_id);
  formData.append("nama_pelanggan", order.value.nama_pelanggan);
  formData.append("produk", order.value.produk);
  formData.append("total_harga", order.value.total_harga);
  formData.append("pengirim", order.value.pengirim);
  formData.append("penerima", order.value.penerima);
  formData.append("alamat", order.value.alamat);
  formData.append("status", order.value.status);
  // formData.append("rating", order.value.rating.toString());


  // if (order.value.password) {
  //   formData.append("password", order.value.password);
  // }

  // if (photo.value.length) {
  //   formData.append("photo", photo.value[0].file);
  // }

 

  block(document.getElementById("form-order"));
  axios({
    method: "post",
    url: props.selected ? `/order/${props.selected}` : "/order/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data order berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error("Gagal menyimpan data: " + err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-order"));
    });
}

onMounted(() => {
  if (props.selected) {
    getEdit();
  }
});

watch(
  () => props.selected,
  () => {
    if (props.selected) {
      getEdit();
    }
  }
);
</script>


<template>
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-order" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} order</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <!-- <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">ID order</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="order_id" v-model="order.order_id" placeholder="Masukkan ID order" />
            <ErrorMessage name="order_id" class="text-danger" />
          </div>
        </div> -->

        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nama Pelanggan</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="nama"
              v-model="order.nama_" placeholder="Masukkan Nama Pelanggan" />
            <ErrorMessage name="nama" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Produk</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="produk"
              v-model="order.produk" placeholder="Masukkan Nama Produk" />
            <ErrorMessage name="produk" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Total Harga</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="total_harga"
              v-model="order.total_harga" placeholder="Masukkan Total Harga" />
            <ErrorMessage name="total_harga" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Pengirim</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="pengirim"
              v-model="order.pengirim" placeholder="Masukkan Nama Pegirim" />
            <ErrorMessage name="pengirim" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div clmanmaass="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Penerima</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="penerima"
              v-model="order.penerima" placeholder="Masukkan Nama penerima " />
            <ErrorMessage name="penerima" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div clmanmaass="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Alamat</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="alamat"
              v-model="order.alamat" placeholder="Masukkan Nama penerima " />
            <ErrorMessage name="alamat" class="text-danger" />
          </div>
        </div>
        <!-- <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Status</label>
            <Field as="select" class="form-select form-select-solid" name="status" v-model="order.status">
              <option value="dalam proses">dalam proses</option>
              <option value="dikirim">dikirim</option>
              <option value="diterima">diterima</option>
              <option value="dibatalkan">dibatalkan</option>
            </Field>
            <ErrorMessage name="status" class="text-danger" />
          </div>
        </div> -->
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
    </div>
  </VForm>
</template>