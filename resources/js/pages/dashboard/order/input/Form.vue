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
interface District {
    name: string;
}
// const allDistrictsOrigin = ref<District[]>([]);

const authStore = useAuthStore();
const user = useAuthStore();
const provinces = ref<Record<string, string>>({});
// const provinces = ref<{ id: string; name: string }[]>([]);
const provincesDestination = ref<{ id: string; name: string }[]>([]);
const provincesOrigin = ref<{ id: string; name: string }[]>([]);
// const searchCityDestination = ref("");
// const showCityDropdownDestination = ref(false);
const searchDistrictDestination = ref("");
const searchDistrictOrigin = ref("");
const showDistrictDropdownDestination = ref(false);
const showDistrictDropdownOrigin = ref(false);
const showDropdownDistrictOrigin = ref(false);
const showDropdownDistrictDestination = ref(false);

const districtsDestination = ref<{ id: string; name: string }[]>([]);
const districtsOrigin = ref<{ id: string; name: string }[]>([]);
//salah semua
// const citiesOrigin = ref<any[]>([]);
const allDistrictsOrigin = ref<any[]>([]);

const cities = ref<{ id: string; name: string }[]>([]);
const citiesDestination = ref<{ id: string; name: string }[]>([]);
const showDropdownCityDestination = ref(false);
const allCitiesDestination = ref([]);
const citiesOrigin = ref<{ id: string; name: string }[]>([]);
const showDropdownCityOrigin = ref(false);
const allCitiesOrigin = ref([]);
const allDistrictsDestination = ref<{ id: string; name: string }[]>([]);
// const allDistrictsOrigin = ref<{ id: string; name: string }[]>([]);
// const allDistrictsOrigin = ref(false);

const showProvinceDropdownOrigin = ref(false);
const showProvinceDropdownDestination = ref(false);
const showCityDropdownOrigin = ref(false);
const showCityDropdownDestination = ref(false);
// const searchCityDestination = ref("");
const searchProvinceDestination = ref('');
const districts = ref<{ id: string; name: string }[]>([]);


// const citiesOrigin = ref<Record<string, string>>({});
// const citiesDestination = ref<Record<string, string>>({});
// const districtsOrigin = ref<Record<string, string>>({});
// const districtsDestination = ref<Record<string, string>>({});
const provinceOrigin = ref("0");
const cityOrigin = ref("");
const districtOrigin = ref("");
const provinceDestination = ref("0");
const cityDestination = ref("");
const districtDestination = ref("");
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
    // no_resi: "",
    id_user: user.user.id,
    // status: "",
});
const formRef = ref();
// Data provinces, cities asal dan tujuan
const couriers = ref([
    { code: "jne", name: "JNE" },
    { code: "tiki", name: "TIKI" },
    // { code: "pos", name: "POS Indonesia" },
    { code: "pos", name: "POS" },
]);
const selectedCourier = ref("");  // ekspedisi/kurir dipilih
const services = ref<{ service: string; description: string; cost: number; etd: string }[]>([]);
const selectedService = ref("");
const berat_barang = ref<number | null>(null);
const biaya = ref<number>(0);
const emit = defineEmits(["close", "refresh"]);
// const props = defineProps({selected: {type: String, default:null}});

// ✅ Validasi form menggunakan Yup
const formSchema = Yup.object().shape({
    nama_pengirim: Yup.string().required("Nama Pengirim harus diisi"),
    alamat_pengirim: Yup.string().required("Alamat Pengirim harus diisi"),
    no_telp_pengirim: Yup.string().required("No.Telpo Pengirim harus diisi"),
    nama_penerima: Yup.string().required("Nama Penerima harus diisi"),
    alamat_penerima: Yup.string().required("Alamat Penerima harus diisi"),
    no_telp_penerima: Yup.string().required("No. Telp Penerima harus diisi"),
    ekspedisi: Yup.string().required("Ekspedisi harus dipilih"),
    jenis_barang: Yup.string().required("Jenis Barang harus diisi"),
    jenis_layanan: Yup.string().required("Jenis Layanan harus diisi"),
    berat_barang: Yup.string().required("Berat Barang harus diisi").min(0.1, "Berat minimal 0.1 kg"),
});

const { handleSubmit, errors, resetForm, setFieldValue } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nama_pengirim: "",
        alamat_pengirim: "",
        no_telp_pengirim: "",
        nama_penerima: "",
        alamat_penerima: "",
        no_telp_penerima: "",
        ekspedisi: "",
        jenis_barang: "",
        jenis_layanan: "",
        berat_barang: "",
        provinceOrigin: "0",
        cityOrigin: "",
        districtOrigin: "",
        provinceDestination: "0",
        cityDestination: "",
        districtDestination: "",
    }
});




