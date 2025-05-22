<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Input } from "@/types";
import ApiService from "@/core/services/ApiService";

// import { useAuthStore } from "@/stores/auth";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const Input = ref<Input>({} as Input);

const photo = ref<any>([]);
const formRef = ref();

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
  nama_barang: Yup.string().required("Nama harus diisi"),
  alamat_asal: Yup.string().nullable(),
  alamat_tujuan: Yup.string().nullable(),
  penerima: Yup.string().required("Nama penerima harus diisi"),

  berat_paket: Yup.number()
    .transform((value, originalValue) =>
      String(originalValue).trim() === "" ? undefined : Number(originalValue)
    )
    .when("metode_pengiriman", {
      is: "drop-off",
      then: (schema) => schema.min(1, "Minimal 1 kg").nullable(),
      otherwise: (schema) => schema.notRequired().nullable(),
    }),

  biaya_pengiriman: Yup.number()
    .transform((value, originalValue) =>
      String(originalValue).trim() === "" ? undefined : Number(originalValue)
    )
    .nullable(),

  metode_pengiriman: Yup.string().nullable(),

  jarak: Yup.number()
    .transform((value, originalValue) =>
      String(originalValue).trim() === "" ? undefined : Number(originalValue)
    )
    .when("metode_pengiriman", {
      is: "pick-up",
      then: (schema) => schema.min(1, "Minimal 1 km").nullable(),
      otherwise: (schema) => schema.notRequired().nullable(),
    }),
});


// ✅ Mendapatkan data Input untuk edit
function getEdit() {
    block(document.getElementById("form-Input"));
    ApiService.get("Ordered", props.selected)
        .then(({ data }) => {
            console.log(data);
            Input.value = {
                nama_barang: data.nama_barang || "",
                alamat_asal: data.alamat_asal || "",
                alamat_tujuan: data.alamat_tujuan || "",
                penerima: data.penerima || "",
                berat_paket: data.berat_paket || "",
                jarak: data.jarak|| "",
                biaya_pengiriman: data.biaya_pengiriman || "1",
                metode_pengiriman: data.metode_pengiriman || "",
                status: data.status || "menunggu",
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
    formData.append("berat_paket", Input.value.berat_paket);
    formData.append("jarak", Input.value.jarak);
    formData.append("biaya_pengiriman", Input.value.biaya_pengiriman);
    formData.append("metode_pengiriman", Input.value.metode_pengiriman);
    // formData.append("id_user",  Input.value.id_user);
    formData.append("status", Input.value.status);

    if (props.selected) {
        formData.append("_method", "PUT");
    } else {
        // formData.append("tanggal_order", new Date().toISOString());
        formData.append("status", "menunggu");
    }

    block(document.getElementById("form-Input"));
    axios({
        method: "post",
        url: props.selected ? `/Ordered/${props.selected}` : "/Ordered/store",
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

// Hitung biaya pengiriman berdasarkan jarak (pick-up)
watch(() => Input.value.jarak, (newVal) => {
  if (Input.value.metode_pengiriman === "pick-up" && newVal != null) {
    const biayaPerKm = 10000;
    Input.value.biaya_pengiriman = Number(newVal) * biayaPerKm;
  }
});

// Hitung biaya pengiriman berdasarkan berat (drop-off)
watch(() => Input.value.berat_paket, (newVal) => {
  if (Input.value.metode_pengiriman === "drop-off" && newVal != null) {
    const biayaPerKg = 10000;
    Input.value.biaya_pengiriman = Number(newVal) * biayaPerKg;
  }
});

// Pantau perubahan metode pengiriman
watch(() => Input.value.metode_pengiriman, (newMethod) => {
  if (!props.selected) {
    Input.value.biaya_pengiriman = null; // Reset biaya jika metode berubah
  }

  // Reset jarak jika bukan pick-up
  if (newMethod !== "pick-up") {
    Input.value.jarak = null;
  }

  // Reset berat jika bukan drop-off
  if (newMethod !== "drop-off") {
    Input.value.berat_paket = null;
  }
});


// ✅ Pantau perubahan selected (Edit Mode)
watch(
    () => props.selected,
    () => {
        if (props.selected) {
            console.log("edit");
            getEdit();
        }
    }
);
// watch(
//     () => Input.value.metode_pengiriman,
//     () => {
//         if (
//             Input.value.metode_pengiriman === "drop-off" &&
//             Input.value.status === "pengambilan paket"
//         ) {
//             Input.value.status = "menunggu";
//         }
//     }
// );

// watch(() => Input.metode_pengiriman, (newVal) => {
//   if (newVal === "pick-up") {
//     Input.berat_paket = "";
//   }
// });


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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Order</h2>
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
                            disabled
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
                            disabled
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
                            disabled
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
                            disabled
                            placeholder="Masukkan Nama Penerima"
                        />
                        <ErrorMessage name="penerima" class="text-danger" />
                    </div>
                </div>

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
                            disabled
                        >
                            <option value="">Pilih Metode</option>
                            <option value="pick-up">
                                Pick Up (kurir jemput)
                            </option>
                            <option value="drop-off">
                                Drop Off (pelanggan antar)
                            </option>
                        </Field>
                        <ErrorMessage
                            name="metode_pengiriman"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div
                    class="col-md-6"
                    v-if="Input.metode_pengiriman === 'drop-off'"
                >
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Berat Paket (kg)</label
                        >
                        <Field
                            class="form-control"
                            type="number"
                            name="berat_paket"
                            v-model="Input.berat_paket"
                            placeholder="Masukkan Berat Paket"
                        />
                        <ErrorMessage name="berat_paket" class="text-danger" />
                    </div>
                </div>
                <div
                    class="col-md-6"
                    v-if="Input.metode_pengiriman === 'pick-up'"
                >
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Jarak (km)</label
                        >
                        <Field
                            class="form-control"
                            type="number"
                            name="jarak"
                            v-model="Input.jarak"
                            placeholder="Masukkan jarak pengiriman"
                        />
                        <ErrorMessage name="jarak" class="text-danger" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Biaya Pengiriman</label
                        >
                        <Field
                            class="form-control"
                            type="number"
                            name="biaya_pengiriman"
                            v-model="Input.biaya_pengiriman"
                            placeholder="Masukkan Biaya Pengiriman"
                        />
                        <ErrorMessage
                            name="biaya_pengiriman"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required"
                            >Status</label
                        >
                        <Field
                            as="select"
                            class="form-control"
                            name="status"
                            v-model="Input.status"
                        >
                            <option value="menunggu">Menunggu</option>
                            <option value="dalam proses" :disabled="Input.metode_pengiriman === 'pick-up'">Dalam Proses</option>
                            <option
                                value="pengambilan paket"
                                :disabled="
                                    Input.metode_pengiriman === 'drop-off'
                                "
                            >
                                Pengambilan Paket
                            </option>
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
                Simpan
            </button>
        </div>
    </VForm>
</template>
