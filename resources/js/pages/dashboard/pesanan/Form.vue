<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Pesanan } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

const Pesanan = ref<Pesanan>({} as Pesanan);
const formRef = ref();

const formSchema = Yup.object().shape({
  nama_pelanggan: Yup.string().required("Nama harus diisi"),
  produk: Yup.string().required("Nama Produk harus diisi"),
  total_harga: Yup.string().required("Total Harga harus diisi"),
  tanggal_pesanan: Yup.string().nullable(),
  pengirim: Yup.string().nullable(),
  penerima: Yup.string().nullable(),
  alamat_pengiriman: Yup.string().nullable(),
  status: Yup.string().required("Status harus diisi"),
});

function getEdit() {
  block(document.getElementById("form-Pesanan"));
  ApiService.get("Pesanans", props.selected)
    .then((res: any) => {
      Pesanan.value = res.data.data;
    })
    .catch((err: any) => {
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-Pesanan"));
    });
}

function submit() {
  const formData = new FormData();
  formData.append("nama_pelanggan", Pesanan.value.nama_pelanggan || "");
  formData.append("produk", Pesanan.value.produk || "");
  formData.append("total_harga", Pesanan.value.total_harga || "");
  formData.append("tanggal_pesanan", Pesanan.value.tanggal_pesan || "");
  formData.append("pengirim", Pesanan.value.pengirim || "");
  formData.append("penerima", Pesanan.value.penerima || "");
  formData.append("alamat_pengiriman", Pesanan.value.alamat_pengiriman || "");
  formData.append("status", Pesanan.value.status || "");

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-Pesanan"));
  axios({
    method: "post",
    url: props.selected ? `/Pesanan/${props.selected}` : "/Pesanan/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data Pesanan berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-Pesanan"));
    });
}

onMounted(() => {
  if (props.selected) getEdit();
});

watch(
  () => props.selected,
  () => {
    if (props.selected) getEdit();
  }
);
</script>


<template>
    <VForm
      class="form card mb-10"
      @submit="submit"
      :validation-schema="formSchema"
      id="form-Pesanan"
      ref="formRef"
    >
      <div class="card-header align-items-center">
        <h2 class="mb-0">{{ selected ? "Detail" : "Tambah" }} Pesanan</h2>
        <button
          type="button"
          class="btn btn-sm btn-light-danger ms-auto"
          @click="emit('close')"
        >
          Tutup <i class="la la-times-circle p-0"></i>
        </button>
      </div>
  
      <div class="card-body">
        <div class="row">
          <!-- Nama -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6 required">Nama Pelanggan</label>
              <Field
                class="form-control"
                type="text"
                name="nama_pelanggan"
                v-model="Pesanan.nama_pelanggan"
                placeholder="Masukkan Nama Pelanggan"
              />
              <ErrorMessage name="nama_pelanggan" class="text-danger" />
            </div>
          </div>
  
          <!-- Produk -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6 required">Produk</label>
              <Field
                class="form-control"
                type="text"
                name="produk"
                v-model="Pesanan.produk"
                placeholder="Masukkan Nama Produk"
              />
              <ErrorMessage name="produk" class="text-danger" />
            </div>
          </div>
  
          <!-- Total Harga -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6 required">Total Harga</label>
              <Field
                class="form-control"
                type="text"
                name="total_harga"
                v-model="Pesanan.total_harga"
                placeholder="Masukkan Total Harga"
              />
              <ErrorMessage name="total_harga" class="text-danger" />
            </div>
          </div>
  
          <!-- Tanggal Pesan -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6">Tanggal Pesan</label>
              <Field
                class="form-control"
                type="text"
                name="tanggal_pesanan"
                v-model="Pesanan.tanggal_pesan"
                placeholder="Masukkan Tanggal Pesan"
              />
              <ErrorMessage name="tanggal_pesanan" class="text-danger" />
            </div>
          </div>
  
          <!-- Pengirim -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6">Pengirim</label>
              <Field
                class="form-control"
                type="text"
                name="pengirim"
                v-model="Pesanan.pengirim"
                placeholder="Masukkan Nama Pengirim"
              />
              <ErrorMessage name="pengirim" class="text-danger" />
            </div>
          </div>
  
          <!-- Penerima -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6">Penerima</label>
              <Field
                class="form-control"
                type="text"
                name="penerima"
                v-model="Pesanan.penerima"
                placeholder="Masukkan Nama Penerima"
              />
              <ErrorMessage name="penerima" class="text-danger" />
            </div>
          </div>
  
          <!-- Alamat -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6">Alamat Pengiriman</label>
              <Field
                class="form-control"
                type="text"
                name="alamat_pengiriman"
                v-model="Pesanan.alamat_pengiriman"
                placeholder="Masukkan Alamat Pengiriman"
              />
              <ErrorMessage name="alamat_pengiriman" class="text-danger" />
            </div>
          </div>
  
          <!-- Status -->
          <div class="col-md-6">
            <div class="fv-row mb-7">
              <label class="form-label fw-bold fs-6 required">Status</label>
              <Field
                as="select"
                class="form-control"
                name="status"
                v-model="Pesanan.status"
              >
                <option value="dikemas">Dikemas</option>
                <option value="dikirim">Dikirim</option>
                <option value="selesai">Selesai</option>
              </Field>
              <ErrorMessage name="status" class="text-danger" />
            </div>
          </div>
        </div>
      </div>
  
      <div class="card-footer d-flex">
        <button type="submit" class="btn btn-primary btn-sm ms-auto">
          Hapus
        </button>
      </div>
    </VForm>
  </template>
  