<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import type { Pelanggan } from "@/types";
import ApiService from "@/core/services/ApiService";
import { toast } from "vue3-toastify";
// import { toast } from "vue3-toastify/index";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const pelanggan = ref({
    alamat: "",
    keluhan: "",
    user: {
        name: "",
        email: "",
        phone: "",
        photo: "",
    },
});
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const photo = ref<any>([]);
const formRef = ref();

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
    name: Yup.string().required("Nama harus diisi"),
    email: Yup.string().email("Email harus valid").nullable(),
    phone: Yup.string().required("Nomor Telepon harus diisi"),
    alamat: Yup.string().nullable(),
    keluhan: Yup.string().nullable(),
});
 
// ✅ Mendapatkan data kurir untuk edit
function getEdit() {
    block(document.getElementById("form-pelanggan"));
    ApiService.get("pelanggan", props.selected)
        .then(({ data }) => {
            console.log(data);
            pelanggan.value = {
                // penilaian: data.user.penilaian || "",
                alamat: data.user.alamat || "",
                keluhann: data.user.keluhann || "",
                user : {
                    name: data.user.name || "",
                    email: data.user.email || "",
                    phone: data.user.phone || "",
                },
            }
            console.log(pelanggan.value);

            photo.value = data.user.photo
                ? ["/storage/" + data.user.photo]
                : [];
                
            // kurir.value.password = "";
        })
        .catch((err: any) => {
            toast.error(err.response.data.message || "Gagal mengambil data");
        })
        .finally(() => {
            unblock(document.getElementById("form-pelanggann"));
        });
}

// ✅ Submit Form (Tambah/Update)
function submit() {
    const formData = new FormData();
    formData.append("name", pelanggan.value.user.name);
    formData.append("email", pelanggan.value.user.email);
    formData.append("phone", pelanggan.value.user.phone);
    formData.append("photo", pelanggan.value.user.photo);
    // formData.append("penilaian", pelanggan.value.penilaian);
    formData.append("alamat", pelanggan.value.alamat);
    formData.append("keluhan", pelanggan.value.keluhan);

    if (photo.value.length && photo.value[0].file) {
        formData.append("photo", photo.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-pelanggan"));
    axios({
        method: "post",
        url: props.selected ? `/pelanggan/${props.selected}` : "/pelanggan/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data pelanggan berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error("Gagal menyimpan data" + err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-pelanggan"));
        });
}

// ✅ Ambil data saat component dipasang
onMounted(() => {
    if (props.selected) {
        getEdit();
    }
});

// ✅ Pantau perubahan selected (Edit Mode)
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
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-Kurir"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <!-- <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kurir</h2> -->
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Pelanggan</h2>
            <button
                type="button"
                class="btn btn-sm btn-light-danger ms-auto"
                @click="emit('close')"
            >
                Batal <i class="la la-times-circle p-0"></i>
            </button>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Nama -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >Nama</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="name"
                            v-model="pelanggan.user.name"
                            placeholder="Masukkan Nama"
                           
                        />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Email</label>
                        <Field
                            class="form-control"
                            type="text"
                            name="email"
                            v-model="pelanggan.user.email"
                            placeholder="Masukkan Email"
                        />
                        <ErrorMessage name="email" class="text-danger" />
                    </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >No. Telp</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="phone"
                            v-model="pelanggan.user.phone"
                            placeholder="0812345678989"
                         
                        />
                        <ErrorMessage name="phone" class="text-danger" />
                    </div>
                </div>

                <!-- Alamat -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Alamat</label>
                        <Field
                            class="form-control"
                            type="text"
                            name="alamat"
                            v-model="pelanggan.alamat"
                            placeholder="Masukkan Alamat"
                        />
                        <ErrorMessage name="alamat" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Keluhan</label>
                        <Field
                            class="form-control"
                            type="text"
                            name="keluhan"
                            v-model="pelanggan.keluhan"
                            placeholder="Masukkan Keluhan"
                        />
                        <ErrorMessage name="keluhan" class="text-danger" />
                    </div>
                </div>
                
                <!-- Foto -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Foto Kurir</label
                        >
                        <file-upload
                            :files="photo"
                            :accepted-file-types="fileTypes"
                            v-on:updatefiles="(file) => (photo = file)"
                            ></file-upload>
                            <ErrorMessage name="photo" class="text-danger" />
                        </div>
                    </div>
                </div>
            </div>
            
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>
