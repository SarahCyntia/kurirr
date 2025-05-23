<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { useAuthStore } from "@/stores/auth";
import ApiService from "@/core/services/ApiService";
import { block, unblock } from "@/libs/utils";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

const user = useAuthStore();

// Data form input dengan properti no_resi
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
  no_resi: "",
  id_user: user.user.id,
});

const formRef = ref();

const formSchema = Yup.object().shape({
  nama_pengirim: Yup.string().required("Nama Pengirim harus diisi"),
  alamat_pengirim: Yup.string().required("Alamat Pengirim harus diisi"),
  no_telp_pengirim: Yup.string().required("No. Telp Pengirim harus diisi"),
  nama_penerima: Yup.string().required("Nama Penerima harus diisi"),
  alamat_penerima: Yup.string().required("Alamat Penerima harus diisi"),
  no_telp_penerima: Yup.string().required("No. Telp Penerima harus diisi"),
  jenis_barang: Yup.string().required("Jenis Barang harus diisi"),
  jenis_layanan: Yup.string().required("Jenis Layanan harus diisi"),
  berat_barang: Yup.number()
    .required("Berat Barang harus diisi")
    .positive("Berat Barang harus lebih dari 0"),
});

function generateNoResi() {
  const prefix = "RESI";
  const timestamp = Date.now().toString();
  const random = Math.floor(1000 + Math.random() * 9000);
  return `${prefix}-${timestamp}-${random}`;
}

async function getEdit() {
  block(document.getElementById("form-Input"));
  try {
    const { data } = await ApiService.get("Input", props.selected);
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
      no_resi: data.no_resi || "",
      id_user: user.user.id,
    };
  } catch (err: any) {
    toast.error(err.response?.data?.message || "Gagal mengambil data");
  } finally {
    unblock(document.getElementById("form-Input"));
  }
}

async function submit() {
  block(document.getElementById("form-Input"));
  try {
    // Generate no_resi jika tambah data baru (bukan edit)
    if (!props.selected) {
      Input.value.no_resi = generateNoResi();
    }

    const formData = new FormData();
    Object.entries(Input.value).forEach(([key, val]) => {
      formData.append(key, val as any);
    });

    if (props.selected) {
      formData.append("_method", "PUT");
    }

    await axios({
      method: "post",
      url: props.selected ? `/input/${props.selected}` : "/input/store",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    toast.success("Data input berhasil disimpan");
    emit("refresh");
    emit("close");
    formRef.value.resetForm();
  } catch (err: any) {
    formRef.value.setErrors(err.response?.data?.errors);
    toast.error("Gagal menyimpan data: " + (err.response?.data?.message || ""));
  } finally {
    unblock(document.getElementById("form-Input"));
  }
}

onMounted(() => {
  if (props.selected) getEdit();
});

watch(
  () => props.selected,
  (val) => {
    if (val) getEdit();
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
        <div class="col-md-6" v-for="field in [
          { label: 'Nama Pengirim', name: 'nama_pengirim', type: 'text' },
          { label: 'Alamat Pengirim', name: 'alamat_pengirim', type: 'text' },
          { label: 'No. Telp Pengirim', name: 'no_telp_pengirim', type: 'text' },
          { label: 'Nama Penerima', name: 'nama_penerima', type: 'text' },
          { label: 'Alamat Penerima', name: 'alamat_penerima', type: 'text' },
          { label: 'No. Telp Penerima', name: 'no_telp_penerima', type: 'text' },
          { label: 'Jenis Barang', name: 'jenis_barang', type: 'text' },
          { label: 'Jenis Layanan', name: 'jenis_layanan', type: 'select', options: ['JNE','NINA','JNT','SICEPAT','SAP','IDE','TIKI','POS'] },
          { label: 'Berat Barang (gram)', name: 'berat_barang', type: 'number' },
        ]" :key="field.name">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6">{{ field.label }}</label>

            <Field
              v-if="field.type !== 'select'"
              class="form-control"
              :type="field.type"
              :name="field.name"
              v-model="Input[field.name]"
              :placeholder="'Masukkan ' + field.label"
              :min="field.type === 'number' ? 0 : undefined"
            />

            <Field
              v-else
              as="select"
              class="form-control"
              :name="field.name"
              v-model="Input[field.name]"
            >
              <option disabled value="">Pilih {{ field.label }}</option>
              <option v-for="option in field.options" :key="option" :value="option">
                {{ option }}
              </option>
            </Field>

            <ErrorMessage :name="field.name" class="text-danger" />
          </div>
        </div>

        <div class="col-12">
          <label class="form-label fw-bold fs-6">No Resi</label>
          <input
            type="text"
            class="form-control"
            v-model="Input.no_resi"
            readonly
          />
        </div>
      </div>
    </div>

    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">
        Simpan Data
      </button>
    </div>
  </VForm>
</template>
