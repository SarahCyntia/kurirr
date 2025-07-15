<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Input } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useAuthStore } from "@/stores/auth";
import Swal from "sweetalert2";
import { Field, ErrorMessage, Form as VForm } from "vee-validate";
// import { ref  } from "vue";

const formData = ref<{
    riwayat_pengiriman: string[];
    // ...field lain
}>({
    riwayat_pengiriman: [],
});

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const user = useAuthStore();

const Input = ref({
    riwayat: "",
    status: "menunggu", // nilai default
    id_user: user.user.id,
    // tanggal_waktu: "", // 🕒 Tanggal dan waktu pengiriman
});


const photo = ref<any>([]);
const formRef = ref();

// ✅ Validasi form menggunakan Yup
// const formSchema = Yup.object().shape({
//     riwayat: Yup.string().nullable(),
//     // status: Yup.string().nullable("Status harus diisi"),
//     // tanggal_waktu: Yup.string().nullable("Tanggal dan waktu harus diisi"),
// });

const formSchema = Yup.object({
  riwayat_pengiriman: Yup.array().of(Yup.string()).nullable(),
});



const submitForm = async () => {
    try {
        const formData = new FormData();
        // formData.append("riwayat_pengiriman", Input.value.riwayat_pengiriman);
        formData.append("riwayat", Input.value.riwayat);
        formData.append("status", Input.value.status);
        formData.append("_method", "PUT"); // Laravel butuh ini untuk method PUT

        await axios.post(`/ordered/${props.selected}`, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        emit("refresh");
        emit("close");

        Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "Riwayat dan status berhasil diperbarui.",
            timer: 2000,
            showConfirmButton: false,
        });
    } catch (error: any) {
        console.error(error);
        Swal.fire("Gagal", error?.response?.data?.message || "Terjadi kesalahan", "error");
    }
};


// ✅ Mendapatkan data  Input untuk edit
function getEdit() {
    block(document.getElementById("form-Input"));
    ApiService.get("Input", props.selected)
        .then(({ data }) => {
            console.log(data);
            Input.value = {
                riwayat: "",
                status: data.status || "menunggu", // atau nilai default lainnya
                id_user: user.user.id,
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
    const form = new FormData();
    form.append("riwayat", Input.value.riwayat);
    form.append("status", Input.value.status);
    if (props.selected) {
        form.append("_method", "PUT");
    }

    block(document.getElementById("form-Input"));
    axios({
        method: "post",
        url: props.selected ? `/riwayat/store/${props.selected}` : "/input/store",
        data: form,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    }).then(() => {
        Swal.fire({
            icon: "success",
            title: "Berhasil Disimpan!",
            confirmButtonText: "Oke",
        });
        emit("close");
        emit("refresh");
        formRef.value.resetForm();
    });
}

// function submit() {
//   const now = new Date();
//   const formatted = now.toLocaleString("id-ID", {
//     weekday: "short",
//     year: "numeric",
//     month: "short",
//     day: "2-digit",
//     hour: "2-digit",
//     minute: "2-digit",
//     second: "2-digit",
//   });

//   const riwayatDenganTanggal = `[${formatted}] ${Input.value.riwayat}`;

//   const formData = new FormData();
//   formData.append("riwayat", riwayatDenganTanggal);
//   formData.append("status", Input.value.status);
//   if (props.selected) {
//     formData.append("_method", "PUT");
//   }

//   block(document.getElementById("form-Input"));
//   axios({
//     method: "post",
//     url: props.selected ? `/riwayat/store/${props.selected}` : "/input/store",
//     data: formData,
//     headers: {
//       "Content-Type": "multipart/form-data",
//     },
//   }).then(() => {
//     Swal.fire({
//       icon: "success",
//       title: "Berhasil Disimpan!",
//       confirmButtonText: "Oke",
//     });
//     emit("close");
//     emit("refresh");
//     formRef.value.resetForm();
//   });
// }

// function submit() {
//     const formData = new FormData();
//     // const noResi = generateNoResi(); // ⬅️ Simpan no_resi di sini

//     formData.append("riwayat", Input.value.riwayat);
//     // formData.append("no_resi", noResi); // ⬅️ Gunakan noResi
//     formData.append('status', Input.value.status);
//     // formData.append("id_user", Input.value.id_user);
//     if (props.selected) {
//         formData.append("_method", "PUT");
//     }

//     block(document.getElementById("form-Input"));
//     axios({
//         method: "post",
//         url: props.selected ? `/riwayat/store/${props.selected}` : "/input/store",
//         data: formData,
//         headers: {
//             "Content-Type": "multipart/form-data",
//         },
//     }).then(() => {
//         Swal.fire({
//             icon: "success",
//             title: "Berhasil Disimpan!",
//             confirmButtonText: "Oke",
//         });
//         emit("close");
//         emit("refresh");
//         formRef.value.resetForm();
//     });
// }

// function generateNoResi() {
//     const prefix = "RESI";
//     const timestamp = Date.now().toString(); // angka unik berdasarkan waktu
//     const random = Math.floor(1000 + Math.random() * 9000); // angka acak 4 digit
//     return `${prefix}-${timestamp}-${random}`;
// }

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
    <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-Input" ref="formRef">
        <div class="card-header align-items-center">
            <h2 class="mb-0">
                {{ props.selected ? "Edit" : "Tambah" }} Orderan
            </h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
                Batal <i class="la la-times-circle p-0"></i>
            </button>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="form-label fw-bold fs-6">Riwayat Pengiriman</label>
                            <Field class="form-control" type="text" name="riwayat" v-model="Input.riwayat"
                                placeholder="Masukkan Riwayat" />
                            <ErrorMessage name="riwayat" class="text-danger" />
                        </div>
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
