<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Input } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useAuthStore } from "@/stores/auth";
import { useForm, Field, ErrorMessage, Form as VForm } from "vee-validate";
import Swal from "sweetalert2";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});
const authStore = useAuthStore();
const user = useAuthStore();
const provinces = ref<Record<string, string>>({});
const citiesOrigin = ref<Record<string, string>>({});
const citiesDestination = ref<Record<string, string>>({});
const provinceOrigin = ref("0");
const cityOrigin = ref("");
const provinceDestination = ref("0");
const cityDestination = ref("");
const Input = ref({
    nama_pengirim: "",
    alamat_pengirim: "",
    no_telp_pengirim: "",
    nama_penerima: "",
    alamat_penerima: "",
    no_telp_penerima: "",
    jenis_barang: "",
    ekspedisi: "",
    jenis_layanan: "",
    berat_barang: "",
    // no_resi: "",
    id_user: user.user.id,
    // status: "",
});
const formRef = ref();
// Data provinces, cities asal dan tujuan
const couriers = ref([
    { code: "jne", name: "JNE" },
    { code: "tiki", name: "TIKI" },
    { code: "pos", name: "POS Indonesia" },
]);
const selectedCourier = ref("");  // ekspedisi/kurir dipilih
const services = ref<{ service: string; description: string; cost: number; etd: string }[]>([]);
const selectedService = ref("");
const berat_barang = ref<number | null>(null);
const biaya = ref<number>(0);
const emit = defineEmits(["close", "refresh"]);

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
    nama_pengirim: Yup.string().required("Nama Pengirim harus diisi"),
    alamat_pengirim: Yup.string().required("Alamat Pengirim harus diisi"),
    no_telp_pengirim: Yup.string().required("No. Telp Pengirim harus diisi"),
    nama_penerima: Yup.string().required("Nama Penerima harus diisi"),
    alamat_penerima: Yup.string().required("Alamat Penerima harus diisi"),
    no_telp_penerima: Yup.string().required("No. Telp Penerima harus diisi"),
    ekspedisi: Yup.string().required("Ekspedisi harus dipilih"),
    jenis_barang: Yup.string().required("Jenis Barang harus diisi"),
    jenis_layanan: Yup.string().required("Jenis Layanan harus diisi"),
    berat_barang: Yup.string().required("Berat Barang harus diisi").min(0.1, "Berat minimal 0.1 kg"),
});

const { handleSubmit, errors, resetForm, } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nama_pengirim: "",
        alamat_pengirim: "",
        no_telp_pengirim: "",
        nama_penerima: "",
        alamat_penerima: "",
        no_telp_penerima: "",
        kurir: "",
        jenis_barang: "",
        jenis_layanan: "",
        berat_barang: "",
        provinceOrigin: "0",
        cityOrigin: "",
        provinceDestination: "0",
        cityDestination: "",
    }
});
const fetchProvinces = async () => {
    try {
        const res = await axios.get("/provinces");
        provinces.value = res.data;
    } catch (error) {
        toast.error("Gagal mengambil data provinsi");
    }
};

const fetchCities = async (type: "origin" | "destination") => {
    const provId = type === "origin" ? provinceOrigin.value : provinceDestination.value;
    if (provId === "0") return;
    try {
        const res = await axios.get(`/cities/${provId}`);
        if (type === "origin") {
            citiesOrigin.value = res.data;
            cityOrigin.value = "";
        } else {
            citiesDestination.value = res.data;
            cityDestination.value = "";
        }
    } catch (error) {
        toast.error("Gagal mengambil data kota");
    }
};
const getSelectedCost = () => {
    console.log('All services:', services.value);
    console.log('Selected service:', selectedService.value);

    const service = services.value.find(s => s.service === selectedService.value);
    const cost = service?.cost ?? 0;
    biaya.value = cost;

    console.log('Selected cost:', cost);
    return cost;
};

const fetchOngkir = async () => {
    if (
        provinceOrigin.value === "0" || !cityOrigin.value ||
        provinceDestination.value === "0" || !cityDestination.value ||
        !selectedCourier.value || !berat_barang.value || berat_barang.value <= 0
    ) {
        console.log(berat_barang.value)
        services.value = [];
        selectedService.value = "";
        biaya.value = 0;
        console.log("Keluar")
        return;
    }

    try {
        block(document.getElementById("form-input"));
        const res = await axios.post("/cost", {
            origin: cityOrigin.value,
            destination: cityDestination.value,
            weight: Math.round(berat_barang.value * 1000), // gram
            courier: selectedCourier.value,
        });
        // const resultServices = res.data.rajaongkir.results[0]?.costs || [];
        services.value = res.data.map((s: any) => ({
            service: s.service,
            description: s.description,
            cost: s.cost[0].value,
            etd: s.cost[0].etd
            // courier: selectedCourier.value.code,
        }));

        // Reset selected service dan biaya
        selectedService.value = "";
        biaya.value = 0;
        console.log("1", biaya.value)
    } catch (error) {
        toast.error("Gagal mengambil data ongkir");
        services.value = [];
        selectedService.value = "";
        biaya.value = 0;
        console.log("2", biaya.value)
    } finally {
        unblock(document.getElementById("form-transaksii"));
    }
};
watch([provinceOrigin, cityOrigin, provinceDestination, cityDestination, selectedCourier, berat_barang], () => {
    fetchOngkir();
});
watch(selectedService, (val) => {
    const service = services.value.find(s => s.service === val);
    biaya.value = service ? service.cost : 0;
    getSelectedCost();
});
// const submitForm = async () => {
//     if (!isEditMode.value) {
//         formData.value.no_resi = generateNoResi();
//     }