// PROVINSI ASAL
const searchProvinceOrigin = ref('');
const selectProvinceOrigin = (prov: { id: string; name: string }) => {
    searchProvinceOrigin.value = prov.name;
    provinceOrigin.value = prov.id;
    setFieldValue("provinceOrigin", prov.id); // sinkron ke VeeValidate
    fetchCities("origin");
    showDropdownOrigin.value = false;

    fetchCities("origin");

    // Reset kota yang sebelumnya mungkin sudah terisi
    cityOrigin.value = "";
    searchCityOrigin.value = "";
};
const filteredProvincesOrigin = computed(() => {
    return provinces.value.filter(prov =>
        prov.name.toLowerCase().includes(searchProvinceOrigin.value.toLowerCase())
    );
});
const hideProvinceDropdownOriginWithDelay = () => {
    setTimeout(() => {
        showProvinceDropdownOrigin.value = false;
    }, 200);
};

watch(searchProvinceOrigin, async (val) => {
    if (!val) return;

    const selectedProvince = provincesOrigin.value.find(
        (prov) => prov.name.toLowerCase() === val.toLowerCase()
    );

    if (!selectedProvince) return;

    try {
        const res = await axios.get(`/cities/${selectedProvince.id}`);
        allCitiesOrigin.value = res.data;
    } catch (err) {
        console.error("Gagal ambil kota", err);
    }
});
const showDropdownOrigin = ref(false);
onMounted(async () => {
    try {
        const res = await axios.get("/provinces");
        provincesOrigin.value = res.data;
    } catch (err) {
        console.error("Gagal ambil provinsi", err);
    }
});

watch(provinceOrigin, (newVal) => {
    if (newVal) {
        fetchCitiesByProvince(newVal);
    } else {
        citiesOrigin.value = [];
    }
});
// watch(provinceOrigin, (newVal) => {
//     if (newVal) {
//         fetchCitiesByProvince(newVal);
//     } else {
//         citiesOrigin.value = [];
//     }
// });







// KOTA ASAL
const searchCityOrigin = ref("");
// const cityOrigin = ref("");
// const showCityDropdownOrigin = ref(false);

const filteredCitiesOrigin = computed(() => {
    return cities.value.filter(city =>
        city.name.toLowerCase().includes(searchCityOrigin.value.toLowerCase())
    );
});

const selectCityOrigin = (city: { id: string; name: string }) => {
    searchCityOrigin.value = city.name;
    cityOrigin.value = city.id;
    setFieldValue("cityOrigin", city.id);
    showDropdownCityOrigin.value = false;
    console.log("cityOrigin:", cityOrigin.value);
    fetchDistricts("origin");
};
// const hideDropdownCityOriginWithDelay = () => {
//     setTimeout(() => {
//         showDropdownCityOrigin.value = false;
//     }, 200);
// };
const fetchCitiesOrigin = async (provinceId: string) => {
    try {
        const res = await axios.get(`/cities/${provinceId}`);
        allCitiesOrigin.value = res.data;
    } catch (err) {
        console.error("Gagal ambil kota asal", err);
    }
};
watch(searchCityOrigin, (val) => {
    console.log("searchCityOrigin input:", val);

    if (!val) {
        filteredCitiesOrigin.value = [];
        showDropdownCityOrigin.value = false;
        return;
    }

    filteredCitiesOrigin.value = allCitiesOrigin.value.filter(city =>
        city.name.toLowerCase().includes(val.toLowerCase())
    );

    console.log("filteredCitiesOrigin:", filteredCitiesOrigin.value);

    // showDropdownCityOrigin.value = filteredCitiesOrigin.value.length > 0;
});
// const hideCityDropdownOriginWithDelay = () => {
//     setTimeout(() => {
//         showCityDropdownOrigin.value = false;
//     }, 200);
// };




// // KECAMATAN ASAL

const filteredDistrictsOrigin = computed(() => {
    return districtsOrigin.value.filter(district =>
        district.name.toLowerCase().includes(searchDistrictOrigin.value.toLowerCase())
    );
});

const fetchDistrictsOrigin = async (cityId: string) => {
    try {
        const res = await axios.get(`/districts/${cityId}`);
        allDistrictsOrigin.value = res.data;
        // filteredDistrictsOrigin.value = allDistrictsOrigin.value;
    } catch (err) {
        console.error("Gagal ambil kecamatan asal", err);
    }
};

const hideDropdownDistrictOriginWithDelay = () => {
    setTimeout(() => {
        showDropdownDistrictOrigin.value = false;
    }, 200);
};
const hideDistrictDropdownOriginWithDelay = () => {
    setTimeout(() => {
        showDistrictDropdownOrigin.value = false;
    }, 200);
};

