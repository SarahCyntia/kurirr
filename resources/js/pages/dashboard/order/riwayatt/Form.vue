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

// const user = useAuthStore();

const Input = ref({
    nilai: "",
    ulasan: "",
});
const photo = ref<any>([]);
const formRef = ref();

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
    nilai: Yup.string().nullable(),
    ulasan: Yup.string().nullable(),
});

// ✅ Mendapatkan data  Input untuk edit
function getEdit() {
    block(document.getElementById("form-Input"));
    ApiService.get("Input", props.selected)
        .then(({ data }) => {
            console.log(data);
            Input.value = {
                id: data.id,
                nilai: data.nilai || "",
                ulasan: data.ulasan || "",
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
    formData.append("id", props.selected || "");
    formData.append("nilai", Input.value.nilai);
    formData.append("ulasan", Input.value.ulasan);

    block(document.getElementById("form-Input"));
    axios({
        method: "post",
        url: "/input/stores",
        // url: props.selected ? `/input/${props.selected}` : "/input/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data Input berhasil disimpan");
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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Penilaian</h2>
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
                            >Nilai</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="nilai"
                            v-model="Input.nilai"
                            placeholder="Masukkan Nilai"
                        />
                        <ErrorMessage name="nilai" class="text-danger" />
                    </div>
                </div>
                
                <!-- Ulasan -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Ulasan</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="ulasan"
                            v-model="Input.ulasan"
                            placeholder="Masukkan Ulasan"
                        />
                        <ErrorMessage name="ulasan" class="text-danger" />
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