//     await axios.post("/input", formData.value);
//     emit("refresh");
//     emit("close");
// };

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
                ekspedisi: data.ekspedisi || "",
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
    formData.append("asal_provinsi_id", provinceOrigin.value);
    formData.append("asal_kota_id", cityOrigin.value);
    formData.append("alamat_pengirim", Input.value.alamat_pengirim);
    formData.append("no_telp_pengirim", Input.value.no_telp_pengirim);
    formData.append("nama_penerima", Input.value.nama_penerima);
    formData.append("tujuan_provinsi_id", provinceDestination.value);
    formData.append("tujuan_kota_id", cityDestination.value);
    formData.append("alamat_penerima", Input.value.alamat_penerima);
    formData.append("no_telp_penerima", Input.value.no_telp_penerima);
    formData.append("jenis_barang", Input.value.jenis_barang);
    formData.append("ekspedisi", Input.value.ekspedisi);
    formData.append("jenis_layanan", selectedService.value);
    formData.append("berat_barang", Input.value.berat_barang);
    formData.append("ekspedisi", selectedCourier.value);
    formData.append("berat_barang", berat_barang.value?.toString() || "0");
    formData.append("no_resi", noResi); // ⬅️ Gunakan noResi
    formData.append("id_user", Input.value.id_user);
    if (props.selected) {
        formData.append("_method", "PUT");
    } else {
        formData.append("waktu", new Date().toISOString());
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
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                html: `No. Resi berhasil dibuat:<br><strong>${noResi}</strong>`,
                showCancelButton: true,
                confirmButtonText: "Cetak Resi",
                cancelButtonText: "Tutup",
            }).then(() => {
                emit("close");
                emit("refresh");
                toast.success("Data berhasil disimpan");
                formRef.value.resetForm(); // Reset form setelah submit
            }).catch((err: any) => {
                const message = err.response?.data?.message || "Terjadi kesalahan.";
                toast.error(message);
            })
                .finally(() => {
                    unblock(document.getElementById("form-input"));
                });
        })

}

function generateNoResi() {
    const prefix = "RESI";
    const timestamp = Date.now().toString(); // angka unik berdasarkan waktu
    const random = Math.floor(1000 + Math.random() * 9000); // angka acak 4 digit
    return `${prefix}-${timestamp}-${random}`;
}

// ✅ Ambil data saat component dipasang
// onMounted(() => {
//     if (props.selected) {
//         getEdit();
//     }
// });

onMounted(() => {
    fetchProvinces();
});

// ✅ Pantau perubahan selected (Edit Mode)
// watch(
//     () => props.selected,
//     () => {
//         if (props.selected) {
//             getEdit();
//         }
//     }
// );
</script>