const limitDistrictOrigin = ref(10);
const displayedDistrictsOrigin = computed(() => {
  return filteredDistrictsOrigin.value.slice(0, limitDistrictOrigin.value);
});

// const limitCityOrigin = ref(10); // batas maksimal kota yang ditampilkan
// const displayedDistrictsOrigin = computed(() => {
//   return filteredDistrictsOrigin.value.slice(0, limitCityOrigin.value);
// });
const selectDistrictOrigin = (district: { id: string; name: string }) => {
  searchDistrictOrigin.value = district.name;
  districtOrigin.value = district.id;
  setFieldValue("districtOrigin", district.id);
  showDistrictDropdownOrigin.value = false;
};
watch(searchDistrictOrigin, (val) => {
    console.log("searchDistrictOrigin input:", val);

    if (!val) {
        filteredDistrictsOrigin.value = [];
        showDropdownDistrictOrigin.value = false;
        return;
    }

    filteredDistrictsOrigin.value = allDistrictsOrigin.value.filter(district =>
        district.name.toLowerCase().includes(val.toLowerCase())
    );

    console.log("filteredDistrictsOrigin:", filteredDistrictsOrigin.value);

    // showDropdownDistrictOrigin.value = filteredDistrictsOrigin.value.length > 0;
});







// PROVINSI TUJUAN
const selectProvinceDestination = (prov: { id: string; name: string }) => {
    searchProvinceDestination.value = prov.name;
    provinceDestination.value = prov.id;
    setFieldValue("provinceDestination", prov.id); // sinkron ke VeeValidate
    fetchCities("destination");
    showDropdownDestination.value = false;

    fetchCities("destination");

    // Reset kota yang sebelumnya mungkin sudah terisi
    cityDestination.value = "";
    searchCityDestination.value = "";
};
// const searchProvinceDestination = ref('');
const filteredProvincesDestination = computed(() => {
    return provinces.value.filter(prov =>
        prov.name.toLowerCase().includes(searchProvinceDestination.value.toLowerCase())
    );
});
const hideProvinceDropdownDestinationWithDelay = () => {
    setTimeout(() => {
        showProvinceDropdownDestination.value = false;
    }, 200);
};
watch(searchProvinceDestination, async (val) => {
    if (!val) return;

    const selectedProvince = provincesDestination.value.find(
        (prov) => prov.name.toLowerCase() === val.toLowerCase()
    );

    if (!selectedProvince) return;

    try {
        const res = await axios.get(`/cities/${selectedProvince.id}`);
        allCitiesDestination.value = res.data;
    } catch (err) {
        console.error("Gagal ambil kota", err);
    }
});
const showDropdownDestination = ref(false);
onMounted(async () => {
    try {
        const res = await axios.get("/provinces");
        provincesDestination.value = res.data;
    } catch (err) {
        console.error("Gagal ambil provinsi", err);
    }
});
watch(provinceDestination, (newVal) => {
    if (newVal) {
        fetchCitiesByProvince(newVal);
    } else {
        citiesDestination.value = [];
    }
});
watch(provinceDestination, () => {
    console.log(provinceDestination.value);
})
// ngambil data provinsi dari tabel

// watch(provinceDestination, (newVal) => {
//     if (newVal) {
//         fetchCitiesByProvince(newVal);
//     } else {
//         cities.value = [];
//     }
// });




// KOTA Tujuan
const searchCityDestination = ref("");
// const cityDestination = ref("");
// const showCityDropdownDestination = ref(false);

const filteredCitiesDestination = computed(() => {
    return cities.value.filter(city =>
        city.name.toLowerCase().includes(searchCityDestination.value.toLowerCase())
    );
});

const selectCityDestination = (city: { id: string; name: string }) => {
    searchCityDestination.value = city.name;
    cityDestination.value = city.id;
    setFieldValue("cityDestination", city.id);
    showDropdownCityDestination.value = false;
    fetchDistricts("destination");
};
// const hideDropdownCityDestinationWithDelay = () => {
//     setTimeout(() => {
//         showDropdownCityDestination.value = false;
//     }, 200);
// };
const fetchCitiesDestination = async (provinceId: string) => {
    try {
        const res = await axios.get(`/cities/${provinceId}`);
        allCitiesDestination.value = res.data;
    } catch (err) {
        console.error("Gagal ambil kota tujuan", err);
    }
};
watch(searchCityDestination, (val) => {
    console.log("searchCityDestination input:", val);

    if (!val) {
        filteredCitiesDestination.value = [];
        showDropdownCityDestination.value = false;
        return;
    }

    filteredCitiesDestination.value = allCitiesDestination.value.filter(city =>
        city.name.toLowerCase().includes(val.toLowerCase())
    );

    console.log("filteredCitiesDestination:", filteredCitiesDestination.value);

    // showDropdownCityDestination.value = filteredCitiesDestination.value.length > 0;
});
// const hideCityDropdownDestinationWithDelay = () => {
//     setTimeout(() => {
//         showCityDropdownDestination.value = false;
//     }, 200);
// };



