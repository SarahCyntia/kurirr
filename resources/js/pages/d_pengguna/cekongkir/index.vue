<template>
  <div class="card mt-30">
    <div class="container-fluid mt-5">
      <div class="row">
        <!-- ORIGIN -->
        <div class="col-md-4">
          <div class="card shadow-lg">
            <div class="card-body p-4">
              <!-- <h3>ORIGIN</h3> -->
              <h5 class="card-title fs-4">🛫 Asal</h5>
              <hr />
              <div class="form-group mb-3">
                <label>PROVINSI ASAL</label>
                <select v-model="provinceOrigin" class="form-control" @change="fetchCities('origin')">
                  <option value="0">-- pilih provinsi asal --</option>
                  <option v-for="(name, id) in provinces" :key="id" :value="id">
                    {{ name }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>KOTA ASAL</label>
                <select v-model="cityOrigin" class="form-control">
                  <option value="">-- pilih kota asal --</option>
                  <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">
                    {{ name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- DESTINATION -->
        <div class="col-md-4">
          <div class="card shadow-lg">
            <div class="card-body p-4">
              <!-- <h3>DESTINATION</h3> -->
              <h5 class="card-title fs-4">🛬 Tujuan</h5>
              <hr />
              <div class="form-group mb-3">
                <label class="fw-semibold">PROVINSI TUJUAN</label>
                <select v-model="provinceDestination" class="form-control" @change="fetchCities('destination')">
                  <option value="0">-- pilih provinsi tujuan --</option>
                  <option v-for="(name, id) in provinces" :key="id" :value="id">
                    {{ name }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>KOTA TUJUAN</label>
                <select v-model="cityDestination" class="form-control">
                  <option value="">-- pilih kota tujuan --</option>
                  <option v-for="(name, id) in citiesDestination" :key="id" :value="id">
                    {{ name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- COURIER & WEIGHT -->
        <div class="col-md-4"> <!-- dari 3 ke 4 biar lebih lebar -->
          <div class="card shadow-lg" >
            <div class="card-body p-4"> <!-- padding lebih besar -->
              <h5 class="card-title fs-4">🚚 Kurir</h5>
              <hr />
              <div class="form-group mb-3">
                <label class="fw-semibold">Kurir</label>
                <select v-model="courier" class="form-control">
                  <option value="0">-- pilih kurir --</option>
                  <option value="jne">JNE</option>
                  <option value="pos">POS</option>
                  <option value="tiki">TIKI</option>
                </select>
              </div>
              <div class="form-group">
                <label>Berat (gram)</label>
                <input type="number" v-model.number="weight" class="form-control" placeholder="Masukkan Berat (GRAM)" />
              </div>
            </div>
          </div>
        </div>

        <!-- <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">🚚 Kurir</h5>
            <h3>🚚 KURIR</h3>
            <hr />
            <div class="form-group">
              <label>KURIR</label>
              <select v-model="courier" class="form-control">
                <option value="0">-- pilih kurir --</option>
                <option value="jne">JNE</option>
                <option value="pos">POS</option>
                <option value="tiki">TIKI</option>
              </select>
            </div>
            <div class="form-group">
              <label>BERAT (GRAM)</label>
              <input type="number" v-model.number="weight" class="form-control" placeholder="Masukkan Berat (GRAM)" />
            </div>
          </div>
        </div>
    </div> -->
        <div class="col-12">
          <button class="btn btn-danger w-100 mt-4" @click="checkOngkir" :disabled="isProcessing">
            💵 CEK ONGKOS KIRIM
          </button>
        </div>
      </div>

      <!-- BUTTON -->

      <!-- HASIL -->
      <div class="row mt-3" v-if="ongkirResults.length > 0">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <ul class="list-group">
                <li class="list-group-item" v-for="(cost, index) in ongkirResults" :key="index">
                  <strong>{{ cost.service }}</strong> - Rp. {{ formatRupiah(cost.cost[0].value) }} ({{ cost.cost[0].etd
                  }} hari)
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Ongkir",
  data() {
    return {
      provinces: {},
      provinceOrigin: "0",
      cityOrigin: "",
      citiesOrigin: {},
      provinceDestination: "0",
      cityDestination: "",
      citiesDestination: {},
      courier: "0",
      weight: null,
      ongkirResults: [],
      isProcessing: false,
    };
  },
  created() {
    this.fetchProvinces();
  },
  methods: {
    fetchProvinces() {
      axios.get("/provinces").then((res) => {
        this.provinces = res.data;
      });
    },
    fetchCities(type) {
      let provinceId = type === "origin" ? this.provinceOrigin : this.provinceDestination;
      if (provinceId !== "0") {
        axios.get(`/cities/${provinceId}`).then((res) => {
          if (type === "origin") {
            this.citiesOrigin = res.data;
            this.cityOrigin = "";
          } else {
            this.citiesDestination = res.data;
            this.cityDestination = "";
          }
        });
      }
    },
    checkOngkir() {
      if (
        !this.cityOrigin ||
        !this.cityDestination ||
        this.courier === "0" ||
        !this.weight
      ) {
        alert("Semua field harus diisi!");
        return;
      }

      this.isProcessing = true;

      axios
        .post("/ongkir", {
          city_origin: this.cityOrigin,
          city_destination: this.cityDestination,
          courier: this.courier,
          weight: this.weight,
        })
        .then((res) => {
          this.ongkirResults = res.data[0].costs || [];
          this.isProcessing = false;
        })
        .catch((err) => {
          console.error(err);
          this.isProcessing = false;
        });
    },
    formatRupiah(value) {
      return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(value);
    },
  },
};
</script>

<style scoped>
/* Tambahkan gaya kustom jika perlu */
.container-fluid {
  font-family: 'Poppins', sans-serif;
  color: #000;
  /* font warna hitam */
}

.card {
  /* border: none; */
  border: 1px solid #f5717c;
  padding: 3rem;
  box-shadow: 0 4px 12px rgba(243, 104, 180, 0.2);
  border-radius: 12px;
  margin-bottom: 2.5rem;
  /* background: #fff0f6; */
  background-color: #fbd1df;
  border: 1px solid #f5717c;

}

.card-title {
  font-weight: 600;
  color: #000000;
}

label {
  font-weight: 500;
  color: #000;
  /* label tetap hitam */
}

.form-control {
  border: 1px solid #f7c0db;
  border-radius: 8px;
  background-color: #fff;
  color: #000;
}

.form-control:focus {
  border-color: #e83e8c;
  box-shadow: 0 0 0 2px rgba(232, 62, 140, 0.25);
}

.btn-danger {
  background-color: #e83e8c;
  border: none;
  font-weight: 600;
  padding: 12px;
  border-radius: 8px;
  /* width: 50%; */
  transition: background-color 0.3s;
  /* box-align: center; */
}

.btn-danger:hover {
  background-color: #c71f6f;
}

.list-group-item {
  background-color: #fff;
  border: 1px solid #f7c0db;
  color: #000;
  font-weight: 500;
  border-radius: 8px;
  margin-bottom: 8px;
}

.list-group-item strong {
  color: #e83e8c;
}

/* Tengahin konten form */
.row {
  justify-content: center;
}
</style>