<template>
    <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-Input" ref="formRef">
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ props.selected ? "Edit" : "Tambah" }} Input</h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
                Batal <i class="la la-times-circle p-0"></i>
            </button>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Nama Pengirim</label>
                        <Field class="form-control" type="text" name="nama_pengirim" v-model="Input.nama_pengirim"
                            placeholder="Masukkan Nama Pengirim" />
                        <ErrorMessage name="nama_pengirim" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6 ">
                    <label class="form-label required fw-bold">Provinsi Asal</label>
                    <Field as="select" name="provinceOrigin" v-model="provinceOrigin" class="form-control"
                        @change="fetchCities('origin')">
                        <option value="0">-- Pilih Provinsi Asal --</option>
                        <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
                    </Field as="select">
                    <ErrorMessage name="provinceOrigin" class="text-danger small" />
                </div>

                <!-- Kota Asal -->
                <div class="col-md-6">
                    <label class="form-label required fw-bold">Kota Asal</label>
                    <Field as="select" name="cityOrigin" v-model="cityOrigin" class="form-control">
                        <option value="">-- Pilih Kota Asal --</option>
                        <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">{{ name }}</option>
                    </Field as="select">
                    <ErrorMessage name="cityOrigin" class="text-danger small" />
                    <!-- <div v-if="ErrorMessage" name="cityOrigin" class="text-danger">{{ errors.cityOrigin }}</div> -->
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Alamat Pengirim</label>
                        <Field class="form-control" type="text" name="alamat_pengirim" v-model="Input.alamat_pengirim"
                            placeholder="Masukkan Alamat Pengirim" />
                        <ErrorMessage name="alamat_pengirim" class="text-danger" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">No. Telp Pengirim</label>
                        <Field class="form-control" type="string" name="no_telp_pengirim"
                            v-model="Input.no_telp_pengirim" placeholder="Masukkan Nomor Telpon" />
                        <ErrorMessage name="no_telp_pengirim" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Nama Penerima</label>
                        <Field class="form-control" type="text" name="nama_penerima" v-model="Input.nama_penerima"
                            placeholder="Masukkan Nama Penerima" />
                        <ErrorMessage name="nama_penerima" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label required fw-bold">Provinsi Tujuan</label>
                    <Field as="select" name="provinceDestination" v-model="provinceDestination" class="form-control"
                        @change="fetchCities('destination')">
                        <option value="0">-- Pilih Provinsi Tujuan --</option>
                        <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
                    </Field as="select">
                    <ErrorMessage name="provinceDestination" class="text-danger small" />
                    <!-- <div v-if="ErrorMessage" name="provinceDestination" class="text-danger">{{ errors.provinceDestination }}</div> -->
                </div>

                <!-- Kota Tujuan -->
                <div class="col-md-6">
                    <label class="form-label required fw-bold">Kota Tujuan</label>
                    <Field as="select" name="cityDestination" v-model="cityDestination" class="form-control">
                        <option value="">-- Pilih Kota Tujuan --</option>
                        <option v-for="(name, id) in citiesDestination" :key="id" :value="id">{{ name }}</option>
                    </Field as="select">
                    <ErrorMessage name="cityDestination" class="text-danger small" />
                    <!-- <div v-if="ErrorMessage" name="cityDestination" class="text-danger">{{ errors.cityDestination }}</div> -->
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Alamat Penerima</label>
                        <Field class="form-control" type="text" name="alamat_penerima" v-model="Input.alamat_penerima"
                            placeholder="Masukkan Alamat Penerima" />
                        <ErrorMessage name="alamat_penerima" class="text-danger" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">No. Telp Penerima</label>
                        <Field class="form-control" type="string" name="no_telp_penerima"
                            v-model="Input.no_telp_penerima" placeholder="Masukkan Nomor Telpon" />
                        <ErrorMessage name="no_telp_penerima" class="text-danger" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Jenis Barang</label>
                        <Field class="form-control" type="text" name="jenis_barang" v-model="Input.jenis_barang"
                            placeholder="Masukkan Jenis Barang" />
                        <ErrorMessage name="jenis_barang" class="text-danger" />
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label required fw-bold">Ekspedisi</label>
                    <field as="select" v-model="selectedCourier" class="form-control" name="ekspedisi">
                        <option value="">-- Pilih Ekspedisi --</option>
                        <option v-for="c in couriers" :key="c.code" :value="c.code">{{ c.name }}</option>
                    </Field as="select">
                    <ErrorMessage name="ekspedisi" class="text-danger small" />
                    <!-- <div v-if="errors.kurir" class="text-danger">{{ errors.kurir }}</div> -->
                </div>

                <div class="col-md-6">
                    <label for="jenis_layanan" class="form-label required fw-bold">Jenis Layanan</label>
                    <Field as="select" id="jenis_layanan" name="jenis_layanan" class="form-select"
                        v-model="selectedService" @change="getSelectedCost">
                        <option value="">Jenis
                            layanan</option>
                        <option v-for="service in services" :key="service.service" :value="service.service">
                            {{ service.service }} - Rp{{ Number(service.cost).toLocaleString() }} { {{ service.etd }}
                            Hari }
                        </option>
                    </Field as="select">
                    <ErrorMessage name="jenis_layanan" class="text-danger small" />
                    <!-- <div v-if="errors.layanan" class="text-danger">{{ errors.layanan }}</div> -->
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">Berat Barang (gram)</label>
                        <Field class="form-control" type="number" name="berat_barang" v-model="Input.berat_barang"
                            placeholder="Masukkan Berat Barang (gram)" min="0" />
                        <ErrorMessage name="berat_barang" class="text-danger" />
                    </div>
                </div>
                <div class="col-md-4 mb-7" v-if="services.length > 0">
                    <label class="form-label fw-bold">Biaya (Rp)</label>
                    <input type="text" name="biaya" class="form-control"
                        :value="biaya ? biaya.toLocaleString('id-ID') : '-'" readonly />
                </div>


                <div class="col-12">
                    <label class="form-label fw-bold fs-6"></label>
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
                Dapatkan No. Resi
            </button>
        </div>
    </VForm>
</template>