// KECAMATAN TUJUAN
const filteredDistrictsDestination = computed(() => {
    return districtsDestination.value.filter(district =>
        district.name.toLowerCase().includes(searchDistrictDestination.value.toLowerCase())
    );
});

const fetchDistrictsDestination = async (cityId: string) => {
    try {
        const res = await axios.get(`/districts/${cityId}`);
        allDistrictsDestination.value = res.data;
        // filteredDistrictsDestination.value = allDistrictsDestination.value;
    } catch (err) {
        console.error("Gagal ambil kecamatan asal", err);
    }
};

const hideDropdownDistrictDestinationWithDelay = () => {
    setTimeout(() => {
        showDropdownDistrictDestination.value = false;
    }, 200);
};
const hideDistrictDropdownDestinationWithDelay = () => {
    setTimeout(() => {
        showDistrictDropdownDestination.value = false;
    }, 200);
};

const limitDistrictDestination = ref(10);
const displayedDistrictsDestination = computed(() => {
  return filteredDistrictsDestination.value.slice(0, limitDistrictDestination.value);
});

// const limitCityDestination = ref(10); // batas maksimal kota yang ditampilkan
// const displayedDistrictsDestination = computed(() => {
//   return filteredDistrictsDestination.value.slice(0, limitCityDestination.value);
// });
const selectDistrictDestination = (district: { id: string; name: string }) => {
  searchDistrictDestination.value = district.name;
  districtDestination.value = district.id;
  setFieldValue("districtDestination", district.id);
  showDistrictDropdownDestination.value = false;
};
watch(searchDistrictDestination, (val) => {
    console.log("searchDistrictDestination input:", val);

    if (!val) {
        filteredDistrictsDestination.value = [];
        showDropdownDistrictDestination.value = false;
        return;
    }

    filteredDistrictsDestination.value = allDistrictsDestination.value.filter(district =>
        district.name.toLowerCase().includes(val.toLowerCase())
    );

    console.log("filteredDistrictsDestination:", filteredDistrictsDestination.value);

    // showDropdownDistrictOrigin.value = filteredDistrictsOrigin.value.length > 0;
});








// const selectProvinceOrigin = (prov: { id: string; name: string }) => {
//     searchProvinceOrigin.value = prov.name;
//     provinceOrigin.value = prov.id;
//     setFieldValue("provinceOrigin", prov.id); // sync ke VeeValidate
//     fetchCities("origin");
//     showProvinceDropdownOrigin.value = false;
// };













let hideCityDropdownTimeout: ReturnType<typeof setTimeout>;







// const showDistrictDropdownDestination = ref(false);








// const hideDropdownWithDelayDestination = () => {
//     setTimeout(() => {
//         showDropdownDestination.value = false;
//     }, 200);
// };
// watch(
//     () => searchProvinceDestination.value,
//     () => {
//         console.log("Hasil filter:", filteredProvincesDestination.value);
//     }
// );

const hideDropdownWithDelay = () => {
    setTimeout(() => {
        showDropdownDestination.value = false;
    }, 200);
};


const hideDropdownWithDelayOrigin = () => {
    setTimeout(() => {
        showDropdownOrigin.value = false;
    }, 200);
};

// watch(
//     () => searchProvinceOrigin.value,
//     () => {
//         console.log("Hasil filter:", filteredProvincesOrigin.value);
//     }
// );
const fetchDistricts = async (type: "origin" | "destination") => {
    const cityId = type === "origin" ? cityOrigin.value : cityDestination.value;
    if (!cityId) return;

    try {
        console.log("Fetching districts for cityId:", cityId, "type:", type);
        const res = await axios.get(`/districts/${cityId}`);
        const formatted = Object.entries(res.data).map(([id, name]) => ({ id, name }));

        if (type === "origin") {
            districtsOrigin.value = formatted;
        } else {
            districtsDestination.value = formatted;
        }
    } catch {
        toast.error("Gagal mengambil kecamatan");
    }
};

// ica
// const fetchProvinces = async () => {
//   try {
//     const res = await axios.get("/provinces");

//     // Jika hasilnya { "11": "Aceh", "12": "Bali", ... }
//     provinceDestination.value = Object.entries(res.data).map(([id, name]) => ({
//       id,
//       name,
//     }));
//     provinceOrigin.value = Object.entries(res.data).map(([id, name]) => ({
//       id,
//       name,
//     }));    

