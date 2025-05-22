<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Input } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useAuthStore } from "@/stores/auth";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const user = useAuthStore();

const Input = ref({
    nama_barang: "",
    alamat_asal: "",
    alamat_tujuan: "",
    penerima: "",
    metode_pengiriman: "",
    id_user: user.user.id,
    // status: "",
});
const photo = ref<any>([]);
const formRef = ref();

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
    nama_barang: Yup.string().required("Nama harus diisi"),
    alamat_asal: Yup.string().nullable(),
    alamat_tujuan: Yup.string().nullable(),
    penerima: Yup.string().required("Nama penerima harus diisi"),
    metode_pengiriman: Yup.string().required("Metode pengiriman harus diisi"),
    // status: Yup.string().required("Status harus diisi"),
    // jenis_kelamin: Yup.string().required("Pilih status  Input"),
});

// ✅ Mendapatkan data  Input untuk edit
function getEdit() {
    block(document.getElementById("form-Input"));
    ApiService.get("Input", props.selected)
        .then(({ data }) => {
            console.log(data);
            Input.value = {
                nama_barang: data.nama_barang || "",
                alamat_asal: data.alamat_asal || "",
                alamat_tujuan: data.alamat_tujuan || "",
                penerima: data.penerima || "",
                metode_pengiriman: data.metode_pengiriman || "",
                // status: data.status || "",
            };
            console.log(Input.value);
        })
        .catch((err: any) => {
            toast.error(err.response.data.message || "Gagal mengambil data");
        })
        .finally(() => {
            unblock(document.getElementById("form-Input"));
        });
}

// ✅ Submit Form (Tambah/Update)
function submit() {
    const formData = new FormData();
    formData.append("nama_barang", Input.value.nama_barang);
    formData.append("alamat_asal", Input.value.alamat_asal);
    formData.append("alamat_tujuan", Input.value.alamat_tujuan);
    formData.append("penerima", Input.value.penerima);
    formData.append("metode_pengiriman", Input.value.metode_pengiriman);
    formData.append("id_user", Input.value.id_user);
    if (props.selected) {
        formData.append("_method", "PUT");
    } else {
        formData.append("tanggal_order", new Date().toISOString());
        formData.append("status", "menunggu");
    }

    block(document.getElementById("form-Input"));
    axios({
        method: "post",
        url: props.selected ? `/input/${props.selected}` : "/input/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data  Input berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error("Gagal menyimpan data" + err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-Input"));
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
        id="form-Input"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <!-- <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }}  Input</h2> -->
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Input order</h2>
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
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Nama Barang</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="nama_barang"
                            v-model="Input.nama_barang"
                            placeholder="Masukkan Nama Barang"
                        />
                        <ErrorMessage name="nama_barang" class="text-danger" />
                    </div>
                </div>

                <!-- Alamat -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Alamat Asal</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="alamat_asal"
                            v-model="Input.alamat_asal"
                            placeholder="Masukkan Alamat Asal"
                        />
                        <ErrorMessage name="alamat_asal" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Alamat Tujuan</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="alamat_tujuan"
                            v-model="Input.alamat_tujuan"
                            placeholder="Masukkan Alamat Tujuan"
                        />
                        <ErrorMessage
                            name="alamat_tujuan"
                            class="text-danger"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Penerima</label>
                        <Field
                            class="form-control"
                            type="text"
                            name="penerima"
                            v-model="Input.penerima"
                            placeholder="Masukkan Nama Penerima"
                        />
                        <ErrorMessage name="penerima" class="text-danger" />
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >Metode Pengiriman</label
                        >
                        <Field
                            as="select"
                            class="form-control"
                            name="metode_pengiriman"
                            v-model="Input.metode_pengiriman"
                        >
                            <option value="pick-up">Pick-up (kurir jemput)</option>
                            <option value="drop-off">Drop-off (pelanggan antar)</option>
                        </Field>
                        <ErrorMessage
                            name="metode_pengiriman"
                            class="text-danger"
                        />
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
