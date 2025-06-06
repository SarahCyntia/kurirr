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

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const user = useAuthStore();

const Input = ref({
    nama_pengirim: "",
    alamat_pengirim: "",
    no_telp_pengirim: "",
    nama_penerima: "",
    alamat_penerima: "",
    no_telp_penerima: "",
    jenis_barang: "",
    jenis_layanan: "",
    berat_barang: "",
    // no_resi: "",
    id_user: user.user.id,
    // status: "",
});
const photo = ref<any>([]);
const formRef = ref();

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
    nama_pengirim: Yup.string().required("Nama Pengirim harus diisi"),
    alamat_pengirim: Yup.string().required("Alamat Pengirim harus diisi"),
    no_telp_pengirim: Yup.string().required("No. Telp Pengirim harus diisi"),
    nama_penerima: Yup.string().required("Nama Penerima harus diisi"),
    alamat_penerima: Yup.string().required("Alamat Penerima harus diisi"),
    no_telp_penerima: Yup.string().required("No. Telp Penerima harus diisi"),
    jenis_barang: Yup.string().required("Jenis Barang harus diisi"),
    jenis_layanan: Yup.string().required("Jenis Layanan harus diisi"),
    berat_barang: Yup.string().required("Berat Barang harus diisi"),
});

const submitForm = async () => {
    if (!isEditMode.value) {
        formData.value.no_resi = generateNoResi();
    }

    await axios.post("/input", formData.value);
    emit("refresh");
    emit("close");
};