//   } catch {
//     toast.error("Gagal mengambil provinsi");
//   }
// };
const fetchProvinces = async () => {
    try {
        const res = await axios.get("/provinces");

        // Jika respon adalah object seperti: { "35": "Jawa Timur", ... }
        // Maka konversi manual:
        provinces.value = Object.entries(res.data).map(([id, name]) => ({
            id,
            name
        }));

        // Kalau res.data sudah array of object { id, name }, cukup langsung:
        // provinces.value = res.data;

    } catch {
        toast.error("Gagal mengambil data provinsi");
    }
};


const fetchCitiesByProvince = async (provinceId: string) => {
    try {
        const res = await axios.get(`/cities/${provinceId}`);

        // Jika respon API adalah object: { "256": "JEMBER", ... }
        cities.value = Object.entries(res.data).map(([id, name]) => ({
            id,
            name
        }));

        // Kalau sudah array of object { id, name } langsung:
        // cities.value = res.data;

    } catch {
        toast.error("Gagal mengambil data kota");
    }
};




const fetchCities = async (type: "origin" | "destination") => {
    const provId = type === "origin" ? provinceOrigin.value : provinceDestination.value;
    if (provId === "0") return;
    try {
        const res = await axios.get(`/cities/${provId}`);
        if (type === "origin") {
            citiesOrigin.value = Object.entries(res.data).map(([id, name]) => ({
                id,
                name,
            }));

        } else {
            citiesDestination.value = Object.entries(res.data).map(([id, name]) => ({
                id,
                name,
            }));
        }
    } catch {
        toast.error("Gagal mengambil kota");
    }
};
// ini sebelumnya
// const fetchCities = async (type: "destination" | "origin") => {
//     try {
//         const provinceId =
//             type === "destination" ? provinceDestination.value : provinceOrigin.value;

//         const res = await axios.get(`/cities/${provinceId}`);

//         const citiesData = Object.entries(res.data).map(([id, name]) => ({
//             id,
//             name
//         }));

//         // if (type === "destination") {
//         //     citiesDestination.value = res.data;
//         //     cityDestination.value = ""
//         // } else {
//         //     citiesOrigin.value = res.data;
//         //     cityOrigin.value = "";
//         // }
//     } catch {
//         toast.error("Gagal mengambil data kota");
//     }
// };

// ini sebelumnya
// const fetchProvinces = async () => {
//     try {
//         const res = await axios.get("/provinces");
//         provinces.value = res.data;
//         console.log(provinces.value)
//     } catch {
//         toast.error("Gagal mengambil data provinsi");
//     }
// };


// const getSelectedCost = () => {
//     console.log('All services:', services.value);
//     console.log('Selected service:', selectedService.value);

//     const service = services.value.find(s => s.service === selectedService.value);
//     const cost = service?.cost ?? 0;
//     biaya.value = cost;

//     console.log('Selected cost:', cost);
//     return cost;
// };



const fetchOngkir = async () => {
  // Validasi input ongkir
  if (
    provinceOrigin.value === "0" || !cityOrigin.value || !districtOrigin.value ||
    provinceDestination.value === "0" || !cityDestination.value || !districtDestination.value ||
    !selectedCourier.value || !berat_barang.value || berat_barang.value <= 0
  ) {
    console.log("provinceOrigin:", provinceOrigin.value);
    console.log("cityOrigin:", cityOrigin.value);
    console.log("districtOrigin:", districtOrigin.value);
    console.log("provinceDestination:", provinceDestination.value);
    console.log("cityDestination:", cityDestination.value);
    console.log("districtDestination:", districtDestination.value);
    console.log("selectedCourier:", selectedCourier.value);
    console.log("berat_barang:", berat_barang.value);
    // Reset data ongkir jika input tidak lengkap
    console.log("Input ongkir tidak lengkap atau tidak valid");
    services.value = [];
    selectedService.value = "";
    biaya.value = 0;
    return;
  }

  try {
    block(document.getElementById("form-input"));
    const res = await axios.post("/cost", {
      // origin: cityOrigin.value,
      // destination: cityDestination.value,
      origin: districtOrigin.value,
      destination: districtDestination.value,
      weight: Math.round(berat_barang.value * 1000),
      courier: selectedCourier.value,
      price: "lowest" // default sesuai backend
    });

    services.value = res.data.map((s: any) => ({
      service: s.service,
      description: s.description,
      cost: s.cost,
      etd: s.cost,
    }));

    selectedService.value = "";
    biaya.value = 0;
  } catch {
    toast.error("Gagal mengambil ongkir");
  } finally {
    unblock(document.getElementById("form-input"));
  }
};

