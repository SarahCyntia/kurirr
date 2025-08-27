<!-- <template>
  <section class="content">
    <div class="text">
      <h1>Halo 👋</h1>

      Guest bisa lacak paket
      <div class="guest-dashboard" v-if="!isLoggedIn">
        <h2>Lacak Paket Anda</h2>
        <p>Masukkan nomor resi untuk cek status pengiriman</p>

        <div class="tracking-box">
          <input
            v-model="resi"
            type="text"
            placeholder="Masukkan Nomor Resi"
            class="tracking-input"
          />
          <button class="cn" @click="trackResi">Lacak</button>
        </div>

        <div v-if="trackingResult" class="tracking-result">
          <h3>Status:</h3>
          <p>{{ trackingResult }}</p>
        </div>

        <hr />
        <p class="note">Untuk memesan kurir, silakan <a href="/login">Login</a></p>
      </div>

      Sudah Login & fitur kurir
      <div v-else>
        <div v-if="!kurir" class="kurir-choose">
          <h2>Pilih Kurir</h2>
          <button class="cn" @click="kurir = 'jnt'">JNT</button>
          <button class="cn" @click="kurir = 'internal'">Kurir Internal</button>
        </div>

        <div v-else class="kurir-card">
          <div class="kurir-header">
            <img
              v-if="kurir === 'jnt'"
              src="/images/jnt-logo.png"
              alt="JNT"
              class="kurir-logo"
            />
            <img
              v-else-if="kurir === 'internal'"
              src="/images/kurir-internal.png"
              alt="Kurir Internal"
              class="kurir-logo"
            />
            <h2>{{ kurirLabel }}</h2>
          </div>

          <div class="kurir-status">
            <span class="badge">📦 Sedang Diproses</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template> -->

<!-- <script setup lang="ts">
import { ref, computed } from "vue"

const isLoggedIn = ref<boolean>(false) // simulasi login
const kurir = ref<string>("")
const resi = ref<string>("")
const trackingResult = ref<string>("")

const kurirLabel = computed(() => {
  if (kurir.value === "jnt") return "JNT Express"
  if (kurir.value === "internal") return "Kurir Internal"
  return ""
})

const trackResi = () => {
  if (!resi.value) {
    trackingResult.value = "Masukkan nomor resi terlebih dahulu."
  } else {
    // simulasi hasil tracking
    trackingResult.value = `Paket dengan resi ${resi.value} sedang dalam perjalanan 🚚`
  }
}
</script> -->

<!-- <style scoped>
.text h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 1rem;
}

.guest-dashboard {
  background: #fff3f5;
  padding: 1.5rem;
  border-radius: 16px;
  margin-bottom: 2rem;
}

.tracking-box {
  display: flex;
  gap: 0.5rem;
  margin: 1rem 0;
}

.tracking-input {
  flex: 1;
  padding: 0.7rem;
  border-radius: 10px;
  border: 1px solid #ccc;
}

.tracking-result {
  margin-top: 1rem;
  background: #fff;
  padding: 1rem;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.note {
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #666;
}

.kurir-card {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
}

.kurir-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.kurir-logo {
  width: 60px;
  height: auto;
}

.badge {
  display: inline-block;
  background: #ff4d6d;
  color: #fff;
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.cn {
  background: #ff4d6d;
  padding: 0.7rem 1.5rem;
  border: none;
  border-radius: 25px;
  font-weight: 600;
  font-size: 1rem;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s;
}
.cn:hover {
  background: #e8435f;
}
</style> -->



<template>
    <section class="content">
      <div class="text">
        <h1>
          Halo <br />
          <!-- <span>Cepat & Terpercaya</span> -->
        </h1>
        <!-- <h1>
          Layanan Pengiriman <br />
          <span>Cepat & Terpercaya</span>
        </h1> -->
        <!-- <p class="par">
          Kami siap mengantarkan paket Anda ke seluruh Indonesia dengan <strong>aman</strong> dan <strong>tepat waktu</strong>.
        </p> -->
        <!-- <button class="cn">
          <a href="/halaman/kirimpaket.html">Kirim Paket</a>
        </button> -->
      </div>
    </section>
</template>
<style scoped>

body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
}

.text h1 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #333;
}
.text span {
  color: #ff4d6d;
}


.cn {
  background: #ff4d6d;
  padding: 0.8rem 1.6rem;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(255, 77, 109, 0.4);
}
.cn:hover {
  transform: translateY(-2px);
  background-color: #e8435f;
}

.cn a {
  color: white;
  text-decoration: none;
}

@media (max-width: 768px) {
  .nav {
    flex-direction: column;
    gap: 1rem;
  }

  .menu ul {
    flex-direction: column;
    gap: 0.5rem;
  }

  .text h1 {
    font-size: 2rem;
  }

  .par {
    font-size: 1rem;
  }
}
</style>