// ✅ Mendapatkan data  Input untuk edit
function getEdit() {
    block(document.getElementById("form-Input"));
    ApiService.get("Input", props.selected)
        .then(({ data }) => {
            console.log(data);
            Input.value = {
                nama_pengirim: data.nama_pengirim || "",
                alamat_pengirim: data.alamat_pengirim || "",
                no_telp_pengirim: data.no_telp_pengirim || "",
                nama_penerima: data.nama_penerima || "",
                alamat_penerima: data.alamat_penerima || "",
                no_telp_penerima: data.no_telp_penerima || "",
                jenis_barang: data.jenis_barang || "",
                jenis_layanan: data.jenis_layanan || "",
                berat_barang: data.berat_barang || "",
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
    const noResi = generateNoResi(); // ⬅️ Simpan no_resi di sini

    formData.append("nama_pengirim", Input.value.nama_pengirim);
    formData.append("alamat_pengirim", Input.value.alamat_pengirim);
    formData.append("no_telp_pengirim", Input.value.no_telp_pengirim);
    formData.append("nama_penerima", Input.value.nama_penerima);
    formData.append("alamat_penerima", Input.value.alamat_penerima);
    formData.append("no_telp_penerima", Input.value.no_telp_penerima);
    formData.append("jenis_barang", Input.value.jenis_barang);
    formData.append("jenis_layanan", Input.value.jenis_layanan);
    formData.append("berat_barang", Input.value.berat_barang);
    formData.append("no_resi", noResi); // ⬅️ Gunakan noResi
    formData.append("id_user", Input.value.id_user);
    if (props.selected) {
        formData.append("_method", "PUT");
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
  Swal.fire({
    icon: "success",
    title: "Berhasil!",
    html: `No. Resi berhasil dibuat:<br><strong>${noResi}</strong>`,
    showCancelButton: true,
    confirmButtonText: "Cetak Resi",
    cancelButtonText: "Tutup",
  }).then((result) => {
    if (result.isConfirmed) {
      const resiHtml = `
        <div style="font-family: Arial; padding: 20px;">
          <h2>📦 Resi Pengiriman</h2>
          <p><strong>No. Resi:</strong> ${noResi}</p>
          <p><strong>Pengirim:</strong><br>
             ${Input.value.nama_pengirim} <br>
             ${Input.value.alamat_pengirim} <br>
             Telp: ${Input.value.no_telp_pengirim}</p>

          <p><strong>Penerima:</strong><br>
             ${Input.value.nama_penerima} <br>
             ${Input.value.alamat_penerima} <br>
             Telp: ${Input.value.no_telp_penerima}</p>

          <p><strong>Jenis Barang:</strong> ${Input.value.jenis_barang}</p>
          <p><strong>Jenis Layanan:</strong> ${Input.value.jenis_layanan}</p>
          <p><strong>Berat Barang:</strong> ${Input.value.berat_barang} gram</p>

          <hr>
          <p><strong>Status:</strong> Menunggu</p>
        </div>
      `;

      // ✅ Langsung cetak HTML dari inputan
      printJS({ printable: resiHtml, type: 'raw-html', style: 'body { font-size: 14px; }' });
    }
  });

  emit("close");
  emit("refresh");
  formRef.value.resetForm();
})

}

function generateNoResi() {
    const prefix = "RESI";
    const timestamp = Date.now().toString(); // angka unik berdasarkan waktu
    const random = Math.floor(1000 + Math.random() * 9000); // angka acak 4 digit
    return `${prefix}-${timestamp}-${random}`;
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
            <h2 class="mb-0">{{ props.selected ? "Edit" : "Tambah" }} Input</h2>
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
                            >Nama Pengirim</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="nama_pengirim"
                            v-model="Input.nama_pengirim"
                            placeholder="Masukkan Nama Pengirim"
                        />
                        <ErrorMessage
                            name="nama_pengirim"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Alamat Pengirim</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="alamat_pengirim"
                            v-model="Input.alamat_pengirim"
                            placeholder="Masukkan Alamat Pengirim"
                        />
                        <ErrorMessage
                            name="alamat_pengirim"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >No. Telp Pengirim</label
                        >
                        <Field
                            class="form-control"
                            type="string"
                            name="no_telp_pengirim"
                            v-model="Input.no_telp_pengirim"
                            placeholder="Masukkan Nomor Telpon"
                        />
                        <ErrorMessage
                            name="no_telp_pengirim"
                            class="text-danger"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Nama Penerima</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="nama_penerima"
                            v-model="Input.nama_penerima"
                            placeholder="Masukkan Nama Penerima"
                        />
                        <ErrorMessage
                            name="nama_penerima"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Alamat Penerima</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="alamat_penerima"
                            v-model="Input.alamat_penerima"
                            placeholder="Masukkan Alamat Penerima"
                        />
                        <ErrorMessage
                            name="alamat_penerima"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >No. Telp Penerima</label
                        >
                        <Field
                            class="form-control"
                            type="string"
                            name="no_telp_penerima"
                            v-model="Input.no_telp_penerima"
                            placeholder="Masukkan Nomor Telpon"
                        />
                        <ErrorMessage
                            name="no_telp_penerima"
                            class="text-danger"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Jenis Barang</label
                        >
                        <Field
                            class="form-control"
                            type="text"
                            name="jenis_barang"
                            v-model="Input.jenis_barang"
                            placeholder="Masukkan Jenis Barang"
                        />
                        <ErrorMessage name="jenis_barang" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Jenis Layanan</label
                        >
                        <Field
                            as="select"
                            class="form-control"
                            name="jenis_layanan"
                            v-model="Input.jenis_layanan"
                        >
                            <option value="" disabled>
                                Pilih Jenis Layanan
                            </option>
                            <option value="JNE">JNE</option>
                            <option value="NINA">NINA</option>
                            <option value="JNT">JNT</option>
                            <option value="SICEPAT">SICEPAT</option>
                            <option value="SAP">SAP</option>
                            <option value="IDE">IDE</option>
                            <option value="TIKI">TIKI</option>
                            <option value="POS">POS</option>
                        </Field>
                        <ErrorMessage
                            name="jenis_layanan"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6"
                            >Berat Barang (gram)</label
                        >
                        <Field
                            class="form-control"
                            type="number"
                            name="berat_barang"
                            v-model="Input.berat_barang"
                            placeholder="Masukkan Berat Barang (gram)"
                            min="0"
                        />
                        <ErrorMessage name="berat_barang" class="text-danger" />
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold fs-6">No Resi</label>
                    <p class="form-control-plaintext">
                        {{
                            Input.no_resi ||
                            "No resi akan dibuat otomatis saat disimpan"
                        }}
                    </p>
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
