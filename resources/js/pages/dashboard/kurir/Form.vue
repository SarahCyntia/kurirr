<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import type { Kurir } from "@/types";
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

const kurir = ref({
    penilaian: "",
    alamat: "",
    status: "",
    jenis_kendaraan: "",
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
    // penilaian: Yup.string().nullable(),
    status: Yup.string().required("Status harus diisi"),
    jenis_kendaraan: Yup.string().required("Pilih Jenis Kendaraan"),
});
 
// ✅ Mendapatkan data kurir untuk edit
function getEdit() {
    block(document.getElementById("form-kurir"));
    ApiService.get("kurir", props.selected)
        .then(({ data }) => {
            console.log(data);
            kurir.value = {
                // penilaian: data.user.penilaian || "",
                alamat: data.user.alamat || "",
                status: data.user.status || "nonaktif",
                jenis_kendaraan: data.user.jenis_kendaraan || "",
                user : {
                    name: data.user.name || "",
                    email: data.user.email || "",
                    phone: data.user.phone || "",

                },
            }
            console.log(kurir.value);

            photo.value = data.user.photo
                ? ["/storage/" + data.user.photo]
                : [];
                
            // kurir.value.password = "";
        })
        .catch((err: any) => {
            toast.error(err.response.data.message || "Gagal mengambil data");
        })
        .finally(() => {
            unblock(document.getElementById("form-kurir"));
        });
}

// ✅ Submit Form (Tambah/Update)
function submit() {
    const formData = new FormData();
    formData.append("name", kurir.value.user.name);
    formData.append("email", kurir.value.user.email);
    formData.append("phone", kurir.value.user.phone);
    formData.append("photo", kurir.value.user.photo);
    // formData.append("penilaian", kurir.value.penilaian);
    formData.append("alamat", kurir.value.alamat);
    formData.append("status", kurir.value.status);
    formData.append("jenis_kendaraan", kurir.value.jenis_kendaraan);

    if (photo.value.length && photo.value[0].file) {
        formData.append("photo", photo.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-kurir"));
    axios({
        method: "post",
        url: props.selected ? `/kurir/${props.selected}` : "/kurir/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data kurir berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error("Gagal menyimpan data" + err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-kurir"));
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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kurir</h2>
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
                            v-model="kurir.user.name"
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
                            v-model="kurir.user.email"
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
                            v-model="kurir.user.phone"
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
                            v-model="kurir.alamat"
                            placeholder="Masukkan Alamat"
                        />
                        <ErrorMessage name="alamat" class="text-danger" />
                    </div>
                </div>

                <!-- Kendaraan -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >Jenis Kendaraan</label
                        >
                        <Field
                            as="select"
                            class="form-control"
                            name="jenis_kendaraan"
                            v-model="kurir.jenis_kendaraan"
                        >
                            <option value="mobil">Mobil</option>
                            <option value="motor">Motor</option>
                        </Field>
                        <ErrorMessage
                            name="jenis_kendaraan"
                            class="text-danger"
                        />
                    </div>
                </div>
                <!-- <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Jenis Kendaraan</label>
                        <Field class="form-control" type="text" name="kendaraan" v-model="kurir.kendaraan"
                            placeholder="Motor/Mobil" />
                            <option value="laki-laki">Mobil</option>
                            <option value="perempuan">Motor</option>
                        <ErrorMessage name="kendaraan" class="text-danger" />
                    </div>
                </div> -->

                <!-- Status -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >Status</label
                        >
                        <Field
                            as="select"
                            class="form-control"
                            name="status"
                            v-model="kurir.status"
                        >
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </Field>
                        <ErrorMessage name="status" class="text-danger" />
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

                <!-- <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Penilaian</label>
                        <Field
                            as="select"
                            class="form-control"
                            name="penilaian"
                            v-model="kurir.penilaian"
                            placeholder="Masukkan Penilaian"
                        >
                            <option value="Tidak Ada">Tidak Ada</option>
                        </Field>
                            
                        <ErrorMessage name="penilaian" class="text-danger" />
                    </div>
                </div> -->

                <!--jenis kelamin-->
                <!-- <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >Jenis Kelamin</label
                        >
                        <Field
                            as="select"
                            class="form-control"
                            name="jenis_kendaraan"
                            v-model="kurir.jenis_kendaraan"
                        >
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </Field>
                        <ErrorMessage
                            name="jenis_kendaraan"
                            class="text-danger"
                        />
                    </div>
                </div> -->
            </div>
        </div>

        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>
