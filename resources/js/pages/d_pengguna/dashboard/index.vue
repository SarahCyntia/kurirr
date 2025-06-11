<template>
    <section class="content">
      <div class="text">
        <h1>
          Halo <br />
          <span>Cepat & Terpercaya</span>
        </h1>
        <!-- <h1>
          Layanan Pengiriman <br />
          <span>Cepat & Terpercaya</span>
        </h1> -->
        <p class="par">
          Kami siap mengantarkan paket Anda ke seluruh Indonesia dengan <strong>aman</strong> dan <strong>tepat waktu</strong>.
        </p>
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