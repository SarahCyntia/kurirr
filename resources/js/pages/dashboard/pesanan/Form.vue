<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Input } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useAuthStore } from "@/stores/auth";

const provinces = ref([]);
const cities = ref([]);
const selectedProvince = ref("");
const selectedCity = ref("");

const asal = ref('')
const tujuan = ref('')
const allCities = ref([])
const filteredKotaAsal = ref([])
const filteredKotaTujuan = ref([])

// Ambil kota di Jawa Timur (id provinsi 11)
onMounted(async () => {
  const res = await axios.get('/api/rajaongkir/cities?province_id=11')
  allCities.value = res.data
})

function searchKota(type) {
  const keyword = (type === 'asal' ? asal.value : tujuan.value).toLowerCase()
  const filtered = allCities.value.filter(kota =>
    kota.city_name.toLowerCase().startsWith(keyword)
  )

  if (type === 'asal') {
    filteredKotaAsal.value = filtered
  } else {
    filteredKotaTujuan.value = filtered
  }
}

function selectKota(type, kota) {
  const namaKota = ${kota.type} ${kota.city_name}
  if (type === 'asal') {
    asal.value = namaKota
    filteredKotaAsal.value = []
  } else {
    tujuan.value = namaKota
    filteredKotaTujuan.value = []
  }
}



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

onMounted(async () => {
    const { data } = await axios.get("/rajaongkir/provinces");
    provinces.value = data;
});

watch(selectedProvince, async (provId) => {
    if (!provId) return;
    const { data } = await axios.get("/rajaongkir/cities", {
        params: { province_id: provId },
    });
    cities.value = data;
});

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

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
              <div>
    <label for="asal">Alamat Asal</label>
    <input
      id="asal"
      v-model="asal"
      @input="searchKota('asal')"
      class="form-control"
      autocomplete="off"
    />
    <ul v-if="filteredKotaAsal.length && asal">
      <li v-for="kota in filteredKotaAsal" :key="kota.city_id" @click="selectKota('asal', kota)">
        {{ kota.type }} {{ kota.city_name }}
      </li>
    </ul>

    <label for="tujuan" class="mt-3">Alamat Tujuan</label>
    <input
      id="tujuan"
      v-model="tujuan"
      @input="searchKota('tujuan')"
      class="form-control"
      autocomplete="off"
    />
    <ul v-if="filteredKotaTujuan.length && tujuan">
      <li v-for="kota in filteredKotaTujuan" :key="kota.city_id" @click="selectKota('tujuan', kota)">
        {{ kota.type }} {{ kota.city_name }}
      </li>
    </ul>
  </div>
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
                            <option value="pick-up">
                                Pick-up (kurir jemput)
                            </option>
                            <option value="drop-off">
                                Drop-off (pelanggan antar)
                            </option>
                        </Field>
                        <ErrorMessage
                            name="metode_pengiriman"
                            class="text-danger"
                        />
                    </div>
                </div>

                <div class="col-md-6 mb-7">
                    <label class="form-label required fw-bold"
                        >Provinsi Asal</label
                    >
                    <select v-model="selectedProvince" class="form-control">
                        <option disabled value="">Pilih Provinsi</option>
                        <option
                            v-for="prov in provinces"
                            :key="prov.province_id"
                            :value="prov.province_id"
                        >
                            {{ prov.province }}
                        </option>
                    </select>
                </div>

                <div class="col-md-6 mb-7">
                    <label class="form-label required fw-bold">Kota Asal</label>
                    <select
                        v-model="transaksi.alamat_asal"
                        class="form-control"
                    >
                        <option disabled value="">Pilih Kota</option>
                        <option
                            v-for="city in cities"
                            :key="city.city_id"
                            :value="city.city_name"
                        >
                            {{ city.city_name }}
                        </option>
                    </select>
                    <ErrorMessage
                        name="alamat_asal"
                        class="text-danger small"
                    />
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

<style scoped>
ul {
  list-style: none;
  margin: 0;
  padding: 0;
  border: 1px solid #ccc;
  max-height: 150px;
  overflow-y: auto;
  background: #fff;
}
li {
  padding: 8px;
  cursor: pointer;
}
li:hover {
  background: #f0f0f0;
}
</style>