// ==========================
// Ongkir & Layanan
// ==========================
const getSelectedCost = () => {
    const service = services.value.find(s => s.service === selectedService.value);
    biaya.value = service?.cost ?? 0;
};
// const fetchOngkir = async () => {
//     if (
//         provinceOrigin.value === "0" || !cityOrigin.value ||
//         provinceDestination.value === "0" || !cityDestination.value ||
//         !selectedCourier.value || !berat_barang.value || berat_barang.value <= 0
//     ) {
//         console.log(berat_barang.value)
//         services.value = [];
//         selectedService.value = "";
//         biaya.value = 0;
//         console.log("Keluar")
//         return;
//     }

//     try {
//         block(document.getElementById("form-input"));
//         const res = await axios.post("/cost", {
//             origin: cityOrigin.value,
//             destination: cityDestination.value,
//             weight: Math.round(berat_barang.value * 1000), // gram
//             courier: selectedCourier.value,
//         });
//         console.log("DEBUG: response from /cost", res.data);
//         // const resultServices = res.data.rajaongkir.results[0]?.costs || [];
//         services.value = res.data.map((s: any) => ({
//             service: s.service,
//             description: s.description,
//             cost: s.cost[0].value,
//             etd: s.cost[0].etd
//             // courier: selectedCourier.value.code,
//         }));

//         // Reset selected service dan biaya
//         selectedService.value = "";
//         biaya.value = 0;
//         console.log("1", biaya.value)
//     } catch (error) {
//         toast.error("Gagal mengambil data ongkir");
//         services.value = [];
//         selectedService.value = "";
//         biaya.value = 0;
//         console.log("2", biaya.value)
//     } finally {
//         unblock(document.getElementById("form-input"));
//     }
// };   
// watch([provinceOrigin, cityOrigin, provinceDestination, cityDestination, selectedCourier, berat_barang], () => {
//     fetchOngkir();
// });
watch([provinceOrigin, cityOrigin, provinceDestination, cityDestination, districtDestination, districtOrigin, selectedCourier, berat_barang], () => {
    console.log("Watching changes in form fields...");
    fetchOngkir();
});

watch(selectedService, (val) => {
    const service = services.value.find(s => s.service === val);
    biaya.value = service ? service.cost : 0;
    getSelectedCost();
});


// ✅ Submit Form (Tambah/Update)
function submit() {
    const formData = new FormData();
    const noResi = generateNoResi(); // ⬅️ Simpan no_resi di sini

    formData.append("nama_pengirim", Input.value.nama_pengirim);
    formData.append("asal_provinsi_id", provinceOrigin.value);
    formData.append("asal_kota_id", cityOrigin.value);
    formData.append("asal_kecamatan_id", districtOrigin.value);
    formData.append("alamat_pengirim", Input.value.alamat_pengirim);
    formData.append("no_telp_pengirim", Input.value.no_telp_pengirim);
    formData.append("nama_penerima", Input.value.nama_penerima);
    formData.append("tujuan_provinsi_id", provinceDestination.value);
    formData.append("tujuan_kota_id", cityDestination.value);
    formData.append("tujuan_kecamatan_id", districtDestination.value);
    formData.append("alamat_penerima", Input.value.alamat_penerima);
    formData.append("no_telp_penerima", Input.value.no_telp_penerima);
    formData.append("jenis_barang", Input.value.jenis_barang);
    formData.append("jenis_layanan", selectedService.value);
    formData.append("ekspedisi", selectedCourier.value);
    formData.append("berat_barang", berat_barang.value?.toString() || "0");
    formData.append("biaya", biaya.value?.toString() || "0"); // ⬅️ Tambahkan biaya
    formData.append("no_resi", noResi); // ⬅️ Gunakan noResi
    formData.append("id_user", Input.value.id_user);
    if (props.selected) {
        formData.append("_method", "PUT");
    } else {
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
            // ⬅️ Modifikasi SweetAlert untuk menampilkan biaya
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                html: `
            <div style="text-align: left; max-width: 400px; margin: 0 auto;">
                <p><strong>No. Resi:</strong> ${noResi}</p>
                <p><strong>Biaya Pengiriman:</strong> Rp ${biaya.value.toLocaleString('id-ID')}</p>
                <p><strong>Ekspedisi:</strong> ${selectedCourier.value.toUpperCase()}</p>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: "Oke",
                cancelButtonText: "Batal",
                customClass: {
                    popup: 'swal-wide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika tombol "Oke" diklik
                    emit("close");
                    emit("refresh");
                    toast.success("Data berhasil disimpan");
                    formRef.value.resetForm(); // Reset form setelah submit
                } else if (result.isDismissed) {
                    // Jika tombol "Batal" diklik, tampilkan pesan dan tetap di form
                    toast.info("Anda tetap berada di halaman input.");
                }
            });
        })
        .catch((err: any) => {
            const message = err.response?.data?.message || "Terjadi kesalahan.";
            toast.error(message);
        })
        .finally(() => {
            unblock(document.getElementById("form-input"));
        });

}


function generateNoResi() {
    const prefix = "RESI";
    const timestamp = Date.now().toString(); // angka unik berdasarkan waktu
    const random = Math.floor(1000 + Math.random() * 9000); // angka acak 4 digit
    return `${prefix}-${timestamp}-${random}`;
}

onMounted(() => {
    fetchProvinces();
});



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

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Provinsi Asal</label>

                        <Field name="provinceOrigin">
                            <input type="text" class="form-control" v-model="searchProvinceOrigin"
                                placeholder="Ketik Provinsi Tujuan" @focus="showDropdownOrigin = true"
                                @blur="hideDropdownWithDelayOrigin" autocomplete="off" />
                        </Field>
                        <ErrorMessage name="provinceOrigin" class="text-danger small" />

                        <ul v-if="showDropdownOrigin && filteredProvincesOrigin.length"
                            class="list-group position-absolute w-100" style="z-index: 1000;">
                            <li v-for="prov in filteredProvincesOrigin" :key="prov.id"
                                class="list-group-item list-group-item-action"
                                @mousedown.prevent="selectProvinceOrigin(prov)">
                                {{ prov.name }}
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- <div class="col-md-6 ">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Provinsi Asal</label>
                        <Field as="select" name="provinceOrigin" v-model="provinceOrigin" class="form-control"
                            @change="fetchCities('origin')">
                            <option value="0">-- Pilih Provinsi Asal--</option>
                            <option v-for="(prov) in provinces" :key="prov.id" :value="prov.id">{{ prov.name }}</option>bukan ini
                            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
                        </Field>
                        <ErrorMessage name="provinceOrigin" class="text-danger small" />
                    </div>
                </div> -->

                <!-- Kota Asal -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Kota Asal</label>
                        <input type="text" v-model="searchCityOrigin" class="form-control"
                            @focus="showDropdownCityOrigin = true"
                            @blur="setTimeout(() => showDropdownCityOrigin = false, 200)"
                            placeholder="Ketik Kota Asal" />

                        <ul v-if="showDropdownCityOrigin" class="dropdown-menu show w-100"
                            style="max-height: 200px; overflow-y: auto;">
                            <li v-for="(city, index) in filteredCitiesOrigin" :key="index"
                                @click="searchCityOrigin = city.name; showDropdownCityOrigin = false"
                                @mousedown.prevent="selectCityOrigin(city)"
                                class="dropdown-item">
                                {{ city.name }}
                            </li>
                        </ul>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Kecamatan Asal</label>
                        <input type="text" v-model="searchDistrictOrigin" class="form-control"
                            @focus="showDropdownDistrictOrigin = true"
                            @blur="setTimeout(() => showDropdownDistrictOrigin = false, 200)"
                            placeholder="Ketik Kecamatan Asal" />

                        <ul v-if="showDropdownDistrictOrigin" class="dropdown-menu show w-100"
                            style="max-height: 200px; overflow-y: auto;">
                            <li v-for="(district, index) in filteredDistrictsOrigin" :key="index"
                                @click="searchDistrictOrigin = district.name; showDropdownDistrictOrigin = false; selectDistrictOrigin(district)"
                                class="dropdown-item">
                                {{ district.name }}    
                            </li>
                        </ul>
                    </div>
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


                <!-- <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Provinsi Tujuan</label>
                        <Field as="select" name="provinceDestination" v-model="provinceDestination" class="form-control"
                            @change="fetchCities('destination')">
                            <option value="0">-- Pilih Provinsi Tujuan --</option>
                            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
                        </Field as="select">
                        <ErrorMessage name="provinceDestination" class="text-danger small" />
                        <div v-if="ErrorMessage" name="provinceDestination" class="text-danger">{{ errors.provinceDestination }}</div>
                    </div>
                </div> -->

                <!-- Provinsi Tujuan -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Provinsi Tujuan</label>

                        <Field name="provinceDestination">
                            <input type="text" class="form-control" v-model="searchProvinceDestination"
                                placeholder="Ketik Provinsi Tujuan" @focus="showDropdownDestination = true"
                                @blur="hideDropdownWithDelay" autocomplete="off" />
                        </Field>
                        <ErrorMessage name="provinceDestination" class="text-danger small" />

                        <ul v-if="showDropdownDestination && filteredProvincesDestination.length"
                            class="list-group position-absolute w-100" style="z-index: 1000;">
                            <li v-for="prov in filteredProvincesDestination" :key="prov.id"
                                class="list-group-item list-group-item-action"
                                @mousedown.prevent="selectProvinceDestination(prov)">
                                {{ prov.name }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Kota Tujuan -->
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Kota Tujuan</label>
                        <input type="text" v-model="searchCityDestination" class="form-control"
                            @focus="showDropdownCityDestination = true"
                            @blur="setTimeout(() => showDropdownCityDestination = false, 200)"
                            placeholder="Ketik Kota Tujuan" />

                        <ul v-if="showDropdownCityDestination" class="dropdown-menu show w-100"
                            style="max-height: 200px; overflow-y: auto;">
                            <li v-for="(city, index) in filteredCitiesDestination" :key="index"
                                @click="searchCityDestination = city.name; showDropdownCityDestination = false"
                                @mousedown.prevent="selectCityDestination(city)"
                                class="dropdown-item">
                                {{ city.name }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Kota Tujuan</label>

                        <Field name="citiesDestination" v-model="searchCityDestination">
                            <input type="text" class="form-control" v-model="searchCityDestination"
                                placeholder="Ketik Kota Tujuan" @focus="showCityDropdownDestination = true"
                                @blur="hideCityDropdownWithDelay" autocomplete="off" />
                        </Field>
                        <ErrorMessage name="citiesDestination" class="text-danger small" />

                        <ul v-if="showCityDropdownDestination && filteredCitiesDestination.length"
                            class="list-group position-absolute w-100" style="z-index: 1000;">
                            <li v-for="city in filteredCitiesDestination" :key="city.id"
                                class="list-group-item list-group-item-action"
                                @mousedown.prevent="selectCityDestination(city)">
                                {{ city.name }}
                            </li>
                        </ul>
                    </div>
                </div> -->

                <!-- Kecamatan Tujuan -->
                 <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Kecamatan Tujuan</label>
                        <input type="text" v-model="searchDistrictDestination" class="form-control"
                            @focus="showDropdownDistrictDestination = true"
                            @blur="setTimeout(() => showDropdownDistrictDestination = false, 200)"
                            placeholder="Ketik Kecamatan Asal" />

                        <ul v-if="showDropdownDistrictDestination" class="dropdown-menu show w-100"
                            style="max-height: 200px; overflow-y: auto;">
                            <li v-for="(district, index) in filteredDistrictsDestination" :key="index"
                                @click="searchDistrictDestination = district.name; showDropdownDistrictDestination = false; selectDistrictDestination(district)"
                                class="dropdown-item">
                                {{ district.name }}    
                            </li>
                        </ul>
                    </div>
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
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Ekspedisi</label>
                        <Field as="select" v-model="selectedCourier" class="form-control" name="ekspedisi">
                            <option value="">-- Pilih Ekspedisi --</option>
                            <option v-for="c in couriers" :key="c.code" :value="c.code">{{ c.name }}</option>
                        </Field as="select">
                        <ErrorMessage name="ekspedisi" class="text-danger small" />
                        <!-- <div v-if="errors.kurir" class="text-danger">{{ errors.kurir }}</div> -->
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label required fw-bold">Berat Barang (Kg)</label>
                        <Field type="number" v-model="berat_barang" class="form-control" placeholder="Contoh: 0.5"
                            min="0.1" step="0.1" name="berat_barang" />
                        <ErrorMessage name="berat_barang" class="text-danger small" />
                        <!-- <div v-if="errors.berat_barang" class="text-danger">{{ errors.berat_barang }}</div> -->
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label for="jenis_layanan" class="form-label required fw-bold">Jenis Layanan</label>
                        <Field as="select" id="jenis_layanan" name="jenis_layanan" class="form-select"
                            v-model="selectedService" @change="getSelectedCost" :disabled="services.length === 0">
                            <option value="">
                                {{ services.length === 0 ? 'Tidak ada jenis layanan tersedia' : 'Pili jenis layanan' }}
                            </option>
                            <option v-for="service in services" :key="service.service" :value="service.service">
                                {{ service.service }} - Rp{{ Number(service.cost).toLocaleString() }} { {{ service.etd
                                }}
                                Hari }
                            </option>
                        </Field>
                        <ErrorMessage name="jenis_layanan" class="text-danger small" />
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold">Biaya (Rp)</label>
                        <input type="text" name="biaya" class="form-control"
                            :value="services.length > 0 && biaya ? biaya.toLocaleString('id-ID') : '-'" readonly />
                    </div>
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

<style>
/* Custom CSS untuk SweetAlert yang lebih lebar */
.swal-wide {
    width: 600px !important;
}
</style>